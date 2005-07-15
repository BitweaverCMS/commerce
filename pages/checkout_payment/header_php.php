<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: header_php.php,v 1.3 2005/07/15 19:14:59 spiderr Exp $
//
// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() <= 0) {
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
  }

// if the customer is not logged on, redirect them to the login page
  if (!$_SESSION['customer_id'] ) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'));
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!$_SESSION['shipping']) {
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && $_SESSION['cartID']) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// Stock Check
  if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (zen_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }

// if no billing destination address was selected, use the customers own address as default
  if (!$_SESSION['billto']) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
  } else {
// verify the selected billing address
    $check_address_query = "select count(*) as total from " . TABLE_ADDRESS_BOOK . "
                            where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                            and address_book_id = '" . (int)$_SESSION['billto'] . "'";

    $check_address = $db->Execute($check_address_query);

    if ($check_address->fields['total'] != '1') {
      $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
      $_SESSION['payment'] = '';
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;
  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;

	// if the no billing address, try to get one by default
	if( !$gBitCustomer->isValidAddress( $order->billing ) ) {
		if( $gBitCustomer->isValidAddress( $order->delivery ) ) {
			$order->billing = $order->delivery;
			$_SESSION['billto'] = $_SESSION['sendto'];
		} elseif( $defaultAddressId = $gBitCustomer->getDefaultAddress() ) {
			$order->billing = $defaultAddressId;
			$_SESSION['billto'] = $defaultAddressId;
		} else {
			$_SESSION['navigation']->set_snapshot();
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'));
		}
	}

//  $_SESSION['comments'] = '';
    $comments = $_SESSION['comments'];

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

// Load the selected shipping module(needed to calculate tax correctly)
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

  require(DIR_WS_MODULES . 'require_languages.php');

  if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
    $messageStack->add('checkout_payment', $error['error'], 'error');
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>

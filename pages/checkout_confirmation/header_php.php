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
// $Id: header_php.php,v 1.7 2005/11/30 07:17:24 spiderr Exp $
//
// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() <= 0) {
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
  }

// if the customer is not logged on, redirect them to the login page
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    zen_redirect(FILENAME_LOGIN);
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && $_SESSION['cartID']) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!$_SESSION['shipping']) {
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (isset($_POST['payment'])) $_SESSION['payment'] = $_POST['payment'];
  $_SESSION['comments'] = zen_db_prepare_input($_POST['comments']);

  if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
    if (!isset($_POST['conditions']) || ($_POST['conditions'] != '1')) {
      $messageStack->add_session('checkout_payment', ERROR_CONDITIONS_NOT_ACCEPTED, 'error');
    }
  }
//echo $messageStack->size('checkout_payment');

  require(DIR_FS_CLASSES . 'order.php');
  $order = new order;

  require(DIR_FS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;
  $order_total_modules->collect_posts();
  $order_total_modules->pre_confirmation_check();

// load the selected payment module
  require(DIR_FS_CLASSES . 'payment.php');

  if ($credit_covers) {
    unset($_SESSION['payment']);
    $_SESSION['payment'] = '';
  }


  $payment_modules = new payment($_SESSION['payment']);
  $payment_modules->update_status();
  if ( (is_array($payment_modules->modules)) && (sizeof($payment_modules->modules) > 1) && (empty($$_SESSION['payment']) || !is_object($$_SESSION['payment'])) && ( empty( $credit_covers ) ) ) {
    $messageStack->add_session('checkout_payment', ERROR_NO_PAYMENT_MODULE_SELECTED, 'error');
  }

  if ($messageStack->size('checkout_payment') > 0) {
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
//echo $messageStack->size('checkout_payment');
//die('here');

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

// load the selected shipping module
  require(DIR_FS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (zen_check_stock($order->products[$i]['id'], $order->products[$i]['quantity'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
    }
  }

// update customers_referral with $_SESSION['gv_id']
  if ($_SESSION['cc_id']) {
    $discount_coupon_query = "select `coupon_code`
                 from " . TABLE_COUPONS . "
                 where `coupon_id` = '" . $_SESSION['cc_id'] . "'";

    $discount_coupon = $db->Execute($discount_coupon_query);

    $customers_referral_query = "select `customers_referral` from " . TABLE_CUSTOMERS . " where `customers_id`='" . $_SESSION['customer_id'] . "'";
    $customers_referral = $db->Execute($customers_referral_query);

// only use discount coupon if set by coupon
    if ($customers_referral->fields['customers_referral'] == '' and CUSTOMERS_REFERRAL_STATUS == 1) {
      $db->Execute("update " . TABLE_CUSTOMERS . " set `customers_referral` ='" . $discount_coupon->fields['coupon_code'] . "' where `customers_id` ='" . $_SESSION['customer_id'] . "'");
    } else {
      // do not update referral was added before
    }
  }

  require(DIR_FS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>

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
// $Id: header_php.php,v 1.4 2005/07/15 09:24:09 spiderr Exp $
//
// if the customer is not logged on, redirect them to the login page
// if there is nothing in the customers cart, redirect them to the shopping cart page
	if ($_SESSION['cart']->count_contents() <= 0) {
		zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
	}

	require(DIR_WS_MODULES . 'require_languages.php');

	$errors = array();
	$process = false;
	if (isset($_POST['action']) && ($_POST['action'] == 'submit')) {
		if( !$gBitUser->isRegistered() ) {
			$gBitCustomer->register( $_REQUEST );
		}

	// process a new billing address
		if( empty( $_REQUEST['address'] ) || (zen_not_null($_POST['firstname']) && zen_not_null($_POST['lastname']) && zen_not_null($_POST['street_address'])) ) {
			$process = true;
			if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
				$_SESSION['billto'] = $_REQUEST['address'];
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
			} else {
				$smarty->assign( 'address', $_REQUEST['address_store'] );
				$errors = array_merge( $errors, $gBitCustomer->mErrors );
			}
		}
	// process the selected billing destination
	} elseif (isset($_POST['address'])) {
		$reset_payment = false;
		if ($_SESSION['billto']) {
			if ($_SESSION['billto'] != $_POST['address']) {
			if ($_SESSION['payment']) {
				$reset_payment = true;
			}
			}
		}
		$_SESSION['billto'] = $_POST['address'];

		$check_address_query = "select count(*) as total from " . TABLE_ADDRESS_BOOK . "
								where customers_id = '" . $_SESSION['customer_id'] . "'
								and address_book_id = '" . $_SESSION['billto'] . "'";

		$check_address = $db->Execute($check_address_query);

		if ($check_address->fields['total'] == '1') {
			if ($reset_payment == true) $_SESSION['payment'] = '';
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
		} else {
			$_SESSION['billto'] = '';
		}
	// no addresses to select from - customer decided to keep the current assigned address
	} elseif( !empty( $_SESSION['customer_default_address_id'] ) ) {
		$_SESSION['billto'] = $_SESSION['customer_default_address_id'];
		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
	}

	// if no billing destination address was selected, use their own address as default
	if (!$_SESSION['billto']) {
		$_SESSION['billto'] = $_SESSION['customer_default_address_id'];
	}

	$smarty->assign_by_ref( 'errors', $errors );

	$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
	$breadcrumb->add(NAVBAR_TITLE_2);

	$addresses_count = zen_count_customer_address_book_entries();
?>

<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($gBitCustomer->mCart->count_contents() <= 0) {
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

// if the customer is not logged on, redirect them to the login page
if (!$_SESSION['customer_id']) {
	$_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
	zen_redirect(FILENAME_LOGIN);
}

// Stock Check and more....
if( !$gBitCustomer->mCart->verifyCheckout() ) {
	$messageStack->add('header', 'Please update your order ...', 'error');
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if( empty( $_SESSION['shipping'] )  && !$gBitCustomer->mCart->get_content_type() == 'virtual' ) {
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

if (isset($_POST['payment'])) {
	$_SESSION['payment'] = $_POST['payment'];
}
if( !empty( $_POST['comments'] ) ) {
	$_SESSION['comments'] = zen_db_prepare_input($_POST['comments']);
}

if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
	if (!isset($_POST['conditions']) || ($_POST['conditions'] != '1')) {
		$messageStack->add_session('checkout_payment', ERROR_CONDITIONS_NOT_ACCEPTED, 'error');
	}
}

require(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
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
require( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
$shipping_modules = new CommerceShipping($_SESSION['shipping']);


// update customers_referral with $_SESSION['gv_id']
if ($_SESSION['cc_id']) {
	$customersReferral = $gBitDb->getOne( "SELECT `customers_referral` FROM " . TABLE_CUSTOMERS . " WHERE `customers_id` = ?", array( $_SESSION['customer_id'] ) );
	// only use discount coupon if set by coupon
	if ($customersReferral == '' and CUSTOMERS_REFERRAL_STATUS == 1) {
		$discountCoupon = $gBitDb->getOne( "SELECT `coupon_code` FROM " . TABLE_COUPONS . " WHERE `coupon_id` = ?", array( $_SESSION['cc_id'] ) );
		$gBitDb->query( "UPDATE " . TABLE_CUSTOMERS . " SET `customers_referral` = ? WHERE `customers_id` = ?", array( $discountCoupon, $_SESSION['customer_id'] ) );
	} else {
		// do not update referral was added before
	}
}

require_once(DIR_FS_MODULES . 'require_languages.php');
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

$gBitSmarty->assign( 'order', $order );

if ( $gCommerceSystem->getConfig( 'MODULE_ORDER_TOTAL_INSTALLED' ) ) {
	$order_totals = $order_total_modules->process();
	$gBitSmarty->assign( 'orderTotalsModules', $order_total_modules );
}

if (is_array($payment_modules->modules)) {
	$gBitSmarty->assign( 'paymentModules', $payment_modules );
	$gBitSmarty->assign( 'paymentConfirmation', $payment_modules->confirmation() );
}
	
$gBitSmarty->assign( 'formActionUrl', (isset($$_SESSION['payment']->form_action_url) ? $$_SESSION['payment']->form_action_url : zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL') ) );

$gBitSmarty->assign('GLOBALS',$GLOBALS);

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_confirmation.tpl' );

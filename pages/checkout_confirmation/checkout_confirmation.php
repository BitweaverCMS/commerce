<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

if ($gBitCustomer->mCart->count_contents() <= 0) {
	// if there is nothing in the customers cart, redirect them to the shopping cart page
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
} elseif (!$_SESSION['customer_id']) {
	// if the customer is not logged on, redirect them to the login page
	$_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
	zen_redirect(FILENAME_LOGIN);
} elseif( empty( $_SESSION['shipping_method'] ) && ($gBitCustomer->mCart->get_content_type() != 'virtual') ) {
	// if no shipping method has been selected, redirect the customer to the shipping method selection page
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
} elseif( !$gBitCustomer->mCart->verifyCheckout() ) {
	// Stock Check and more....
	$messageStack->add('header', 'Please update your order ...', 'error');
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

if( isset( $_POST['payment_method'] ) ) {
	$_SESSION['payment_method'] = $_POST['payment_method'];
}

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'page_checkout_parameters_inc.php' );

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php' );
global $gBitCustomer;
$order = CommerceOrder::orderFromCart( $gBitCustomer->mCart, $_SESSION );

if( $errors = $order->otCollectPosts( $_REQUEST, $_SESSION ) ) {
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'ot_error_encode=' . urlencode(serialize($errors)), 'SSL'));
}

// load the selected payment module
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php' );

$paymentManager = new CommercePaymentManager($_SESSION['payment_method']);
$paymentManager->update_status( $_REQUEST );

if( $order->hasPaymentDue( $_SESSION ) ) {
	if( (empty( $_SESSION['payment_method'] ) || !$paymentManager->isModuleActive( $_SESSION['payment_method'] ) ) ) {
		$messageStack->add_session('checkout_payment', ERROR_NO_PAYMENT_MODULE_SELECTED, 'error');
		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
	}

	if( !$paymentManager->verifyPayment( $order, $_REQUEST, $_SESSION ) ) {
		$messageStack->add_session('checkout_payment', current( $paymentManager->mErrors ), 'error');
		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, NULL, 'SSL', true, false));
	}
}

// load the selected shipping module
if( !empty( $_SESSION['shipping'] ) ) {
	require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShipping.php');
	$shipping_modules = new CommerceShipping($_SESSION['shipping']);
}


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

$order->otProcess( $_REQUEST, $_SESSION );

$gBitSmarty->assign( 'formActionUrl', $paymentManager->get_form_action_url() );
$gBitSmarty->assign( 'paymentModules', $paymentManager );
$gBitSmarty->assign( 'paymentConfirmation', $paymentManager->confirmation( $_SESSION ) );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_confirmation.tpl' );

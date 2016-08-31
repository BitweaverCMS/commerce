<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id$
//

require_once(DIR_FS_MODULES . 'require_languages.php');

// if the customer is not logged on, redirect them to the time out page
if (!$_SESSION['customer_id']) {
	zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// confirm where link came from
if (!strstr($_SERVER['HTTP_REFERER'], FILENAME_CHECKOUT_CONFIRMATION)) {
//		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT,'','SSL'));
}

// load selected payment module
require( BITCOMMERCE_PKG_PATH . 'classes/CommercePaymentManager.php' );
$paymentManager = new CommercePaymentManager($_SESSION['payment']);

// load the selected shipping module
require( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
$shipping_modules = new CommerceShipping($_SESSION['shipping']);

require(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
$order = new order;

require(DIR_FS_CLASSES . 'order_total.php');
$order_total_modules = new order_total;
$order_totals = $order_total_modules->process( $_REQUEST );

$gBitDb->mDb->StartTrans();
// load the before_process function from the payment modules
if( $order->hasPaymentDue() && !$paymentManager->processPayment( $_REQUEST, $order ) ) {
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, NULL, 'SSL', true, false));
}

$insert_id = $order->create($order_totals, 2);
$order->create_add_products($insert_id);

$paymentManager->after_order_create($insert_id);
$order->send_order_email($insert_id);

$gBitDb->mDb->completeTrans();

$paymentManager->after_process();

$gBitCustomer->mCart->reset(true);

// unregister session variables used during checkout
foreach( array( 'sendto', 'billto', 'shipping', 'payment', 'comments' ) as $key ) {
	if( isset( $_SESSION[$key] ) ) {
		unset( $_SESSION[$key] );
	}
}

$order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM

zen_redirect(zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));


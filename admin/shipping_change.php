<?php
// +----------------------------------------------------------------------+
// | bitcommerce Open Source E-commerce                                   |
// | Copyright (c) 2009 bitcommerce.org                                   |
// | http://www.bitcommerce.org/                                          |
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id$
require('includes/application_top.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShipping.php');

$order->calculate();

// get all available shipping quotes
if( !empty( $_REQUEST['change_shipping'] ) && !empty( $_REQUEST['shipping_method'] ) ) {
	list($module, $method) = explode('_', $_REQUEST['shipping_method'], 2);
	$fulfillmentModules = $gCommerceSystem->scanModules( 'fulfillment' );
	global $gCommerceShipping;
	if( $shipModule = $gCommerceShipping->getShippingModule( $module ) ) {
		$quote = $gCommerceShipping->quote( $order, $method, $module);
		$order->changeShipping( current( $quote ), $_REQUEST );
		zen_redirect( $_SERVER['HTTP_REFERER'] );
	}
} else {
	$gBitSmarty->assign( 'quotes', $gCommerceShipping->quote( $order ) );
	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_shipping_change_ajax.tpl' );
}


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
//  $Id: packingslip.php,v 1.8 2006/09/05 05:25:33 spiderr Exp $
//
	require('includes/application_top.php');
	require_once( BITCOMMERCE_PKG_PATH.'includes/classes/order.php' );

	$currencies = new currencies();

	$gBitOrder = new order( $_REQUEST['oID'] );
	$gBitSmarty->assign_by_ref( 'gBitOrder', $gBitOrder );

	$gBitSystem->setBrowserTitle( tra( 'Order #{$gBitOrder->mOrdersId} Packing Slip Order #' ).$gBitOrder->getField( 'orders_id' ) );

	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_packing_slip.tpl' );

?>

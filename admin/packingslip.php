<?php
// +----------------------------------------------------------------------+
// | bitcommerce Open Source E-commerce                                   |
// | Copyright (c) 2009 bitcommerce.org                                   |
// | http://www.bitcommerce.org/                                          |
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id$

require('includes/application_top.php');
require_once( BITCOMMERCE_PKG_PATH.'includes/classes/order.php' );

$currencies = new currencies();

$gBitOrder = new order( $_REQUEST['oID'] );
$gBitSmarty->assign_by_ref( 'gBitOrder', $gBitOrder );

$gBitSystem->setBrowserTitle( tra( 'Order #' ).' '.$gBitOrder->mOrdersId.' '.tra( 'Packing Slip' ) );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_packing_slip.tpl' );

?>

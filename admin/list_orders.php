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
//  $Id$
//
$version_check_index=true;
require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php' );

define( 'HEADING_TITLE', 'List Orders' );

$listHash = array( 'max_records' => '250', 'recent_comment' => TRUE );
if( !empty( $_REQUEST['orders_status_comparison'] ) ) {
	$listHash['orders_status_comparison'] = $_REQUEST['orders_status_comparison'];
	$_SESSION['orders_status_comparison'] = $_REQUEST['orders_status_comparison'];
} elseif( !empty( $_SESSION['orders_status_comparison'] ) && !empty( $_REQUEST['list_filter'] ) ) {
	unset( $_SESSION['orders_status_comparison'] );
} elseif( !empty( $_SESSION['orders_status_comparison'] ) ) {
	$listHash['orders_status_comparison'] = $_SESSION['orders_status_comparison'];
} 

if( @BitBase::verifyId( $_REQUEST['products_options_values_id'] ) ) {
	$listHash['products_options_values_id'] = $_REQUEST['products_options_values_id'];
}

if( !empty( $_REQUEST['products_type'] ) ) {
	$listHash['products_type'] = $_REQUEST['products_type'];
}
if( !empty( $_REQUEST['period'] ) ) {
	$listHash['period'] = $_REQUEST['period'];
}
if( !empty( $_REQUEST['timeframe'] ) ) {
	$listHash['timeframe'] = $_REQUEST['timeframe'];
}

if( @BitBase::verifyId( $_REQUEST['orders_status_id'] ) ) {
	$listHash['orders_status_id'] = $_REQUEST['orders_status_id'];
	$_SESSION['orders_status_id'] = $_REQUEST['orders_status_id'];
}

if( !empty( $_REQUEST['search'] ) ) {
	$listHash['search'] = $_REQUEST['search'];
}
if( @BitBase::verifyId( $_REQUEST['user_id'] ) ) {
	$listHash['user_id'] = $_REQUEST['user_id'];
}

$listHash['orders_products'] = TRUE;

$orders = order::getList( $listHash );
$gBitSmarty->assign_by_ref( 'listOrders', $orders );
$statuses['all'] = 'All';

foreach( commerce_get_statuses( TRUE ) as $statusId=>$statusName ) {
	$statuses[$statusId] = $statusName;
}
$gBitSmarty->assign( 'commerceStatuses', $statuses );

$types[''] = 'All';
$productTypes = CommerceProduct::getTypes();
foreach( $productTypes as $typeId=>$typeHash ) {
	$types[$typeId] = $typeHash['type_name'].' &raquo; '.$typeHash['type_class'];
}
$gBitSmarty->assign_by_ref( 'commerceProductTypes', $types );

$gBitSmarty->display( 'bitpackage:bitcommerce/admin_list_orders.tpl' );

require('includes/application_bottom.php');
require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 


<?php
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2010 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceStatistics.php' );

if( count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$_REQUEST['max_records']  = -1;
if( !empty( $_REQUEST['period'] ) ) {
	$listHash['period'] = $_REQUEST['period'];
}

$stats = new CommerceStatistics();

$retainedCustomers = $stats->getRetainedCustomers( $_REQUEST );
$gBitSmarty->assign_by_ref( 'retainedCustomers', $retainedCustomers );
$abandonedCustomers = $stats->getAbandonedCustomers( $_REQUEST );
$gBitSmarty->assign_by_ref( 'abandonedCustomers', $abandonedCustomers );

$averageRetention = 100 * $retainedCustomers['totals']['customers'] / ($retainedCustomers['totals']['customers'] + $abandonedCustomers['totals']['customers']);
$gBitSmarty->assign( 'averageRetention', $averageRetention );
$gBitSystem->display( 'bitpackage:bitcommerce/admin_stats_customers.tpl', 'Customer Statistics' , array( 'display_mode' => 'admin' ));

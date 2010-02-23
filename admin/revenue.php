<?php
//
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id: revenue.php,v 1.4 2010/02/23 20:12:20 spiderr Exp $
//

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );

// $gBitSmarty->assign( 'loadAjax', 'mochikit' );
// $gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$listHash['max_records']  = -1;
if( $_REQUEST['period'] ) {
	$listHash['period'] = $_REQUEST['period'];
}

$stats = new CommerceStatistics();
if( !empty( $_REQUEST['timeframe'] ) ) {
	$gBitSmarty->assign_by_ref( 'statsByType', $stats->getRevenueByType( $_REQUEST ) );
	$gBitSmarty->assign_by_ref( 'statsByOption', $stats->getRevenueByOption( $_REQUEST ) );
	$gBitSmarty->assign_by_ref( 'statsCustomers', $stats->getCustomerConversions( $_REQUEST ) );
	$gBitSmarty->assign_by_ref( 'valuableInterests' , $stats->getMostValuableInterests( $_REQUEST ) );
	$gBitSmarty->assign_by_ref( 'valuableCustomers' , $stats->getMostValuableCustomers( $_REQUEST ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_timeframe.tpl', 'Revenue' , array( 'display_mode' => 'admin' ));
} else {
	$gBitSmarty->assign_by_ref( 'statsCustomers', $stats->getCustomerConversions( array( 'period' => $_REQUEST['period'] ) ) );
	$gBitSmarty->assign_by_ref( 'stats', $stats->getAggregateRevenue( $listHash ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue.tpl', 'Revenue' , array( 'display_mode' => 'admin' ));
}
?>


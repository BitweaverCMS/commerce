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
//  $Id$
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
if( !empty( $_REQUEST['interests_id'] ) ) {
	$interests = $gBitCustomer->getInterests();
	$gBitSmarty->assign_by_ref( 'interestsList', $interests );
	$gBitSmarty->assign( 'interestsName', $interests[$_REQUEST['interests_id']] );
	$_REQUEST['orders_products'] = TRUE;
	$_REQUEST['orders_status_comparison'] = '>';
	$_REQUEST['orders_status_id'] = 0;
	$gBitSmarty->assign_by_ref( 'listOrders', order::getList( $_REQUEST ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_interest.tpl', tra( 'Revenue By Interest' ).' '.$interests[$_REQUEST['interests_id']].' : '.$_REQUEST['timeframe'] , array( 'display_mode' => 'admin' ) );
} elseif( !empty( $_REQUEST['timeframe'] ) ) {
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

function list_customers_interests( $pCustomersId ) {
	$ret = 'none';
	if( $interests = CommerceCustomer::getCustomerInterests( $pCustomersId ) ) {
		$ret = '';
		foreach( $interests as $i ) {
			if( $i['is_interested'] ) {
				$ret .= $i['interests_name'].',';
			}
		}
	} 	
	return $ret;
}


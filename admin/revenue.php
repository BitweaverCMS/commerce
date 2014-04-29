<?php
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );

// $gBitSmarty->assign( 'loadAjax', 'mochikit' );
// $gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

if( count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$_REQUEST['max_records']  = -1;
if( !empty( $_REQUEST['period'] ) ) {
	$listHash['period'] = $_REQUEST['period'];
}

if( isset( $_REQUEST['compare'] ) && is_array( $_REQUEST['compare'] ) ) {
	// flip array for easy lookup
	$_REQUEST['compare'] = array_flip( $_REQUEST['compare'] );
}

$stats = new CommerceStatistics();
if( !empty( $_REQUEST['interests_id'] ) ) {
	$interests = $gBitCustomer->getInterests();
	$gBitSmarty->assign_by_ref( 'interestsList', $interests );
	$gBitSmarty->assign( 'interestsName', $interests[$_REQUEST['interests_id']] );
	$_REQUEST['orders_products'] = TRUE;
	$_REQUEST['orders_status_comparison'] = '>';
	$_REQUEST['orders_status_id'] = 0;
	$gBitSmarty->assign( 'listOrders', order::getList( $_REQUEST ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_interest.tpl', tra( 'Revenue By Interest' ).' '.$interests[$_REQUEST['interests_id']].' : '.$_REQUEST['timeframe'] , array( 'display_mode' => 'admin' ) );
} elseif( !empty( $_REQUEST['referer'] ) ) {
	foreach( array( 'period', 'timeframe', 'referer', 'exclude', 'include', 'new_reg' ) as $param ) {
		if( !empty( $_REQUEST[$param] ) ) {
			$_REQUEST['listInfo']['parameters'][$param] = $_REQUEST[$param];
		}
	}
	$statsByReferer = $stats->getRevenueByReferer( $_REQUEST );
	BitBase::postGetList( $_REQUEST );
	$_REQUEST['listInfo']['block_pages'] = 3;
	$_REQUEST['listInfo']['item_name'] = 'coupons';
	$_REQUEST['listInfo']['page_records'] = count( $statsByReferer['hosts'] );
	$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );

	$gBitSmarty->assign( 'statsByReferer', $stats->getRevenueByReferer( $_REQUEST ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_referer.tpl', 'Revenue By Referer' , array( 'display_mode' => 'admin' ));
} elseif( !empty( $_REQUEST['timeframe'] ) ) {
	$statsByType = $stats->getRevenueByType( $_REQUEST );
	$gBitSmarty->assign_by_ref( 'statsByType', $statsByType );
	$statsByOption = $stats->getRevenueByOption( $_REQUEST );
	foreach( $statsByOption as $stat ) {
		@$statsByOptionTotalUnits[$stat['products_options_id']] += $stat['total_units'];
	}
	$gBitSmarty->assign( 'statsByOption', $statsByOption );
	$gBitSmarty->assign( 'statsByOptionTotalUnits', $statsByOptionTotalUnits );
	$gBitSmarty->assign( 'statsCustomers', $stats->getCustomerConversions( $_REQUEST ) );
	$gBitSmarty->assign( 'valuableInterests' , $stats->getMostValuableInterests( $_REQUEST ) );
	$gBitSmarty->assign( 'valuableCustomers' , $stats->getMostValuableCustomers( $_REQUEST ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_timeframe.tpl', 'Revenue By Timeframe' , array( 'display_mode' => 'admin' ));
} else {
	$listHash['max_records'] = -1;
	$gBitSmarty->assign( 'statsCustomers', $stats->getCustomerConversions( array( 'period' => $_REQUEST['period'] ) ) );
	$gBitSmarty->assign( 'stats', $stats->getAggregateRevenue( $listHash ) );
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


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

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceStatistics.php' );

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
	$gBitSmarty->assignByRef( 'interestsList', $interests );
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
	$gBitSmarty->assignByRef( 'listInfo', $_REQUEST['listInfo'] );

	$gBitSmarty->assign( 'statsByReferer', $stats->getRevenueByReferer( $_REQUEST ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_referer.tpl', 'Revenue By Referer' , array( 'display_mode' => 'admin' ));
} elseif( !empty( $_REQUEST['timeframe'] ) ) {
	$statsByType = $stats->getRevenueByType( $_REQUEST );
	$gBitSmarty->assignByRef( 'statsByType', $statsByType );
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
	$listHash['order_min'] = .01;
	$revStats = $stats->getAggregateRevenue( $listHash );
	$gBitSmarty->assign( 'stats', $revStats );
	if( BitBase::getParameter( $_REQUEST, 'display' ) == 'matrix' ) {
		switch( BitBase::getParameter( $_REQUEST, 'period' ) ) {
			case 'Y-':
				$headers = array( '' );
				break;
			case 'Y-\QQ':
				$headers = array( 'Q1', 'Q2', 'Q3', 'Q4' );
				break;
			case 'Y-m':
				$headers = array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' );
				break;
			case 'Y-\WeekW':
			default:
				for( $i = 1; $i <= 53; $i++ ) {
					$headers[] = 'Week'.str_pad($i, 2, '0', STR_PAD_LEFT);
				}
				break;
		}
		next( $revStats ); // first hashKey is summary stats
		$keyParts = explode( '-', key( $revStats ) );
		$gBitSmarty->assign( 'beginYear', $keyParts[0] );
		end( $revStats );
		$keyParts = explode( '-', key( $revStats ) );
		$gBitSmarty->assign( 'endYear', $keyParts[0] );
		reset( $revStats );
	

		$gBitSmarty->assign( 'matrixHeaders', $headers );
		$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue_matrix.tpl', 'Revenue Matrix' , array( 'display_mode' => 'admin' ));
	} else {
		$gBitSmarty->assign( 'statsCustomers', $stats->getCustomerConversions( array( 'period' => $_REQUEST['period'] ) ) );
		$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue.tpl', 'Revenue' , array( 'display_mode' => 'admin' ));
	}
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


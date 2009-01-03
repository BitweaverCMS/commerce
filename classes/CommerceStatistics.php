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
//  $Id: CommerceStatistics.php,v 1.2 2009/01/03 07:28:56 spiderr Exp $
//
	class CommerceStatistics extends BitBase {

		function getAggregateRevenue( $pParamHash ) {
			if( empty( $pParamHash['period'] ) ) {
				$pParamHash['period'] = 'YYYY-MM';
			}
			if( empty( $pParamHash['max_records'] ) ) {
				$pParamHash['max_records'] = 12;
			}
			
			$ret = array();
			$ret['stats']['gross_revenue_max'] = 0;
			$ret['stats']['order_count_max'] = 0;

			$sql = "SELECT TO_CHAR( `date_purchased`, '$pParamHash[period]' ) AS `hash_key`, ROUND( SUM( `order_total` ), 2 )  AS gross_revenue, count(orders_id) AS order_count, round( sum(order_total) / count(orders_id), 2) AS avg_order_size 
					FROM " . TABLE_ORDERS . " WHERE `orders_status` > 0 GROUP BY TO_CHAR( `date_purchased`, '$pParamHash[period]' ) ORDER BY TO_CHAR( `date_purchased`, '$pParamHash[period]' ) DESC";
			$bindVars = array( $pParamHash['period'], $pParamHash['period'], $pParamHash['period'] );	
$bindVars = array();
			if( $rs = $this->mDb->query( $sql, $bindVars, $pParamHash['max_records'] ) ) {
				while( $row = $rs->fetchRow() ) {
					$ret[$row['hash_key']] = $row;
					if( $ret['stats']['order_count_max'] < $row['order_count'] ) {
						$ret['stats']['order_count_max'] = $row['order_count'];
					}
					if( $ret['stats']['gross_revenue_max'] < $row['gross_revenue'] ) {
						$ret['stats']['gross_revenue_max'] = $row['gross_revenue'];
					}
				}
			}
			return( $ret );
		}

		function getProductRevenue( $pParamHash ) {
			switch( $pParamHash['period'] ) {
				case 'day':
				break;
			}
		}
	}
?>


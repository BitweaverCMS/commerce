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
//  $Id: CommerceStatistics.php,v 1.4 2009/02/25 17:44:56 spiderr Exp $
//
	class CommerceStatistics extends BitBase {

		function getAggregateRevenue( $pParamHash ) {
			if( empty( $pParamHash['period'] ) ) {
				$pParamHash['period'] = 'Y-m';
			}
			if( empty( $pParamHash['max_records'] ) ) {
				$pParamHash['max_records'] = 12;
			}
			
			$ret = array();
			$ret['stats']['gross_revenue_max'] = 0;
			$ret['stats']['order_count_max'] = 0;

			$sql = "SELECT ".$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' )." AS `hash_key`, ROUND( SUM( `order_total` ), 2 )  AS `gross_revenue`, COUNT( `orders_id` ) AS `order_count`, ROUND( SUM( `order_total` ) / COUNT( `orders_id` ), 2) AS `avg_order_size` 
					FROM " . TABLE_ORDERS . " WHERE `orders_status` > 0 GROUP BY `hash_key` ORDER BY `hash_key` DESC";
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


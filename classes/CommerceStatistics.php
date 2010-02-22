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
//  $Id: CommerceStatistics.php,v 1.7 2010/02/22 19:23:46 spiderr Exp $
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

		function getRevenueByOption( $pParamHash ) {
			$ret = array();

			$bindVars = array();
			$whereSql = '';

			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}

			$sql = "SELECT copa.`products_options_values_id` AS `hash_key`, copa.`products_options_id`, copa.`products_options`, COALESCE( cpa.`products_options_values_name`, copa.`products_options_values`) AS `products_options_values_name`, SUM(cop.`products_quantity` * copa.`options_values_price`) AS `total_revenue`, SUM(cop.`products_quantity`) AS `total_units`
					FROM " . TABLE_ORDERS . " co
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(co.`orders_id`=cop.`orders_id`)
						INNER JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " copa ON(cop.`orders_products_id`=copa.`orders_products_id`)
						INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " cpa ON(cpa.`products_options_values_id`=copa.`products_options_values_id`)
					WHERE co.`orders_status` > 0 $whereSql
					GROUP BY copa.`products_options_values_id`, copa.`products_options`, copa.`products_options_values`, cpa.`products_options_values_name`, copa.`products_options_id`
					ORDER BY copa.`products_options`, SUM(cop.`products_quantity`) DESC, copa.`products_options_values`";

			$ret = $this->mDb->getAll( $sql, $bindVars );
			return $ret;
		}

		function getCustomerConversions( $pParamHash ) {
			$ret = array();

			$whereSql = '';
			$bindVars = array();
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
			$sql = "SELECT COUNT( DISTINCT `user_id` ) 
					FROM `".BIT_DB_PREFIX."users_users` uu
					WHERE 1=1 $whereSql";
			$ret['new_registrations'] = $this->mDb->getOne( $sql, $bindVars );

//$this->debug();

	
			// #### All Customers That Created Products
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
			$sql = "SELECT COUNT( DISTINCT( lc.`user_id` ) ) AS `all_customers_that_created_products`, COUNT( lc.`content_id` ) AS `new_products_created_by_all_customers`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
					WHERE `content_type_guid`=? $whereSql";
			$ret = array_merge( $ret, $this->mDb->getRow( $sql, $bindVars ) );

			// #### New Customers That Created Products
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
			$sql = "SELECT COUNT( DISTINCT( lc.`user_id` ) ) AS `new_customers_that_created_products`, COUNT( lc.`content_id` ) AS `new_products_created_by_new_customers`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
					WHERE `content_type_guid`=? $whereSql";
			$ret = array_merge( $ret, $this->mDb->getRow( $sql, $bindVars ) );

			// #### New Products Purchased By All Customers
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
			$sql = "SELECT COUNT( DISTINCT cop.`products_id` ) AS `new_products_purchased_by_all_customers`, COUNT( DISTINCT( lc.`user_id` ) ) AS `all_customers_that_purchased_new_products`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
						INNER JOIN " . TABLE_PRODUCTS . " cp ON(lc.`content_id`=cp.`content_id`)
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(cp.`products_id`=cop.`products_id`)
						INNER JOIN " . TABLE_ORDERS . " co ON(co.`orders_id`=cop.`orders_id`)
					WHERE `content_type_guid`=? AND co.`orders_status` > 0 $whereSql";
			$ret = array_merge( $ret, $this->mDb->getRow( $sql, $bindVars ) );

			// #### New Product Purchased By New Customers
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
			$sql = "SELECT COUNT( DISTINCT cop.`products_id` ) AS `new_products_purchased_by_new_customers`, COUNT( DISTINCT( lc.`user_id` ) ) AS `new_customers_that_purchased_new_products`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
						INNER JOIN " . TABLE_PRODUCTS . " cp ON(lc.`content_id`=cp.`content_id`)
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(cp.`products_id`=cop.`products_id`)
						INNER JOIN " . TABLE_ORDERS . " co ON(co.`orders_id`=cop.`orders_id`)
					WHERE `content_type_guid`=? AND co.`orders_status` > 0 $whereSql";
			$ret = array_merge( $ret, $this->mDb->getRow( $sql, $bindVars ) );

			$whereSql = '';
			$bindVars = array();
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
			$sql = "SELECT COUNT( DISTINCT( `products_id` ) ) AS `unique_products_ordered`, COUNT( co.`orders_id` ) AS `total_orders`
					FROM " . TABLE_ORDERS_PRODUCTS . " cop 
						INNER JOIN " . TABLE_ORDERS . " co ON (co.`orders_id`=cop.`orders_id`) 
					WHERE co.`orders_status`>0 $whereSql";
			$ret = array_merge( $ret, $this->mDb->getRow( $sql, $bindVars ) );
$this->debug(0);

			return $ret;
		}

		function getRevenueByType( $pParamHash ) {
			$ret = array();

			$bindVars = array();
			$whereSql = '';

			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}

			$sql = "SELECT cpt.`type_id`, cpt.`type_name`, cpt.`type_class`, SUM(cop.`products_quantity` * cop.`products_price`) AS `total_revenue`, SUM(cop.`products_quantity`) AS `total_units`
					FROM " . TABLE_ORDERS . " co
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(co.`orders_id`=cop.`orders_id`)
						INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
						INNER JOIN " . TABLE_PRODUCT_TYPES . " cpt ON(cpt.`type_id`=cp.`products_type`)
					WHERE co.`orders_status` > 0 $whereSql
					GROUP BY cpt.type_id, cpt.`type_name`, cpt.type_class
					ORDER BY SUM(cop.`products_quantity` * cop.`products_price`)";

			$ret = $this->mDb->getAssoc( $sql, $bindVars );
			return $ret;
		}
	}
?>


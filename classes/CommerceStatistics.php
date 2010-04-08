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
//  $Id: CommerceStatistics.php,v 1.13 2010/04/08 17:51:51 spiderr Exp $
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

			$sql = "SELECT $selectSql copa.`products_options_values_id` AS `hash_key`, copa.`products_options_values_id`, copa.`products_options_id`, copa.`products_options`, COALESCE( cpa.`products_options_values_name`, copa.`products_options_values`) AS `products_options_values_name`, SUM(cop.`products_quantity` * copa.`options_values_price`) AS `total_revenue`, SUM(cop.`products_quantity`) AS `total_units`
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

		function getMostValuableInterests( $pParamHash ) {
			$selectSql = '';
			$whereSql = '';
			$groupSql = '';
			$bindVars = array();
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$selectSql = ' ci.`interests_name` AS `hash_key`, ';
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' ).' AS `hash_key`, ';
				$groupSql .= ', '.$this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' );
				$sqlFunc = 'getAssoc';
			}
			$sql = "SELECT $selectSql ci.`interests_id`, ci.`interests_name`, SUM( co.`order_total` ) AS `total_revenue`, COUNT( co.`orders_id` ) AS `total_orders`
					FROM " . TABLE_CUSTOMERS_INTERESTS . " ci , " . TABLE_ORDERS . " co, " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim 
					WHERE co.`orders_status`>0 
						AND cim.`customers_id`=co.`customers_id` 
						AND cim.`interests_id`=ci.`interests_id` 
						AND ci.`interests_id` = (SELECT cim2.`interests_id` FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim2 WHERE cim2.`customers_id`=co.`customers_id` AND cim2.`customers_id`=co.`customers_id` LIMIT 1)
						$whereSql
					GROUP BY ci.`interests_name`, ci.`interests_id` $groupSql
					ORDER BY SUM( co.`order_total`) DESC";
			$ret = $this->mDb->getAssoc( $sql, $bindVars );
			return $ret;
		}

		function getMostValuableCustomers( $pParamHash ) {
			$selectSql = '';
			$whereSql = '';
			$groupSql = '';
			$bindVars = array();
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' ).' AS `hash_key`, ';
				$groupSql .= ', '.$this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' );
				$sqlFunc = 'getAssoc';
			}
			BitBase::prepGetList( $pParamHash );
			$sql = "SELECT $selectSql co.`customers_id`, SUM( co.`order_total`) AS `total_revenue`, COUNT( co.`orders_id` ) AS `total_orders`
					FROM " . TABLE_ORDERS . " co 
					WHERE co.`orders_status`>0 $whereSql 
					GROUP BY co.`customers_id` $groupSql
					ORDER BY SUM( co.`order_total`) DESC";
			if( $rs = $this->mDb->query( $sql, $bindVars, $pParamHash['max_records'] ) ) {
				while( $row = $rs->fetchRow() ) {
					$ret[current($row)] = $row;
				}
			}
			return $ret;
		}

		function getCustomerConversions( $pParamHash ) {
//$this->debug();
			$ret = array();

			// #### Total Registrations
			$sqlFunc = 'getRow';
			$selectSql = '';
			$whereSql = '';
			$bindVars = array();
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' WHERE '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$sqlFunc = 'getAssoc';
				// foo selection is to force hash with keys to be selected, else a simple array is returned with just registeration count
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' AS `hash_key`, 1 AS `foo`, ';
				$whereSql .= ' GROUP BY '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) );
			}
			$sql = "SELECT $selectSql COUNT( DISTINCT `user_id` ) as `new_registrations`
					FROM `".BIT_DB_PREFIX."users_users` uu
					$whereSql";
			$ret = array_merge_recursive( $ret, $this->mDb->$sqlFunc( $sql, $bindVars ) );

	
			// #### All Customers That Created Products
			$sqlFunc = 'getRow';
			$selectSql = '';
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$sqlFunc = 'getAssoc';
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' AS `hash_key`, ';
				$whereSql .= ' GROUP BY '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) );
			}
			$sql = "SELECT $selectSql COUNT( DISTINCT( lc.`user_id` ) ) AS `all_customers_that_created_products`, COUNT( lc.`content_id` ) AS `new_products_created_by_all_customers`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
					WHERE `content_type_guid`=? $whereSql";
			$ret = array_merge_recursive( $ret, $this->mDb->$sqlFunc( $sql, $bindVars ) );

			// #### New Customers That Created Products
			$sqlFunc = 'getRow';
			$selectSql = '';
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$sqlFunc = 'getAssoc';
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' AS `hash_key`, ';
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) );
				$whereSql .= ' GROUP BY '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) );
			}
			$sql = "SELECT $selectSql COUNT( DISTINCT( lc.`user_id` ) ) AS `new_customers_that_created_products`, COUNT( lc.`content_id` ) AS `new_products_created_by_new_customers`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
					WHERE `content_type_guid`=? $whereSql";
			$ret = array_merge_recursive( $ret, $this->mDb->$sqlFunc( $sql, $bindVars ) );

			// #### New Products Purchased By All Customers
			$sqlFunc = 'getRow';
			$selectSql = '';
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$sqlFunc = 'getAssoc';
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).', ';
				$whereSql .= ' GROUP BY '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) );
			}
			$sql = "SELECT $selectSql COUNT( DISTINCT cop.`products_id` ) AS `new_products_purchased_by_all_customers`, COUNT( DISTINCT( lc.`user_id` ) ) AS `all_customers_that_purchased_new_products`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
						INNER JOIN " . TABLE_PRODUCTS . " cp ON(lc.`content_id`=cp.`content_id`)
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(cp.`products_id`=cop.`products_id`)
						INNER JOIN " . TABLE_ORDERS . " co ON(co.`orders_id`=cop.`orders_id`)
					WHERE `content_type_guid`=? AND co.`orders_status` > 0 $whereSql";
			$ret = array_merge_recursive( $ret, $this->mDb->$sqlFunc( $sql, $bindVars ) );

			// #### New Product Purchased By New Customers
			$sqlFunc = 'getRow';
			$selectSql = '';
			$whereSql = '';
			$bindVars = array( 'bitproduct' );
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) ).' AS `hash_key`, ';
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ).' = '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) );
				$whereSql .= ' GROUP BY '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SqlIntToTimestamp( 'lc.`created`' ) );
				$sqlFunc = 'getAssoc';
			}
			$sql = "SELECT $selectSql COUNT( DISTINCT cop.`products_id` ) AS `new_products_purchased_by_new_customers`, COUNT( DISTINCT( lc.`user_id` ) ) AS `new_customers_that_purchased_new_products`
					FROM `".BIT_DB_PREFIX."liberty_content` lc
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lc.`user_id`=uu.`user_id`)
						INNER JOIN " . TABLE_PRODUCTS . " cp ON(lc.`content_id`=cp.`content_id`)
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(cp.`products_id`=cop.`products_id`)
						INNER JOIN " . TABLE_ORDERS . " co ON(co.`orders_id`=cop.`orders_id`)
					WHERE `content_type_guid`=? AND co.`orders_status` > 0 $whereSql";
			$ret = array_merge_recursive( $ret, $this->mDb->$sqlFunc( $sql, $bindVars ) );

			// #### Unique Totals
			$sqlFunc = 'getRow';
			$selectSql = '';
			$whereSql = '';
			$bindVars = array();
			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			} elseif( !empty( $pParamHash['period'] ) ) {
				$selectSql .= $this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' ).' AS `hash_key`, ';
				$whereSql .= ' GROUP BY '.$this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' );
				$sqlFunc = 'getAssoc';
			}
			$sql = "SELECT $selectSql COUNT( DISTINCT( `products_id` ) ) AS `unique_products_ordered`, COUNT( co.`orders_id` ) AS `total_orders`
					FROM " . TABLE_ORDERS_PRODUCTS . " cop 
						INNER JOIN " . TABLE_ORDERS . " co ON (co.`orders_id`=cop.`orders_id`) 
					WHERE co.`orders_status`>0 $whereSql";
			$ret = array_merge_recursive( $ret, $this->mDb->$sqlFunc( $sql, $bindVars ) );
$this->debug(0);

			if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
			} elseif( !empty( $pParamHash['period'] ) ) {
				$maxStats = array();
				foreach( array_keys( $ret ) as $periodKey ) {
					if( is_array( $ret[$periodKey] ) ) {
						foreach( array_keys( $ret[$periodKey] ) as $statKey ) {
							if( empty( $maxStats[$statKey] ) || $maxStats[$statKey] < $ret[$periodKey][$statKey] ) {
								$maxStats[$statKey] = $ret[$periodKey][$statKey];
							}
						}
					}
				}
				$ret['max_stats'] = $maxStats;
			}

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


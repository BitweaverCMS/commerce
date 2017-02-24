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
/* vim: :set fdm=marker : */

require_once( KERNEL_PKG_PATH . 'BitSingleton.php' );

class CommerceStatistics extends BitSingleton {

    public function __wakeup() {
		parent::__wakeup();
	}

	public function __sleep() {
		return parent::__sleep();
	}

// {{{ =================== Customers ====================

	function getAbandonedCustomers( &$pParamHash ) {
		return $this->getCustomerActivity( $pParamHash, FALSE );
	}
	function getRetainedCustomers( &$pParamHash ) {
		return $this->getCustomerActivity( $pParamHash );
	}

	function getCustomerActivity( &$pParamHash, $pRetained=TRUE ) {
		$sortMode = '';
		if( !empty( $pParamHash['sort_mode'] ) ) {
			switch( $pParamHash['sort_mode'] ) {
				case 'orders_asc':
					$sortMode = 'COUNT(`orders_id`) ASC, ';
					break;
				case 'orders_desc':
					$sortMode = 'COUNT(`orders_id`) DESC, ';
					break;
				case 'first_purchase_asc':
					$sortMode = 'MIN(co.`date_purchased`) ASC, ';
					break;
				case 'first_purchase_desc':
					$sortMode = 'MIN(co.`date_purchased`) DESC, ';
					break;
				case 'age_asc':
					$sortMode = 'MAX(co.`date_purchased`) - MIN(co.`date_purchased`) ASC, ';
					break;
				case 'age_desc':
					$sortMode = 'MAX(co.`date_purchased`) - MIN(co.`date_purchased`) DESC, ';
					break;
				case 'last_purchase_asc':
					$sortMode = 'MAX(co.`date_purchased`) ASC, ';
					break;
				case 'last_purchase_desc':
					$sortMode = 'MAX(co.`date_purchased`) DESC, ';
					break;
				case 'revenue_asc':
					$sortMode = 'SUM(co.`order_total`) DESC, ';
					break;
			}
		} else {
			$pParamHash['sort_mode'] = 'revenue_desc';
		}


		$interval = BitBase::getParameter( $pParamHash, 'interval', '2 years' );

		$bindVars[] = $interval;
		$bindVars[] = $interval;
		$bindVars[] = $interval;

		$comparison = $pRetained ? '<' : '>';

		$sortMode .= 'SUM(co.`order_total`) DESC';

		BitBase::prepGetList( $pParamHash );

		$sql = "SELECT uu.`user_id`,uu.`real_name`, uu.`login`,SUM(order_total) AS `revenue`, COUNT(orders_id) AS `orders`, MIN(`date_purchased`) AS `first_purchase`, MIN(`orders_id`) AS `first_orders_id`, MAX(date_purchased) AS `last_purchase`, MAX(`orders_id`) AS `last_orders_id`, MAX(date_purchased) - MIN(date_purchased) AS `age`
				FROM com_orders co 
					INNER JOIN users_users uu ON(co.customers_id=uu.user_id) 
				WHERE NOW() - uu.registration_date::int::abstime::timestamptz > ? 
				GROUP BY  uu.`user_id`,uu.`real_name`, uu.`login`
				HAVING NOW() - MIN(co.date_purchased) > ? AND NOW() - MAX(co.date_purchased) ".$comparison." ? ORDER BY $sortMode";
		if( $rs = $this->mDb->query( $sql, $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret['customers'][$row['user_id']] = $row;
				@$ret['totals']['orders'] += $row['orders'];
				@$ret['totals']['revenue'] += $row['revenue'];
				@$ret['totals']['customers']++;
			}
		}
		return $ret;
	}

// }}}

// {{{ =================== Revenue ====================
	function getAggregateRevenue( $pParamHash ) {

		$bindVars = array();
		$whereSql = '';

		if( empty( $pParamHash['period'] ) ) {
			$pParamHash['period'] = 'Y-m';
		}
		if( empty( $pParamHash['max_records'] ) ) {
			$pParamHash['max_records'] = 12;
		}

		if( !empty( $pParamHash['delivery_country'] ) ) {
			$whereSql .= ' AND co.`delivery_country`=? ';
			$bindVars[] = $pParamHash['delivery_country'];
		}
		
		$ret = array();

		$sql = "SELECT ".$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' )." AS `hash_key`, ROUND( SUM( `order_total` ), 2 )  AS `gross_revenue`, COUNT( `orders_id` ) AS `order_count`, ROUND( SUM( `order_total` ) / COUNT( `orders_id` ), 2) AS `avg_order_size` 
				FROM " . TABLE_ORDERS . " WHERE `orders_status` > 0 GROUP BY `hash_key` $whereSql
				ORDER BY `hash_key` DESC";
		if( $rs = $this->mDb->query( $sql, $bindVars, $pParamHash['max_records'] ) ) {
			if( $rs->RowCount() ) {
				$ret['stats']['gross_revenue_max'] = 0;
				$ret['stats']['order_count_max'] = 0;
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
		}

		return( $ret );
	}

	public static function getCustomerRevenue( $pParamHash ) {
		global $gBitDb;

		$bindVars['customers_id'] = $pParamHash['customers_id'];
		$sql = "SELECT SUM( co.`order_total` ) AS `total_revenue`, COUNT( co.`orders_id` ) AS `total_orders`
				FROM " . TABLE_ORDERS . " co
				WHERE co.`orders_status`>0 AND co.`customers_id`=?";
		return $gBitDb->getRow( $sql, $bindVars );
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
		$selectSql = '';
		$whereSql = '';

		if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
			$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
			$bindVars[] = $pParamHash['timeframe'];
		}

		if( !empty( $pParamHash['products_type'] ) ) {
			$whereSql .= ' AND cp.`products_type` = ?';
			$bindVars[] = $pParamHash['products_type'];
		}

		if( !empty( $pParamHash['delivery_country'] ) ) {
			$whereSql .= ' AND co.`delivery_country`=? ';
			$bindVars[] = $pParamHash['delivery_country'];
		}
		
		$sql = "SELECT $selectSql copa.`products_options_values_id` AS `hash_key`, copa.`products_options_values_id`, copa.`products_options_id`, copa.`products_options`, copa.`products_options_values` AS `products_options_values_name`, SUM(cop.`products_quantity` * copa.`options_values_price`) AS `total_revenue`, SUM(cop.`products_quantity`) AS `total_units`
				FROM " . TABLE_ORDERS . " co
					INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(co.`orders_id`=cop.`orders_id`)
					INNER JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " copa ON(cop.`orders_products_id`=copa.`orders_products_id`)
					INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
				WHERE co.`orders_status` > 0 $whereSql
				GROUP BY copa.`products_options_values_id`, copa.`products_options`, copa.`products_options_values`, copa.`products_options_id`
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

		if( !empty( $pParamHash['delivery_country'] ) ) {
			$whereSql .= ' AND co.`delivery_country`=? ';
			$bindVars[] = $pParamHash['delivery_country'];
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
		global $gBitSystem;

		$keySql = '';
		$selectSql = '';
		$whereSql = '';
		$groupSql = '';
		$joinSql = '';
		$bindVars = array();
		if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
			$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
			$bindVars[] = $pParamHash['timeframe'];
		} elseif( !empty( $pParamHash['period'] ) ) {
			$keySql .= $this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' ).' AS `hash_key`, ';
			$groupSql .= ', '.$this->mDb->SQLDate( $pParamHash['period'], 'co.`date_purchased`' );
			$sqlFunc = 'getAssoc';
		}

		if( !empty( $pParamHash['delivery_country'] ) ) {
			$whereSql .= ' AND co.`delivery_country`=? ';
			$bindVars[] = $pParamHash['delivery_country'];
		}
		
		if( $gBitSystem->isPackageActive( 'stats' ) ) {
			$selectSql .= " , sru.`referer_url` ";
			$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=uu.`user_id`) 
						  LEFT JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (sru.`referer_url_id`=srum.`referer_url_id`) ";
			$groupSql .= $selectSql;
		}

		BitBase::prepGetList( $pParamHash );
		$sql = "SELECT $keySql co.`customers_id`, uu.`registration_date`, SUM( co.`order_total`) AS `total_revenue`, COUNT( co.`orders_id` ) AS `total_orders` $selectSql
				FROM " . TABLE_ORDERS . " co 
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (co.`customers_id`=uu.`user_id`)
					$joinSql
				WHERE co.`orders_status`>0 $whereSql 
				GROUP BY co.`customers_id`, uu.`registration_date` $groupSql
				ORDER BY SUM( co.`order_total`) DESC";
		if( $rs = $this->mDb->query( $sql, $bindVars, $pParamHash['max_records'] ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[current($row)] = $row;
			}
		}
		return $ret;
	}

	function getCustomerConversions( $pParamHash ) {
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

		if( !empty( $pParamHash['products_type'] ) ) {
			$whereSql .= ' AND cp.`products_type` = ?';
			$bindVars[] = $pParamHash['products_type'];
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

		if( !empty( $pParamHash['date_from'] ) ) {
			$whereSql .= ' AND co.`date_purchased` >= ? ';
			$bindVars[] = $pParamHash['date_from'];
		}
 
		if( !empty( $pParamHash['date_to'] ) ) {
			$whereSql .= ' AND co.`date_purchased` <= ? ';
			$bindVars[] = $pParamHash['date_to'];
		}
 
		if( !empty( $pParamHash['products_model'] ) ) {
			$whereSql .= ' AND cp.`products_model` = ?';
			$bindVars[] = $pParamHash['products_model'];
		}

		if( !empty( $pParamHash['products_type'] ) ) {
			$whereSql .= ' AND cp.`products_type` = ?';
			$bindVars[] = $pParamHash['products_type'];
		}

		if( !empty( $pParamHash['delivery_country'] ) ) {
			$whereSql .= ' AND co.`delivery_country`=? ';
			$bindVars[] = $pParamHash['delivery_country'];
		}
		
		$sql = "SELECT cpt.`type_id`, cpt.`type_name`, cpt.`type_class`, COALESCE( cop.`products_model`, cp.`products_model` ) AS `co_products_model`, SUM(cop.`products_quantity` * cop.`products_price`) AS `total_revenue`, SUM(cop.`products_quantity`) AS `total_units`
				FROM " . TABLE_ORDERS . " co
					INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(co.`orders_id`=cop.`orders_id`)
					INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
					INNER JOIN " . TABLE_PRODUCT_TYPES . " cpt ON(cpt.`type_id`=cp.`products_type`)
				WHERE co.`orders_status` > 0 $whereSql
				GROUP BY cpt.`type_id`, cpt.`type_name`, cpt.`type_class`, `co_products_model`
				ORDER BY SUM(cop.`products_quantity` * cop.`products_price`) DESC";

		if( $rs = $this->mDb->query( $sql, $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				$row['products_model'] = $row['co_products_model']; // The coalesced column name for GROUP BY back to default
				$ret[$row['type_id']]['models'][$row['co_products_model']] = $row;
				@$ret[$row['type_id']]['totals']['type_name'] = $row['type_name'];
				@$ret[$row['type_id']]['totals']['type_class'] = $row['type_class'];
				@$ret[$row['type_id']]['totals']['total_units'] += $row['total_units'];
				@$ret[$row['type_id']]['totals']['total_revenue'] += $row['total_revenue'];
			}
		}

		return $ret;
	}

	function getRevenueByReferer( &$pParamHash ) {
		$ret = array();

		$bindVars = array();
		$whereSql = '';

		if( empty( $pParamHash['sort_mode'] ) ) {
			$pParamHash['sort_mode'] = 'revenue_desc';
		}

		BitBase::prepGetList( $pParamHash );

		if( !empty( $pParamHash['period'] ) && !empty( $pParamHash['timeframe'] ) ) {
			$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' = ?';
			$bindVars[] = $pParamHash['timeframe'];
			if( !empty( $pParamHash['new_reg'] ) ) {
				$whereSql .= ' AND '.$this->mDb->SQLDate( $pParamHash['period'], $this->mDb->SQLIntToTimestamp( 'uu.`registration_date`' ) ).' = ?';
				$bindVars[] = $pParamHash['timeframe'];
			}
		}

		if( !empty( $pParamHash['delivery_country'] ) ) {
			$whereSql .= ' AND co.`delivery_country`=? ';
			$bindVars[] = $pParamHash['delivery_country'];
		}
		
		if( !empty( $pParamHash['include'] ) ) {
			$includes = explode( ',', $pParamHash['include'] );
			foreach( $includes  as $in ) {
				$whereSql .= " AND LOWER( `referer_url` ) LIKE ? ";
				$bindVars[] = '%'.strtolower( trim( urlencode( $in ) ) ).'%';
			}
/*
			// OR the terms

			$max = count( $includes );
			for( $i = 0; $i < $max; $i++ ) {
				$whereSql .= " LOWER( `referer_url` ) LIKE ? ";
				$bindVars[] = '%'.strtolower( trim( urlencode( $includes[$i] ) ) ).'%';
				if( $i <  $max - 1 ) {
					$whereSql .= ' OR ';
				}
			}
			$whereSql .= ' ) ';
*/
		}

		if( !empty( $pParamHash['exclude'] ) ) {
			$excludes = explode( ',', $pParamHash['exclude'] );
			foreach( $excludes  as $ex ) {
				$whereSql .= " AND LOWER( `referer_url` ) NOT LIKE ? ";
				$bindVars[] = '%'.strtolower( trim( urlencode( $ex ) ) ).'%';
			}
		}

		$sql = "SELECT sru.`referer_url`, co.`customers_id`, SUM(cop.`products_quantity` * cop.`products_price`) AS `total_revenue`, SUM(cop.`products_quantity`) AS `total_units`, COUNT(co.`orders_id`) AS `total_orders`
				FROM " . TABLE_ORDERS . " co
					INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(co.`orders_id`=cop.`orders_id`)
					INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
					INNER JOIN " . TABLE_PRODUCT_TYPES . " cpt ON(cpt.`type_id`=cp.`products_type`)
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=co.`customers_id`) 
					INNER JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=co.`customers_id`) 
					INNER JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (sru.`referer_url_id`=srum.`referer_url_id`) 
				WHERE co.`orders_status` > 0 $whereSql
				GROUP BY sru.`referer_url`, co.`customers_id`
				ORDER BY SUM(cop.`products_quantity` * cop.`products_price`)";

		$rows = $this->mDb->getAll( $sql, $bindVars );
		foreach( $rows as $row ) {
			if( $urlHash = parse_url( $row['referer_url'] ) ) {
				@$ret['hosts'][$urlHash['host']]['revenue'] += $row['total_revenue'];
				@$ret['hosts'][$urlHash['host']]['units'] += $row['total_units'];
				@$ret['hosts'][$urlHash['host']]['orders'] += $row['total_orders'];
				@$ret['hosts'][$urlHash['host']]['customers']++;
				if( !empty( $urlHash['query'] ) && $searchTerm = (strpos( $urlHash['query'], 'q=' ) !== FALSE ? 'q' : (strpos( $urlHash['query'], 'p=' ) !== FALSE ? 'p' : FALSE)) ) {
					parse_str( $urlHash['query'] );
					if( !empty( $$searchTerm ) ) {	
						$urlKey = $$searchTerm;
					}
				} else {
					$urlKey = $row['referer_url'];
					@$ret['hosts'][$urlHash['host']]['referer_urls'][$row['referer_url']]++;
				}
				@$ret['hosts'][$urlHash['host']]['refs'][$urlKey]['revenue'] += $row['total_revenue'];
				@$ret['hosts'][$urlHash['host']]['refs'][$urlKey]['units'] += $row['total_units'];
				@$ret['hosts'][$urlHash['host']]['refs'][$urlKey]['orders'] += $row['total_orders'];
				@$ret['hosts'][$urlHash['host']]['refs'][$urlKey]['customers'] ++;

				@$ret['totals']['revenue'] += $row['total_revenue'];
				@$ret['totals']['units'] += $row['total_units'];
				@$ret['totals']['orders'] += $row['total_orders'];
				@$ret['totals']['customers']++;
			}
		}

		if( !empty( $ret['hosts'] ) ) {
			$sortFunction = 'commerce_statistics_referer_sort_'.$pParamHash['sort_mode'];
			if( function_exists( $sortFunction ) ) {
				uasort( $ret['hosts'], $sortFunction );
				foreach( array_keys( $ret['hosts'] ) as $key ) {
					uasort( $ret['hosts'][$key]['refs'], $sortFunction );
				}
			}
		}

		return $ret;
	}


	function getSalesAndIncome( $pParamHash ) {
		$ret = array();

		$bindVars = array();

		if( empty( $pParamHash['period'] ) ) {
			$pParamHash['period'] = 'Y-m';
		}

		$period = ', '.$this->mDb->SQLDate( $pParamHash['period'], '`date_purchased`' ).' as `period` ';

		// coupon value is stored as positive, gift_certificate is stored as negative
		$sql = "SELECT co.`orders_id`, co.`date_purchased`, co.`order_total`, cotgc.`orders_value` AS `gift_certificate`, (cotcp.`orders_value` * -1) AS `coupon_discount` $period
				FROM `".BIT_DB_PREFIX."com_orders` co 
					LEFT JOIN `".BIT_DB_PREFIX."com_orders_total` cotcp ON (co.`orders_id`=cotcp.`orders_id` AND cotcp.`class`='ot_coupon')
					LEFT JOIN `".BIT_DB_PREFIX."com_orders_total` cotgc ON (co.`orders_id`=cotgc.`orders_id` AND cotgc.`class`='ot_gv')
				WHERE co.`orders_status` > 0
				ORDER BY co.`orders_id` DESC";

		if( $rs = $this->mDb->query( $sql, $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				$sql = "SELECT SUM(cop.`products_cogs`) AS `cogs`, SUM((cop.`products_price` - cop.`products_wholesale`) * cop.`products_quantity`) AS wholesale_gross, SUM((cop.`products_wholesale` - cop.`products_cogs`) * cop.`products_quantity`) AS `supply_gross`
						FROM `".BIT_DB_PREFIX."com_orders_products` cop
						WHERE cop.`orders_id`=?";
				$ret['orders'][$row['orders_id']] = array_merge( $row, $this->mDb->getRow( $sql, array( $row['orders_id'] ) ) );
				$ret['orders'][$row['orders_id']]['wholesale_net'] = $ret['orders'][$row['orders_id']]['wholesale_gross'] + $ret['orders'][$row['orders_id']]['gift_certificate'] + $ret['orders'][$row['orders_id']]['coupon_discount'] ;

				@$ret['totals'][$row['period']]['gift_certificate'] += $ret['orders'][$row['orders_id']]['gift_certificate'];
				@$ret['totals'][$row['period']]['coupon_discount'] += $ret['orders'][$row['orders_id']]['coupon_discount'];
				@$ret['totals'][$row['period']]['wholesale_gross'] += $ret['orders'][$row['orders_id']]['wholesale_gross'];
				@$ret['totals'][$row['period']]['wholesale_net'] += $ret['orders'][$row['orders_id']]['wholesale_net'];


				@$ret['totals']['sum']['gift_certificate'] += $ret['orders'][$row['orders_id']]['gift_certificate'];
				@$ret['totals']['sum']['coupon_discount'] += $ret['orders'][$row['orders_id']]['coupon_discount'];
				@$ret['totals']['sum']['wholesale_gross'] += $ret['orders'][$row['orders_id']]['wholesale_gross'];
				@$ret['totals']['sum']['wholesale_net'] += $ret['orders'][$row['orders_id']]['wholesale_net'];
				
			}
		}
		return ( $ret );	
	}
}

function commerce_statistics_referer_sort_revenue_desc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'revenue' ); }
function commerce_statistics_referer_sort_revenue_asc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'revenue', 1 ); }
function commerce_statistics_referer_sort_orders_desc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'orders' ); }
function commerce_statistics_referer_sort_orders_asc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'orders', 1 ); }
function commerce_statistics_referer_sort_units_desc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'units' ); }
function commerce_statistics_referer_sort_units_asc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'units', 1 ); }
function commerce_statistics_referer_sort_customers_desc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'customers' ); }
function commerce_statistics_referer_sort_customers_asc($a, $b) { return commerce_statistics_referer_sort( $a, $b, 'customers', 1 ); }

function commerce_statistics_referer_sort( $a, $b, $pSort='revenue', $pDirection='-1' ) {
    if ($a[$pSort] == $b[$pSort]) {
        return 0;
    }
    return ( $a[$pSort] < $b[$pSort] ) ? $pDirection * -1 : $pDirection * 1;
}

// }}} 

CommerceStatistics::loadSingleton();


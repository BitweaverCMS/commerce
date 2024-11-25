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
require_once( KERNEL_PKG_CLASS_PATH.'BitSingleton.php' );

class CommerceOrderManager extends BitSingleton {

	function __construct() {
		parent::__construct();
	}

	public function __wakeup() {
		parent::__wakeup();
	}

	public function __sleep() {
		return parent::__sleep();
	}


	public function getPaymentTypes() {
		$paymentTypes = $this->mDb->getCol( "SELECT DISTINCT payment_type FROM " . TABLE_ORDERS_PAYMENTS . " ORDER BY payment_type" );
		$ret = array_merge( array( 'Check', 'Cash', 'Credit' ), $paymentTypes );
		return $ret;
	}

	public function getProductHistory( $pListHash ) {
		$whereSql = '';
		$bindVars = array();
		if( @BitBase::verifyId( $pListHash['products_id'] ) ) {
			$whereSql = 'cop.`products_id`=?';
			$bindVars[] = $pListHash['products_id'];
		}
		if( @BitBase::verifyId( $pListHash['user_id'] ) ) {
			$whereSql = 'lc.`user_id`=?';
			$bindVars[] = $pListHash['user_id'];
		}

		if( $whereSql ) {
			$sql = "SELECT cop.`orders_products_id` AS `hash_key`, co.*, cop.*,".$this->mDb->SQLDate( 'Y-m-d H:i', 'co.`date_purchased`' )." AS `purchase_time` 
					FROM " . TABLE_ORDERS . " co 
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(co.`orders_id`=cop.`orders_id`)
						LEFT OUTER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(lc.`content_id`=cp.`content_id`)
					WHERE $whereSql
					ORDER BY co.`orders_id` DESC";
			$ret = $this->mDb->getAssoc( $sql, $bindVars  );
		}

		return $ret;
	}

	public function getOrdersToAddress( $pAddress, $pFromStatusId ) {
		$ret = array();

		$addressHash = array();
		$bindVars = array( $pFromStatusId );
		foreach( array( 'street_address'=>'street_address', 'suburb'=>'suburb', 'city'=>'city', 'postcode'=>'postcode', 'state'=>'state', 'countries_name' => 'country' ) as $key=>$col ) {
			if( isset( $pAddress[$key] ) ) {
				$addressHash[$col] = $pAddress[$key];
				$bindVars[] = $pAddress[$key];
			}
		}

		if( !empty( $addressHash ) ) {
			$sql = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_status_id > 0 AND orders_status_id <= ? AND delivery_".implode( '=? AND delivery_', array_keys( $addressHash ) ).'=?';
			$ret = $this->mDb->getCol( $sql, $bindVars, -1, -1, -1 );
		}

		return $ret;
	}
}

CommerceOrderManager::loadSingleton();

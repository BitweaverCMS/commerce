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

class CommerceOrderManager extends BitBase {

	function CommerceOrderManager() {
		parent::__construct();
	}

	function getProductHistory( $pListHash ) {
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
}

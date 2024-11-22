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

	private function verifyOrdersPayment( &$pParamHash, $pOrder ) {
		$ret = FALSE;

		global $gBitUser;
		$pParamHash['payment_store']['user_id'] = $gBitUser->mUserId;
		$pParamHash['payment_store']['customers_id'] = $pOrder->customer['customers_id'];
		$pParamHash['payment_store']['ip_address'] = $_SERVER['REMOTE_ADDR'];

		$columns = array( 
			'oID' => 'orders_id', 
			'orders_id' => 'orders_id', 
			'payment_ref_id' => 'payment_ref_id', 
			'payment_result' => 'payment_result', 
			'payment_auth_code' => 'payment_auth_code', 
			'payment_message' => 'payment_message', 
			'payment_amount' => 'payment_amount', 
			'payment_date' => 'payment_date', 
			'customers_id' => 'customers_id', 
			'is_success' => 'is_success', 
			'customers_email' => 'customers_email', 
			'payment_type' => 'payment_type', 
			'payment_owner' => 'payment_owner', 
			'payment_number' => 'payment_number', 
			'payment_expires' => 'payment_expires', 
			'transaction_date' => 'transaction_date', 
			'payment_module' => 'payment_module', 
			'payment_mode' => 'payment_mode', 
			'payment_status' => 'payment_status', 
			'trans_parent_ref_id' => 'trans_parent_ref_id', 
			'payment_currency' => 'payment_currency', 
			'exchange_rate' => 'exchange_rate', 
			'payment_parent_ref_id' => 'payment_parent_ref_id', 
			'pending_reason' => 'pending_reason', 
			'first_name' => 'first_name', 
			'last_name' => 'last_name', 
			'address_company' => 'address_company', 
			'address_name' => 'address_name', 
			'address_street_address' => 'address_street', 
			'address_suburb' => 'address_suburb', 
			'address_city' => 'address_city', 
			'state' => 'address_state', 
			'address_postcode' => 'address_postcode', 
			'address_country' => 'address_country', 
			'num_cart_items' 
		);


		if( BitBase::verifyIdParameter( $pParamHash, 'country_id' ) ) {
			$pParamHash['address_country'] = zen_get_country_name( $pParamHash['country_id'] );
		}
		if( empty( $pParamHash['payment_status'] ) ) {
			$pParamHash['payment_status'] = ($pParamHash['is_success'] == 'y' ? 'PAID' : 'unsuccessful');
		}

		foreach( $columns as $inputKey => $colName ) {
			if( isset( $pParamHash[$inputKey] ) ) {
				$pParamHash['payment_store'][$colName] = $pParamHash[$inputKey];
			}
		}

		// No bounds checking yet
		$ret = TRUE;

		return $ret;
	}

	public function storeOrdersPayment( &$pParamHash, $pOrder ) {
		$ret = FALSE;
		$sessionParams = array();

		if( !empty( $pParamHash['adjust_total'] ) ) {
			$ret = $pOrder->adjustOrder( $pParamHash, $sessionParams );
		} else {
			$ret = TRUE;
			$this->mDb->StartTrans();
			if( $this->verifyOrdersPayment( $pParamHash, $pOrder ) ) {
				$ordersUpdate = array();
				$this->mDb->associateInsert( TABLE_ORDERS_PAYMENTS, $pParamHash['payment_store'] );
			
				$statusHash['comments'] = trim( "New Payment Recorded:" . $pParamHash['payment_number'] . "\n\n" . BitBase::getParameter( $pParamHash, 'comments', NULL ) );
				$statusHash['status'] = BitBase::getParameter( $pParamHash, 'status' );
				$pOrder->updateStatus( $statusHash );

				if( $pOrder->getField( 'amount_due' ) ) {
					$amountDue = ($pOrder->getField( 'amount_due' ) - $pParamHash['payment_store']['payment_amount']);
					$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `amount_due` = ? WHERE `orders_id` = ?", array( $amountDue, $pOrder->mOrdersId ) );
				}
			}
			$this->mDb->CompleteTrans();
		}

		return $ret;
	}

	private function prepGetDueList(&$pListHash){
		// keep a copy of user_id for later...
		$userId = parent::getParameter( $pListHash, 'user_id' );
		parent::prepGetList($pListHash);
	}

	public function getDueOrders( $pListHash = array() ) {
		global $gBitUser;

		$ret = array();
		$whereSql = '';
$this->prepGetDueList( $pListHash );
		$bindVars = array();

		if( !$gBitUser->hasPermission( 'p_bitcommerce_admin' ) ) {
			$whereSql .= ' AND co.`customers_id`=? ';
			$bindVars[] = $gBitUser->mUserId;
		}

		if( !empty( $pListHash['payment_number'] ) ) {
			$whereSql .= ' AND cop.`payment_number`=? ';
			$bindVars[] = $pListHash['payment_number'];
		}

		if( $rs = $this->mDb->query( "SELECT * FROM " . TABLE_ORDERS . " co INNER JOIN " . TABLE_ORDERS_PAYMENTS . " cop ON (co.`orders_id`=cop.`orders_id`) WHERE co.`orders_status_id` > 0 AND co.`amount_due` > 0 $whereSql ORDER BY cop.`payment_number`", $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[$row['customers_id']][$row['payment_number']][] = $row;
			}
		}

		return $ret;
	}

	private function verifyPayment( &$pParamHash, $pOrder ) {
		global $gBitUser;

		$pParamHash['payment_store']['user_id'] = $gBitUser->mUserId;
		$pParamHash['payment_store']['customers_id'] = $gOrder->getField( 'customers_id' );
		$pParamHash['payment_store']['orders_id'] = $gOrder->getField( 'orders_id' );

		return( empty( $pParamHash['payment_store']['errors'] ) );
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

	function getOrdersToAddress( $pAddress, $pFromStatusId ) {
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

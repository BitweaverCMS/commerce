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
//  $Id: CommerceCommission.php,v 1.9 2009/04/27 19:23:35 spiderr Exp $
//

require_once( KERNEL_PKG_PATH.'BitBase.php' );

define( 'COMMISSION_TYPE_PRODUCT_SALE', 'product sale' );

class CommerceCommission extends BitBase {

	function verifyPayment( &$pParamHash ) {
		global $gBitUser;
		$pParamHash['payment_store']['payee_user_id'] = $pParamHash['user_id'];
		$pParamHash['payment_store']['payer_user_id'] = $gBitUser->mUserId;
		if( empty( $pParamHash['period_start_date'] ) ) {
			$pParamHash['period_start_date'] = '1970-01-01';
		}
		$pParamHash['payment_store']['period_start_date'] =  $this->mDb->mDb->DBTimeStamp( $pParamHash['period_start_date'].' 00:00:00-0' );
		$pParamHash['payment_store']['period_end_date'] = $this->mDb->mDb->DBTimeStamp( $pParamHash['period_end_date'].' 23:59:59-0' );
		$pParamHash['payment_store']['payment_date'] = $this->mDb->NOW();
		$pParamHash['payment_store']['payment_amount'] = preg_replace( '/[^\d\.]/', '', $pParamHash['payment_amount'] );
		$pParamHash['payment_store']['payment_method'] = $pParamHash['payment_method'];
		$pParamHash['payment_store']['payment_reference_number'] = (!empty( $pParamHash['payment_reference_number'] ) ? $pParamHash['payment_reference_number'] : NULL);
		$pParamHash['payment_store']['payment_note'] = $pParamHash['payment_note'];
		if( !empty( $pParamHash['commission_type'] ) ) {
			$pParamHash['payment_store']['commission_type'] = $pParamHash['commission_type'];
			if( empty( $pParamHash['commissions_payments_id'] ) && $pParamHash['payment_store']['commission_type'] == COMMISSION_TYPE_PRODUCT_SALE ) {
				$sql = "SELECT cop.`orders_products_id`, cop.`products_commission` * cop.`products_quantity` AS products_commissions_total
						FROM " . TABLE_ORDERS . " co  
							INNER JOIN	" . TABLE_ORDERS_PRODUCTS . " cop ON (co.`orders_id`=cop.`orders_id`)
							INNER JOIN	" . TABLE_PRODUCTS . " cp ON (cp.`products_id`=cop.`products_id`)
							INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (cp.`content_id`=lc.`content_id`)
						WHERE lc.`user_id`=? AND co.`date_purchased` > ? AND co.`date_purchased` <= ?";

				$payedProducts = $this->mDb->getAssoc( $sql, array( $pParamHash['payment_store']['payee_user_id'], $pParamHash['payment_store']['period_start_date'], $pParamHash['payment_store']['period_end_date'] ) );
				$totalPayed = 0;
				foreach( $payedProducts AS $ordersProductsId => $productsCommissionsTotal ) {
					$this->mDb->query( "UPDATE  " . TABLE_ORDERS_PRODUCTS . " SET `commissions_payments_id`=? WHERE `orders_products_id`=?", array( $pParamHash['commissions_payments_id'], $ordersProductsId ) );
					$totalPayed += $productsCommissionsTotal;
				}

				if( (int)$totalPayed != (int)$pParamHash['payment_amount'] ) {
					$this->mErrors['commissions_payment'] = "Payment amount is not equal to products commissions ($totalPayed != $pParamHash[payment_amount] user " . $pParamHash['payment_store']['payee_user_id'] . ")";
					bit_log_error( $this->mErrors['commissions_payment'] );
				}
			}				
		} else {
			$this->mErrors['commissions_payment'] = tra( 'Commission type not specified' );
		}

		return( count( $this->mErrors ) == 0 );
	}

	function storePayment( &$pParamHash ) {
		$this->mDb->StartTrans();
		if( $this->verifyPayment( $pParamHash ) ) {
			if( @BitBase::verifyId( $pParamHash['commissions_payments_id'] ) ) {
				$this->mDb->associateUpdate( TABLE_COMMISSIONS_PAYMENTS, $pParamHash['payment_store'], array( 'commissions_payments_id' =>$pParamHash['commissions_payments_id'] ) );
			} else {
				$pParamHash['commissions_payments_id'] = $this->mDb->GenID( 'com_commissions_payments_id_seq' );
				$pParamHash['payment_store']['commissions_payments_id'] = $pParamHash['commissions_payments_id'];
				$this->mDb->associateInsert( TABLE_COMMISSIONS_PAYMENTS, $pParamHash['payment_store'] );
			}
			switch( $pParamHash['payment_store']['payment_method'] ) {
				case 'storecredit':
					if( $this->mDb->getAssoc( "SELECT * FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id`=?", array( $pParamHash['payment_store']['payee_user_id'] ) ) ) {
						$this->mDb->query( "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET `amount`=`amount`+? WHERE `customer_id`=?", array( $pParamHash['payment_store']['payment_amount'], $pParamHash['payment_store']['payee_user_id'] ) );
					} else {
						$this->mDb->query( "INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (`amount`,`customer_id`) VALUES (?,?)", array( $pParamHash['payment_store']['payment_amount'], $pParamHash['payment_store']['payee_user_id'] ) );
					}
					break;
				default:
					break;
			}
			$this->mDb->CompleteTrans();
		} else {
			$this->mDb->RollbackTrans();
		}
		return( count($this->mErrors) == 0 );
	}

	function getProductCommissionHistory( $pListHash ) {
		$ret = array();
		if( !empty( $pListHash['user_id'] ) ) {
			$pListHash['commission_type'] = COMMISSION_TYPE_PRODUCT_SALE;
			$sql = "SELECT cop.`orders_products_id` AS `hash_key`, co.*,cop.* 
					FROM " . TABLE_ORDERS . " co  
						INNER JOIN	" . TABLE_ORDERS_PRODUCTS . " cop ON (co.`orders_id`=cop.`orders_id`)
						INNER JOIN	" . TABLE_PRODUCTS . " cp ON (cp.`products_id`=cop.`products_id`)
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (cp.`content_id`=lc.`content_id`)
					WHERE lc.`user_id`=? AND cop.`products_commission` IS NOT NULL AND cop.`products_commission` > 0
					ORDER BY co.`date_purchased` ASC";
			if( $sales = $this->mDb->getAssoc( $sql, array( $pListHash['user_id'] ) ) ) {
				foreach( array_keys( $sales ) as $hashKey ) {
					$sales[$hashKey]['purchased_epoch'] = strtotime($sales[$hashKey]['date_purchased'] );
				}
			}

			if( $commissions = $this->getCommissionPayments( $pListHash ) ) {
				foreach( array_keys( $commissions ) as $commId ) {
					$commissions[$commId]['period_end_epoch'] = strtotime( $commissions[$commId]['period_end_date'] );
				}
			}
			$commission = current( $commissions );
			foreach( $sales AS $sale ) {
				if( !empty( $commission ) && ((int)$commission['period_end_epoch'] < (int)$sale['purchased_epoch']) ) {
					array_push( $ret, $commission );
					$commission = next( $commissions );
				}
				array_push( $ret, $sale );
			}
			// add the last commission if no sales since last payment
			if( !empty( $commission ) ) {
				array_push( $ret, $commission );
			}
			$ret = array_reverse( $ret );
		}
		return( $ret );
	}

	function getCommissionPayments( $pListHash ) {
		$ret = array();
		$whereSql  = '';
		$havingSql  = '';
		$bindVars = array( $pListHash['user_id'] );

		if( !empty( $pListHash['commission_type'] ) ) {
			$whereSql = ' AND `commission_type` = ?';
			$bindVars[] = $pListHash['commission_type'];
		}
		
		$sql = "SELECT `commissions_payments_id` AS `hash_key`, ccp.* 
				FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp 
				WHERE `payee_user_id`=? $whereSql 
				ORDER BY `period_end_date` ASC";
		$ret = $this->mDb->getAssoc( $sql, $bindVars );
		return $ret;
	}

	function getCommissions( $pListHash ) {
		$whereSql  = '';
		$havingSql  = '';
		$bindVars = array();

		if( !empty( $pListHash['commissions_due'] ) ) {
			$whereSql .= " AND (co.`date_purchased` > (SELECT COALESCE( MAX(ccp.`period_end_date`), '1970-01-01 00:00:00-0' ) FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp WHERE ccp.payee_user_id=lc.`user_id`) )";
			$throughDate = $this->mDb->sqlIntToTimestamp( $pListHash['commissions_due'] );
		} else {
			$throughDate = $this->mDb->NOW();
		}

		if( !empty( $pListHash['sum_floor'] ) ) {
			$havingSql .= ' HAVING SUM(cop.`products_commission` * cop.`products_quantity`) >= ?';
			$bindVars[] = $pListHash['sum_floor'];
		}

		if( !empty( $pListHash['commissions_delay'] ) ) {
			$whereSql .= ' AND co.`date_purchased` < '.$throughDate;
		}

		$sql = "SELECT lc.`user_id` AS `hash_key`, lc.`user_id`, uu.`content_id`, uu.`real_name`, uu.`login`, uu.`email`, lcp.`pref_value` AS `payment_method`, SUM(cop.`products_commission` * cop.`products_quantity`) AS `commission_sum`
				FROM " . TABLE_ORDERS . " co  
					INNER JOIN	" . TABLE_ORDERS_PRODUCTS . " cop ON (co.`orders_id`=cop.`orders_id`)
					INNER JOIN	" . TABLE_PRODUCTS . " cp ON (cp.`products_id`=cop.`products_id`)
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (cp.`content_id`=lc.`content_id`)
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=lc.`user_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_prefs` lcp ON (lcp.`content_id`=uu.`content_id` AND lcp.`pref_name`='commissions_payment_method')
				WHERE cop.`products_commission` IS NOT NULL AND cop.`products_commission` > 0
				$whereSql 
				GROUP BY lc.`user_id`, uu.`content_id`, uu.`real_name`, uu.`login`, uu.`email`, lcp.`pref_value`
				$havingSql ";
		$ret = $this->mDb->getAssoc( $sql, $bindVars );

		if( !empty( $pListHash['commissions_due'] ) ) {
			foreach( array_keys( $ret ) AS $userId ) {
				$lastTimestamp = $this->mDb->getOne( "SELECT MAX(ccp.`period_end_date`) FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp WHERE ccp.`payee_user_id`=? AND ccp.`commission_type`=?", array( $userId, COMMISSION_TYPE_PRODUCT_SALE ) );
				$ret[$userId]['last_period_end_date'] = substr( $lastTimestamp, 0, strpos( $lastTimestamp, ' ' ) );
			}
		}

		return $ret;
	}

}

?>

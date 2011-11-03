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

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCommissionBase.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/functions/functions_customers.php' );

define( 'COMMISSION_TYPE_PRODUCT_SALE', 'product sale' );

class CommerceProductCommission extends CommerceCommissionBase {

	function __construct() {
		$this->mCommissionType = COMMISSION_TYPE_PRODUCT_SALE;
		parent::__construct();
	}

	function verifyPayment( &$pParamHash ) {
		global $gBitUser;
		if( parent::verifyPayment( $pParamHash ) ) {
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

		return( count( $this->mErrors ) == 0 );
	}

	// Get a mixed list of commissions and payments in alphabetical order for a given user
	function getUserHistory( $pListHash ) {
		$ret = array();
		if( !empty( $pListHash['user_id'] ) ) {
			$sql = "SELECT cop.`orders_products_id` AS `hash_key`, co.*,cop.*, cop.`products_commission` AS `unit_commission_earned`, ".$this->mDb->SqlTimestampToInt('date_purchased')." AS `purchased_epoch`, '".BITCOMMERCE_PKG_URL."' || cop.`products_id` AS `products_link` 
					FROM " . TABLE_ORDERS . " co  
						INNER JOIN	" . TABLE_ORDERS_PRODUCTS . " cop ON (co.`orders_id`=cop.`orders_id`)
						INNER JOIN	" . TABLE_PRODUCTS . " cp ON (cp.`products_id`=cop.`products_id`)
						INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (cp.`content_id`=lc.`content_id`)
					WHERE lc.`user_id`=? AND cop.`products_commission` IS NOT NULL AND cop.`products_commission` > 0
					ORDER BY co.`date_purchased` ASC";
			if( $sales = $this->mDb->getAssoc( $sql, array( $pListHash['user_id'] ) ) ) {
				if( $commissions = $this->getCommissionPayments( $pListHash ) ) {
					$commission = current( $commissions );
				}
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
		}
		return( $ret );
	}

	// Get a list of all aggregate commissions grouped by user
	function getCommissions( $pListHash ) {
		$whereSql  = '';
		$havingSql  = '';
		$bindVars = array();

		if( !empty( $pListHash['commissions_due'] ) ) {
			$whereSql .= " AND (co.`date_purchased` > (SELECT COALESCE( MAX(ccp.`Period_end_date`), '1970-01-01 00:00:00-0' ) FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp WHERE ccp.payee_user_id=lc.`user_id` AND ccp.commission_type='".$this->mCommissionType."') )";
			$throughDate = $this->mDb->sqlIntToTimestamp( $pListHash['commissions_due'] );
		} else {
			$throughDate = $this->mDb->NOW();
		}

		if( !empty( $pListHash['sum_floor'] ) ) {
			$havingSql .= ' HAVING SUM(cop.`products_commission` * cop.`products_quantity`) >= ?';
			$bindVars[] = $pListHash['sum_floor'];
		}

		if( !empty( $pListHash['commissions_delay'] ) ) {
			$whereSql .= ' AND co.`date_purchased` <= '.$throughDate;
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

		if( $ret = $this->mDb->getAssoc( $sql, $bindVars ) ) {
			$this->cleanupGetCommissions( $ret, $pListHash );
		}

		return $ret;
	}

}

?>

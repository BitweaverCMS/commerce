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

require_once( KERNEL_PKG_PATH.'BitBase.php' );

class CommerceCommissionBase extends BitBase {

	// Must be set by all derivative classes
	public $mCommissionType = NULL;

	function verifyPayment( &$pParamHash ) {
		global $gBitUser;
		$pParamHash['payment_store']['payee_user_id'] = $pParamHash['user_id'];
		$pParamHash['payment_store']['payer_user_id'] = $gBitUser->mUserId;
		if( empty( $pParamHash['period_start_date'] ) ) {
			$pParamHash['period_start_date'] = '1970-01-01';
		}
		if( empty( $pParamHash['period_end_date'] ) ) {
			$pParamHash['period_end_date'] = date( 'Y-m-d' );
		}
		$pParamHash['payment_store']['period_start_date'] =  $this->mDb->mDb->DBTimeStamp( $pParamHash['period_start_date'].' 00:00:00' );
		$pParamHash['payment_store']['period_end_date'] = date( 'c', strtotime( $pParamHash['period_end_date'].' +1 day ' ) - 1 ); // $this->mDb->mDb->DBTimeStamp( $pParamHash['period_end_date'].' 23:59:59 '.date('O') );
		$pParamHash['payment_store']['payment_date'] = $this->mDb->NOW();
		$pParamHash['payment_store']['payment_amount'] = preg_replace( '/[^\d\.]/', '', $pParamHash['payment_amount'] );
		$pParamHash['payment_store']['payment_method'] = $pParamHash['payment_method'];
		$pParamHash['payment_store']['payment_reference_number'] = (!empty( $pParamHash['payment_reference_number'] ) ? $pParamHash['payment_reference_number'] : NULL);
		$pParamHash['payment_store']['payment_note'] = $pParamHash['payment_note'];
		$pParamHash['payment_store']['commission_type'] = $this->mCommissionType;

		return( count( $this->mErrors ) == 0 );
	}

	function storePayment( &$pParamHash ) {
		$this->StartTrans();
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
			$this->CompleteTrans();
		} else {
			$this->mDb->RollbackTrans();
		}
		return( count($this->mErrors) == 0 );
	}

	function getCommissionPayments( $pListHash ) {
		$ret = array();
		$whereSql  = '';
		$havingSql  = '';
		$bindVars = array( $pListHash['user_id'], $this->mCommissionType );
		
		$sql = "SELECT `commissions_payments_id` AS `hash_key`, ccp.*, ".$this->mDb->SqlTimestampToInt('period_end_date')." AS `period_end_epoch` 
				FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp 
				WHERE `payee_user_id`=? AND `commission_type` = ? 
				ORDER BY `period_end_date` ASC";
		$ret = $this->mDb->getAssoc( $sql, $bindVars );
		return $ret;
	}

	// get additional data for getCommissions data
	function cleanupGetCommissions( &$pCommissions, $pListHash ) {
		if( $pCommissions ) {
			if( !empty( $pListHash['commissions_due'] ) ) {
				$date = getdate( $pListHash['commissions_due'] );
				$periodEndDate = $date['year'].'-'.str_pad( $date['mon'], 2, '0', STR_PAD_LEFT ).'-'.str_pad( $date['mday'], 2, '0', STR_PAD_LEFT );
			} else {
				$periodEndDate = NULL;
			}
			foreach( array_keys( $pCommissions ) AS $userId ) {
				if( $lastTimestamp = $this->mDb->getOne( "SELECT MAX(ccp.`period_end_date`) FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp WHERE ccp.`payee_user_id`=? AND ccp.`commission_type`=?", array( $userId, $this->mCommissionType ) ) ) {
					$pCommissions[$userId]['last_period_end_epoch'] = strtotime( $lastTimestamp ) + 1;
					$pCommissions[$userId]['last_period_end_date'] = date( 'Y-m-d', $pCommissions[$userId]['last_period_end_epoch'] );
				}
				$pCommissions[$userId]['period_end_date'] = $periodEndDate;
				$pCommissions[$userId]['commission_type'] = $this->mCommissionType;

				switch( $pCommissions[$userId]['payment_method'] ) {
					case 'paypal':
						$pCommissions[$userId]['commissions_paypal_address'] = LibertyContent::getPreference( 'commissions_paypal_address', NULL, $pCommissions[$userId]['content_id'] );
						break;
					case 'worldpay':
						$pCommissions[$userId]['commissions_worldpay_address'] = LibertyContent::getPreference( 'commissions_worldpay_address', NULL, $pCommissions[$userId]['content_id'] );
						break;
					case 'storecredit':
						break;
					case 'check':
						$pCommissions[$userId]['commissions_check_address'] = LibertyContent::getPreference( 'commissions_check_address', NULL, $pCommissions[$userId]['content_id'] );
						break;
				}
			}
		}
	}

}

?>

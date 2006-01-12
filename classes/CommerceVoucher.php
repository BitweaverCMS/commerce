<?php

require_once( KERNEL_PKG_PATH.'BitBase.php' );

class CommerceVoucher extends BitBase {
	var $pCategoryId;

	function CommerceVoucher( $pCouponId=NULL ) {
		$this->mCouponId = $pCouponId;
		BitBase::BitBase();
	}

	function load( $pCode=NULL ) {
		$this->mInfo = array();
		if( !empty( $pCode ) || $this->isValid() ) {
			$error = true;
			if( !empty( $pCode ) ) {
				$lookup = 'UPPER(`coupon_code`)';
				$bindVars =  array( strtoupper( $pCode ) );
			} else {
				$lookup = 'coupon_id';
				$bindVars =  array( $this->mCouponId );
			}

			$query = "SELECT * FROM " . TABLE_COUPONS . " WHERE $lookup=? AND coupon_active='Y'";
			if( ($this->mInfo = $this->mDb->getRow( $query, $bindVars )) ) {
				$this->mCouponId = $this->mInfo['coupon_id'];
			}
		}
		return( count( $this->mInfo ) );
	}

	function isValid() {
		return( !empty( $this->mCouponId ) && is_numeric( $this->mCouponId ) );
	}

	function isRedeemable() {
		if( $this->isValid() ) {
			$couponStart = $this->mDb->getOne("select coupon_start_date from " . TABLE_COUPONS . "
										where coupon_start_date <= now() and
										coupon_id=?", array( $this->mCouponId ) );
			if ( !$couponStart ) {
				$this->mError['redeem_error'] = TEXT_INVALID_STARTDATE_COUPON;
			}

			$couponExpire=$this->mDb->getOne("SELECT coupon_expire_date FROM " . TABLE_COUPONS . "
									   WHERE coupon_expire_date >= now() AND coupon_id=?", array( $this->mCouponId ) );
			if ( !$couponExpire ) {
				$this->mError['redeem_error'] = TEXT_INVALID_FINISDATE_COUPON;
			}

			if( $this->getField( 'uses_per_coupon' ) > 0 ) {
				$query = "SELECT COUNT( `coupon_id` ) from " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id=?";
				$redeemCount = $this->mDb->getOne( $query, array( $this->mCouponId ) );
				if( $redeemCount >= $this->getField( 'uses_per_coupon' ) ) {
					$this->mError['redeem_error'] =TEXT_INVALID_USES_COUPON . $this->getField( 'uses_per_coupon' ) . TIMES;
				}
			}

			if ( $this->getField( 'uses_per_user' ) > 0 ) {
				$query = "SELECT COUNT( `coupon_id` ) from " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = ? AND customer_id = ?";
				$redeemCountCustomer = $this->mDb->getOne( $query, array( $this->mCouponId, $_SESSION['customer_id'] ) );
				if ( $redeemCountCustomer >= $this->getField('uses_per_user') && $this->getField('uses_per_user') > 0) {
					$this->mError['redeem_error'] = TEXT_INVALID_USES_USER_COUPON . $this->getField( 'uses_per_user' ) . TIMES;
				}
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	function getGiftAmount( $pFormat=TRUE ) {
		global $gBitUser, $gBitDb, $currencies;
		$ret = NULL;
		if( $gBitUser->isRegistered() ) {
			$gv_query = "select `amount`
						from " . TABLE_COUPON_GV_CUSTOMER . "
						where `customer_id` = ?";
			if( ($ret = $gBitDb->getOne($gv_query, array( $gBitUser->mUserId ) )) && $pFormat ) {
				$ret = $currencies->format( $ret );
			}
		}
		return $ret;
	}

	function getCouponAmount( $pFormat=TRUE ) {
		global $gBitDb, $currencies;
		$ret = NULL;
		if( !empty( $_SESSION['gv_id'] ) ) {
			$gv_query = "select `coupon_amount`
						from " . TABLE_COUPONS . "
						where `coupon_id` = ?";
			if( ($ret = $gBitDb->getOne($gv_query, array( $_SESSION['gv_id'] ) )) && $pFormat ) {
				$ret = $currencies->format( $ret );
			}
		}
		return $ret;
	}

	function redeemCoupon( $pCode ) {
		global $gBitDb, $gBitUser;
		$ret = FALSE;
		if( !empty( $pCode ) ) {
			$error = true;

			$gv_query = "select `coupon_id`, `coupon_amount`
						from " . TABLE_COUPONS . "
						where `coupon_code` = ? AND `coupon_active`='Y'";
			if( $coupon = $gBitDb->getRow($gv_query, array( $pCode ) ) ) {
				$redeem_query = "select coupon_id
								 from ". TABLE_COUPON_REDEEM_TRACK . "
								 where coupon_id = ?";
				$isRedeemed = $gBitDb->getOne( $redeem_query, array( $coupon['coupon_id'] ) );
				if( !$isRedeemed ) {
			// check for required session variables
					$_SESSION['gv_id'] = $coupon['coupon_id'];
					$error = false;
				}
			}
			if( !$error ) {
			// Update redeem status
				$gBitDb->StartTrans();
				$gv_query = "insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip)
							values ( ?, ?, now(), ? )";
				$gBitDb->query($gv_query, array( $coupon['coupon_id'], $gBitUser->mUserId, $_SERVER['REMOTE_ADDR'] ) );

				$gv_update = "update " . TABLE_COUPONS . "
							set coupon_active = 'N'
							where coupon_id = ?";
				$gBitDb->query($gv_update, array( $coupon['coupon_id'] ) );

				zen_gv_account_update( $_SESSION['customer_id'], $_SESSION['gv_id'] );
				$gBitDb->CompleteTrans();
				$ret = $coupon['coupon_amount'];
			}
		}
		return $ret;
	}
}

?>

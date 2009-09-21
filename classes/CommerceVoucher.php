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

	////
	// Create a Coupon Code. length may be between 1 and 16 Characters
	// $salt needs some thought.

	function generateCouponCode( $salt="secret", $length = SECURITY_CODE_LENGTH ) {
		global $gBitDb;
		$ccid = md5(uniqid("","salt"));
		$ccid .= md5(uniqid("","salt"));
		$ccid .= md5(uniqid("","salt"));
		$ccid .= md5(uniqid("","salt"));
		srand((double)microtime()*1000000); // seed the random number generator
		$random_start = @rand(0, (128-$length));
		do {
			$code = substr($ccid, $random_start,$length);
			$query = "SELECT coupon_code FROM " . TABLE_COUPONS . " WHERE coupon_code = ?";
			$exists = $gBitDb->getOne( $query, array( $code ) );
		} while( $exists );
		return $code;
	}

	function isRedeemable() {
		if( $this->isValid() ) {
			$couponStart = $this->mDb->getOne("select coupon_start_date from " . TABLE_COUPONS . "
										where coupon_start_date <= now() and
										coupon_id=?", array( $this->mCouponId ) );
			if ( !$couponStart ) {
				$this->mErrors['redeem_error'] = TEXT_INVALID_STARTDATE_COUPON;
			}

			$isExpired = $this->mDb->getOne("SELECT coupon_expire_date FROM " . TABLE_COUPONS . "
									   WHERE coupon_expire_date < now() AND coupon_id=?", array( $this->mCouponId ) );
			if ( $isExpired ) {
				$this->mErrors['redeem_error'] = 'This coupon has expired.';
			}

			if( $this->getField( 'uses_per_coupon' ) ) {
				$query = "SELECT COUNT( `coupon_id` ) from " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id=?";
				$redeemCount = $this->mDb->getOne( $query, array( $this->mCouponId ) );
				if( $redeemCount >= $this->getField( 'uses_per_coupon' ) ) {
					$this->mErrors['redeem_error'] = 'This coupon has been used the maximum number of times allowed.';
				}
			}

			if ( $this->getField( 'uses_per_user' ) ) {
				$query = "SELECT COUNT( `coupon_id` ) from " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = ? AND customer_id = ?";
				$redeemCountCustomer = $this->mDb->getOne( $query, array( $this->mCouponId, $_SESSION['customer_id'] ) );
				if ( $redeemCountCustomer >= $this->getField('uses_per_user') && $this->getField('uses_per_user') > 0) {
					$this->mErrors['redeem_error'] = 'You have used coupon code the maximum number of times allowed per customer.';
				}
			}
		} else {
			$this->mErrors['redeem_error'] = "Invalid coupon code";
		}

		return( count( $this->mErrors ) === 0 );
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

	// This method is very similar to customerSendCoupon, however is done via
	// admin or automated means. Eventually these two functions should be
	// merged/simplified
	function adminSendCoupon( $pParamHash ) {
		global $gBitUser, $gBitCustomer, $gBitSystem, $currencies;

		require_once( BITCOMMERCE_PKG_PATH. 'admin/'. DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/gv_mail.php' );
		require_once( BITCOMMERCE_PKG_PATH. 'admin/'. DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/email_extras.php' );
		$ret = FALSE;
		if( !empty( $pParamHash['email_to'] ) && !empty( $pParamHash['amount'] ) )  {
			$this->mDb->StartTrans();

			if( empty( $pParamHash['from'] ) ) {
				$pParamHash['from'] = EMAIL_FROM;
			}
			$from = zen_db_prepare_input( $pParamHash['from'] );
			if( empty( $pParamHash['subject'] ) ) {
				$pParamHash['subject'] = $gBitSystem->getConfig( 'site_title' ).' '.tra( "Gift Certificate" );
			}
			$subject = zen_db_prepare_input( $pParamHash['subject'] );
			$mailSentTo = $pParamHash['email_to'];
			$id1 = $this->generateCouponCode( $pParamHash['email_to'] );
			if( empty( $pParamHash['message'] ) ) {
				$pParamHash['message'] = trim( stripslashes( strip_tags( TEXT_GV_ANNOUNCE ) ) );
			}
			$message = zen_db_prepare_input($pParamHash['message']);
			$message .= "\n\n" . TEXT_GV_WORTH  . $currencies->format($pParamHash['amount']) . "\n\n";
			$message .= TEXT_TO_REDEEM;
			$message .= TEXT_WHICH_IS . ' ' . $id1 . ' ' . TEXT_IN_CASE . "\n\n";

			$html_msg['GV_WORTH']  = TEXT_GV_WORTH  . $currencies->format($pParamHash['amount']) .'<br />';
			$html_msg['GV_REDEEM'] = TEXT_TO_REDEEM . TEXT_WHICH_IS . ' <strong>' . $id1 . '</strong> ' . TEXT_IN_CASE . "\n\n";

			if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
				$message .= HTTP_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1 . "\n\n";
				$html_msg['GV_CODE_URL']  = '<a href="'.HTTP_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1.'">' .TEXT_CLICK_TO_REDEEM . '</a>'. "&nbsp;";
			} else {
				$message .= HTTP_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 . "\n\n";
				$html_msg['GV_CODE_URL']  =  '<a href="'. HTTP_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 .'">' .TEXT_CLICK_TO_REDEEM . '</a>' . "&nbsp;";
			}
			$message .= TEXT_OR_VISIT . HTTP_SERVER  . DIR_WS_CATALOG  . TEXT_ENTER_CODE . "\n\n";
			$html_msg['GV_CODE_URL']  .= TEXT_OR_VISIT .  '<a href="'.HTTP_SERVER  . DIR_WS_CATALOG.'">' . STORE_NAME . '</a>' . TEXT_ENTER_CODE;
			$html_msg['EMAIL_MESSAGE_HTML'] = !empty( $pParamHash['message_html'] ) ? zen_db_prepare_input($pParamHash['message_html']) : '';
			$html_msg['EMAIL_FIRST_NAME'] = ''; // unknown, since only an email address was supplied
			$html_msg['EMAIL_LAST_NAME']  = ''; // unknown, since only an email address was supplied
			// disclaimer
			$message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

			// Now create the coupon main entry
			$this->mDb->query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) values ( ?, 'G', ?, now())", array( $id1, $pParamHash['amount'] ) );
			$insert_id = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );
			$this->mDb->query( "INSERT INTO " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ( ?, '0', ?, ?, now() )", array( $insert_id, $gBitUser->getDisplayName(), $pParamHash['email_to'] ) );

			// Send the emails
			zen_mail( '', $pParamHash['email_to'], $subject , $message, $from, $from, $html_msg, 'gv_mail' );
			if (SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO != '') {
				zen_mail('', SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO, SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, $from, $from, $html_msg, 'gv_mail_extra');
			}

			if( !empty( $pParamHash['oID'] ) ) {
				$order = new order( $pParamHash['oID'] );
				$status['comments'] = 'A $'.$pParamHash['amount'].' Gift Certificate ( '.$id1.' ) was emailed to '.$pParamHash['email_to'].' in relation to order '.$pParamHash['oID'].'';
				$order->updateStatus( $status );
			}
			$ret = TRUE;
			$this->mDb->CompleteTrans();
		}
		return $ret;
	}

	function customerSendCoupon( $pFromUser, $pRecipient, $pAmount ) {
		global $gBitDb, $gBitSmarty, $gCommerceSystem, $currencies;
		$ret = NULL;

		$gBitDb->StartTrans();

		$code = CommerceVoucher::generateCouponCode();
		$gvBalance = CommerceVoucher::getGiftAmount( FALSE );
		$newBalance = $gvBalance - $pAmount;
		if ($new_amount < 0) {
			$error = ERROR_ENTRY_AMOUNT_CHECK;
		} else {
			$gv_query = "UPDATE " . TABLE_COUPON_GV_CUSTOMER . "
						 SET `amount` = ?
						 WHERE `customer_id` = ?";
			$gBitDb->query( $gv_query, array( $newBalance, $pFromUser->mUserId ) );

			$gv_query = "INSERT INTO " . TABLE_COUPONS . " (`coupon_type`, `coupon_code`, `date_created`, `coupon_amount`) values ('G', ?, NOW(), ?)";
			$gv = $gBitDb->query($gv_query, array( $code, $pAmount ) );
			$gvId = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );

			$gv_query="insert into " . TABLE_COUPON_EMAIL_TRACK . "	(`coupon_id`, `customer_id_sent`, `emailed_to`, `date_sent`)
						values ( ?, ?, ?, now())";
			$gBitDb->query( $gv_query, array( $gvId, $pFromUser->mUserId, $pRecipient['email'] ) );
			$ret = $code;

			$gv_email_subject = tra( 'A gift from' ).' '.$pFromUser->getDisplayName().' '.tra( 'to' ).' '.$gCommerceSystem->getConfig( 'STORE_NAME' );

			$gBitSmarty->assign( 'gvCode', $code );
			$gBitSmarty->assign( 'gvSender', $pFromUser->getDisplayName() );
			$gBitSmarty->assign( 'gvAmount', $currencies->format( $pAmount, false ) );
			$gBitSmarty->assign( 'gvRedeemUrl', BITCOMMERCE_PKG_URI.'index.php?main_page=gv_redeem&gv_no='.$code );
			if( !empty( $pRecipient['message'] ) ) {
				$gBitSmarty->assign( 'gvMessage', $pRecipient['message'] );
			}

			$textMessage = $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_send_email_text.tpl' );
			$htmlMessage = $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_send_email_html.tpl' );

		// send the email
			zen_mail('', $pRecipient['email'], $gv_email_subject, $textMessage, STORE_NAME, EMAIL_FROM, $htmlMessage,'gv_send');

		// send additional emails
			if (SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' and SEND_EXTRA_GV_CUSTOMER_EMAILS_TO !='') {
				zen_mail('', SEND_EXTRA_GV_CUSTOMER_EMAILS_TO, $gv_email_subject, $textMessage, STORE_NAME, EMAIL_FROM, $htmlMessage,'gv_send');
				if ($_SESSION['customer_id']) {
					$account_query = "select `customers_firstname`, `customers_lastname`, `customers_email_address`
										from " . TABLE_CUSTOMERS . "
										where `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

					$account = $gBitDb->Execute($account_query);
				}
				$extra_info=email_collect_extra_info($pRecipient['to_name'],$pRecipient['email'], $pFromUser->getDisplayName() , $pFromUser->getField( 'email' ) );
				$html_msg['EXTRA_INFO'] = $gCommerceSystem->getConfig('TEXT_GV_NAME').' Code: '.$code.'<br/>'.$extra_info['HTML'];
				zen_mail('', SEND_EXTRA_GV_CUSTOMER_EMAILS_TO, tra( '[GV CUSTOMER SENT]' ). ' ' . $gv_email_subject,
					$gCommerceSystem->getConfig('TEXT_GV_NAME').' Code: '.$code."\n".$gv_email . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg,'gv_send_extra');
			}


		}
		$gBitDb->CompleteTrans();
		return $ret;
	}

	function redeemCoupon( $pCustomerId, $pCode ) {
		global $gBitDb;
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
				$gBitDb->query($gv_query, array( $coupon['coupon_id'], $pCustomerId, $_SERVER['REMOTE_ADDR'] ) );

				$gv_update = "update " . TABLE_COUPONS . "
							set coupon_active = 'N'
							where coupon_id = ?";
				$gBitDb->query($gv_update, array( $coupon['coupon_id'] ) );

				CommerceVoucher::updateCustomerBalance( $pCustomerId, $_SESSION['gv_id'] );
				$gBitDb->CompleteTrans();
				$ret = $coupon['coupon_amount'];
			}
		}
		return $ret;
	}

	function updateCustomerBalance( $pCustomerId, $pCouponId ) {
		global $gBitDb;
		if( BitBase::verifyId( $pCustomerId ) ) {
			$query = "SELECT customer_id, amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id` = ?";
			$customerBalance = $gBitDb->getRow( $query, array( $pCustomerId ) );

			$coupon_gv_query = "SELECT coupon_amount FROM " . TABLE_COUPONS . " WHERE coupon_id = ?";
			$couponAmount = $gBitDb->getOne($coupon_gv_query, array( $pCouponId ) );

			if( !empty( $customerBalance['customer_id'] ) ) {
				$newAmount = $customerBalance['amount'] + $couponAmount;
				$gv_query = "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = ? WHERE `customer_id` = ?";
				$gBitDb->query($gv_query, array( $newAmount, $pCustomerId ) );
			} else {
				$gv_query = "INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) VALUES (?,?)";
				$gBitDb->query($gv_query, array( $pCustomerId, $couponAmount ) );
			}
		}
	}

}

?>

<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentBase.php' );

abstract class CommercePluginPaymentCardBase extends CommercePluginPaymentBase {

	var $cc_type;
	var $cc_owner;
	var $cc_number;
	var $cc_cvv;
	var $cc_expires_month;
	var $cc_expires_year;
	var $pnref = -1;
	var $paymentOrderId;

	public function __construct() {
		parent::__construct();
	}

	protected function getSessionVars() {
		return array( 'cc_owner', 'cc_number', 'cc_cvv', 'cc_expires_month', 'cc_expires_year' );
	}

	protected function logTransaction( $pResponseHash, $pOrder ) {
		global $messageStack, $gBitUser;
		$this->mDb->query( "INSERT INTO " . TABLE_PUBS_CREDIT_CARD_LOG . " (orders_id, customers_id, ref_id, trans_result, trans_auth_code, trans_message, trans_amount, trans_date) values ( ?, ?, ?, ?, '-', ?, ?, 'NOW' )", array( $this->paymentOrderId, $gBitUser->mUserId, $this->getTransactionReference(), (int)$this->result, 'cust_id: '.$gBitUser->mUserId.' - '.$pOrder->customer['email_address'].':'.BitBase::getParameter( $pResponseHash, 'RESPMSG' ), number_format($pOrder->info['total'], 2,'.','') ) );
	}

	function verifyPayment( &$pPaymentParameters, &$pOrder ) {
		unset( $_SESSION[$this->code.'_error'] );

		if( !empty( $pPaymentParameters['cc_ref_id'] ) ) {
			// reference transation
			$this->cc_ref_id = $pPaymentParameters['cc_ref_id'];
		} elseif( empty( $pPaymentParameters['cc_number'] ) ) {
			$this->mErrors['number'] = tra( 'Please enter a credit card number.' );
		} elseif( $this->verifyCreditCard( $pPaymentParameters['cc_number'], $pPaymentParameters['cc_expires_month'], $pPaymentParameters['cc_expires_year'], $pPaymentParameters['cc_cvv'] ) ) {
			if( empty( $pPaymentParameters['cc_owner'] ) ) {
				$this->mErrors['owner'] = tra( 'Please enter the name card holders name as it is written on the card.' );
			} else {
				$this->cc_owner = $pPaymentParameters['cc_owner'];
			}
			if( preg_match( '/^37/', $pPaymentParameters['cc_number'] ) && BitBase::getParameter( $pOrder->info, 'currency' ) != 'USD' ) {
				 $this->mErrors['number'] = tra( 'American Express cannot process transactions in currencies other than USD. Change the currency in your cart, or use a different card.' );
			}
		}

		$this->saveSessionDetails();

		if( $this->mErrors ) {
			$_SESSION[$this->code.'_error'] = $this->mErrors;
		}
		return count( $this->mErrors ) === 0;
	}
	public static function privatizeCard( $pCardNumber ) {
		if( $pCardNumber ) {
			return substr($pCardNumber, 0, 4) . str_repeat('X', (strlen($pCardNumber) - 8)) . substr($pCardNumber, -4);
		}
	}

	function verifyCreditCard($number, $expires_m, $expires_y, $cvv) {
		$this->cc_type = NULL;

		if( $this->cc_number = $this->validateCreditCardNumber( $number ) ) {
			if (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $this->cc_number) and CC_ENABLED_VISA=='1') {
				$this->cc_type = 'Visa';
			} elseif (preg_match('/^5[1-5][0-9]{14}$/', $this->cc_number) and CC_ENABLED_MC=='1') {
				$this->cc_type = 'MasterCard';
			} elseif (preg_match('/^3[47][0-9]{13}$/', $this->cc_number) and CC_ENABLED_AMEX=='1') {
				$this->cc_type = 'American Express';
			} elseif (preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $this->cc_number) and CC_ENABLED_DINERS_CLUB=='1') {
				$this->cc_type = 'Diners Club';
			} elseif (preg_match('/^6011[0-9]{12}$/', $this->cc_number) and CC_ENABLED_DISCOVER=='1') {
				$this->cc_type = 'Discover';
			} elseif (preg_match('/^(3[0-9]{4}|2131|1800)[0-9]{11}$/', $this->cc_number) and CC_ENABLED_JCB=='1') {
				$this->cc_type = 'JCB';
			} elseif (preg_match('/^5610[0-9]{12}$/', $this->cc_number) and CC_ENABLED_AUSTRALIAN_BANKCARD=='1') {
				$this->cc_type = 'Australian BankCard';
			} else {
				$this->mErrors['number'] = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($number, 0, 4));
			}
		} else {
			$this->mErrors['number'] = TEXT_CCVAL_ERROR_INVALID_NUMBER;
		}

		if (is_numeric($expires_m) && ($expires_m > 0) && ($expires_m < 13)) {
			$this->cc_expires_month = $expires_m;
		} else {
			$this->mErrors['date'] = TEXT_CCVAL_ERROR_INVALID_DATE;
		}

		if( !empty( $cvv ) ) {
			$this->cc_cvv = $cvv;
		}

		$current_year = date('Y');
		if( $expires_y < 100 ) {
			// two digit expire year
			$expires_y = substr($current_year, 0, 2) . $expires_y;
		}

		if (is_numeric($expires_y) && ($expires_y >= $current_year) && ($expires_y <= ($current_year + 10))) {
			$this->cc_expires_year = $expires_y;
			$this->cc_expires = $this->cc_expires_month.( $this->cc_expires_year % 1000 );
		} else {
			$this->mErrors['date'] = TEXT_CCVAL_ERROR_INVALID_DATE;
		}

		if ($expires_y == $current_year) {
			if ($expires_m < date('n')) {
				$this->mErrors['date'] = TEXT_CCVAL_ERROR_INVALID_DATE;
			}
		}

		return (count( $this->mErrors ) === 0);
	}

	static function validateCreditCardNumber( $pCardNumber ) {
		$pCardNumber = preg_replace('/[^0-9]/', '', $pCardNumber);

		$checkNumber = strrev( $pCardNumber );
		
		$numSum = 0;

		for ($i=0; $i<strlen($checkNumber); $i++) {
			$currentNum = substr($checkNumber, $i, 1);

			// Double every second digit
			if ($i % 2 == 1) {
				$currentNum *= 2;
			}

			// Add digits of 2-digit numbers together
			if ($currentNum > 9) {
				$firstNum = $currentNum % 10;
				$secondNum = ($currentNum - $firstNum) / 10;
				$currentNum = $firstNum + $secondNum;
			}

			$numSum += $currentNum;
		}
		// If the total has no remainder it's OK
		return ($numSum % 10 == 0 ? $pCardNumber : false);
	}

}


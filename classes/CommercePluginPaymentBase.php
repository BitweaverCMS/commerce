<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginPaymentBase extends CommercePluginBase {

	var $cc_type;
	var $cc_owner;
	var $cc_number;
	var $cc_cvv;
	var $cc_expires_month;
	var $cc_expires_year;

	public function __construct() {
		parent::__construct();
	}

	function getTransactionReference() {
		// default implementation
		return NULL;
	}

	function processPayment( $pPaymentParameters ) {
		$this->mErrors['process_payment'] = 'This modules has not implemented the ::processPayment method. ('.$this->code.')';
		return FALSE;
	}

	function before_process() {
		return false;
	}

	function after_process() {
		return false;
	}

	function get_error() {
		return false;
	}

	protected function getVarNames() {
		return array( 'cc_owner', 'cc_number', 'cc_cvv', 'cc_expires_month', 'cc_expires_year' );
	}

	function clearSessionDetails() {
		foreach( $this->getVarNames() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	function saveSessionDetails() {
		foreach( $this->getVarNames() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	function pre_confirmation_check( $pPaymentParameters ) {
		unset( $_SESSION[$this->code.'_error'] );

		$ret = FALSE;

		if( empty( $pPaymentParameters['cc_number'] ) ) {
			$error = tra( 'Please enter a credit card number.' );
		} elseif( $this->verifyCreditCard( $pPaymentParameters['cc_number'], $pPaymentParameters['cc_expires_month'], $pPaymentParameters['cc_expires_year'], $pPaymentParameters['cc_cvv'] ) ) {
			$ret = TRUE;
		}

		$this->saveSessionDetails();

		if( $this->mErrors ) {
			$_SESSION[$this->code.'_error'] = $this->mErrors;
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, NULL, 'SSL', true, false));
		}
		return $ret;
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
			$this->cc_cvv = (int)$cvv;
		}

		$current_year = date('Y');
		$expires_y = substr($current_year, 0, 2) . $expires_y;
		if (is_numeric($expires_y) && ($expires_y >= $current_year) && ($expires_y <= ($current_year + 10))) {
			$this->cc_expires_year = $expires_y;
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

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

	var $pnref = -1;

	public function __construct() {
		parent::__construct();
		$this->title = 'Credit Card';

		if( $this->isEnabled() ) {
			if( $statusId = $this->getModuleConfigValue( '_ORDER_STATUS_ID' ) ) {
				$this->order_status = $statusId;
			}

			if( $paymentZoneId = $this->getModuleConfigValue( '_ZONE' ) ) {
				$this->enabled = ($paymentZoneId == $order->billing['zone_id']);
/*
			function update_status() {
				global $order, $gBitDb;

				if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_AUTHORIZENET_AIM_ZONE > 0) ) {
					$check_flag = false;
					$check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_AUTHORIZENET_AIM_ZONE . "' and `zone_country_id` = '" . $order->billing['countries_id'] . "' order by `zone_id`");
					while (!$check->EOF) {
						if ($check->fields['zone_id'] < 1) {
							$check_flag = true;
							break;
						} elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
							$check_flag = true;
							break;
						}
						$check->MoveNext();
					}

					if ($check_flag == false) {
						$this->enabled = false;
					}
				}
			}
*/
			}
		}
	}

	public function getCustomerTitle() {
		return 'Credit Card';
	}

	protected function getSessionVars() {
		return array( 'payment_owner', 'payment_number', 'cc_cvv', 'payment_expires_month', 'payment_expires_year' );
	}

	// Display Credit Card Information Submission Fields on the Checkout Payment Page
	function selection() {
		global $order;

		$expireMonths = array();
		for ($i=1; $i<13; $i++) {
			$expireMonths[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
		}

		$today = getdate();
		$expireYears = array();
		for ($i=$today['year']; $i < $today['year']+15; $i++) {
			$expireYears[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
		}

		$selection = array('id' => $this->code,
						 'module' => $this->title,
						 'fields' => array(
							array(	'title' => tra( 'Name On Card' ),
									'field' => zen_draw_input_field('payment_owner', BitBase::getParameter( $_SESSION, 'payment_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'] ), 'autocomplete="cc-name"' )
							),
							array(	'field' => '<div class="row"><div class="col-xs-8 col-sm-8"><label class="control-label">'.tra( 'Card Number' ).'</label>' . zen_draw_input_field('payment_number', BitBase::getParameter( $_SESSION, 'payment_number' ), ' autocomplete="cc-number" ', 'number' ) . '</div><div class="col-xs-4 col-sm-4"><label class="control-label"><i class="icon-credit-card"></i> ' . tra( 'CVV Number' ) . '</label>' . zen_draw_input_field('cc_cvv', BitBase::getParameter( $_SESSION, 'cc_cvv' ), ' autocomplete="cc-csc" ', 'number')  . '</div></div>',
							),
							array(	'title' => tra( 'Expiration Date' ),
									'field' => '<div class="row"><div class="col-xs-7 col-sm-9">' . zen_draw_pull_down_menu('payment_expires_month', $expireMonths, BitBase::getParameter( $_SESSION, 'payment_expires_month' ), ' class="input-small" autocomplete="cc-exp-month" ') . '</div><div class="col-xs-5 col-sm-3">' . zen_draw_pull_down_menu('payment_expires_year', $expireYears, substr( BitBase::getParameter( $_SESSION, 'payment_expires_year', (date('Y') + 1) ), -2 ), ' class="input-small" autocomplete="cc-exp-year" ') . '</div></div>'
							),
						)
					);

		if( !empty( $_SESSION[$this->code.'_error']['name'] ) ) {
			$selection['fields'][0]['error'] = $_SESSION[$this->code.'_error']['name'];
		}

		if( !empty( $_SESSION[$this->code.'_error']['number'] ) ) {
			$selection['fields'][1]['error'] = $_SESSION[$this->code.'_error']['number'];
		}

		if( !empty( $_SESSION[$this->code.'_error']['date'] ) ) {
			$selection['fields'][2]['error'] = $_SESSION[$this->code.'_error']['date'];
		}
		return $selection;
	}

	public function verifyPayment( &$pPaymentParams, &$pOrder ) {
		global $gCommerceSystem;
		unset( $_SESSION[$this->code.'_error'] );

		if( !empty( $pPaymentParams['trans_ref_id'] ) ) {
			// reference transation
			$this->trans_ref_id = $pPaymentParams['trans_ref_id'];
		} elseif( empty( $pPaymentParams['payment_number'] ) ) {
			$this->mErrors['number'] = tra( 'Please enter a credit card number.' );
		} elseif( $this->verifyCreditCard( $pPaymentParams ) ) {
			if( !$this->getPaymentOwner( $pPaymentParams ) ) {
				$this->mErrors['owner'] = tra( 'Please enter the name card holders name as it is written on the card.' );
			}
			if( preg_match( '/^37/', $pPaymentParams['payment_number'] ) && BitBase::getParameter( $pOrder->info, 'currency', $gCommerceSystem->getConfig( 'DEFAULT_CURRENCY' ) ) != 'USD' ) {
				 $this->mErrors['number'] = tra( 'American Express cannot process transactions in currencies other than USD. Change the currency in your cart, or use a different card.' );
			}
		} 

		if( parent::verifyPayment( $pPaymentParams, $pOrder ) ) {
			$pOrder->info['cc_cvv'] = $this->getParameter( $pPaymentParams, 'cc_cvv' );
			// payment is fully verified
			if( $this->mErrors ) {
				$_SESSION[$this->code.'_error'] = $this->mErrors;
			}
		}

		return count( $this->mErrors ) === 0;
	}

	public function getPaymentType( $pPaymentParams ) {
		if( !$ret = $this->getParameter( $pPaymentParams, 'payment_type' ) ) {
			if( !empty( $pPaymentParams['payment_number'] ) ) {
				$ret = $this->getCreditCardType( $pPaymentParams['payment_number'] );
			} elseif( !empty( $pPaymentParams['trans_ref_id'] ) ) {
				$ret = 'Reference';
			} else {
			}
		}
		return $ret;
	}

	public function privatizePaymentNumber( $pPaymentNumber ) {
		if( $pPaymentNumber ) {
			return substr($pPaymentNumber, 0, 6) . str_repeat('X', (strlen($pPaymentNumber) - 6)) . substr($pPaymentNumber, -4);
		}
	}

	public function getCreditCardType( $pPaymentNumber ) {
		$ret = '';

		if (preg_match('/^(6334[5-9][0-9]|6767[0-9]{2})[0-9]{10}([0-9]{2,3}?)?$/', $pPaymentNumber) && CC_ENABLED_SOLO=='1') {
			$ret = "Solo"; // is also a Maestro product
		} else if (preg_match('/^(49369[8-9]|490303|6333[0-4][0-9]|6759[0-9]{2}|5[0678][0-9]{4}|6[0-9][02-9][02-9][0-9]{2})[0-9]{6,13}?$/', $pPaymentNumber) && CC_ENABLED_MAESTRO=='1') {
			$ret = "Maestro";
		} else if (preg_match('/^(49030[2-9]|49033[5-9]|4905[0-9]{2}|49110[1-2]|49117[4-9]|49918[0-2]|4936[0-9]{2}|564182|6333[0-4][0-9])[0-9]{10}([0-9]{2,3}?)?$/', $pPaymentNumber) && CC_ENABLED_MAESTRO=='1') {
			$ret = "Maestro"; // SWITCH is now Maestro
		} elseif (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $pPaymentNumber) && CC_ENABLED_VISA=='1') {
			$ret = 'Visa';
		} elseif (preg_match('/^(5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/', $pPaymentNumber) && CC_ENABLED_MC=='1') {
			$ret = 'MasterCard'; // 510000-550000, 222100-272099
		} elseif (preg_match('/^3[47][0-9]{13}$/', $pPaymentNumber) && CC_ENABLED_AMEX=='1') {
			$ret = 'American Express';
		} elseif (preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $pPaymentNumber) && CC_ENABLED_DINERS_CLUB=='1') {
			$ret = 'Diners Club';
		} elseif (preg_match('/^(6011[0-9]{12}|622[1-9][0-9]{12}|64[4-9][0-9]{13}|65[0-9]{14})$/', $pPaymentNumber) && CC_ENABLED_DISCOVER=='1') {
			$ret = 'Discover';
		} elseif (preg_match('/^(35(28|29|[3-8][0-9])[0-9]{12}|2131[0-9]{11}|1800[0-9]{11})$/', $pPaymentNumber) && CC_ENABLED_JCB=='1') {
			$ret = "JCB";
		} elseif (preg_match('/^5610[0-9]{12}$/', $pPaymentNumber) && CC_ENABLED_AUSTRALIAN_BANKCARD=='1') {
			$ret = 'Australian BankCard'; // NOTE: is now obsolete
		}

		return $ret;
	}

	function verifyCreditCard( &$pPaymentParams ) {
		$this->payment_type = NULL;

		if( $validPaymentNumber = $this->validateCreditCardNumber( $pPaymentParams['payment_number'] ) ) {
			$pPaymentParams['payment_number'] = $validPaymentNumber;
			if( $paymentType = $this->getCreditCardType( $validPaymentNumber ) ) {
				$pPaymentParams['payment_type'] = $paymentType;
			} else {
				$this->mErrors['number'] = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($number, 0, 4));
			}
		} else {
			$this->mErrors['number'] = TEXT_CCVAL_ERROR_INVALID_NUMBER;
		}

		if( ($paymentMonth = (int)$this->getParameter( $pPaymentParams, 'payment_expires_month' )) && ($paymentMonth > 0) && ($paymentMonth < 13)) {
			$pPaymentParams['payment_expires_month'] = $paymentMonth;
		} else {
			$this->mErrors['date'] = TEXT_CCVAL_ERROR_INVALID_DATE;
		}

		if( !empty( $cvv ) ) {
			$this->cc_cvv = $cvv;
		}

		$currentYear = date('Y');
		if( ($paymentYear = (int)$this->getParameter( $pPaymentParams, 'payment_expires_year' )) < 100 ) {
			// fix two digit expire year
			$paymentYear = substr($currentYear, 0, 2) . $paymentYear;
		}

		if( ($paymentYear >= $currentYear) && ($paymentYear <= ($currentYear + 10)) ) {
			$pPaymentParams['payment_expires_year'] = $paymentYear;
			$pPaymentParams['payment_expires'] = $pPaymentParams['payment_expires_month'].( $pPaymentParams['payment_expires_year'] % 1000 );
		} else {
			$this->mErrors['date'] = TEXT_CCVAL_ERROR_INVALID_DATE;
		}

		if ($paymentYear == $currentYear) {
			if ($paymentMonth < date('n')) {
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

	function getTransactionReference() {
		return $this->trans_ref_id;
	}

	function javascript_validation() {
		return "";
	}

}

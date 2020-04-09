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

/*
	var $payment_type;
	var $payment_expires;
	var $payment_owner;
	var $payment_number;
	var $trans_ref_id;
*/
	public function __construct() {
		parent::__construct();
	}

	public function getPaymentNumber( $pPaymentParams ) {
		return $this->getParameter( $pPaymentParams, 'payment_number' );
	}

	public function getPaymentExpires( $pPaymentParams ) {
		$expMonth = $expYear = '';

		if( $expMonth = (int)$this->getParameter( $pPaymentParams, 'payment_expires_month' ) ) {
			if( $expMonth > 12 || $expMonth < 1 ) {
				$expMonth = '';
			} elseif( $expMonth < 10 ) {
				$expMonth = '0'.$expMonth;
			}
		}
		if( $expMonth && ($expYear = (int)$this->getParameter( $pPaymentParams, 'payment_expires_year' )) ) {
			if( $expYear < 10 ) {
				$expYear = '0'.$expYear;
			} elseif( $expYear > 1000 ) {
				$expYear = $expYear % 1000;
			}
		}

		return $expMonth.$expYear;
	}

	public function getPaymentType( $pPaymentParams ) {
		return $this->getParameter( $pPaymentParams, 'payment_type' );
	}

	public function getPaymentOwner( $pPaymentParams ) {
		return $this->getParameter( $pPaymentParams, 'payment_owner' );
	}

	protected function getModuleType() {
		return 'payment';
	}

	protected function clearSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	protected function saveSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	protected function logTransactionPrep( $pPaymentParams, $pOrder ) {
		global $gBitUser;
		$logHash = array();

		$logHash['user_id'] = $gBitUser->mUserId;
		$logHash['orders_id'] = $this->getParameter( $pPaymentParams, 'orders_id' );
		$logHash['payment_number'] = $this->getPaymentNumber( $pPaymentParams );
		$logHash['payment_expires'] = $this->getPaymentExpires( $pPaymentParams );
		$logHash['payment_type'] = $this->getPaymentType( $pPaymentParams );
		$logHash['payment_owner'] = $this->getPaymentOwner( $pPaymentParams );
		$logHash['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$logHash['payment_module'] = $this->code;

		$logHash['customers_id'] = $pOrder->customer['customers_id'];
		$logHash['customers_email'] = $pOrder->customer['email_address'];
		$logHash['num_cart_items'] = count( $pOrder->contents );

		$logHash['address_company'] = $pOrder->delivery['company'];
		$logHash['address_street'] =  $pOrder->delivery['street_address'];
		$logHash['address_suburb'] =  $pOrder->delivery['suburb'];
		$logHash['address_city'] =    $pOrder->delivery['city'];
		$logHash['address_state'] =   $pOrder->delivery['state'];
		$logHash['address_zip'] =     $pOrder->delivery['postcode'];
		$logHash['address_country'] = $pOrder->delivery['countries_iso_code_2'];

		// We assume a default error, and let payment method set the success
		$logHash['is_success'] = 'n';
		$logHash['exchange_rate'] = '1.0';
		$logHash['payment_status'] = 'default';

		return $logHash;
	}

	protected function logTransaction( $pTransactionHash ) {
		$this->mDb->associateInsert( TABLE_ORDERS_PAYMENTS, $pTransactionHash );
	}

	function getTransactionReference() {
		// default implementation
		return NULL;
	}

	// Default methods that should be overridden in derived classes
	protected function getSessionVars() {
		return array();
	}

	function selection() {
		return array( 'id' => $this->code, 'module' => $this->title );
	}

	public function verifyPayment( &$pPaymentParams, &$pOrder ) {

		global $gBitUser, $currencies;

		$pPaymentParams['payment_email'] = BitBase::getParameter( $pOrder->customer, 'email_address', $gBitUser->getField('email') );
		$pPaymentParams['payment_user_id'] = BitBase::getParameter( $pOrder->customer, 'user_id', $gBitUser->getField('user_id') );

		if( !empty( $pPaymentParams['trans_ref_id'] ) && empty( $pPaymentParams['charge_amount'] ) ) {
			$this->mErrors['charge_amount'] = 'Invalid amount';
		} elseif( empty( $pPaymentParams['charge_amount'] ) ) {
			if( !( $pPaymentParams['charge_amount'] = $pOrder->getPaymentDue()) ) {
				$this->mErrors['charge_amount'] = 'Invalid amount';
			}
		}

		if( empty( $this->mErrors ) ) {
			if( !empty( $pPaymentParams['trans_ref_id'] ) ) {
				// reference transaction
				$pPaymentParams['orders_id'] = $pOrder->mOrdersId;
				$pPaymentParams['payment_currency'] = BitBase::getParameter( $pPaymentParams, 'charge_currency', DEFAULT_CURRENCY );
				$pPaymentParams['payment_decimal'] = $currencies->get_decimal_places( $pPaymentParams['payment_currency'] );
				$pPaymentParams['payment_localized'] = number_format( $pPaymentParams['charge_amount'], $pPaymentParams['payment_decimal'], '.', '' ) ;
				$pPaymentParams['payment_native'] = (( $pPaymentParams['payment_currency'] != DEFAULT_CURRENCY ) ? $pPaymentParams['payment_localized'] / $pPaymentParams['charge_currency_value'] : $pPaymentParams['payment_localized']);
				// completed orders have a single joined 'name' field
				$pOrder->billing['firstname'] = substr( $pOrder->billing['name'], 0, strpos( $pOrder->billing['name'], ' ' ) );
				$pOrder->billing['lastname'] = substr( $pOrder->billing['name'], strpos( $pOrder->billing['name'], ' ' ) + 1 );
				$pOrder->delivery['firstname'] = substr( $pOrder->billing['name'], 0, strpos( $pOrder->billing['name'], ' ' ) );
				$pOrder->delivery['lastname'] = substr( $pOrder->billing['name'], strpos( $pOrder->billing['name'], ' ' ) + 1 );
			} else {
				// Calculate the next expected order id
				$pPaymentParams['orders_id'] = (!empty( $_SESSION['orders_id'] ) ? $_SESSION['orders_id'] : $pOrder->getNextOrderId());
				$_SESSION['orders_id'] = $pPaymentParams['orders_id'];
				$pOrder->info['payment_number'] = $this->getParameter( $pPaymentParams, 'payment_number' );
				$pOrder->info['payment_expires'] = $this->getPaymentExpires( $pPaymentParams );
				$pOrder->info['payment_type'] = $this->getParameter( $pPaymentParams, 'payment_type' );
				$pOrder->info['payment_owner'] = $this->getPaymentOwner( $pPaymentParams );
				$pOrder->info['cc_cvv'] = $this->cc_cvv;
				// $pPaymentParams['charge_amount'] is in the system DEFAULT_CURRENCY. charge_amount * currency_value = localizedPayment
				$pPaymentParams['payment_currency'] = BitBase::getParameter( $pOrder->info, 'currency', DEFAULT_CURRENCY );
				$pPaymentParams['payment_decimal'] = $currencies->get_decimal_places( $pPaymentParams['payment_currency'] );
				$pPaymentParams['payment_native'] = $pPaymentParams['charge_amount'];
				$pPaymentParams['payment_localized'] = number_format( ($pPaymentParams['payment_currency'] != DEFAULT_CURRENCY ? ($pPaymentParams['payment_native'] * $pOrder->getField('currency_value')) : $pPaymentParams['payment_native']), $pPaymentParams['payment_decimal'], '.', '' ) ;
			}

			foreach( $this->getSessionVars() as $var ) {
				$this->$var = $this->getParameter( $pPaymentParams, $var, NULL );
			}

			if( ($maxPayment = (int)$this->getModuleConfigValue('_PAYMENT_LIMIT_MAX')) && $pPaymentParams['payment_native'] > $maxPayment ) {
				// purchase price exceeds payment limit
				$this->mErrors['charge_amount'] = 'Cart total is above maximum limit '.$maxPayment;
			}

			if( ($minPayment = (int)$this->getModuleConfigValue('_PAYMENT_LIMIT_MIN')) && $pPaymentParams['payment_native'] < $minPayment ) {
				// purchase price is less than payment limit
				$this->mErrors['charge_amount'] = 'Cart total is below minimum limit '.$minPayment;
			}

			$this->saveSessionDetails();

			if( $this->mErrors ) {
				$_SESSION[$this->code.'_error'] = $this->mErrors;
			}
		}

		return count( $this->mErrors ) === 0;
	}

	function processPayment( &$pPaymentParams, &$pOrder ) {
		$this->mErrors['process_payment'] = 'This modules has not implemented the ::processPayment method. ('.$this->code.')';
		return FALSE;
	}

	function confirmation( $pPaymentParams ) {
		return false;
	}

	function process_button( $pPaymentParams ) {
		return false;
	}

	function get_error() {
		return false;
	}

	function javascript_validation() {
		return false;
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 10;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_ORDER_STATUS_ID' => array(
				'configuration_title' => 'Initial Order Status',
				'configuration_description' => 'Orders made with this payment module will be set to the status.',
				'sort_order' => $i++,
				'configuration_value' => '20',
				'set_function' => 'zen_cfg_pull_down_order_statuses(',
				'use_function' => 'zen_get_order_status_name',
			),
			$this->getModuleKeyTrunk().'_ZONE' => array(
				'configuration_title' => 'Payment Zone',
				'configuration_description' => 'If a zone is selected, only enable this payment method for that zone.',
				'sort_order' => $i++,
				'set_function' => 'zen_cfg_pull_down_zone_classes(',
				'use_function' => 'zen_get_zone_class_title',
			),
			$this->getModuleKeyTrunk().'_PAYMENT_LIMIT_MAX' => array(
				'configuration_title' => 'Maximum Payment Limit',
				'configuration_description' => 'Maximum this payment method can accept in your store\'s default currency',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_PAYMENT_LIMIT_MIN' => array(
				'configuration_title' => 'Minimum Payment Limit',
				'configuration_description' => 'Minimum this payment method can accept in your store\'s default currency',
				'sort_order' => $i++,
			),
		) );
	}

}

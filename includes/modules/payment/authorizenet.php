<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |   
// | http://www.zen-cart.com/index.php                                    |   
// |                                                                      |   
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginPaymentCardBase.php' );

class authorizenet extends CommercePluginPaymentCardBase {
	var $code, $title, $description, $enabled;

// class constructor
	function __construct() {
		parent::__construct();

		$this->code = 'authorizenet';
		$this->adminTitle = tra( 'Authorize.net' );
		$this->description = tra( 'Credit card processing with Authorize.net payment processor.' );
		$this->form_action_url = 'https://secure.authorize.net/gateway/transact.dll';
	}

	// Authorize.net utility functions
	// DISCLAIMER:
	//		 This code is distributed in the hope that it will be useful, but without any warranty; 
	//		 without even the implied warranty of merchantability or fitness for a particular purpose.

	// Main Interfaces:
	//
	// function InsertFP ($loginid, $txnkey, $amount, $sequence) - Insert HTML form elements required for SIM
	// function CalculateFP ($loginid, $txnkey, $amount, $sequence, $tstamp) - Returns Fingerprint.

	// compute HMAC-MD5
	// Uses PHP mhash extension. Pl sure to enable the extension
	// function hmac ($key, $data) {
	//	 return (bin2hex (mhash(MHASH_MD5, $data, $key)));
	//}

	// Thanks is lance from http://www.php.net/manual/en/function.mhash.php
	//lance_rushing at hot* spamfree *mail dot com
	//27-Nov-2002 09:36 
	// 
	//Want to Create a md5 HMAC, but don't have hmash installed?
	//
	//Use this:

	function hmac ($key, $data) {
		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// Hacked by Lance Rushing

		$b = 64; // byte length for md5
		if (strlen($key) > $b) {
			 $key = pack("H*",md5($key));
		}
		$key	= str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return md5($k_opad	. pack("H*",md5($k_ipad . $data)));
	}
	// end code from lance (resume authorize.net code)

	// Calculate and return fingerprint
	// Use when you need control on the HTML output
	function CalculateFP ($loginid, $txnkey, $amount, $sequence, $tstamp, $currency = "") {
		return ($this->hmac ($txnkey, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency));
	}

	// Inserts the hidden variables in the HTML FORM required for SIM
	// Invokes hmac function to calculate fingerprint.

	function InsertFP ($loginid, $txnkey, $amount, $sequence, $currency = "") {
		$tstamp = time ();
		$fingerprint = $this->hmac ($txnkey, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency);

		$str = zen_draw_hidden_field('x_fp_sequence', $sequence) .
					 zen_draw_hidden_field('x_fp_timestamp', $tstamp) .
					 zen_draw_hidden_field('x_fp_hash', $fingerprint);

		return $str;
	}
	// end authorize.net code

	// class methods
	function update_status() {
		global $order, $gBitDb;

		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_AUTHORIZENET_ZONE > 0) ) {
			$check_flag = false;
			$check = $this->mDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_AUTHORIZENET_ZONE . "' and `zone_country_id` = '" . $order->billing['countries_id'] . "' order by `zone_id`");
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

	function javascript_validation() {
		$js = '	if (payment_value == "' . $this->code . '") {' . "\n" .
					'		var cc_owner = document.checkout_payment.authorizenet_cc_owner.value;' . "\n" .
					'		var cc_number = document.checkout_payment.authorizenet_cc_number.value;' . "\n" .
					'		if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
					'			error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER . '";' . "\n" .
					'			error = 1;' . "\n" .
					'		}' . "\n" .
					'		if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
					'			error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER . '";' . "\n" .
					'			error = 1;' . "\n" .
					'		}' . "\n" .
					'	}' . "\n";

		return $js;
	}

	function selection() {
		global $order;

		for ($i=1; $i<13; $i++) {
			$expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
		}

		$today = getdate(); 
		for ($i=$today['year']; $i < $today['year']+10; $i++) {
			$expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
		}
		$selection = array('id' => $this->code,
							 'module' => $this->title,
							 'fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER,
													 'field' => zen_draw_input_field('authorizenet_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
										 array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER,
													 'field' => zen_draw_input_field('authorizenet_cc_number')),
										 array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES,
													 'field' => zen_draw_pull_down_menu('authorizenet_cc_expires_month', $expires_month) . '&nbsp;' . zen_draw_pull_down_menu('authorizenet_cc_expires_year', $expires_year))));

		return $selection;
	}

	public function verifyPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {
		if( empty( $pPaymentParams['authorizenet_cc_number'] ) ) {
			$error = tra( 'Please enter a credit card number.' );
		} elseif( $this->verifyCreditCard( $pPaymentParams['authorizenet_cc_number'], $pPaymentParams['authorizenet_cc_expires_month'], $pPaymentParams['authorizenet_cc_expires_year'], $pPaymentParams['authorizenet_cc_cvv'] ) ) {
			$ret = TRUE;
		} else {
			foreach( array( 'authorizenet_cc_owner', 'authorizenet_cc_number', 'authorizenet_cc_expires_month', 'authorizenet_cc_expires_year', 'authorizenet_cc_cvv' ) as $key ) {
				$pSessionParams[$key] = BitBase::getParameter( $pPaymentParams, $key );
			}
			$pSessionParams['pfp_error'] = $this->mErrors;
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, NULL, 'SSL', true, false));
		}
		return $ret;
	}

	function confirmation( $pPaymentParams ) {
		global $_POST;

		$confirmation = array('title' => $this->title . ': ' . $this->cc_type,
								'fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER,
														'field' => $_POST['authorizenet_cc_owner']),
											array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER,
														'field' => substr($this->cc_number, 0, 4) . str_repeat('X', (strlen($this->cc_number) - 8)) . substr($this->cc_number, -4)),
											array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES,
														'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['authorizenet_cc_expires_month'], 1, '20' . $_POST['authorizenet_cc_expires_year'])))));

		return $confirmation;
	}

	function process_button( $pPaymentParams ) {
		global $_SERVER, $order;

		$sequence = rand(1, 1000);
		$process_button_string = zen_draw_hidden_field('x_Login', MODULE_PAYMENT_AUTHORIZENET_LOGIN) .
														 zen_draw_hidden_field('x_Card_Num', $this->cc_number) .
														 zen_draw_hidden_field('x_Exp_Date', $this->cc_expires_month . substr($this->cc_expires_year, -2)) .
														 zen_draw_hidden_field('x_Amount', number_format($order->info['total'], 2)) .
														 zen_draw_hidden_field('x_Relay_URL', zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false)) .
														 zen_draw_hidden_field('x_Method', ((MODULE_PAYMENT_AUTHORIZENET_METHOD == 'Credit Card') ? 'CC' : 'ECHECK')) .
														 zen_draw_hidden_field('x_Version', '3.0') .
														 zen_draw_hidden_field('x_Cust_ID', $_SESSION['customer_id']) .
														 zen_draw_hidden_field('x_Email_Customer', ((MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER == 'True') ? 'TRUE': 'FALSE')) .
														 zen_draw_hidden_field('x_first_name', $order->billing['firstname']) .
														 zen_draw_hidden_field('x_last_name', $order->billing['lastname']) .
														 zen_draw_hidden_field('x_address', $order->billing['street_address']) .
														 zen_draw_hidden_field('x_city', $order->billing['city']) .
														 zen_draw_hidden_field('x_state', $order->billing['state']) .
														 zen_draw_hidden_field('x_zip', $order->billing['postcode']) .
														 zen_draw_hidden_field('x_country', $order->billing['title']) .
														 zen_draw_hidden_field('x_phone', $order->customer['telephone']) .
														 zen_draw_hidden_field('x_email', $order->customer['email_address']) .
														 zen_draw_hidden_field('x_ship_to_first_name', $order->delivery['firstname']) .
														 zen_draw_hidden_field('x_ship_to_last_name', $order->delivery['lastname']) .
														 zen_draw_hidden_field('x_ship_to_address', $order->delivery['street_address']) .
														 zen_draw_hidden_field('x_ship_to_city', $order->delivery['city']) .
														 zen_draw_hidden_field('x_ship_to_state', $order->delivery['state']) .
														 zen_draw_hidden_field('x_ship_to_zip', $order->delivery['postcode']) .
														 zen_draw_hidden_field('x_ship_to_country', $order->delivery['title']) .
														 zen_draw_hidden_field('x_Customer_IP', $_SERVER['REMOTE_ADDR']) .
														 $this->InsertFP(MODULE_PAYMENT_AUTHORIZENET_LOGIN, MODULE_PAYMENT_AUTHORIZENET_TXNKEY, number_format($order->info['total'], 2), $sequence);
		if (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test') $process_button_string .= zen_draw_hidden_field('x_Test_Request', 'TRUE');

		$process_button_string .= zen_draw_hidden_field(session_name(), session_id());

		return $process_button_string;
	}

	public function processPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {

		if ($pPaymentParams['x_response_code'] == '1') return;
		if ($pPaymentParams['x_response_code'] == '2') {
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE), 'SSL', true, false));
		}
		// Code 3 is an error - but anything else is an error too (IMHO)
		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE), 'SSL', true, false));
	}

	function get_error() {
		global $_GET;

		$error = array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR,
									 'error' => stripslashes(urldecode($_GET['error'])));

		return $error;
	}

	function install() {
		parent::install();
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Login Username', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'testing', 'The login username used for the Authorize.net service', '6', '0', 'NOW')");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'Test', 'Transaction Key used for encrypting TP data', '6', '0', 'NOW')");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'zen_cfg_select_option(array(''Test'', ''Production''), ', 'NOW')");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'Credit Card', 'Transaction method used for processing orders', '6', '0', 'zen_cfg_select_option(array(''Credit Card'', ''eCheck''), ', 'NOW')");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net e-mail a receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
	}

	public function keys() {
		return array_merge( 
					array_keys( $this->config() ), 
					array('MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'MODULE_PAYMENT_AUTHORIZENET_ZONE')
				);
	}
}
?>

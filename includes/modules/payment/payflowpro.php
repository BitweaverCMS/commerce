<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * Portions Copyright (c) 2003 Zen Cart									|
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentBase.php' );

class payflowpro extends CommercePluginPaymentBase {
	var $code, $title, $description, $enabled;

	public function __construct() {
		parent::__construct();
		global $order, $messageStack;

		$this->code = 'payflowpro';
		if( !empty( $_GET['main_page'] ) ) {
			 $this->title = tra( 'Credit Card' ); // Payment module title in Catalog
		} else {
			 $this->title = tra( 'PayPal PayFlow Pro' ); // Payment module title in Admin
		}
		$this->description = tra( 'Credit Card Test Info:<br /><br />CC#: 4111111111111111 or<br />5105105105105100<br />Expiry: Any' );
		$this->sort_order = defined( 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER' ) ? MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER : 0;

		$this->enabled =((defined( 'MODULE_PAYMENT_PAYFLOWPRO_STATUS' ) && MODULE_PAYMENT_PAYFLOWPRO_STATUS == 'True') ? true : false);

		if ( defined( 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID' ) && (int)MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID;
		}

		$this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false); // Page to go to upon submitting page info
	}

	////////////////////////////////////////////////////
	// Javascript form validation
	// Check the user input submited on checkout_payment.php with javascript (client-side).
	// Examples: validate credit card number, make sure required fields are filled in
	////////////////////////////////////////////////////

	function javascript_validation() {
		return false;
	}

	////////////////////////////////////////////////////
	// !Form fields for user input
	// Output any required information in form fields
	// Examples: ask for extra fields (credit card number), display extra information
	////////////////////////////////////////////////////



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
									'field' => zen_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])
							),
							array(	'field' => '<div class="row"><div class="col-xs-8 col-sm-8"><label class="control-label">'.tra( 'Card Number' ).'</label>' . zen_draw_input_field('cc_number', BitBase::getParameter( $_SESSION, 'cc_number' ), NULL, 'text' ) . '</div><div class="col-xs-4 col-sm-4"><label class="control-label"><i class="icon-credit-card"></i> ' . tra( 'CVV Number' ) . '</label>' . zen_draw_input_field('cc_cvv', BitBase::getParameter( $_SESSION, 'cc_cvv' ), NULL, 'number')  . '</div></div>',
							),
							array(	'title' => tra( 'Expiration Date' ),
									'field' => '<div class="row"><div class="col-xs-7 col-sm-9">' . zen_draw_pull_down_menu('cc_expires_month', $expireMonths, BitBase::getParameter( $_SESSION, 'cc_expires_month' ), ' class="input-small" ') . '</div><div class="col-xs-5 col-sm-3">' . zen_draw_pull_down_menu('cc_expires_year', $expireYears, substr( BitBase::getParameter( $_SESSION, 'cc_expires_year', (date('Y') + 1) ), -2 ), ' class="input-small" ') . '</div></div>'
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

	////////////////////////////////////////////////////
	// Pre confirmation checks (ie, check if credit card
	// information is right before sending the info to
	// the payment server
	////////////////////////////////////////////////////

	////////////////////////////////////////////////////
	// Functions to execute before displaying the checkout
	// confirmation page
	////////////////////////////////////////////////////

	function confirmation( $pPaymentParameters ) {
		$confirmation = array('title' => $this->title . ': ' . $this->cc_type,
								'fields' => array(
									array(	'title' => tra( 'Card Owner' ),
											'field' => $pPaymentParameters['cc_owner']),
									array(	'title' => tra( 'Card Number' ),
											'field' => substr($this->cc_number, 0, 4) . str_repeat('X', (strlen($this->cc_number) - 8)) . substr($this->cc_number, -4).' +'.$pPaymentParameters['cc_cvv'] ),
									array(	'title' => tra( 'Expiration Date' ),
											'field' => strftime('%B,%Y', mktime(0,0,0,$pPaymentParameters['cc_expires_month'], 1, '20' . $pPaymentParameters['cc_expires_year']))),
									)
								);

		return $confirmation;
	}

	////////////////////////////////////////////////////
	// Functions to execute before finishing the form
	// Examples: add extra hidden fields to the form
	////////////////////////////////////////////////////
	function process_button( $pPaymentParameters ) {
		global $_SERVER, $order, $total_tax, $shipping_cost, $customer_id;
		// These are hidden fields on the checkout confirmation page
		$process_button_string = zen_draw_hidden_field('cc_owner', $pPaymentParameters['cc_owner']) .
								 zen_draw_hidden_field('cc_expires', $this->cc_expires_month . substr($this->cc_expires_year, -2)) .
								 zen_draw_hidden_field('cc_type', $this->cc_type) .
								 zen_draw_hidden_field('cc_number', $this->cc_number) .
								 zen_draw_hidden_field('cc_cvv', $this->cc_cvv);

		$process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());
		return $process_button_string;
	}


	protected function verifyPaymentParameters( &$pPaymentParameters ) {
		global $gCommerceSystem, $currencies;

		if( empty( $pPaymentParameters['charge_amount'] ) || !is_numeric( $pPaymentParameters['charge_amount'] ) ) {
			$this->mErrors['charge_amount'] = 'Invalid amount';
		} else {
			$pPaymentParameters['charge_amount'] = (float)$pPaymentParameters['charge_amount'];
		}

		$payflowCurrency = $gCommerceSystem->getConfig( 'MODULE_PAYMENT_PAYFLOWPRO_CURRENCY', 'USD' );

		if( DEFAULT_CURRENCY != $payflowCurrency ) {
			$pPaymentParameters['charge_amount'] = $currencies->convert( $pPaymentParameters['charge_amount'], $payflowCurrency, DEFAULT_CURRENCY );
		}

		return count( $this->mErrors ) == 0;
	}

	function processPayment( $pPaymentParameters ) {
		global $gDebug;
		if( self::verifyPaymentParameters( $pPaymentParameters ) ) {
			$postFields = array();
/*
ACCT 
AMT 
CITY 
COMMENT1
COMMENT2 
COMPANYNAME 
BILLTOCOUNTRY 
CUSTCODE
CUSTIP 
DUTYAMT 
EMAIL 
EXPDATE
FIRSTNAME 
MIDDLENAME 
LASTNAME 
FREIGHTAMT
INVNUM 
PONUM 
SHIPTOCITY 
SHIPTOCOUNTRY
SHIPTOFIRSTNAME 
SHIPTOMIDDLENAME 
SHIPTOLASTNAME 
SHIPTOSTATE
SHIPTOSTREET 
SHIPTOZIP 
STATE 
STREET
SWIPE 
TAXAMT 
PHONENUM 
TAXEXEMPT
ZIP
*/
			$postFields['PWD'] = MODULE_PAYMENT_PAYFLOWPRO_PWD;
			$postFields['USER'] = MODULE_PAYMENT_PAYFLOWPRO_LOGIN;
			$postFields['VENDOR'] = MODULE_PAYMENT_PAYFLOWPRO_VENDOR;
			$postFields['PARTNER'] = MODULE_PAYMENT_PAYFLOWPRO_PARTNER;
			$postFields['VERBOSITY'] = 'MEDIUM';
			$postFields['TENDER'] = 'C';
			$postFields['REQUEST_ID'] = time();
			foreach( $pPaymentParameters as $key=>$value ) {
				if( $key == 'cc_ref_id' ) {
					$key = 'ORIGID';
				}
				if( $key == 'charge_amount' ) {
					$key = 'AMT';
				}
				$postFields[$key] = $value;
			}

			if( MODULE_PAYMENT_PAYFLOWPRO_TYPE == 'Authorization' ) {
				$postFields['TRXTYPE'] = 'A';
			} elseif( $postFields['AMT'] > 0 ) {
				$postFields['TRXTYPE'] = 'S';
			} elseif( $postFields['AMT'] < 0 ) {
				$postFields['TRXTYPE'] = 'C';
				$postFields['AMT'] = -1 * $postFields['AMT'];
			}

			if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') {
				$url='https://pilot-payflowpro.paypal.com';
			} else {
				$url='https://payflowpro.paypal.com';
			}

			// request-id must be unique within 30 days
			$requestId = md5(uniqid(mt_rand()));
			$headers[] = 'Content-Type: text/namevalue';
			$headers[] = 'X-VPS-Timeout: 45';
			$headers[] = 'X-VPS-VIT-Client-Type: PHP/cURL';
			$headers[] = 'X-VPS-VIT-Integration-Product: PHP::bitcommerce - Payflow Pro';
			$headers[] = 'X-VPS-VIT-Integration-Version: 1.0';
			$this->lastHeaders = $headers;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildNameValueList($postFields));

			$_curlOptions = array(	CURLOPT_HEADER => 0,
									CURLOPT_RETURNTRANSFER => 1,
									CURLOPT_TIMEOUT => 60,
									CURLOPT_FOLLOWLOCATION => 0,
									CURLOPT_SSL_VERIFYPEER => 0,
									CURLOPT_SSL_VERIFYHOST => 2,
									CURLOPT_FORBID_REUSE => true,
									CURLOPT_POST => 1,
								);

			foreach ($_curlOptions as $name => $value) {
				curl_setopt($ch, $name, $value);
			}

			$response = curl_exec($ch);
			$commError = curl_error($ch);
			$commErrNo = curl_errno($ch);

			$commInfo = @curl_getinfo($ch);
			curl_close($ch);

			$rawdata = "CURL raw data:\n" . $response . "CURL RESULTS: (" . $commErrNo . ') ' . $commError . "\n" . print_r($commInfo, true) . "\nEOF";

			if ($response) {
				$response .= '&CURL_ERRORS=' . ($commErrNo != 0 ? urlencode('(' . $commErrNo . ') ' . $commError) : '') ;
				$responseHash = $this->_parseNameValueList($response);

				if ( $gDebug ) {
					$this->_logTransaction($operation,	$response, $errors . ($commErrNo != 0 ? "\n" . print_r($commInfo, true) : ''));
				}

				$errors = ($commErrNo != 0 ? "\n(" . $commErrNo . ') ' . $commError : '');

				$this->pnref = '';
				# Check result
				if( isset( $responseHash['PNREF'] ) ) {
					$this->pnref = $responseHash['PNREF'];
				}

				switch( BitBase::getParameter( $responseHash, 'RESULT' ) ) {
					case '1': // User authentication failed
					case '2': // Invalid tender
					case '3': // Invalid transaction type
					case '4': // Invalid amount
					case '5': // Invalid merchant information
					case '7': // Field format error 
					case '12': // Declined
					case '13': // Referral
						$_SESSION[$this->code.'_error']['cc_number'] = BitBase::getParameter( $responseHash, 'RESPMSG' );
						break;
				}
				if( isset( $responseHash['RESULT'] ) ) {
					$this->result = (int)$responseHash['RESULT'];
					if( $this->result ) {
						$this->mErrors['process_payment'] = $responseHash['RESPMSG'].' ('.$this->result.')';
						$_SESSION[$this->code.'_error']['number'] = $responseHash['RESPMSG'];
					}
				} else {
					foreach( $this->getVarNames() as $key ) {
						unset( $_SESSION[$key] );
					}
					$this->result = 'X';
				}

				$this->response = $response;

			}
		}

		return ( count( $this->mErrors ) == 0 && $this->result === 0 );
	}

	public function getTransactionReference() {
		return $this->pnref;
	}

	function before_process( $pPaymentParameters ) {
		global $_GET, $messageStack, $response, $gBitDb, $gBitUser, $order;
		$order->info['cc_number'] = $pPaymentParameters['cc_number'];
		$order->info['cc_expires'] = $pPaymentParameters['cc_expires'];
		$order->info['cc_type'] = $pPaymentParameters['cc_type'];
		$order->info['cc_owner'] = $pPaymentParameters['cc_owner'];
		$order->info['cc_cvv'] = $pPaymentParameters['cc_cvv'];
		// Calculate the next expected order id
		$nextOrderId = $this->mDb->getOne( "select MAX(`orders_id`) + 1 FROM " . TABLE_ORDERS );

		if( $ret = $this->processPayment( array( 
			'ACCT' => $order->info['cc_number'], 
			'EXPDATE' => $order->info['cc_expires'], 
			'STREET' => $order->billing['street_address'],
			'ZIP' => $order->customer['postcode'],
			'COMMENT1' => 'OrderID: ' . $nextOrderId . ' ' . $order->customer['email_address'] . ' (' . $gBitUser->mUserId . ')',
			'charge_amount' => number_format($order->info['total'], 2,'.',''),
			'CVV2' => $order->getField( 'cc_cvv' ),
			'NAME' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
		) ) ) {
			if( MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY == 'True' ) {
				//replace middle CC num with XXXX
				$order->info['cc_number'] = substr($pPaymentParameters['cc_number'], 0, 6) . str_repeat('X', (strlen($pPaymentParameters['cc_number']) - 6)) . substr($pPaymentParameters['cc_number'], -4);
			}
		}
		if ( $this->result !== 0 ) {
			$this->mDb->RollbackTrans();
			$this->mDb->query( "insert into " . TABLE_PUBS_CREDIT_CARD_LOG . " (customers_id, ref_id, trans_result,trans_auth_code, trans_message, trans_amount, trans_date) values ( ?, ?, ?, '-', ?, ?, 'NOW' )", array(	$gBitUser->mUserId, $this->pnref, (int)$this->result, 'failed for cust_id: '.$gBitUser->mUserId.' - '.$order->customer['email_address'].':'.$responseHash['RESPMSG'], number_format($order->info['total'], 2,'.','') ) );
			$messageStack->add_session('checkout_payment',tra( 'There has been an error processing your credit card, please try again.' ).'<br/>'.$responseHash['RESPMSG'],'error');
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode( tra( 'There has been an error processing your credit card, please try again.' ) ), 'SSL', true, false));
		}
		return $ret;
	}

	/**
	 * Take an array of name-value pairs and return a properly
	 * formatted list. Enforces the following rules:
	 *
	 *	 - Names must be uppercase, all characters must match [A-Z].
	 *	 - Values cannot contain quotes.
	 *	 - If values contain & or =, the name has the length appended to
	 *		 it in brackets (NAME[4] for a 4-character value.
	 *
	 * If any of the "cannot" conditions are violated the function
	 * returns false, and the caller must abort and not proceed with
	 * the transaction.
	 */
	function _buildNameValueList($pairs) {
		// Add the parameters that are always sent.
		$commpairs = array();

		$pairs = array_merge($pairs, $commpairs);

		$string = array();
		foreach ($pairs as $name => $value) {
			if (preg_match('/[^A-Z_0-9]/', $name)) {
				if (PAYPAL_DEV_MODE == 'true') $this->log('_buildNameValueList - datacheck - ABORTING - preg_match found invalid submission key: ' . $name . ' (' . $value . ')');
				return false;
			}
			// remove quotation marks
			$value = str_replace('"', '', $value);
			// if the value contains a & or = symbol, handle it differently
			$string[] = $name . '[' . strlen($value) . ']=' . $value;
		}

		$this->lastParamList = implode('&', $string);
		return $this->lastParamList;
	}

	/**
	 * Take a name/value response string and parse it into an
	 * associative array. Doesn't handle length tags in the response
	 * as they should not be present.
	 */
	function _parseNameValueList($string) {
		$string = str_replace('&amp;', '|', $string);
		$pairs = explode('&', str_replace(array("\r\n","\n"), '', $string));
		//$this->log('['.$string . "]\n\n[" . print_r($pairs, true) .']');
		$values = array();
		foreach ($pairs as $pair) {
			list($name, $value) = explode('=', $pair, 2);
			$values[$name] = str_replace('|', '&amp;', $value);
		}
		return $values;
	}

	/**
	 * Log the current transaction depending on the current log level.
	 *
	 * @access protected
	 *
	 * @param string $operation	The operation called.
	 * @param integer $elapsed	 Microseconds taken.
	 * @param object $response	 The response.
	 */
	function _logTransaction($operation, $response, $errors) {
		$values = $this->_parseNameValueList($response);
		$token = preg_replace('/[^0-9.A-Z\-]/', '', urldecode($values['TOKEN']));
		switch ($this->_logLevel) {
		case PEAR_LOG_DEBUG:
			$message =	 date('Y-m-d h:i:s') . "\n-------------------\n";
			$message .=	'(' . $this->_server . ' transaction) --> ' . $this->_endpoints[$this->_server] . "\n";
			$message .= 'Request Headers: ' . "\n" . $this->_sanitizeLog($this->lastHeaders) . "\n\n";
			$message .= 'Request Parameters: {' . $operation . '} ' . "\n" . urldecode($this->_sanitizeLog($this->_parseNameValueList($this->lastParamList))) . "\n\n";
			$message .= 'Response: ' . "\n" . urldecode($this->_sanitizeLog($values)) . $errors;
			bit_error_log( $message );
			// extra debug email: //
			if (MODULE_PAYMENT_PAYPALWPP_DEBUGGING == 'Log and Email') {
				zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, 'PayPal Debug log - ' . $operation, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array('EMAIL_MESSAGE_HTML'=>nl2br($message)), 'debug');
			}

		case PEAR_LOG_INFO:
			$success = false;
			if ($response) {
				if ((isset($values['RESULT']) && $values['RESULT'] == 0) || strstr($values['ACK'],'Success')) {
					$success = true;
				}
			}
			bit_error_log($operation . ', Elapsed: ' . 'ms -- ' . (isset($values['ACK']) ? $values['ACK'] : ($success ? 'Succeeded' : 'Failed')) . $errors );

		case PEAR_LOG_ERR:
			if (!$response) {
				$this->log('No response from server' . $errors, $token);
			} else {
				if ((isset($values['RESULT']) && $values['RESULT'] != 0) || strstr($values['ACK'],'Failure')) {
					bit_error_log( $response . $errors );
				}
			}
		}
	}

	function after_process() {
		global $insert_id, $order, $gBitDb, $gBitUser, $result;
		$this->mDb->query("insert into " . TABLE_PUBS_CREDIT_CARD_LOG . " (orders_id, customers_id, ref_id, trans_result,trans_auth_code, trans_message, trans_amount,trans_date) values ( ?, ?, ?, ?,'-', ?, ?, 'NOW' )", array( $insert_id, $gBitUser->mUserId, $this->pnref, $this->result, 'success for cust_id:'.$order->customer['email_address'].":".urldecode( $this->response ), number_format( $order->info['total'], 2, '.', '' ) ) );
		return false;
	}


	////////////////////////////////////////////////////
	// If an error occurs with the process, output error messages here
	////////////////////////////////////////////////////

	function get_error() {
		global $_GET;

		$error = array('title' => tra( 'There has been an error processing your credit card, please try again.' ),
									 'error' => stripslashes(urldecode($_GET['error'])));

		return $error;
	}

	function check() {
		global $gBitDb;
		if (!isset($this->_check)) {
			$check_query = $this->mDb->query("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_PAYMENT_PAYFLOWPRO_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}

	function install() {
		global $gBitDb;
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable PayFlow Pro Module', 'MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'True', 'Do you want to accept PayFlow Pro payments?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Login', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'login', 'Your case-sensitive login that you defined at registration.', '6', '2', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Login', 'MODULE_PAYMENT_PAYFLOWPRO_CURRENCY', 'USD', '3-Letter Currency Code in which your Payflow transactions are made. Most typically: USD', '6', '2', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Password', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'password', 'Your case-sensitive password that you defined at registration.', '6', '3', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('PayFlow Pro Activation Mode', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'Test', 'What mode is your account in?<br><em>Test Accounts:</em><br>Visa:4111111111111111<br>MC: 5105105105105100<br><li><b>Live</b> = Activated/Live.</li><li><b>Test</b> = Test Mode</li>', '6', '4', 'zen_cfg_select_option(array(\'Live\', \'Test\'), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Method', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'Authorization', 'Transaction method used for processing orders', '6', '5', 'zen_cfg_select_option(array(\'Authorization\', \'Sale\'), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Vendor ID', 'MODULE_PAYMENT_PAYFLOWPRO_VENDOR', '', 'Your merchant login ID that you created when you registered for the account.', '6', '6', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Partner ID', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'PayPal', 'Your Payflow Partner is provided to you by the authorized Payflow Reseller who signed you up for the PayFlow service. This value is case-sensitive.<br />Typical values: <strong>PayPal</strong> or <strong>VeriSign</strong>', '6', '6', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '9', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Credit Card Privacy', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'True', 'Replace the middle digits of the credit card with XXXX? You will not be able to retrieve the original card number.', '6', '10', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Card Privacy', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'True', '4111XXXXXXXX1111 out credit card numbers in database.', '6', '13', 'NOW')");
	}

	function keys() {
		return array('MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'MODULE_PAYMENT_PAYFLOWPRO_VENDOR', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY');


	}
}


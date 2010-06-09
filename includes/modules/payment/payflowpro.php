<?php
//
// +------------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											|
// +------------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers								|
// |																		|
// | http://www.zen-cart.com/index.php										|
// |																		|
// | Portions Copyright (c) 2003 Zen Cart									|
// +------------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			|
// | that is bundled with this package in the file LICENSE, and is			|
// | available through the world-wide-web at the following url:				|
// | http://www.zen-cart.com/license/2_0.txt.								|
// | If you did not receive a copy of the zen-cart license and are unable 	|
// | to obtain it through the world-wide-web, please send a note to			|
// | license@zen-cart.com so we can mail you a copy immediately.			|
// +------------------------------------------------------------------------+
// $Id$
//
// JJ: This code really needs cleanup as there's some code that really isn't called at all.
//		 I only made enough modifications to make it work with UNIX servers
//		 so you are free to a) cleanup the code or b) make it work with Windows :)
//

class payflowpro {
	var $code, $title, $description, $enabled;

////////////////////////////////////////////////////
// Class constructor -> initialize class variables.
// Sets the class code, description, and status.
////////////////////////////////////////////////////


	function payflowpro() {
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

	// class methods
	function update_payflowpro_status() {
		global $order, $gBitDb;

		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYFLOWPRO_ZONE > 0) ) {
			$check_flag = false;
			$check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_PAYFLOWPRO_ZONE . "' and `zone_country_id` = '" . $order->billing['country']['countries_id'] . "' order by `zone_id`");
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

////////////////////////////////////////////////////
// Javascript form validation
// Check the user input submited on checkout_payment.php with javascript (client-side).
// Examples: validate credit card number, make sure required fields are filled in
////////////////////////////////////////////////////

function javascript_validation() {
if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Advanced') {
		$js = '	if (payment_value == "' . $this->code . '") {' . "\n" .
					'		var cc_owner = document.checkout_payment.payflowpro_cc_owner.value;' . "\n" .
					' var cc_number = document.checkout_payment.payflowpro_cc_number.value;' . "\n" .
											'				 var cc_cvv = document.checkout_payment.payflowpro_cc_csc.value;' . "\n" .
					'		if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
					'			error_message = error_message + "' . tra( '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n' ) . '";' . "\n" .
					'			error = 1;' . "\n" .
					'		}' . "\n" .
					'		if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
					'			error_message = error_message + "' . tra( '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n' ) . '";' . "\n" .
					'			error = 1;' . "\n" .
					'		}' . "\n" .
											'				 if (cc_cvv == "" || cc_cvv.length < "3") {' . "\n".
											'					 error_message = error_message + "' . tra( '* You must enter the 3 or 4 digit number on the back of your credit card\n' ) . '";' . "\n" .
											'					 error = 1;' . "\n" .
											'				 }' . "\n" .
					'	}' . "\n";

		return $js;
	} else {
		return false;
							}
	}
////////////////////////////////////////////////////
// !Form fields for user input
// Output any required information in form fields
// Examples: ask for extra fields (credit card number), display extra information
////////////////////////////////////////////////////



	// Display Credit Card Information Submission Fields on the Checkout Payment Page
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
							 'fields' => array(
								array(	'title' => tra( 'Card Owner\'s Name:' ),
							 			'field' => zen_draw_input_field('payflowpro_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])
								),
								array(	'title' => tra( 'Card Number:' ),
										'field' => zen_draw_input_field('payflowpro_cc_number')
								),
								array(	'title' => tra( 'Expiration Date:' ),
										'field' => zen_draw_pull_down_menu('payflowpro_cc_expires_month', $expires_month) . '&nbsp;' . zen_draw_pull_down_menu('payflowpro_cc_expires_year', $expires_year)
								),
								array(	'title' => tra( 'CVV Number' ) . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . tra( ' (<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . 'More Info' . '</a>)' ) . '</a>',
										'field' => zen_draw_input_field('payflowpro_cc_csc','','SIZE=4, MAXLENGTH=4')
								)
							)
						);

		return $selection;
}

////////////////////////////////////////////////////
// Pre confirmation checks (ie, check if credit card
// information is right before sending the info to
// the payment server
////////////////////////////////////////////////////

	function pre_confirmation_check() {
		global $_POST;

		include(DIR_WS_CLASSES . 'cc_validation.php');
		$result = FALSE;
		if( empty( $_POST['payflowpro_cc_number'] ) ) {
			$error = tra( 'Please enter a credit card number.' );
		} else {
			$cc_validation = new cc_validation();
			$result = $cc_validation->validate($_POST['payflowpro_cc_number'], $_POST['payflowpro_cc_expires_month'], $_POST['payflowpro_cc_expires_year'], $_POST['payflowpro_cc_csc']);

			$error = '';
			switch ($result) {
				case -1:
					$error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
					break;
				case -2:
				case -3:
				case -4:
					$error = TEXT_CCVAL_ERROR_INVALID_DATE;
					break;
				case false:
					$error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
					break;
			}
		}

		if ( ($result == false) || ($result < 1) ) {
			$payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&payflowpro_cc_owner=' . urlencode($_POST['payflowpro_cc_owner']) . '&payflowpro_cc_expires_month=' . $_POST['payflowpro_cc_expires_month'] . '&payflowpro_cc_expires_year=' . $_POST['payflowpro_cc_expires_year'];
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
		}

		$this->cc_card_type = $cc_validation->cc_type;
		$this->cc_card_number = $cc_validation->cc_number;
		$this->cc_expiry_month = $cc_validation->cc_expiry_month;
		$this->cc_expiry_year = $cc_validation->cc_expiry_year;
}

////////////////////////////////////////////////////
// Functions to execute before displaying the checkout
// confirmation page
////////////////////////////////////////////////////

	function confirmation() {
		global $_POST;

		$confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
								'fields' => array(
									array(	'title' => tra( 'Card Owner:' ),
											'field' => $_POST['payflowpro_cc_owner']),
									array(	'title' => tra( 'Card Number:' ),
											'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
									array(	'title' => tra( 'Expiration Date:' ),
											'field' => strftime('%B,%Y', mktime(0,0,0,$_POST['payflowpro_cc_expires_month'], 1, '20' . $_POST['payflowpro_cc_expires_year']))),
									array(	'title' => tra( 'CVV Number' ),
											'field' => $_POST['payflowpro_cc_csc'])
									)
								);

		return $confirmation;
	}

	////////////////////////////////////////////////////
	// Functions to execute before finishing the form
	// Examples: add extra hidden fields to the form
	////////////////////////////////////////////////////
	function process_button() {
		global $_SERVER, $_POST, $order, $total_tax, $shipping_cost, $customer_id;
		// These are hidden fields on the checkout confirmation page
		$process_button_string = zen_draw_hidden_field('cc_owner', $_POST['payflowpro_cc_owner']) .
								 zen_draw_hidden_field('cc_expires', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
								 zen_draw_hidden_field('cc_type', $this->cc_card_type) .
								 zen_draw_hidden_field('cc_number', $this->cc_card_number) .
								 zen_draw_hidden_field('cc_cvv', $_POST['payflowpro_cc_csc']);

		$process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());
		return $process_button_string;
	}


////////////////////////////////////////////////////
// Test Credit Card# 4111111111111111
// Expiration any date after current date.
// Functions to execute before processing the order
// Examples: retreive result from online payment services
////////////////////////////////////////////////////

	function before_process() {
		global $_GET, $messageStack, $gDebug, $_POST, $response, $gBitDb, $gBitUser, $order;
		$order->info['cc_number'] = $_POST['cc_number'];
		$order->info['cc_expires'] = $_POST['cc_expires'];
		$order->info['cc_type'] = $_POST['cc_type'];
		$order->info['cc_owner'] = $_POST['cc_owner'];
		$order->info['cc_cvv'] = $_POST['cc_cvv'];
		// Calculate the next expected order id
		$nextOrderId = $gBitDb->getOne( "select MAX(`orders_id`) + 1 FROM " . TABLE_ORDERS );

		$values['PWD'] = MODULE_PAYMENT_PAYFLOWPRO_PWD;
		$values['USER'] = MODULE_PAYMENT_PAYFLOWPRO_LOGIN;
		$values['VENDOR'] = MODULE_PAYMENT_PAYFLOWPRO_VENDOR;
		$values['PARTNER'] = MODULE_PAYMENT_PAYFLOWPRO_PARTNER;

		$values['ZIP'] = $order->customer['postcode'];
		$values['COMMENT1'] = 'OrderID: ' . $nextOrderId . ' ' . $order->customer['email_address'] . ' (' . $gBitUser->mUserId . ')';
//		$values['COMMENT2'] = 'ZenSessName:' . zen_session_name() . ' ZenSessID:' . zen_session_id() ;
		$values['ACCT'] = $order->info['cc_number'];
		$values['EXPDATE'] = $order->info['cc_expires'];
		$values['STREET'] = $order->billing['street_address'];

		$values['AMT'] = number_format($order->info['total'], 2,'.','');
		if( $order->getField( 'cc_cvv' ) ) {
			$values['CVV2'] = $order->getField( 'cc_cvv' );
		}

		$values['TENDER'] = 'C';
		$values['TRXTYPE'] = (MODULE_PAYMENT_PAYFLOWPRO_TYPE == 'Authorization') ? 'A' : 'S';
		$values['VERBOSITY'] = 'MEDIUM';
		$values['NAME'] = $order->billing['firstname'] . ' ' . $order->billing['lastname'];

		if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') {
			$url='https://pilot-payflowpro.verisign.com/transaction';
		} else {
			$url='https://payflowpro.verisign.com/transaction';
		}

		$values['REQUEST_ID'] = time();

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
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_buildNameValueList($values));

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

			# Check result
			if( isset( $responseHash['PNREF'] ) ) {
				$this->pnref = $responseHash['PNREF'];
			}

			if( isset( $responseHash['RESULT'] ) ) {
				$this->result = $responseHash['RESULT'];
			} else {
				$this->result = 'X';
			}

			$this->response = $response;

			if( MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY == 'True' ) {
				//replace middle CC num with XXXX
				$order->info['cc_number'] = substr($_POST['cc_number'], 0, 6) . str_repeat('X', (strlen($_POST['cc_number']) - 6)) . substr($_POST['cc_number'], -4);
			}

		}

		//if ($result['RESULT'] != "0")
		if ($this->result != "0") {
			$gBitDb->RollbackTrans();
			$gBitDb->query( "insert into " . TABLE_PUBS_CREDIT_CARD_LOG . " (orders_id, customers_id, ref_id, trans_result,trans_auth_code, trans_message, trans_amount, trans_date) values ( NULL, ?, ?, ?, '-', ?, ?, 'NOW' )", array(	$gBitUser->mUserId, $this->pnref, $this->result, 'failed for cust_id: '.$gBitUser->mUserId.' - '.$order->customer['email_address'].':'.$responseHash['RESPMSG'], number_format($order->info['total'], 2,'.','') ) );
			$messageStack->add_session('checkout_payment',tra( 'There has been an error processing you credit card, please try again.' ).'<br/>'.$responseHash['RESPMSG'],'error');
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode( tra( 'There has been an error processing you credit card, please try again.' ) ), 'SSL', true, false));
		}
























/*
		 if (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') {
			 $url="test-payflow.verisign.com";
		 } else {
			 $url="payflow.verisign.com";
		 }

	if (MODULE_PAYMENT_PAYFLOWPRO_SERVEROS=='Windows') { // for Windows servers only
		$objCOM = new COM("PFProCOMControl.PFProCOMControl.1");
		$ctx1 = $objCOM->CreateContext($url, 443, 30, "", 0, "", "");
		$result = $objCOM->SubmitTransaction($ctx1, $parmList, strlen($parmList));
		$objCOM->DestroyContext($ctx1);

		} else {	// end Windows version

			$parmList = str_replace('"','~',$parmList);

		 // The following method requires that the "pfpro" components be compiled into PHP on your server.
		 // Detailed information on the compiling process is contained here:	http://www.php.net/manual/en/ref.pfpro.php
			$transaction = array(USER=> MODULE_PAYMENT_PAYFLOWPRO_LOGIN,
								 PWD => MODULE_PAYMENT_PAYFLOWPRO_PWD,
								 VENDOR=> MODULE_PAYMENT_PAYFLOWPRO_LOGIN,
								 PARTNER=> MODULE_PAYMENT_PAYFLOWPRO_PARTNER,
								 TRXTYPE => ((MODULE_PAYMENT_PAYFLOWPRO_TYPE == 'Authorization') ? 'A' : 'S'),
								 TENDER=> 'C',
								 ZIP=> $order->customer['postcode'],
								 COMMENT1=> 'CustID:' . $_SESSION['customer_id'] . '+OrderID:' . $nextOrderId . '+Email:'. $order->customer['email_address'],
								 COMMENT2=> (MODULE_PAYMENT_PAYFLOWPRO_MODE =='Test') ? '+++Test Transaction+++' : '',
								 ACCT=> $order->info['cc_number'],
								 EXPDATE=> $order->info['cc_expires'],
								 CVV2=> $order->info['cc_cvv'],
								 AMT=> number_format($order->info['total'], 2,'.',''),
								 NAME=> $order->billing['firstname'] . ' ' . $order->billing['lastname'],
								 STREET => $order->customer['street_address']
			);
		putenv("LD_LIBRARY_PATH=".getenv("LD_LIBRARY_PATH").":".DIR_FS_CATALOG."includes/modules/payment/payflowpro");
		putenv("PFPRO_CERT_PATH=".MODULES_PAYMENT_PAYFLOW_PRO_CERT_PATH);
		$resultcodes=exec(DIR_FS_CATALOG.'includes/modules/payment/payflowpro/pfpro '.$url. ' 443 "'.$parmList.'" 30	2>&1', $output, $return_value);

		$resultStrings	= explode( '&', $resultcodes );
		$responseHash = array();
		foreach( $resultStrings as $s ) {
			list($key, $val) = explode( '=', $s, 2 );
			$responseHash[$key] = $val;
		}

		//debug code
		if( $gDebug ){
			echo "calling exec " . (DIR_FS_CATALOG.'includes/modules/payment/bin/pfpro '.$url. ' 443 "'.$parmList.'" 30	2>&1')."<BR>\n";
			echo "RESULTS:<BR>\n";
			print_r($resultcodes);
			echo "<BR>\n";
			exit;
		}

			//$debug='ON';
		list($strA, $strB) = split ('[|]', $resultcodes);
		if ($debug=='ON') $messageStack->add_session("valueA: " . $strA,'error');
		if ($debug=='ON') $messageStack->add_session("valueB: " . $strB,'error');
		if ($debug=='ON' || (zen_not_null($return_value) && $return_value!='0')) $messageStack->add_session('Result code: '.$return_value, 'caution');
		if ($debug=='ON') foreach($output as $key=>$value) {$messageStack->add_session("$key => $value<br />",'caution'); }
		exec("exit");

		$return = '&'.$output[0].'&';

		# Check result
		if( isset( $responseHash['PNREF'] ) ) {
			$this->pnref = $responseHash['PNREF'];
		}

		if( isset( $responseHash['RESULT'] ) ) {
			$this->result = $responseHash['RESULT'];
		} else {
			$this->result = 'X';
		}

		while (list ($key, $val) = each ($output)) {
			$result_list .= $key.'='.urlencode($val).'&';
		}

		$this->result_list = $result_list;

		if( MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY == 'True' ) {
			//replace middle CC num with XXXX
			$order->info['cc_number'] = substr($_POST['cc_number'], 0, 4) . str_repeat('X', (strlen($_POST['cc_number']) - 8)) . substr($_POST['cc_number'], -4);
		}


		$message .= DIR_FS_CATALOG.'includes/modules/payment/bin/pfpro '.$url. ' 443 "'.$parmList.'" 30 ';
		$message .= $url ."\n";
		$message .= $this->result ."\n";
		$message .= $result_list ."\n";
		$message .= $this->pnref ."\n";
		$message .= $return ."\n";
		if ($debug=='ON') {
			zen_mail(STORE_NAME.'payflow pro debug - pre pfpro_process()', EMAIL_FROM, 'payflow pro debug codes' , $message, STORE_NAME, EMAIL_FROM);
		}
	}//End of if Not Windows (else)
*/
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
   * @param string $operation  The operation called.
   * @param integer $elapsed   Microseconds taken.
   * @param object $response   The response.
   */
  function _logTransaction($operation, $response, $errors) {
    $values = $this->_parseNameValueList($response);
    $token = preg_replace('/[^0-9.A-Z\-]/', '', urldecode($values['TOKEN']));
    switch ($this->_logLevel) {
    case PEAR_LOG_DEBUG:
      $message =   date('Y-m-d h:i:s') . "\n-------------------\n";
      $message .=  '(' . $this->_server . ' transaction) --> ' . $this->_endpoints[$this->_server] . "\n";
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
//		$gBitDb->query( "insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, `date_added`) values ('Credit Card processed', ?,?, 'NOW' )", array( (int)$insert_id, DEFAULT_ORDERS_STATUS_ID ) );
	$gBitDb->query("insert into " . TABLE_PUBS_CREDIT_CARD_LOG . " (orders_id, customers_id, ref_id, trans_result,trans_auth_code, trans_message, trans_amount,trans_date) values ( ?, ?, ?, ?,'-', ?, ?, 'NOW' )", array( $insert_id, $gBitUser->mUserId, $this->pnref, $this->result, 'success for cust_id:'.$order->customer['email_address'].":".urldecode( $this->response ), number_format( $order->info['total'], 2, '.', '' ) ) );
	return false;
}


////////////////////////////////////////////////////
// If an error occurs with the process, output error messages here
////////////////////////////////////////////////////

	function get_error() {
		global $_GET;

		$error = array('title' => tra( 'There has been an error processing you credit card, please try again.' ),
									 'error' => stripslashes(urldecode($_GET['error'])));

		return $error;
	}

////////////////////////////////////////////////////
// Check if module is installed (Administration Tool)
// TABLES: configuration
////////////////////////////////////////////////////


	function check() {
		global $gBitDb;
		if (!isset($this->_check)) {
			$check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_PAYMENT_PAYFLOWPRO_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}

////////////////////////////////////////////////////
// Install the module (Administration Tool)
// TABLES: configuration
////////////////////////////////////////////////////

function install() {
global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable PayFlow Pro Module', 'MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'True', 'Do you want to accept PayFlow Pro payments?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Login', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'login', 'Your case-sensitive login that you defined at registration.', '6', '2', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Password', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'password', 'Your case-sensitive password that you defined at registration.', '6', '3', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('PayFlow Pro Activation Mode', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'Test', 'What mode is your account in?<br><em>Test Accounts:</em><br>Visa:4111111111111111<br>MC: 5105105105105100<br><li><b>Live</b> = Activated/Live.</li><li><b>Test</b> = Test Mode</li>', '6', '4', 'zen_cfg_select_option(array(\'Live\', \'Test\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Method', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'Authorization', 'Transaction method used for processing orders', '6', '5', 'zen_cfg_select_option(array(\'Authorization\', \'Sale\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Vendor ID', 'MODULE_PAYMENT_PAYFLOWPRO_VENDOR', '', 'Your merchant login ID that you created when you registered for the account.', '6', '6', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Partner ID', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'PayPal', 'Your Payflow Partner is provided to you by the authorized Payflow Reseller who signed you up for the PayFlow service. This value is case-sensitive.<br />Typical values: <strong>PayPal</strong> or <strong>VeriSign</strong>', '6', '6', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_PAYFLOWPRO_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '8', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '9', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Credit Card Privacy', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'True', 'Replace the middle digits of the credit card with XXXX? You will not be able to retrieve the original card number.', '6', '10', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Card Privacy', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'True', '4111XXXXXXXX1111 out credit card numbers in database.', '6', '13', 'NOW')");
	}

////////////////////////////////////////////////////
// Remove the module (Administration Tool)
// TABLES: configuration
////////////////////////////////////////////////////

	function remove() {
	 global $gBitDb;
		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
	}
////////////////////////////////////////////////////
// Create our Key - > Value Arrays
////////////////////////////////////////////////////
	function keys() {
		return array('MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'MODULE_PAYMENT_PAYFLOWPRO_VENDOR', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', 'MODULE_PAYMENT_PAYFLOWPRO_ZONE', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY');


	}
}













#
# This function creates a temporary file and store some data in it
# It will return filename if successful and "false" if it fails.
#
function func_temp_store($data) {
	$tmpfile = @tempnam(DIR_FS_SQL_CACHE,"zentmp");
	if (empty($tmpfile)) return false;
	$fp = @fopen($tmpfile,"w");
	if (!$fp) {
		@unlink($tmpfile);
		return false;
	}
	fwrite($fp,$data);
	fclose($fp);
		return $tmpfile;
}

#
# This function quotes arguments for shell command according
# to the host operation system
#
function func_shellquote() {
	static $win_s = array(' ', '&');
	static $win_r = array('" "','"&"');
	$result = "";
	$args = func_get_args();
	foreach ($args as $idx=>$arg)
		$args[$idx] = X_DEF_OS_WINDOWS ? (str_replace($win_s,$win_r,$arg)) : (escapeshellarg($arg));

	return implode(' ', $args);
}

?>

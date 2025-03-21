<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * Portions Copyright (c) 2003 Zen Cart									|
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 *
 * Documentation from https://developer.paypal.com/docs/classic/payflow/integration-guide/
 */

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginPaymentCardBase.php' );

class payflowpro extends CommercePluginPaymentCardBase {

	public function __construct() {
		parent::__construct();
		$this->adminTitle = tra( 'PayPal PayFlow Pro' ); // Payment module title in Admin
		$this->description = tra( 'Process credit cards with the Payflow Pro payment Gateway.' );
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



	////////////////////////////////////////////////////
	// Pre confirmation checks (ie, check if credit card
	// information is right before sending the info to
	// the payment server
	////////////////////////////////////////////////////

	////////////////////////////////////////////////////
	// Functions to execute before displaying the checkout
	// confirmation page
	////////////////////////////////////////////////////

	function confirmation( $pPaymentParams ) {
		$confirmation = array('title' => $this->getPaymentType( $pPaymentParams ),
								'fields' => array(
									array(	'title' => tra( 'Card Owner' ),
											'field' => $pPaymentParams['payment_owner']),
									array(	'title' => tra( 'Card Number' ),
											'field' => $this->getPaymentNumber( $pPaymentParams, TRUE )),
									array(	'title' => tra( 'Expiration Date' ),
											'field' => strftime('%B,%Y', mktime(0,0,0,$pPaymentParams['payment_expires_month'], 1, '20' . $pPaymentParams['payment_expires_year']))),
									)
								);

		return $confirmation;
	}

	////////////////////////////////////////////////////
	// Functions to execute before finishing the form
	// Examples: add extra hidden fields to the form
	////////////////////////////////////////////////////
	function process_button( $pPaymentParams ) {
		// These are hidden fields on the checkout confirmation page
		$process_button_string = zen_draw_hidden_field('payment_owner', $this->payment_owner ) .
								 zen_draw_hidden_field('payment_expires_month', $this->payment_expires_month ) .
								 zen_draw_hidden_field('payment_expires_year', $this->payment_expires_year ) .
								 zen_draw_hidden_field('payment_type', $this->payment_type) .
								 zen_draw_hidden_field('payment_number', $this->payment_number) .
								 zen_draw_hidden_field('payment_cvv', $this->payment_cvv);
		return $process_button_string;
	}

	function getDefaultCurrency() {
		return $this->getCommerceConfig( 'MODULE_PAYMENT_PAYFLOWPRO_CURRENCY', 'USD' );
	}

	function getSupportedCurrencies() {
		$ret = array();
		if( $currencyList = $this->getModuleConfigValue( '_FOREIGN_CURRENCIES' ) ) {
			$ret = explode( ',', $currencyList );
		}
		$ret[] = $this->getDefaultCurrency();

		return $ret;
	}

	public function processPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {
		global $messageStack, $response, $gBitDb, $gBitUser, $currencies;

		$postFields = array();
		$responseHash = array();
		$this->result = NULL;

		if( self::verifyPayment( $pOrder, $pPaymentParams, $pSessionParams ) ) {

			/* === Core Credit Card Parameters ===

			All credit card processors accept the basic parameters described in the following table* with one exception: the PayPal processor does not support SWIPE*.

			TENDER	(Required) The method of payment. Values are:
			- A = Automated clearinghouse (ACH)
			- C = Credit card
			- D = Pinless debit
			- K = Telecheck
			- P = PayPal
			Note: If your processor accepts non-decimal currencies, such as, Japanese Yen, include a decimal in the amount you pass to Payflow (use 100.00 not 100). Payflow removes the decimal portion before sending the value to the processor.

			// Not implemented

			RECURRING	(Optional) Identifies the transaction as recurring. It is one of the following values: - Y - Identifies the transaction as recurring. - N - Does not identify the transaction as recurring (default).
			SWIPE		(Required for card-present transactions only) Used to pass the Track 1 or Track 2 data (card's magnetic stripe information) for card-present transactions. Include either Track 1 or Track 2 data, not both. If Track 1 is physically damaged, the point-of-sale (POS) application can send Track 2 data instead.
			ORDERID		(Optional) Checks for a duplicate order. If you pass ORDERID in a request and pass it again in the future, the response returns DUPLICATE=2 along with the ORDERID.  Note: Do not use ORDERID to catch duplicate orders processed within seconds of each other. Use ORDERID with Request ID to prevent duplicates as a result of processing or communication errors. * bitcommerce note - this cannot be $pPaymentParams['orders_id'] as a failed process will block any future transactions
			*/


			$postFields =  array( 
				'PWD' => MODULE_PAYMENT_PAYFLOWPRO_PWD,
				'USER' => MODULE_PAYMENT_PAYFLOWPRO_LOGIN,
				'VENDOR' => MODULE_PAYMENT_PAYFLOWPRO_VENDOR,
				'PARTNER' => MODULE_PAYMENT_PAYFLOWPRO_PARTNER,
				'VERBOSITY' => 'HIGH',
				'TENDER' => 'C',
				'REQUEST_ID' => time(),

				'STREET' => $pOrder->billing['street_address'],
				'ZIP' => $pOrder->billing['postcode'],
				'COMMENT1' => 'OrderID: ' . $pOrder->mDb->mName . '-' . $pPaymentParams['orders_id'] . ' ' . $pPaymentParams['payment_email'] . ' (' . $pPaymentParams['payment_user_id'] . ')', // (Optional) Merchant-defined value for reporting and auditing purposes.  Limitations: 128 alphanumeric characters
				'EMAIL' => $pPaymentParams['payment_email'],	// (Optional) Email address of payer.  Limitations: 127 alphanumeric characters.
				'NAME' => $this->getPaymentOwner( $pPaymentParams ),

				'BILLTOFIRSTNAME' => $pOrder->billing['firstname'], //	(Optional) Cardholder's first name.  Limitations: 30 alphanumeric characters
				'BILLTOLASTNAME' => $pOrder->billing['lastname'], //	(Optional but recommended) Cardholder's last name.  Limitations: 30 alphanumeric characters
				'BILLTOSTREET' => $pOrder->billing['street_address'], //	(Optional) The cardholder's street address (number and street name).  The address verification service verifies the STREET address.  Limitations: 30 alphanumeric characters
				'BILLTOSTREET2' => $pOrder->billing['suburb'], //	(Optional) The second line of the cardholder's street address.  The address verification service verifies the STREET address.  Limitations: 30 alphanumeric characters
				'BILLTOCITY' => $pOrder->billing['city'], //	(Optional) Bill-to city.  Limitations: 20-character string.
				'BILLTOSTATE' => $pOrder->billing['state'], //	(Optional) Bill-to state.  Limitations: 2-character string.
				'BILLTOZIP' => $pOrder->billing['postcode'], //	(Optional) Cardholder's 5- to 9-digit zip (postal) code.  Limitations: 9 characters maximum. Do not use spaces, dashes, or non-numeric characters
				'BILLTOCOUNTRY' => $pOrder->billing['countries_iso_code_2'], //	(Optional) Bill-to country. The Payflow API accepts 3-digit numeric country codes. Refer to the ISO 3166-1 numeric country codes.  Limitations: 3-character country code.
				'BILLTOPHONENUM' => $pOrder->billing['telephone'], // (Optional) Account holder's telephone number.  Character length and limitations: 10 characters

				'SHIPTOFIRSTNAME' => $pOrder->delivery['firstname'], //	(Optional) Ship-to first name.  Limitations: 30-character string.
				'SHIPTOLASTNAME' => $pOrder->delivery['lastname'], //	(Optional) Ship-to last name.  Limitations: 30-character string.billingbilling
				'SHIPTOSTREET' => $pOrder->delivery['street_address'], //	(Optional) Ship-to street address.  Limitations: 30-character string.
				'SHIPTOCITY' => $pOrder->delivery['city'], //	(Optional) Ship-to city.  Limitations: 20-character string.
				'SHIPTOSTATE' => $pOrder->delivery['state'], //	(Optional) Ship-to state.  Limitations: 2-character string.
				'SHIPTOZIP' => $pOrder->delivery['postcode'], //	(Optional) Ship-to postal code.  Limitations: 9-character string.
				'SHIPTOCOUNTRY' => $pOrder->delivery['countries_iso_code_2'], //	(Optional) Ship-to country. The Payflow API accepts 3-digit numeric country codes. Refer to the ISO 3166-1 numeric country codes.  Limitations: 3-character country code

			);

			if( $pPaymentParams['payment_user_id'] != $gBitUser->mUserId ) {
				$postFields['COMMENT1'] .= ' / '.$gBitUser->getField( 'login' ).' ('.$gBitUser->mUserId.')';
			}
			if( !empty( $pPaymentParams['payment_ref_id'] ) ) {	
				$postFields['ORIGID'] = $pPaymentParams['payment_ref_id'];
				$postFields['COMMENT2'] = 'Reference Trans for '.$postFields['ORIGID']; //	(Optional) Merchant-defined value for reporting and auditing purposes.  Limitations: 128 alphanumeric characters
			} else {
				$postFields['ACCT'] = $this->getPaymentNumber( $pPaymentParams ); // (Required for credit cards) Credit card or purchase card number. For example, ACCT=5555555555554444. For the pinless debit TENDER type, ACCT can be the bank account number. 
				$postFields['CVV2'] = $pOrder->getField( 'payment_cvv' ); // (Optional) A code printed (not imprinted) on the back of a credit card. Used as partial assurance that the card is in the buyer's possession.  Limitations: 3 or 4 digits
				$postFields['EXPDATE'] = $this->getPaymentExpires( $pPaymentParams ); // (Required) Expiration date of the credit card. For example, 1215 represents December 2015.
				$postFields['INVNUM'] = $pOrder->mDb->mName.'-'.$pPaymentParams['orders_id']; // (Optional) Your own unique invoice or tracking number.

				$postFields['FREIGHTAMT'] = $pOrder->getFieldLocalized( 'shipping_cost' ); // 	(Optional) Total shipping costs for this order.  Nine numeric characters plus decimal.
				// TAXAMT = L_QTY0 * L_TAXAMT0 + L_QTY1 * L_TAXAMT1 + L_QTYn * L_TAXAMTn
				$postFields['TAXAMT'] = $pOrder->getFieldLocalized('tax');
			}

			/*
			TRXTYPE	(Required) Indicates the type of transaction to perform. Values are:
			- A = Authorization
			- B = Balance Inquiry
			- C = Credit (Refund)
			- D = Delayed Capture
			- F = Voice Authorization
			- I = Inquiry
			- K = Rate Lookup
			- L = Data Upload
			- N = Duplicate Transaction Note: A type N transaction represents a duplicate transaction (version 4 SDK or HTTPS interface only) with a PNREF that is the same as the original. It appears only in the PayPal Manager user interface and never settles.
			- S = Sale 
			- V = Void
			*/ 

			$postFields['CURRENCY'] = $pPaymentParams['payment_currency'];

			$processors = static::getProcessors();

			switch( $this->getCommerceConfig( 'MODULE_PAYMENT_PAYFLOWPRO_PROCESSOR' ) ) {
				case 'Cielo Payments':
					// TODO Additional Credit Card Parameters
					break;
				case 'Elavon':
					// TODO Additional Credit Card Parameters
					break;
				case 'First Data Merchant Services Nashville':
					// TODO Additional Credit Card Parameters
					break;
				case 'First Data Merchant Services North':
					// TODO Additional Credit Card Parameters
					break;
				case 'Heartland':
					// TODO Additional Credit Card Parameters
					break;
				case 'Litle':
					// TODO Additional Credit Card Parameters
					break;
				case 'Paymentech Salem New Hampshire':
					// TODO Additional Credit Card Parameters
					break;
				case 'PayPal':
					$postFields['CUSTIP'] = $_SERVER['REMOTE_ADDR']; // (Optional) IP address of payer's browser as recorded in its HTTP request to your website. This value is optional but recommended.  Note: PayPal records this IP address as a means to detect possible fraud.  Limitations: 15-character string in dotted quad format: xxx.xxx.xxx.xxx
					//$postFields['MERCHDESCR'] = ''; //	(Optional) Information that is usually displayed in the account holder's statement, for example, <Your-Not-For-Profit> <State>, <Your-Not-For-Profit> <Branch-Name>, <Your-Website> dues or <Your-Website> list fee.  Character length and limitations: 23 alphanumeric characters, can include the special characters dash (-) and dot (.) only. Asterisks (*) are NOT permitted. If it includes a space character (), enclose the "<Soft-Descriptor>" value in double quotes.
					$postFields['MERCHANTCITY'] = substr( $_SERVER['SERVER_NAME'], 0, 21 ); //	(Optional) A unique phone number, email address or URL, which is displayed on the account holder's statement. PayPal recommends passing a toll-free phone number because, typically, this is the easiest way for a buyer to contact the seller in the case of an inquiry.

					/* === PayPal Specific Parameters

					Note: You must set CURRENCY to one of the three-character currency codes for any of the supported PayPal currencies. See CURRENCY in this table for details.
					Limitations: Nine numeric characters plus decimal (.) character. No currency symbol. Specify the exact amount to the cent using a decimal point; use 34.00, not 34. Do not include comma separators; use 1199.95 not 1,199.95. Nine numeric characters plus decimal.

					* BUTTONSOURCE	(Optional) Identification code for use by third-party applications to identify transactions.  Limitations: 32 alphanumeric characters.
					* CAPTURECOMPLETE	(Optional) Indicates if this Delayed Capture transaction is the last capture you intend to make. The values are: - Y (default) - N
					Note: If CAPTURECOMPLETE is Y, any remaining amount of the original reauthorized transaction is automatically voided.
					Limitations: 12-character alphanumeric string.
					* CUSTOM	(Optional) A free-form field for your own use.  Limitations: 256-character alphanumeric string.
					Limitations: 127 alphanumeric characters.
					* TAXAMT	(Required if L_TAXAMTn is specified) Sum of tax for all items in this order.
					Limitations: Nine numeric characters plus decimal.
					* HANDLINGAMT	(Optional) Total handling costs for this order.
					Nine numeric characters plus decimal.
					* INSURANCEAMT	(Optional) Total shipping insurance cost for this order.  Limitations: Nine numeric characters plus decimal (.) character. No currency symbol. Specify the exact amount to the cent using a decimal point; use 34.00, not 34. Do not include comma separators; use 1199.95 not 1,199.95.
					* L_NAMEn	(Optional) Line-item name.
					Character length and limitations: 36 alphanumeric characters.
					* L_DESCn	(Optional) Line-item description of the item purchased such as hiking boots or cooking utensils.
					* L_COSTn	(Required if L_QTYn is supplied) Cost of the line item. The line-item unit price must be a positive number and be greater than zero.
					Note: You must set CURRENCY to one of the three-character currency codes for any of the supported PayPal currencies. See CURRENCY in this table for details.
					Limitations: Nine numeric characters plus decimal (.) character. No currency symbol. Specify the exact amount to the cent using a decimal point; use 34.00, not 34. Do not include comma separators; use 1199.95 not 1,199.95. Nine numeric characters plus decimal.
					* L_QTYn	(Required if L_COSTn is supplied) Line-item quantity.
					Limitations: 10-character integer.
					* L_SKUn	(Optional) Product number. 
					Limitations: 18-characters.
					* L_TAXAMTn	(Optional) Line-item tax amount.
					Limitations: Nine numeric characters plus decimal (.) character. No currency symbol. Specify the exact amount to the cent using a decimal point; use 34.00, not 34. Do not include comma separators; use 1199.95 not 1,199.95.
					* MERCHANTSESSIONID	(Optional) Your customer Direct Payment session identification token. PayPal records this session token as an additional means to detect possible fraud.  Limitations: 64 characters.
					Character length and limitations: 13 characters including special characters, such as, space, !, ", #, $, %, &, ', (, ), +, -,*, /, :, ;, <, =, >, ?, @, comma and period.

					If it includes the space character (), enclose the "<Soft-Descriptor-City>" value in double quotes.

					Note: Underscore (_) is an illegal character for this field. If it is passed, then it will be removed leaving the remaining characters in the same order. For example, New_York changes to NewYork.
					Added in version 115 of the API.

					* NOTIFYURL	(Optional) Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify NOTIFYURL in the request, the notification URL from your Merchant Profile is used, if one exists.
					Limitations: 2048 alphanumeric characters.
					* ORDERDESC	(Optional) Description of items the customer is purchasing.
					Limitations: 127 alphanumeric characters.
					* RECURRINGTYPE	(Optional) Type of transaction occurrence. The values are: - F = First occurrence - S = Subsequent occurrence (default) Limitations: One alpha character.
					*/
					break;
				case 'SecureNet':
					// TODO Additional Credit Card Parameters
					break;
				case 'Vantiv':
					// TODO Additional Credit Card Parameters
					break;
				case 'WorldPay':
					// TODO Additional Credit Card Parameters
					break;
			}

			if( MODULE_PAYMENT_PAYFLOWPRO_TYPE == 'Authorization' ) {
				$postFields['TRXTYPE'] = 'A';
			} elseif( $pPaymentParams['payment_amount'] > 0 ) {
				$postFields['TRXTYPE'] = 'S';
			} elseif( $pPaymentParams['payment_amount'] < 0 ) {
				$postFields['TRXTYPE'] = 'C';
				$pPaymentParams['payment_amount'] = -1.0 * $pPaymentParams['payment_amount'];
			}

			$postFields['AMT'] = $pPaymentParams['payment_amount']; // (Required) Amount (Default: U.S. based currency). Nnumeric characters and a decimal only. The maximum length varies depending on your processor. Specify the exact amount to the cent using a decimal point (use 34.00 not 34). Do not include comma separators (use 1199.95 not 1,199.95). Your processor or Internet Merchant Account provider may stipulate a maximum amount.

			$paymentDecimal = $currencies->get_decimal_places( $pPaymentParams['payment_currency'] );

			// ITEMAMT	(Required if L_COSTn is specified). Sum of cost of all items in this order. 
			// ITEMAMT = L_QTY0 * LCOST0 + L_QTY1 * LCOST1 + L_QTYn * L_COSTn Limitations: Nine numeric characters plus decimal.
			$shippingAmount = $currencies->convert( $pPaymentParams['payment_currency'] );
			$postFields['ITEMAMT'] = number_format( $pPaymentParams['payment_amount'] - $pOrder->getFieldLocalized('shipping_cost') - $pOrder->getFieldLocalized('tax'), $paymentDecimal, '.', '' );
			// DISCOUNT	(Optional) Shipping discount for this order. Specify the discount as a positive amount.  Limitations: Nine numeric characters plus decimal (.) character. No currency symbol. Specify the exact amount to the cent using a decimal point; use 34.00, not 34. Do not include comma separators; use 1199.95 not 1,199.95.

//			$postFields['DISCOUNT'] = number_format( ($pOrder->getFieldLocalized('total') - $postFields['AMT']), $paymentDecimal, '.', '' );

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
									CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
								);
			foreach ($_curlOptions as $name => $value) {
				curl_setopt($ch, $name, $value);
			}

			$response = curl_exec($ch);

			if( $commError = curl_error($ch) ) {
				$this->mErrors['curl_errno'] = curl_errno($ch);
				$this->mErrors['curl_info'] = @curl_getinfo($ch);
				$this->mErrors['process_payment'] = 'CURL ERROR '.$this->mErrors['curl_errno'];
			}

			curl_close($ch);

			$logHash = $this->prepPayment( $pOrder, $pPaymentParams );

			if( $response ) {
				$responseHash = $this->_parseNameValueList($response);

				$this->result = NULL;
				$this->pnref = '';
				# Check result
				if( isset( $responseHash['PNREF'] ) ) {
					$this->pnref = $responseHash['PNREF'];
					$logHash['payment_ref_id'] = $responseHash['PNREF'];
				}

				if( isset( $responseHash['RESULT'] ) ) {
					$this->result = (int)$responseHash['RESULT'];
					$logHash['payment_mode'] = 'charge';
					$logHash['payment_result'] = $this->result;
					$logHash['payment_message'] = $responseHash['RESPMSG'];
					if( BitBase::getParameter( $responseHash, 'DUPLICATE' ) == 2 ) {
						$duplicateError = 'Duplicate Order ( '.$responseHash['ORDERID'].' )';
						$this->mErrors['process_payment'] = $duplicateError;
						$pSessionParams[$this->code.'_error']['number'] = $duplicateError;
					} elseif( $this->result ) {
						$this->mErrors['process_payment'] = $responseHash['RESPMSG'].' ('.$this->result.')';
						$pSessionParams[$this->code.'_error']['number'] = $responseHash['RESPMSG'];
					} else {
						$this->clearSessionDetails();
					}
				} else {
					$this->clearSessionDetails();
					$this->result = 'X';
				}
				$this->response = $response;
			}
		} 

		if( count( $this->mErrors ) == 0 && $this->result === 0 ) {
			$ret = TRUE;
			$logHash['is_success'] = 'y';
			$logHash['payment_status'] = 'Success';
			$logHash['payment_ref_id'] = BitBase::getParameter( $responseHash, 'PNREF' );
			$pOrder->info['payment_ref_id'] = BitBase::getParameter( $responseHash, 'PNREF' );
			if( !empty( $postFields['ACCT'] ) && MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY == 'True' ) {
				//replace middle CC num with XXXX
				$pOrder->info['payment_number'] = $this->privatizePaymentNumber( $postFields['ACCT'] );
			}
		} else {
			foreach( array( 'PWD', 'USER', 'VENDOR', 'PARTNER', 'CVV2' ) as $field ) {
				if( isset( $postFields[$field] ) ) { unset( $postFields[$field] ); }
			}

			if( isset( $postFields['ACCT'] ) ) { $postFields['ACCT'] = $this->privatizePaymentNumber( $postFields['ACCT'] ); }
			
			bit_error_email( 'PAYMENT ERROR on '.php_uname( 'n' ).': '.BitBase::getParameter( $this->mErrors, 'process_payment' ), bit_error_string(), array( 'mErrors' => $this->mErrors, 'CURL' => $postFields, 'RESPONSE' => $responseHash ) );
			$this->mDb->RollbackTrans();
			$messageStack->add_session('checkout_payment',tra( 'There has been an error processing your payment, please try again.' ).'<br/>'.BitBase::getParameter( $responseHash, 'RESPMSG' ),'error');
			$ret = FALSE;
		}
		if( !empty( $logHash ) ) {
			$pPaymentParams['result'] = $logHash;
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

	////////////////////////////////////////////////////
	// If an error occurs with the process, output error messages here
	////////////////////////////////////////////////////

	function get_error() {
		global $_GET;

		$error = array('title' => tra( 'There has been an error processing your credit card, please try again.' ),
									 'error' => stripslashes(urldecode($_GET['error'])));

		return $error;
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 20;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_MODE' => array(
				'configuration_title' => 'PayFlow Pro Activation Mode',
				'configuration_value' => 'Test',
				'configuration_description' => 'What mode is your account in?<br><em>Test Accounts:</em><br>Visa:4111111111111111<br>MC: 5105105105105100<br><li><b>Live</b> = Activated/Live.</li><li><b>Test</b> = Test Mode</li>',
				'set_function' => "zen_cfg_select_option(array('Live', 'Test'), ",
			),
			$this->getModuleKeyTrunk().'_LOGIN' => array(
				'configuration_title' => 'PayFlow Pro Login',
				'configuration_value' => 'login',
				'configuration_description' => 'Your case-sensitive login that you defined at registration.',
			),
			$this->getModuleKeyTrunk().'_PWD' => array(
				'configuration_title' => 'PayFlow Pro Password',
				'configuration_value' => 'password',
				'configuration_description' => 'Your case-sensitive password that you defined at registration.',
			),
			$this->getModuleKeyTrunk().'_PROCESSOR' => array(
				'configuration_title' => 'Payment Processor',
				'configuration_value' => 'PayPal',
				'configuration_description' => 'Payment processor configured in your Payflow Pro account.',
				'set_function' => "zen_cfg_select_option(payflowpro::getProcessors(), ",
			),
			$this->getModuleKeyTrunk().'_CURRENCY' => array(
				'configuration_title' => 'PayFlow Pro Currency',
				'configuration_value' => 'USD',
				'configuration_description' => '3-Letter Currency Code in which your Payflow transactions are made. Most typically: USD',
			),
			$this->getModuleKeyTrunk().'_FOREIGN_CURRENCIES' => array(
				'configuration_title' => 'Foreign Currency Support',
				'configuration_description' => 'Enter comma-separated list of foreign currencies abbreviations. PayPal Processor has limited support. Example: AUD,CAD,EUR,GBP,JPY',
			),
			$this->getModuleKeyTrunk().'_TYPE' => array(
				'configuration_title' => 'Transaction Method',
				'configuration_value' => 'Sale',
				'configuration_description' => 'Transaction method used for processing orders',
				'set_function' => "zen_cfg_select_option(array('Authorization', 'Sale'), ",
			),
			$this->getModuleKeyTrunk().'_VENDOR' => array(
				'configuration_title' => 'PayFlow Vendor ID', 
				'configuration_description' => 'Your merchant login ID that you created when you registered for the account.',
			),
			$this->getModuleKeyTrunk().'_PARTNER' => array(
				'configuration_title' => 'PayFlow Partner ID',
				'configuration_value' => 'PayPal',
				'configuration_description' => 'Your Payflow Partner is provided to you by the authorized Payflow Reseller who signed you up for the PayFlow service. This value is case-sensitive.<br />Typical values: <strong>PayPal</strong> or <strong>VeriSign</strong>',
			),
			$this->getModuleKeyTrunk().'_CARD_PRIVACY' => array(
				'configuration_title' => 'Credit Card Privacy',
				'configuration_value' => 'True',
				'configuration_description' => 'Replace the middle digits of the credit card with XXXX? You will not be able to retrieve the original card number.',
				'set_function' => "zen_cfg_select_option(array('True', 'False'), ",
			),
		) );
	}

	static function getProcessors() {
		return array(
			'Cielo Payments',
			'Elavon',
			'First Data Merchant Services Nashville',
			'First Data Merchant Services North',
			'Heartland',
			'Litle',
			'Paymentech Salem New Hampshire',
			'PayPal',
			'SecureNet',
			'Vantiv',
			'WorldPay',
		);
	}
}

	function zen_cfg_paypflow_gateway( $pProcessor, $key = '' ) {
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

		return zen_draw_pull_down_menu($name, payflowpro::getProcessors(), $pProcessor );
	}

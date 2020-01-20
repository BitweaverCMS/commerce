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

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentCardBase.php' );

class payflowpro extends CommercePluginPaymentCardBase {
	var $code, $title, $description, $enabled;

	public function __construct() {
		parent::__construct();

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
									'field' => zen_draw_input_field('cc_owner', BitBase::getParameter( $_SESSION, 'cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'] ), 'autocomplete="cc-name"' )
							),
							array(	'field' => '<div class="row"><div class="col-xs-8 col-sm-8"><label class="control-label">'.tra( 'Card Number' ).'</label>' . zen_draw_input_field('cc_number', BitBase::getParameter( $_SESSION, 'cc_number' ), ' autocomplete="cc-number" ', 'number' ) . '</div><div class="col-xs-4 col-sm-4"><label class="control-label"><i class="icon-credit-card"></i> ' . tra( 'CVV Number' ) . '</label>' . zen_draw_input_field('cc_cvv', BitBase::getParameter( $_SESSION, 'cc_cvv' ), ' autocomplete="cc-csc" ', 'number')  . '</div></div>',
							),
							array(	'title' => tra( 'Expiration Date' ),
									'field' => '<div class="row"><div class="col-xs-7 col-sm-9">' . zen_draw_pull_down_menu('cc_expires_month', $expireMonths, BitBase::getParameter( $_SESSION, 'cc_expires_month' ), ' class="input-small" autocomplete="cc-exp-month" ') . '</div><div class="col-xs-5 col-sm-3">' . zen_draw_pull_down_menu('cc_expires_year', $expireYears, substr( BitBase::getParameter( $_SESSION, 'cc_expires_year', (date('Y') + 1) ), -2 ), ' class="input-small" autocomplete="cc-exp-year" ') . '</div></div>'
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
		$confirmation = array('title' => $this->cc_type,
								'fields' => array(
									array(	'title' => tra( 'Card Owner' ),
											'field' => $pPaymentParameters['cc_owner']),
									array(	'title' => tra( 'Card Number' ),
											'field' => $this->privatizeCard( $pPaymentParameters['cc_number'] )),
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
		// These are hidden fields on the checkout confirmation page
		$process_button_string = zen_draw_hidden_field('cc_owner', $this->cc_owner ) .
								 zen_draw_hidden_field('cc_expires_month', $this->cc_expires_month ) .
								 zen_draw_hidden_field('cc_expires_year', $this->cc_expires_year ) .
								 zen_draw_hidden_field('cc_type', $this->cc_type) .
								 zen_draw_hidden_field('cc_number', $this->cc_number) .
								 zen_draw_hidden_field('cc_cvv', $this->cc_cvv);
		return $process_button_string;
	}

	function getProcessorCurrency() {
		global $gCommerceSystem;
		return $gCommerceSystem->getConfig( 'MODULE_PAYMENT_PAYFLOWPRO_CURRENCY', 'USD' );
	}

	function processPayment( &$pPaymentParameters, &$pOrder ) {
		global $gCommerceSystem, $messageStack, $response, $gBitDb, $gBitUser, $currencies;

		$postFields = array();
		$responseHash = array();
		$this->result = NULL;


		if( !self::verifyPayment ( $pPaymentParameters, $pOrder ) ) {
			// verify basics failed
		} elseif( !empty( $pPaymentParameters['cc_ref_id'] ) && empty( $pPaymentParameters['charge_amount'] ) ) {
			$this->mErrors['charge_amount'] = 'Invalid amount';
		} elseif( !($orderTotal = $pOrder->getPaymentDue()) ) {
			$this->mErrors['charge_amount'] = 'Invalid amount';
		} else {
			if( !empty( $pPaymentParameters['cc_ref_id'] ) ) {
				// reference transaction
				$this->paymentOrderId = $pOrder->mOrdersId;
				$paymentCurrency = BitBase::getParameter( $pPaymentParameters, 'charge_currency', DEFAULT_CURRENCY );
				$paymentDecimal = $currencies->get_decimal_places( $paymentCurrency );
				$paymentLocalized = number_format( $pPaymentParameters['charge_amount'], $paymentDecimal, '.', '' ) ;
				$paymentNative = (( $paymentCurrency != DEFAULT_CURRENCY ) ? $paymentLocalized / $pPaymentParameters['charge_currency_value'] : $paymentLocalized);
				// completed orders have a single joined 'name' field
				$pOrder->billing['firstname'] = substr( $pOrder->billing['name'], 0, strpos( $pOrder->billing['name'], ' ' ) );
				$pOrder->billing['lastname'] = substr( $pOrder->billing['name'], strpos( $pOrder->billing['name'], ' ' ) + 1 );
				$pOrder->delivery['firstname'] = substr( $pOrder->billing['name'], 0, strpos( $pOrder->billing['name'], ' ' ) );
				$pOrder->delivery['lastname'] = substr( $pOrder->billing['name'], strpos( $pOrder->billing['name'], ' ' ) + 1 );
			} else {
				$pOrder->info['cc_number'] = $this->cc_number;
				$pOrder->info['cc_expires'] = $this->cc_expires;
				$pOrder->info['cc_type'] = $this->cc_type;
				$pOrder->info['cc_owner'] = $this->cc_owner;
				$pOrder->info['cc_cvv'] = $this->cc_cvv;
				// Calculate the next expected order id
				$this->paymentOrderId = $pOrder->getNextOrderId();
				// orderTotal is in the system DEFAULT_CURRENCY. orderTotal * currency_value = localizedPayment
				$paymentCurrency = BitBase::getParameter( $pOrder->info, 'currency', DEFAULT_CURRENCY );
				$paymentDecimal = $currencies->get_decimal_places( $paymentCurrency );
				$paymentNative = $orderTotal;
				$paymentLocalized = number_format( ($paymentCurrency != DEFAULT_CURRENCY ? ($paymentNative * $pOrder->getField('currency_value')) : $paymentNative), $paymentDecimal, '.', '' ) ;
			}

			$paymentEmail = BitBase::getParameter( $pOrder->customer, 'email_address', $gBitUser->getField('email') );
			$paymentUserId = BitBase::getParameter( $pOrder->customer, 'user_id', $gBitUser->getField('user_id') );


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
			ORDERID		(Optional) Checks for a duplicate order. If you pass ORDERID in a request and pass it again in the future, the response returns DUPLICATE=2 along with the ORDERID.  Note: Do not use ORDERID to catch duplicate orders processed within seconds of each other. Use ORDERID with Request ID to prevent duplicates as a result of processing or communication errors. * bitcommerce note - this cannot be paymentOrderId as a failed process will block any future transactions
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
				'COMMENT1' => 'OrderID: ' . $pOrder->mDb->mName . '-' . $this->paymentOrderId . ' ' . $paymentEmail . ' (' . $paymentUserId . ')', // (Optional) Merchant-defined value for reporting and auditing purposes.  Limitations: 128 alphanumeric characters
				'EMAIL' => $paymentEmail,	// (Optional) Email address of payer.  Limitations: 127 alphanumeric characters.
				'NAME' => BitBase::getParameter( $pOrder->info, 'cc_owner' ),

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

			if( $paymentUserId != $gBitUser->mUserId ) {
				$postFields['COMMENT1'] .= ' / '.$gBitUser->getField( 'login' ).' ('.$gBitUser->mUserId.')';
			}
			if( !empty( $pPaymentParameters['cc_ref_id'] ) ) {	
				$postFields['ORIGID'] = $pPaymentParameters['cc_ref_id'];
				$postFields['COMMENT2'] = 'Reference Trans for '.$postFields['ORIGID']; //	(Optional) Merchant-defined value for reporting and auditing purposes.  Limitations: 128 alphanumeric characters
			} else {
				$postFields['ACCT'] = $pOrder->info['cc_number']; // (Required for credit cards) Credit card or purchase card number. For example, ACCT=5555555555554444. For the pinless debit TENDER type, ACCT can be the bank account number. 
				$postFields['CVV2'] = $pOrder->getField( 'cc_cvv' ); // (Optional) A code printed (not imprinted) on the back of a credit card. Used as partial assurance that the card is in the buyer's possession.  Limitations: 3 or 4 digits
				$postFields['EXPDATE'] = $pOrder->info['cc_expires']; // (Required) Expiration date of the credit card. For example, 1215 represents December 2015.
				$postFields['INVNUM'] = $pOrder->mDb->mName.'-'.$this->paymentOrderId; // (Optional) Your own unique invoice or tracking number.

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

			// Assume we are charging the native amount in the default currency. Some gateways support multiple currencies, check for that shortly
			if( (DEFAULT_CURRENCY != $this->getProcessorCurrency()) && $paymentCurrency == DEFAULT_CURRENCY ) {
				global $currencies;
				// weird situtation where payflow currency default is different from the site. Need to convert site native to processor native
				$paymentNative = $currencies->convert( $paymentNative, $this->getProcessorCurrency(), $paymentCurrency );
				bit_error_email( 'PAYMENT WARNING on '.php_uname( 'n' ).': mismatch Payflow currency '.$this->getProcessorCurrency().' != Default Currency '.DEFAULT_CURRENCY, bit_error_string(), array() );
			}
			$paymentAmount = $paymentNative;
			$postFields['CURRENCY'] = $this->getProcessorCurrency();

			if( $this->cc_type == 'American Express' ) {
				// TODO American Express Additional Credit Card Parameters
			}

			$processors = static::getProcessors();

			switch( $gCommerceSystem->getConfig( 'MODULE_PAYMENT_PAYFLOWPRO_PROCESSOR' ) ) {
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
					if( $gCommerceSystem->isConfigActive( 'MODULE_PAYMENT_PAYFLOWPRO_MULTI_CURRENCY' ) ) {
						switch( $paymentCurrency ) {
							// PayPal supports charging natively in these 5 currencies
							case 'AUD': // Australian dollar 
							case 'CAD': // Canadian dollar 
							case 'EUR': // Euro 
							case 'GBP': // British pound 
							case 'JPY': // Japanese Yen 
							case 'USD': // US dollar 
								if( $paymentCurrency != $postFields['CURRENCY'] ) {
									$paymentAmount =  number_format( $paymentLocalized, $paymentDecimal, '.','' );
									$postFields['CURRENCY'] = strtoupper( $paymentCurrency );
								}
								break;
							default:
								// all other currencies to gateway default
								break;
						}
					}

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
			} elseif( $paymentAmount > 0 ) {
				$postFields['TRXTYPE'] = 'S';
			} elseif( $paymentAmount < 0 ) {
				$postFields['TRXTYPE'] = 'C';
				$paymentAmount = -1.0 * $paymentAmount;
			}

			$postFields['AMT'] = number_format($paymentAmount, $paymentDecimal,'.',''); // (Required) Amount (Default: U.S. based currency). Nnumeric characters and a decimal only. The maximum length varies depending on your processor. Specify the exact amount to the cent using a decimal point (use 34.00 not 34). Do not include comma separators (use 1199.95 not 1,199.95). Your processor or Internet Merchant Account provider may stipulate a maximum amount.

			// ITEMAMT	(Required if L_COSTn is specified). Sum of cost of all items in this order. 
			// ITEMAMT = L_QTY0 * LCOST0 + L_QTY1 * LCOST1 + L_QTYn * L_COSTn Limitations: Nine numeric characters plus decimal.
			$postFields['ITEMAMT'] = number_format( $pOrder->getFieldLocalized('total') - $pOrder->getFieldLocalized('shipping_cost') - $pOrder->getFieldLocalized('tax'), $paymentDecimal, '.', '' );
			// DISCOUNT	(Optional) Shipping discount for this order. Specify the discount as a positive amount.  Limitations: Nine numeric characters plus decimal (.) character. No currency symbol. Specify the exact amount to the cent using a decimal point; use 34.00, not 34. Do not include comma separators; use 1199.95 not 1,199.95.

			$postFields['DISCOUNT'] = number_format( ($pOrder->getFieldLocalized('total') - $postFields['AMT']), $paymentDecimal, '.', '' );

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

			$logHash = array( 'orders_id' => $this->paymentOrderId );
			if( $response ) {
				$responseHash = $this->_parseNameValueList($response);

				$this->result = NULL;
				$this->pnref = '';
				# Check result
				if( isset( $responseHash['PNREF'] ) ) {
					$this->pnref = $responseHash['PNREF'];
					$logHash['ref_id'] = $responseHash['PNREF'];
				}

				if( isset( $responseHash['RESULT'] ) ) {
					$this->result = (int)$responseHash['RESULT'];
					$logHash['trans_result'] = $this->result;
					$logHash['trans_message'] = $responseHash['RESPMSG'];
					if( BitBase::getParameter( $responseHash, 'DUPLICATE' ) == 2 ) {
						$duplicateError = 'Duplicate Order ( '.$responseHash['ORDERID'].' )';
						$this->mErrors['process_payment'] = $duplicateError;
						$_SESSION[$this->code.'_error']['number'] = $duplicateError;
					} elseif( $this->result ) {
						$this->mErrors['process_payment'] = $responseHash['RESPMSG'].' ('.$this->result.')';
						$_SESSION[$this->code.'_error']['number'] = $responseHash['RESPMSG'];
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
			$pOrder->info['cc_ref_id'] = BitBase::getParameter( $responseHash, 'PNREF' );
			if( !empty( $postFields['ACCT'] ) && MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY == 'True' ) {
				//replace middle CC num with XXXX
				$pOrder->info['cc_number'] = substr($postFields['ACCT'], 0, 6) . str_repeat('X', (strlen($postFields['ACCT']) - 6)) . substr($postFields['ACCT'], -4);
			}
		} else {
			foreach( array( 'PWD', 'USER', 'VENDOR', 'PARTNER', 'CVV2' ) as $field ) {
				if( isset( $postFields[$field] ) ) { unset( $postFields[$field] ); }
			}

			if( isset( $postFields['ACCT'] ) ) { $postFields['ACCT'] = $this->privatizeCard( $postFields['ACCT'] ); }
			
			bit_error_email( 'PAYMENT ERROR on '.php_uname( 'n' ).': '.BitBase::getParameter( $this->mErrors, 'process_payment' ), bit_error_string(), array( 'mErrors' => $this->mErrors, 'CURL' => $postFields, 'RESPONSE' => $responseHash ) );
			$this->mDb->RollbackTrans();
			$messageStack->add_session('checkout_payment',tra( 'There has been an error processing your payment, please try again.' ).'<br/>'.BitBase::getParameter( $responseHash, 'RESPMSG' ),'error');
			$ret = FALSE;
		}
		if( !empty( $logHash ) ) {
			$this->logTransaction( $logHash, $pOrder );
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
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable PayFlow Pro Module', 'MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'True', 'Do you want to accept PayFlow Pro payments?', '6', '1', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('PayFlow Pro Activation Mode', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'Test', 'What mode is your account in?<br><em>Test Accounts:</em><br>Visa:4111111111111111<br>MC: 5105105105105100<br><li><b>Live</b> = Activated/Live.</li><li><b>Test</b> = Test Mode</li>', '6', '4', 'zen_cfg_select_option(array(''Live'', ''Test''), ', 'NOW')");

		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Login', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'login', 'Your case-sensitive login that you defined at registration.', '6', '2', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Password', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'password', 'Your case-sensitive password that you defined at registration.', '6', '3', 'NOW')");

		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Payment Processor', 'MODULE_PAYMENT_PAYFLOWPRO_PROCESSOR', 'PayPal', 'Payment processor configured in your Payflow Pro account.', '6', '10', 'zen_cfg_select_option(payflowpro::getProcessors(), ', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Multiple Currency Support', 'MODULE_PAYMENT_PAYFLOWPRO_MULTI_CURRENCY', 'False', 'Support multiple currencies? PayPal Processor only; AUD, CAD, EUR, GBP, JPY, and USD only.', '6', '10', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Pro Currency', 'MODULE_PAYMENT_PAYFLOWPRO_CURRENCY', 'USD', '3-Letter Currency Code in which your Payflow transactions are made. Most typically: USD', '6', '2', 'NOW')");

		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Method', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'Sale', 'Transaction method used for processing orders', '6', '5', 'zen_cfg_select_option(array(''Authorization'', ''Sale''), ', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Vendor ID', 'MODULE_PAYMENT_PAYFLOWPRO_VENDOR', '', 'Your merchant login ID that you created when you registered for the account.', '6', '6', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('PayFlow Partner ID', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'PayPal', 'Your Payflow Partner is provided to you by the authorized Payflow Reseller who signed you up for the PayFlow service. This value is case-sensitive.<br />Typical values: <strong>PayPal</strong> or <strong>VeriSign</strong>', '6', '6', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', '20', 'Set the status of orders made with this payment module to this value', '6', '9', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
		$this->mDb->query("INSERT INTO " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Credit Card Privacy', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY', 'True', 'Replace the middle digits of the credit card with XXXX? You will not be able to retrieve the original card number.', '6', '10', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
	}

	function keys() {
		return array('MODULE_PAYMENT_PAYFLOWPRO_STATUS', 'MODULE_PAYMENT_PAYFLOWPRO_MODE', 'MODULE_PAYMENT_PAYFLOWPRO_PARTNER', 'MODULE_PAYMENT_PAYFLOWPRO_VENDOR', 'MODULE_PAYMENT_PAYFLOWPRO_LOGIN', 'MODULE_PAYMENT_PAYFLOWPRO_PWD', 'MODULE_PAYMENT_PAYFLOWPRO_PROCESSOR', 'MODULE_PAYMENT_PAYFLOWPRO_MULTI_CURRENCY', 'MODULE_PAYMENT_PAYFLOWPRO_CURRENCY', 'MODULE_PAYMENT_PAYFLOWPRO_TYPE', 'MODULE_PAYMENT_PAYFLOWPRO_SORT_ORDER', 'MODULE_PAYMENT_PAYFLOWPRO_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYFLOWPRO_CARD_PRIVACY');


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

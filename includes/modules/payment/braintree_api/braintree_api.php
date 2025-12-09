<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2020 bitweaver.org
 * Portions Copyright (c) 2019 Zen Cart									|
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 *
 * Documentation from https://developer.paypal.com/docs/classic/payflow/integration-guide/
 */

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginPaymentCardBase.php' );

class braintree_api extends CommercePluginPaymentCardBase {

    var $enableDebugging = false;
    var $order_pending_status = 1;
    var $_logLevel = 0;

	var $mCurrencySupport = array();

    /**
     * this module collects card-info onsite
     */
    var $collectsCardDataOnsite = TRUE;

	var $cards = array();

    /**
     * class constructor
     */

	public function __construct() {
		parent::__construct();
		$this->adminTitle = tra( 'Braintree' ); // Payment module title in Admin
		$this->description = tra( 'Process credit cards with the Braintree payment gateway.' );

		if( $this->isEnabled() ) {
			global $gBitUser;

			require_once( __DIR__ . DIRECTORY_SEPARATOR . 'lib/Braintree.php');

			include_once(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . 'en/modules/payment/', $this->code.'.php', 'false'));


			// Set the title & description text based on the mode we're in
			if( $gBitUser->isAdmin() ) {
				$this->adminTitle .= ' (rev' . $this->getModuleConfigValue( '_VERSION' ). ')';
				if ($this->getModuleConfigValue( '_SERVER' ) == 'sandbox') {
					$this->title .= '<strong><span class="alert"> (sandbox active)</span></strong>';
				}
				if ($this->getModuleConfigValue( '_DEBUGGING' ) == 'Log File' || $this->getModuleConfigValue( '_DEBUGGING' ) == 'Log and Email') {
					$this->title .= '<strong> (Debug)</strong>';
				}
				if (!function_exists('curl_init')) {
					$this->title .= '<strong><span class="alert"> CURL NOT FOUND. Cannot Use.</span></strong>';
				}
			}

			if ((!defined('BRAINTREE_OVERRIDE_CURL_WARNING') || (defined('BRAINTREE_OVERRIDE_CURL_WARNING') && BRAINTREE_OVERRIDE_CURL_WARNING != 'True')) && !function_exists('curl_init'))
				$this->enabled = false;

			$this->enableDebugging = ($this->getModuleConfigValue( '_DEBUGGING' ) == 'Log File' || $this->getModuleConfigValue( '_DEBUGGING' ) == 'Log and Email');
			$this->emailAlerts = ($this->getModuleConfigValue( '_DEBUGGING' ) == 'Log and Email');
			$this->sort_order = $this->getModuleConfigValue( '_SORT_ORDER' );
			$this->order_pending_status = $this->getModuleConfigValue( '_ORDER_PENDING_STATUS_ID' );

			$this->zone = (int) $this->getModuleConfigValue( '_ZONE' );

			// debug setup
			if (!defined('DIR_FS_LOGS')) {
				$log_dir = 'cache/';
			} else {
				$log_dir = DIR_FS_LOGS;
			}

			if (!@is_writable($log_dir))
				$log_dir = DIR_FS_CATALOG . $log_dir;
			if (!@is_writable($log_dir))
				$log_dir = DIR_FS_SQL_CACHE;
			// Regular mode:
			if ($this->enableDebugging)
				$this->_logLevel = 2;
			// DEV MODE:
			if (defined('BRAINTREE_DEV_MODE') && BRAINTREE_DEV_MODE == 'true')
				$this->_logLevel = 3;

			$this->mCurrencySupport = array();
			if( $currencyList = $this->getModuleConfigValue( '_FOREIGN_CURRENCIES' ) ) {
				if( $currencyHash = explode( ',', $currencyList ) ) {
					foreach( $currencyHash as $currencyPair ) {
						list( $merchId, $currencyCode ) = explode( ':', $currencyPair );
						$this->mCurrencySupport[$currencyCode] = $merchId;
					}
				}
			}
			if( ($defaultMerchId = $this->getModuleConfigValue( '_DEFAULT_MERCHANT_ACCOUNT_ID' )) && ($defaultCurrency = $this->getModuleConfigValue( '_DEFAULT_CURRENCY' )) ) {
				$this->mCurrencySupport[$defaultCurrency] = $defaultMerchId;
			}
		}
    }

    /**
     *  Validate the credit card information via javascript (Number, Owner, and CVV Lengths)
     */
    function javascript_validation() {
        return '  if(payment_value == "' . $this->code . '") {' . "\n" .
                '    var payment_owner = document.checkout_payment.payment_owner.value;' . "\n" .
                '    var payment_number = document.checkout_payment.payment_number.value;' . "\n" .
                '    var cc_checkcode = document.checkout_payment.payment_cvv.value;' . "\n" .
                '    if(payment_owner == "" || eval(payment_owner.length) < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
                '      error = 1;' . "\n" .
                '      jQuery(\'[name="payment_owner"]\').addClass("missing");' . "\n" .
                '      jQuery(\'[name="payment_owner"]\').after(\' <span class="alert validation">\' + \'' . addslashes(nl2br(stripslashes(str_replace('\\n', '', $this->getModuleConfigValue( '_TEXT_JS_CC_OWNER' ))))) . '\' + \'</span>\');' . "\n" .
                '    }' . "\n" .
                '    if(payment_number == "" || payment_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
                '      error = 1;' . "\n" .
                '      jQuery(\'[name="payment_number"]\').addClass("missing");' . "\n" .
                '      jQuery(\'[name="payment_number"]\').after(\' <span class="alert validation">\' + \'' . addslashes(nl2br(stripslashes(str_replace('\\n', '', $this->getModuleConfigValue( '_TEXT_JS_CC_NUMBER' ))))) . '\' + \'</span>\');' . "\n" .
                '    }' . "\n" .
                '    if(document.checkout_payment.payment_cvv.disabled == false && (cc_checkcode == "" || cc_checkcode.length < 3 || cc_checkcode.length > 4)) {' . "\n" .
                '      jQuery(\'[name="payment_cvv"]\').addClass("missing");' . "\n" .
                '      jQuery(\'[name="payment_cvv"]\').siblings(\'small\').after(\' <span class="alert validation">\' + \'' . addslashes(nl2br(stripslashes(str_replace('\\n', '', $this->getModuleConfigValue( '_TEXT_JS_CC_CVV' ))))) . '\' + \'</span>\');' . "\n" .
                '      error = 1;' . "\n" .
                '    }' . "\n" .
                '  }' . "\n";
    }

    /**
     * Display Credit Card Information Submission Fields on the Checkout Payment Page
     */
    function selection() {
        global $order, $gBitUser;

        $this->payment_type_check = 'var value = document.checkout_payment.bt_payment_type.value;' .
                'if(value == "Solo" || value == "Maestro" || value == "Switch") {' .
                '    document.checkout_payment.bt_cc_issue_month.disabled = false;' .
                '    document.checkout_payment.bt_cc_issue_year.disabled = false;' .
                '    document.checkout_payment.payment_cvv.disabled = false;' .
                '    if(document.checkout_payment.bt_cc_issuenumber) document.checkout_payment.bt_cc_issuenumber.disabled = false;' .
                '} else {' .
                '    if(document.checkout_payment.bt_cc_issuenumber) document.checkout_payment.bt_cc_issuenumber.disabled = true;' .
                '    if(document.checkout_payment.bt_cc_issue_month) document.checkout_payment.bt_cc_issue_month.disabled = true;' .
                '    if(document.checkout_payment.bt_cc_issue_year) document.checkout_payment.bt_cc_issue_year.disabled = true;' .
                '    document.checkout_payment.payment_cvv.disabled = false;' .
                '}';
        if (sizeof($this->cards) == 0)
            $this->payment_type_check = '';

        /**
         * since we are processing via the gateway, prepare and display the CC fields
         */
        $expires_month = array();
        $expires_year = array();
        $issue_year = array();

        for ($i = 1; $i < 13; $i++) {
            $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('(%m) - %B', mktime(0, 0, 0, $i, 1, 2000)));
        }

        $today = getdate();

        for ($i = $today['year']; $i < $today['year'] + 15; $i++) {
            $expires_year[] = array('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
        }

        $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

		if( !($defaultName = BitBase::getParameter( $_SESSION, 'payment_owner' )) ) {
			if( !empty( $order->billing['firstname'] ) && !empty( $order->billing['lastname'] ) ) {
				$defaultName = $order->billing['firstname'].' '.$order->billing['lastname'];
			} else {
				$defaultName = $gBitUser->getField( 'real_name' );
			}
		}

		$selection = array('id' => $this->code,
						 'module' => $this->title,
						 'fields' => array(
							array(	'title' => tra( 'Name On Card' ),
									'field' => '<div class="row"><div class="col-sm-12">' . zen_draw_input_field('payment_owner', $defaultName, 'id="' . $this->code . '-payment-owner"' . $onFocus . ' autocomplete="off"') . '</div></div>',
							),
							array(	'field' => '<div class="row"><div class="col-xs-8 col-sm-7"><label class="control-label"><i class="fa fal fa-credit-card"></i> '.tra( 'Card Number' ).'</label>' . zen_draw_input_field('payment_number', BitBase::getParameter( $_SESSION, 'payment_number' ), 'id="' . $this->code . '-cc-number" inputmode="numeric" pattern="[0-9\s]{13,19}"' . $onFocus . ' autocomplete="cc-number"', 'tel') . '</div><div class="col-xs-4 col-sm-5"><label class="control-label">' . tra( 'CVC Code' ) . '</label>' . zen_draw_input_field('payment_cvv', BitBase::getParameter( $_SESSION, 'payment_cvv' ), 'size="4" maxlength="4"' . ' id="' . $this->code . '-cc-cvv"' . $onFocus . ' autocomplete="cc-csc"', 'tel')  . '</div></div>',
							),
							array(	'title' => tra( 'Expiration Date' ),
									'field' => '<div class="row"><div class="col-xs-7 col-sm-8 col-md-9">' . zen_draw_pull_down_menu('payment_expires_month', $expires_month, BitBase::getParameter( $_SESSION, 'payment_expires_month', strftime('%m') ), 'id="' . $this->code . '-cc-expires-month" class="input-small" autocomplete="cc-exp-month" ') . '</div><div class="col-xs-5 col-sm-4 col-md-3">' . zen_draw_pull_down_menu('payment_expires_year', $expires_year, substr( BitBase::getParameter( $_SESSION, 'payment_expires_year', (date('Y') + 1) ), -2 ), 'id="' . $this->code . '-cc-expires-year" class="input-small" autocomplete="cc-exp-year" ') . '</div></div>'
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

    /**
     * Display Credit Card Information for review on the Checkout Confirmation Page
     */
	function confirmation( $pPaymentParams ) {

        $confirmation = array('title' => '',
            'fields' => array());

		if( isset( $pPaymentParams['payment_owner'] ) ) {
			$confirmation['fields'][] = array('title' => $this->getModuleConfigValue( '_TEXT_CREDIT_CARD_FIRSTNAME' ), 'field' => $pPaymentParams['payment_owner']);
		}
		if( isset( $pPaymentParams['payment_number'] ) ) {
			$confirmation['fields'][] = array('title' => $this->getModuleConfigValue( '_TEXT_CREDIT_CARD_NUMBER' ), 'field' => substr($pPaymentParams['payment_number'], 0, 4) . str_repeat('X', (strlen($pPaymentParams['payment_number']) - 8)) . substr($pPaymentParams['payment_number'], -4));
		}
		if( isset( $pPaymentParams['payment_expires_month'] ) && isset( $pPaymentParams['payment_expires_year'] ) ) {
			$confirmation['fields'][] = array('title' => $this->getModuleConfigValue( '_TEXT_CREDIT_CARD_EXPIRES' ), 'field' => strftime('%B, %Y', mktime(0, 0, 0, $pPaymentParams['payment_expires_month'], 1, $pPaymentParams['payment_expires_year'])));
		}
		if( isset( $pPaymentParams['bt_cc_issuenumber'] ) ) {
			$confirmation['fields'][] = array('title' => $this->getModuleConfigValue( '_TEXT_ISSUE_NUMBER' ), 'field' => $pPaymentParams['bt_cc_issuenumber']);
		}

        return $confirmation;
    }

    /**
     * Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page
     */
	function process_button( $pPaymentParams ) {
        global $order;
        $process_button_string = '';
/*
        $process_button_string .= "\n" . zen_draw_hidden_field('bt_payment_type', $this->getParameter( $pPaymentParams, 'bt_payment_type' )) . "\n" .
                zen_draw_hidden_field('payment_expires_month', $this->getParameter( $pPaymentParams, 'payment_expires_month' )) . "\n" .
                zen_draw_hidden_field('payment_expires_year', $this->getParameter( $pPaymentParams, 'payment_expires_year' )) . "\n" .
                zen_draw_hidden_field('bt_cc_issue_month', $this->getParameter( $pPaymentParams, 'bt_cc_issue_month' )) . "\n" .
                zen_draw_hidden_field('bt_cc_issue_year', $this->getParameter( $pPaymentParams, 'bt_cc_issue_year' )) . "\n" .
                ZEN_draw_hidden_field('bt_cc_issuenumber', $this->getParameter( $pPaymentParams, 'bt_cc_issuenumber' )) . "\n" .
                zen_draw_hidden_field('payment_number', $this->getParameter( $pPaymentParams, 'payment_number' )) . "\n" .
                zen_draw_hidden_field('payment_cvv', $this->getParameter( $pPaymentParams, 'payment_cvv' )) . "\n" .
                zen_draw_hidden_field('bt_payer_firstname', $this->getParameter( $pPaymentParams, 'bt_cc_firstname' )) . "\n" .
                zen_draw_hidden_field('bt_payer_lastname', $this->getParameter( $pPaymentParams, 'bt_cc_lastname' )) . "\n";
        $process_button_string .= zen_draw_hidden_field(session_name(), session_id());
*/
        return $process_button_string;
    }

    /**
     * Zen Cart 1.5.4 Prepare the hidden fields comprising the parameters for the Submit button on the checkout confirmation page
     */
    function process_button_ajax() {
        global $order;
        $processButton = array('ccFields' => array('bt_payment_type' => 'bt_payment_type',
                'payment_expires_month' => 'payment_expires_month',
                'payment_expires_year' => 'payment_expires_year',
                'bt_cc_issue_month' => 'bt_cc_issue_month',
                'bt_cc_issue_year' => 'bt_cc_issue_year',
                'bt_cc_issuenumber' => 'bt_cc_issuenumber',
                'payment_number' => 'payment_number',
                'payment_cvv' => 'payment_cvv',
                'payment_owner' => 'payment_owner',
            ), 'extraFields' => array(session_name() => session_id()));
        return $processButton;
    }

	function getSupportedCurrencies() {
		return array_keys( $this->mCurrencySupport );
	}

    /**
     * Prepare and submit the final authorization to Braintree via the appropriate means as configured
     */
	public function processPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {
		global $gCommerceSystem;

		$postFields = array();
		$responseHash = array();

		$ret = FALSE;

		// Generic default class for error message
		$result = new stdClass();
		$result->errors = array();

		if( self::verifyPayment ( $pOrder, $pPaymentParams, $pSessionParams ) ) {

			$logHash = $this->prepPayment( $pOrder, $pPaymentParams );
			$logHash['payment_mode'] = 'charge';

			try {
				// making a sale
				$this->braintree_init();
				
				$transHash = array();

				if( $pPaymentParams['payment_amount'] > 0 ) {
					$transHash = array(
						'merchantAccountId' => $this->getParameter( $this->mCurrencySupport, $pPaymentParams['payment_currency'] ),
						'amount' => $pPaymentParams['payment_amount'],
						'options' => array(
							'storeInVaultOnSuccess' => true,
							'submitForSettlement' => $this->getModuleConfigValue( '_SETTLEMENT' )
						),
						'descriptor' => array(
							'name' => $this->getModuleConfigValue( '_DESCRIPTOR_NAME' ),
							'phone' => $this->getModuleConfigValue( '_DESCRIPTOR_PHONE' ),
							'url' => $this->getModuleConfigValue( '_DESCRIPTOR_URL' )
						),
					);
					if( $refId = $this->getParameter( $pPaymentParams, 'payment_ref_id' ) ) {
						// Process a reference transaction
						$payment = $this->mDb->getRow( "SELECT * FROM " . TABLE_ORDERS_PAYMENTS . " WHERE `payment_ref_id`=?", array( $refId ) );
						if( !empty( $payment['payment_auth_code'] ) ) {
							$transHash['paymentMethodToken'] = $payment['payment_auth_code'];
						} else {
							$this->mErrors['process_payment'] = 'No payment_auth_code is available for '.$refId;
						}
					} else {
						// Process a new transaction
						$transHash['amount'] = $pPaymentParams['payment_amount'];
						$transHash['merchantAccountId'] = $this->getParameter( $this->mCurrencySupport, $pPaymentParams['payment_currency'] );
						$transHash['creditCard'] = array(
							'number' => $this->getPaymentNumber( $pPaymentParams ),
							'expirationMonth' => $this->getParameter( $pPaymentParams, 'payment_expires_month' ),
							'expirationYear' => $this->getParameter( $pPaymentParams, 'payment_expires_year' ),
							'cardholderName' => $this->getPaymentOwner( $pPaymentParams ),
							'cvv' => $this->payment_cvv
						);
						$transHash['customer'] = array(
							'firstName' => $pOrder->customer['firstname'],
							'lastName' => $pOrder->customer['lastname'],
							'phone' => BitBase::getParameter( $pOrder->customer, 'telephone' ),
							'email' => $pPaymentParams['payment_email']
						);
						$transHash['billing'] = array(
							'firstName' => $pOrder->billing['firstname'],
							'lastName' => $pOrder->billing['lastname'],
							'streetAddress' => $pOrder->billing['street_address'],
							'extendedAddress' => $pOrder->billing['suburb'],
							'locality' => $pOrder->billing['city'],
							'region' => $pOrder->billing['state'],
							'postalCode' => $pOrder->billing['postcode'],
							'countryCodeAlpha2' => $pOrder->billing['countries_iso_code_2']
						);
						$transHash['shipping'] = array(
							'firstName' => $pOrder->delivery['firstname'],
							'lastName' => $pOrder->delivery['lastname'],
							'streetAddress' => $pOrder->delivery['street_address'],
							'extendedAddress' => $pOrder->delivery['suburb'],
							'locality' => $pOrder->delivery['city'],
							'region' => $pOrder->delivery['state'],
							'postalCode' => $pOrder->delivery['postcode'],
							'countryCodeAlpha2' => $pOrder->delivery['countries_iso_code_2']
						);

						// Prepare products list
						$products_list = '';
						foreach( $pOrder->contents as $key=>$hash ) {
							if (isset($products_list)) {
								$products_list .= "\n";
							}
							$current_products_id = explode(':', $hash['id']);
							$products_list .= $hash['products_quantity'] . 'x' . $hash['name'] . ' (' . $current_products_id[0] . ') ';
							if (isset($hash['attributes']) && sizeof($hash['attributes']) > 0) {
								for ($j = 0, $n2 = sizeof($hash['attributes']); $j < $n2; $j++) {
									$products_list .= ' ' . $hash['attributes'][$j]['value'];
								}
							}
							$products_list .= ' $' . zen_round(zen_add_tax($hash['final_price'], $hash['tax']), 2);
						}

						$products_list = (strlen($products_list) > 255) ? substr($products_list, 0, 250) . ' ...' : $products_list;

						$transHash['customFields']['products_purchased'] = $products_list;
						$transHash['customFields']['orders_id'] = $pOrder->mDb->mName . '-' . BitBase::getParameter( $pPaymentParams, 'orders_id', BitBase::getParameter( $pPaymentParams, 'payment_parent_ref_id' ) );
						$transHash['customFields']['customers_id'] = $pPaymentParams['payment_user_id'];
						if( $poNumber = BitBase::getParameter( $pPaymentParams, 'purchase_order' ) ) {
							$transHash['purchaseOrderNumber'] = $poNumber;
						}

					}

					$result = Braintree_Transaction::sale($transHash);
					if( !empty( $result->transaction->id ) ) {
						$transactionId = $result->transaction->id;
					}
				} else if( $pPaymentParams['charge_amount'] < 0 ) {
					// Process a refund
					if( $txnID = $this->getParameter( $pPaymentParams, 'payment_ref_id' ) ) {
						$findResult = Braintree_Transaction::find($txnID);

						// Transaction is Settled so Refund
						$creditAmount = abs( $pPaymentParams['charge_amount'] );

						if( $creditAmount == $pOrder->info['total'] ) {
							if( $findResult->status == "submitted_for_settlement" || $findResult->status == "authorized" ) {
								// Transaction is pending so Void
								$result = Braintree_Transaction::void($txnID);
								$transactionId = $txnID;
							} else if ($findResult->status == "settled" || $findResult->status == "settling") {
								$result = Braintree_Transaction::refund( $txnID );
								$transactionId = $result->transaction->refundId;
							}
						} else {
							$result = Braintree_Transaction::refund( $txnID, $creditAmount );
							if( $result->success ) {
								$transactionId = $result->transaction->refundId;
							}
						}
					} else {
						$this->mErrors['process_payment'] = 'Credit parent transaction ID not set.';
					}
				}

				$pPaymentParams['result'] = array();
				if( $result->success ) {
					$logHash['payment_result'] = $result->transaction->processorResponseCode;
					$pnref = $result->transaction->id;
					$this->payment_ref_id = $pnref;
					$logHash['exchange_rate'] = 1.0;
					if( $transExchange = urldecode($result->transaction->disbursementDetails->settlementCurrencyExchangeRate) ) {
						$logHash['exchange_rate'] = $transExchange;
					}
					$logHash['payment_status'] = $result->transaction->status;
					$logHash['payment_date'] = $result->transaction->createdAt->format('Y-m-d H:i:s+00');
					$logHash['payment_amount'] = (float) urldecode( $result->transaction->amount );
					if( $pPaymentParams['charge_amount'] < 0 ) {
						// credits are logged as a negative amount
						$logHash['payment_amount'] *= -1;
					}
					$logHash['payment_currency'] = $result->transaction->currencyIsoCode;
					$logHash['payment_message'] = trim( $result->transaction->processorResponseText );
					$logHash['payment_ref_id'] = $pnref;
					$logHash['payment_parent_ref_id'] = $result->transaction->refundedTransactionId;
//					$logHash['pending_reason'] = $this->pendingreason;
					$logHash['address_company'] = $result->transaction->billingDetails->company;
					$logHash['address_street_address'] = $result->transaction->shippingDetails->streetAddress;
					$logHash['address_suburb'] = $result->transaction->shippingDetails->extendedAddress;
					$logHash['address_city'] = $result->transaction->shippingDetails->locality;
					$logHash['address_state'] = $result->transaction->shippingDetails->region;
					$logHash['address_postcode'] = $result->transaction->shippingDetails->postalCode;
					$logHash['address_country'] = $result->transaction->shippingDetails->countryName;

					$ret = TRUE;
					$logHash['is_success'] = 'y';

					$pOrder->info['payment_ref_id'] = $pnref;
					//replace middle CC num with XXXX
					if( !empty( $transHash['payment_number'] ) ) {
						$pOrder->info['payment_number'] = substr($transHash['payment_number'], 0, 6) . str_repeat('X', (strlen($transHash['payment_number']) - 6)) . substr($transHash['payment_number'], -4);
					}
					
					$logHash['payment_auth_code'] = $result->transaction->creditCardDetails->token;
					$this->payment_type = $this->getModuleConfigValue( '_TEXT_TITLE' ) . '(' . $result->transaction->creditCardDetails->cardType . ')';
					$this->mPaymentStatus = 'Completed';
					$this->avs = $result->transaction->avsPostalCodeResponseCode;
					$this->cvv2 = $result->transaction->cvvResponseCode;

					$this->amt = $result->transaction->amount;
					$this->transactiontype = 'cart';
					$this->numitems = sizeof($pOrder->contents);

				} elseif( empty( $this->mErrors ) ) {
					$this->mErrors['process_payment'] = $result->message."\n\n";

					if( !empty( $result->transaction->processorResponseCode ) ) {
						if( preg_match('/^1(\d+)/', $result->transaction->processorResponseCode)) {
							// If it's a 1000 code it's Card Approved but since it didn't suceed above we assume it's Verification Failed.
							// FROM " . TABLE_BRAINTREE . " : 1000 class codes mean the processor has successfully authorized the transaction; success will be true. However, the transaction could still be gateway rejected even though the processor successfully authorized the transaction if you have AVS and/or CVV rules set up and/or duplicate transaction checking is enabled and the transaction fails those validation.
							$this->mErrors['process_payment'] .= 'We were unable to process your credit card. Please make sure that your credit card and billing information is accurate and entered properly.';
						} else if (preg_match('/^2(\d+)/', $result->transaction->processorResponseCode)) {
							// If it's a 2000 code it's Card Declined
							// FROM " . TABLE_BRAINTREE . " : 2000 class codes means the authorization was declined by the processor ; success will be false and the code is meant to tell you more about why the card was declined.                
							if (defined('BRAINTREE_ERROR_CODE_' . $result->transaction->processorResponseCode)) {
								$this->mErrors['process_payment'] .= constant('BRAINTREE_ERROR_CODE_' . $result->transaction->processorResponseCode);
							} else {
								$this->mErrors['process_payment'] .= 'Processor Decline - Please try another card. ('.$result->transaction->processorResponseCode.')';
							}
						} else if (preg_match('/^3(\d+)/', $result->transaction->processorResponseCode)) {
							// If it's a 3000 code it's a processor failure
							// FROM " . TABLE_BRAINTREE . " : 3000 class codes are problems with the back-end processing network, and dont necessarily mean a problem with the card itself.
							$this->mErrors['process_payment'] .= 'Processor Network Unavailable - Try Again.';
						} else {
							// This is the default error msg but technically it shouldn't be able to get here, Braintree in the future may add codes making it possible to not be a 1, 2, or 3k class code though.
							$this->mErrors['process_payment'] .= 'We were unable to process your credit card. Please make sure that your billing information is accurate and entered properly.';
						}
					}

					$logHash['payment_message'] = trim( $this->mErrors['process_payment'] );
					$logHash['payment_result'] = 'Failure';
				}
			} catch (Exception $e) {
				if( !($msg = $e->getMessage()) ) {
					$msg = "Payment Execption";
				}
				$this->mErrors['process_payment'] = $e->getMessage();
			}
		} else {
			$errorString = implode( $this->mErrors );
			$this->mErrors = array( 'process_payment' => $errorString );
			bit_error_email( 'PAYMENT ERROR: DID NOT VERIFY', 'verifyPayment failed', array( $this->mErrors, $pPaymentParams ) );
		}

		if( !empty( $this->mErrors['process_payment'] ) ) {
			$pSessionParams[$this->code.'_error']['number'] = $this->mErrors['process_payment'];
			bit_error_email( 'PAYMENT ERROR on '.php_uname( 'n' ).': '.BitBase::getParameter( $this->mErrors, 'process_payment' ), bit_error_string(), array( 'mErrors' => $this->mErrors, $result->errors, 'RESPONSE' => $responseHash ) );
			$ret = FALSE;
		}

		$pPaymentParams['result'] = $logHash;

		return $ret;
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 20;
		$ret = array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_ORDER_PENDING_STATUS_ID' => array(
				'configuration_title' => 'Set Unpaid Order Status',
				'configuration_value' => '1',
				'configuration_description' => 'Set the status of unpaid orders made with this payment module to this value. <br /><strong>Recommended: Pending[1]</strong>',
				'set_function' => "zen_cfg_pull_down_order_statuses(",
				'use_function' => "zen_get_order_status_name",
			),
			$this->getModuleKeyTrunk().'_REFUNDED_STATUS_ID' => array(
				'configuration_title' => 'Set Refund Order Status',
				'configuration_value' => '1',
				'configuration_description' => 'Set the status of refunded orders to this value. <br /><strong>Recommended: Pending[1]</strong>',
				'set_function' => "zen_cfg_pull_down_order_statuses(",
				'use_function' => "zen_get_order_status_name",
			),
			$this->getModuleKeyTrunk().'_VERSION' => array(
				'configuration_title' => 'Version',
				'configuration_value' => '1.4.0',
				'configuration_description' => 'Version installed',
			),
			$this->getModuleKeyTrunk().'_MERCHANTID' => array(
				'configuration_title' => 'Merchant Key',
				'configuration_description' => 'Your Merchant ID provided under the API Keys section.',
			),
			$this->getModuleKeyTrunk().'_PUBLICKEY' => array(
				'configuration_title' => 'Public Key',
				'configuration_description' => 'Your Public Key provided under the API Keys section.',
			),
			$this->getModuleKeyTrunk().'_PRIVATEKEY' => array(
				'configuration_title' => 'Private Key',
				'configuration_description' => 'Your Private Key provided under the API Keys section.',
			),
			$this->getModuleKeyTrunk().'_DESCRIPTOR_NAME' => array(
				'configuration_title' => 'Descriptor Statement Name',
				'configuration_description' => 'The company name that will appear on customer financial statements. Company name/DBA section must be either 3, 7 or 12 characters and the product descriptor can be up to 18, 14, or 9 characters respectively (with an * in between for a total descriptor name of 22 characters). Example: COMPANY*PRODUCT',
			),
			$this->getModuleKeyTrunk().'_DESCRIPTOR_URL' => array(
				'configuration_title' => 'Descriptor Statement URL',
				'configuration_description' => 'The company URL that will appear on customer financial statements. Url must be 13 characters or shorter and can only contain letters, numbers and periods.',
			),
			$this->getModuleKeyTrunk().'_DESCRIPTOR_PHONE' => array(
				'configuration_title' => 'Descriptor Statement Telephone',
				'configuration_description' => 'The company phone number that will appear on customer credit card statements. Phone must contain exactly 10 digits, and can only contain numbers, dashes and parentheses.',
			),
			$this->getModuleKeyTrunk().'_DEFAULT_MERCHANT_ACCOUNT_ID' => array(
				'configuration_title' => 'Default Merchant Account ID',
				'configuration_description' => 'Your Default Merchant Account ID, this should contain your <strong>Merchant Account Name</strong>.<br>Example: myaccountUSD',
			),
			$this->getModuleKeyTrunk().'_DEFAULT_CURRENCY' => array(
				'configuration_title' => 'Merchant Account Default Currency',
				'configuration_value' => 'USD',
				'configuration_description' => 'Your Merchant Account Settlement Currency, must be the same as currency code in your Merchant Account Name.<br> Example: USD, CAD, AUD - You can see your store currencies from the <a target=\"_blank\" href=\"currencies.php\">Localization/Currency</a>(Opens New Window).',
			),
			$this->getModuleKeyTrunk().'_FOREIGN_CURRENCIES' => array(
				'configuration_title' => 'Foreign Currency Support',
				'configuration_description' => 'Enter comma-separated list of [Currency Merchant Account ID]:[Foreign Currency Abbreviation]. For example:<br>myaccountAUD:AUD,myaccountCAD:CAD',
			),
			$this->getModuleKeyTrunk().'_SERVER' => array(
				'configuration_title' => 'Production or Sandbox',
				'configuration_value' => 'sandbox',
				'configuration_description' => '<strong>Production: </strong> Used to process Live transactions<br><strong>Sandbox: </strong>For developers and testing',
				'set_function' => "zen_cfg_select_option(array('production', 'sandbox'), ",
			),
			$this->getModuleKeyTrunk().'_DEBUGGING' => array(
				'configuration_title' => 'Debug Mode',
				'configuration_value' => 'Alerts Only',
				'configuration_description' => 'Would you like to enable debug mode?  A complete detailed log of failed transactions will be emailed to the store owner if Log and Email is selected.',
				'set_function' => "zen_cfg_select_option(array('Alerts Only', 'Log File', 'Log and Email'), ",
			),
			$this->getModuleKeyTrunk().'_SETTLEMENT' => array(
				'configuration_title' => 'Submit for Settlement',
				'configuration_value' => 'true',
				'configuration_description' => 'Would you like to automatically Submit for Settlement?  Setting to false will only authorize and not submit for settlement (also know as capture) the transaction',
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
		) );

		$ret[$this->getModuleKeyTrunk().'_PAYMENT_LIMIT_MAX']['configuration_value'] = '10000';
		return $ret;
	}

    /**
     * Initialize the Braintree object for communication to the processing gateways
     */
    function braintree_init() {

        if ($this->getModuleConfigValue( '_MERCHANTID' ) != '' && $this->getModuleConfigValue( '_PUBLICKEY' ) != '' && $this->getModuleConfigValue( '_PRIVATEKEY' ) != '') {

            Braintree_Configuration::environment($this->getModuleConfigValue( '_SERVER' ));
            Braintree_Configuration::merchantId($this->getModuleConfigValue( '_MERCHANTID' ));
            Braintree_Configuration::publicKey($this->getModuleConfigValue( '_PUBLICKEY' ));
            Braintree_Configuration::privateKey($this->getModuleConfigValue( '_PRIVATEKEY' ));
        } else {
            return FALSE;
        }
    }

}

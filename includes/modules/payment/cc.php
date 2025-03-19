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

class cc extends CommercePluginPaymentCardBase {

	public function __construct() {
		parent::__construct();
		$this->adminTitle = tra( 'Generic Credit Card' );
		$this->description = tra( 'This method collects credit card information, but does not process it' );
	}

	// class constructor
	function javascript_validation() {
		$js = '	if (payment_value == "' . $this->code . '") {' . "\n" .
			'		var payment_owner = document.checkout_payment.payment_owner.value;' . "\n" .
			'		var payment_number = document.checkout_payment.payment_number.value;' . "\n";

		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$js .= '		var payment_cvv = document.checkout_payment.payment_cvv.value;' . "\n";
		}

		$js .= '		if (payment_owner == "" || payment_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
			 '			error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER . '";' . "\n" .
			 '			error = 1;' . "\n" .
			 '		}' . "\n" .
			 '		if (payment_number == "" || payment_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
			 '			error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER . '";' . "\n" .
			 '			error = 1;' . "\n" .
			 '		}' . "\n";

		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$js .= '		if (payment_cvv == "" || payment_cvv.length < ' . CC_CVV_MIN_LENGTH . ') {' . "\n" .
				 '			error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '";' . "\n" .
				 '			error = 1;' . "\n" .
				 '		}' . "\n";
		}

		$js .= '	}' . "\n";


		return $js;
	}

	public function verifyPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {
		if( parent::verifyPayment( $pOrder, $pPaymentParams, $pSessionParams ) ) {
			foreach( array( 'payment_owner', 'payment_number', 'payment_expires_month', 'payment_expires_year', 'payment_cvv' ) as $key ) {
				$pSessionParams[$key] = BitBase::getParameter( $pPaymentParams, $key );
			}
		}
		return (count( $this->mErrors ) === 0);
	}


	function confirmation( $pPaymentParams ) {

		$confirmation = array('title' => $this->title . ': ' . $this->payment_type,
								'fields' => array(array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
														'field' => $pPaymentParams['payment_owner']),
												array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
														'field' => substr($this->payment_number, 0, 4) . str_repeat('X', (strlen($this->payment_number) - 8)) . substr($this->payment_number, -4)),
												array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
														'field' => strftime('%B, %Y', mktime(0,0,0,$pPaymentParams['payment_expires_month'], 1, '20' . $pPaymentParams['payment_expires_year'])))));

		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$confirmation['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
											 'field' => $pPaymentParams['payment_cvv']);
		}
		return $confirmation;
	}

	function process_button( $pPaymentParams ) {

		$process_button_string = zen_draw_hidden_field('payment_owner', $pPaymentParams['payment_owner']) .
								 zen_draw_hidden_field('payment_expires', $pPaymentParams['payment_expires_month'] . $pPaymentParams['payment_expires_year']) .
								 zen_draw_hidden_field('payment_type', $this->payment_type) .
								 zen_draw_hidden_field('payment_number', $this->payment_number);
		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$process_button_string .= zen_draw_hidden_field('payment_cvv', $pPaymentParams['payment_cvv']);
		}

		return $process_button_string;
	}

	public function processPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {

		if( $ret = self::verifyPayment ( $pOrder, $pPaymentParams, $pSessionParams ) ) {
			$logHash = $this->prepPayment( $pOrder, $pPaymentParams );

			$ret = TRUE;
			$logHash['payment_result'] = '1';
			$logHash['is_success'] = 'y';
			$logHash['payment_status'] = 'pending';
			$logHash['payment_mode'] = 'charge';
			$pOrder->info['payment_expires'] = $pPaymentParams['payment_expires'];
			$pOrder->info['payment_type'] = $pPaymentParams['payment_type'];
			$pOrder->info['payment_owner'] = $pPaymentParams['payment_owner'];
			$pOrder->info['payment_cvv'] = $pPaymentParams['payment_cvv'];

			if (MODULE_PAYMENT_CC_STORE_NUMBER == 'True') {
				$pOrder->info['payment_number'] = $pPaymentParams['payment_number'];
			} else {
				$pOrder->info['payment_number'] = $this->privatizePaymentNumber( $pPaymentParams['payment_number'] );
			}

			$pPaymentParams['result'] = $logHash;
		}
		return $ret;
	}

	function install() {
		parent::install();
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Collect & store the CVV number', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'True', 'Do you want to collect the CVV number. Note: If you do the CVV number will be stored in the database in an encoded format.', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Store the Credit Card Number', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'True', 'Do you want to store the Credit Card Number. Note: The Credit Card Number will be stored unenecrypted, and as such may represent a security problem', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
		$this->CompleteTrans();
	}

	public function keys() {
		return array_merge( 
					array_keys( $this->config() ), 
					array('MODULE_PAYMENT_CC_COLLECT_CVV', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'MODULE_PAYMENT_CC_ZONE') 
				);
	}
}

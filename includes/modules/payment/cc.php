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

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentBase.php' );

class cc extends CommercePluginPaymentBase {
	var $code, $title, $description, $enabled;

// class constructor
	function __construct() {
		global $order;

		parent::__construct();

		$this->code = 'cc';
		$this->title = MODULE_PAYMENT_CC_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_CC_TEXT_DESCRIPTION;
		$this->sort_order = defined( 'MODULE_PAYMENT_CC_SORT_ORDER' ) ? MODULE_PAYMENT_CC_SORT_ORDER : 0;
		$this->enabled = (( defined( 'MODULE_PAYMENT_CC_STATUS' ) && MODULE_PAYMENT_CC_STATUS == 'True') ? true : false);

		if ( defined( 'MODULE_PAYMENT_CC_ORDER_STATUS_ID' ) && (int)MODULE_PAYMENT_CC_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_CC_ORDER_STATUS_ID;
		}

		if (is_object($order)) $this->update_status();
	}

	// class methods
	function update_status() {
		global $order, $gBitDb;

		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CC_ZONE > 0) ) {
			$check_flag = false;
			$check = $this->mDb->query("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_CC_ZONE . "' and `zone_country_id` = '" . $order->billing['country']['countries_id'] . "' order by `zone_id`");
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
			'		var cc_owner = document.checkout_payment.cc_owner.value;' . "\n" .
			'		var cc_number = document.checkout_payment.cc_number.value;' . "\n";

		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$js .= '		var cc_cvv = document.checkout_payment.cc_cvv.value;' . "\n";
		}

		$js .= '		if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
			 '			error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER . '";' . "\n" .
			 '			error = 1;' . "\n" .
			 '		}' . "\n" .
			 '		if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
			 '			error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER . '";' . "\n" .
			 '			error = 1;' . "\n" .
			 '		}' . "\n";

		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$js .= '		if (cc_cvv == "" || cc_cvv.length < ' . CC_CVV_MIN_LENGTH . ') {' . "\n" .
				 '			error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '";' . "\n" .
				 '			error = 1;' . "\n" .
				 '		}' . "\n";
		}

		$js .= '	}' . "\n";


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
							 'fields' => array(array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
											 'field' => zen_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
										 array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
											 'field' => zen_draw_input_field('cc_number')),
										 array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
											 'field' => zen_draw_pull_down_menu('cc_expires_month', $expires_month) . '&nbsp;' . zen_draw_pull_down_menu('cc_expires_year', $expires_year))));

		if( MODULE_PAYMENT_CC_COLLECT_CVV == 'True' ) {
			$selection['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
											 'field' => zen_draw_input_field('cc_cvv'));
		}
		return $selection;
	}

function pre_confirmation_check( $pPaymentParameters ) {
	if( empty( $pPaymentParameters['cc_number'] ) ) {
		$error = tra( 'Please enter a credit card number.' );
	} elseif( $this->verifyCreditCard( $pPaymentParameters['cc_number'], $pPaymentParameters['cc_expires_month'], $pPaymentParameters['cc_expires_year'], $pPaymentParameters['cc_cvv'] ) ) {
		$ret = TRUE;
	} else {
		foreach( array( 'cc_owner', 'cc_number', 'cc_expires_month', 'cc_expires_year', 'cc_cvv' ) as $key ) {
			$_SESSION[$key] = BitBase::getParameter( $pPaymentParameters, $key );
		}
		$_SESSION['pfp_error'] = $this->mErrors;
		zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, NULL, 'SSL', true, false));
	}
	return $ret;
}


	function confirmation() {
		global $_POST;

		$confirmation = array('title' => $this->title . ': ' . $this->cc_type,
								'fields' => array(array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
														'field' => $_POST['cc_owner']),
												array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
														'field' => substr($this->cc_number, 0, 4) . str_repeat('X', (strlen($this->cc_number) - 8)) . substr($this->cc_number, -4)),
												array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
														'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['cc_expires_month'], 1, '20' . $_POST['cc_expires_year'])))));

		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$confirmation['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
											 'field' => $_POST['cc_cvv']);
		}
		return $confirmation;
	}

	function process_button() {
		global $_POST;

		$process_button_string = zen_draw_hidden_field('cc_owner', $_POST['cc_owner']) .
								 zen_draw_hidden_field('cc_expires', $_POST['cc_expires_month'] . $_POST['cc_expires_year']) .
								 zen_draw_hidden_field('cc_type', $this->cc_type) .
								 zen_draw_hidden_field('cc_number', $this->cc_number);
		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$process_button_string .= zen_draw_hidden_field('cc_cvv', $_POST['cc_cvv']);
		}

		return $process_button_string;
	}

	function before_process( $pPaymentParameters ) {
		global $_POST, $order;

		$order->info['cc_expires'] = $_POST['cc_expires'];
		$order->info['cc_type'] = $_POST['cc_type'];
		$order->info['cc_owner'] = $_POST['cc_owner'];
		$order->info['cc_cvv'] = $_POST['cc_cvv'];

		if (MODULE_PAYMENT_CC_STORE_NUMBER == 'True') {
			$order->info['cc_number'] = $_POST['cc_number'];
		} else {
			$order->info['cc_number'] = substr($_POST['cc_number'], 0, 4) . str_repeat('X', (strlen($_POST['cc_number']) - 8)) . substr($_POST['cc_number'], -4);
		}
	}

	function after_process( $pPaymentParameters ) {
	}

	function after_order_create($zf_order_id) {
		global $gBitDb, $order;
		if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')	{
			$gBitDb->execute("update "	. TABLE_ORDERS . " set cc_cvv ='" . $order->info['cc_cvv'] . "' where `orders_id` = '" . $zf_order_id ."'");
		}
	}
	
	function get_error() {
		global $_GET;

		$error = array('title' => MODULE_PAYMENT_CC_TEXT_ERROR,
									 'error' => stripslashes(urldecode($_GET['error'])));

		return $error;
	}

	function check() {
		global $gBitDb;
		if (!isset($this->_check)) {
			$check_query = $this->mDb->query("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_PAYMENT_CC_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}

	function install() {
		global $gBitDb;
		$this->StartTrans();
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', '6', '0', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Collect & store the CVV number', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'True', 'Do you want to collect the CVV number. Note: If you do the CVV number will be stored in the database in an encoded format.', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Store the Credit Card Number', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'True', 'Do you want to store the Credit Card Number. Note: The Credit Card Number will be stored unenecrypted, and as such may represent a security problem', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
		$this->CompleteTrans();
	}

	function remove() {
		global $gBitDb;
		$this->mDb->query("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		return array('MODULE_PAYMENT_CC_STATUS', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'MODULE_PAYMENT_CC_ZONE', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', 'MODULE_PAYMENT_CC_SORT_ORDER');
	}
}

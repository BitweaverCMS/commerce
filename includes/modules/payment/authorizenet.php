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
// $Id: authorizenet.php,v 1.5 2006/12/19 00:11:34 spiderr Exp $
//

  class authorizenet {
    var $code, $title, $description, $enabled;

// class constructor
    function authorizenet() {
      global $order;

      $this->code = 'authorizenet';
     if( !empty( $_GET['main_page'] ) ) {
       $this->title = MODULE_PAYMENT_AUTHORIZENET_TEXT_CATALOG_TITLE; // Payment module title in Catalog
     } else {
       $this->title = MODULE_PAYMENT_AUTHORIZENET_TEXT_ADMIN_TITLE; // Payment module title in Admin
     }
      $this->description = MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION;
		if( defined( 'MODULE_PAYMENT_AUTHORIZENET_STATUS' ) ) {
	      $this->enabled = ((MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True') ? true : false);
    	  $this->sort_order = MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER;

	      if ((int)MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID > 0) {
    	    $this->order_status = MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID;
    	  }

	      if (is_object($order)) $this->update_status();

		}
    	  $this->form_action_url = 'https://secure.authorize.net/gateway/transact.dll';
    }

// Authorize.net utility functions
// DISCLAIMER:
//     This code is distributed in the hope that it will be useful, but without any warranty; 
//     without even the implied warranty of merchantability or fitness for a particular purpose.

// Main Interfaces:
//
// function InsertFP ($loginid, $txnkey, $amount, $sequence) - Insert HTML form elements required for SIM
// function CalculateFP ($loginid, $txnkey, $amount, $sequence, $tstamp) - Returns Fingerprint.

// compute HMAC-MD5
// Uses PHP mhash extension. Pl sure to enable the extension
// function hmac ($key, $data) {
//   return (bin2hex (mhash(MHASH_MD5, $data, $key)));
//}

// Thanks is lance from http://www.php.net/manual/en/function.mhash.php
//lance_rushing at hot* spamfree *mail dot com
//27-Nov-2002 09:36 
// 
//Want to Create a md5 HMAC, but don't have hmash installed?
//
//Use this:

function hmac ($key, $data)
{
   // RFC 2104 HMAC implementation for php.
   // Creates an md5 HMAC.
   // Eliminates the need to install mhash to compute a HMAC
   // Hacked by Lance Rushing

   $b = 64; // byte length for md5
   if (strlen($key) > $b) {
       $key = pack("H*",md5($key));
   }
   $key  = str_pad($key, $b, chr(0x00));
   $ipad = str_pad('', $b, chr(0x36));
   $opad = str_pad('', $b, chr(0x5c));
   $k_ipad = $key ^ $ipad ;
   $k_opad = $key ^ $opad;

   return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
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
        $check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_AUTHORIZENET_ZONE . "' and `zone_country_id` = '" . $order->billing['country']['id'] . "' order by `zone_id`");
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
      $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
            '    var cc_owner = document.checkout_payment.authorizenet_cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.authorizenet_cc_number.value;' . "\n" .
            '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n";

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

    function pre_confirmation_check() {
      global $_POST;

      include(DIR_WS_CLASSES . 'cc_validation.php');

      $cc_validation = new cc_validation();
      $result = $cc_validation->validate($_POST['authorizenet_cc_number'], $_POST['authorizenet_cc_expires_month'], $_POST['authorizenet_cc_expires_year']);
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

      if ( ($result == false) || ($result < 1) ) {
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&authorizenet_cc_owner=' . urlencode($_POST['authorizenet_cc_owner']) . '&authorizenet_cc_expires_month=' . $_POST['authorizenet_cc_expires_month'] . '&authorizenet_cc_expires_year=' . $_POST['authorizenet_cc_expires_year'];

        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $this->cc_card_type = $cc_validation->cc_type;
      $this->cc_card_number = $cc_validation->cc_number;
      $this->cc_expiry_month = $cc_validation->cc_expiry_month;
      $this->cc_expiry_year = $cc_validation->cc_expiry_year;
    }

    function confirmation() {
      global $_POST;

      $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['authorizenet_cc_owner']),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['authorizenet_cc_expires_month'], 1, '20' . $_POST['authorizenet_cc_expires_year'])))));

      return $confirmation;
    }

    function process_button() {
      global $_SERVER, $order;

      $sequence = rand(1, 1000);
      $process_button_string = zen_draw_hidden_field('x_Login', MODULE_PAYMENT_AUTHORIZENET_LOGIN) .
                               zen_draw_hidden_field('x_Card_Num', $this->cc_card_number) .
                               zen_draw_hidden_field('x_Exp_Date', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
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
                               zen_draw_hidden_field('x_country', $order->billing['country']['title']) .
                               zen_draw_hidden_field('x_phone', $order->customer['telephone']) .
                               zen_draw_hidden_field('x_email', $order->customer['email_address']) .
                               zen_draw_hidden_field('x_ship_to_first_name', $order->delivery['firstname']) .
                               zen_draw_hidden_field('x_ship_to_last_name', $order->delivery['lastname']) .
                               zen_draw_hidden_field('x_ship_to_address', $order->delivery['street_address']) .
                               zen_draw_hidden_field('x_ship_to_city', $order->delivery['city']) .
                               zen_draw_hidden_field('x_ship_to_state', $order->delivery['state']) .
                               zen_draw_hidden_field('x_ship_to_zip', $order->delivery['postcode']) .
                               zen_draw_hidden_field('x_ship_to_country', $order->delivery['country']['title']) .
                               zen_draw_hidden_field('x_Customer_IP', $_SERVER['REMOTE_ADDR']) .
                               $this->InsertFP(MODULE_PAYMENT_AUTHORIZENET_LOGIN, MODULE_PAYMENT_AUTHORIZENET_TXNKEY, number_format($order->info['total'], 2), $sequence);
      if (MODULE_PAYMENT_AUTHORIZENET_TESTMODE == 'Test') $process_button_string .= zen_draw_hidden_field('x_Test_Request', 'TRUE');

      $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

      return $process_button_string;
    }

    function before_process() {
      global $_POST;

      if ($_POST['x_response_code'] == '1') return;
      if ($_POST['x_response_code'] == '2') {
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE), 'SSL', true, false));
      }
      // Code 3 is an error - but anything else is an error too (IMHO)
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE), 'SSL', true, false));
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $_GET;

      $error = array('title' => MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR,
                     'error' => stripslashes(urldecode($_GET['error'])));

      return $error;
    }

    function check() {
      global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_PAYMENT_AUTHORIZENET_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
      global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable Authorize.net Module', 'MODULE_PAYMENT_AUTHORIZENET_STATUS', 'True', 'Do you want to accept Authorize.net payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Login Username', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'testing', 'The login username used for the Authorize.net service', '6', '0', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'Test', 'Transaction Key used for encrypting TP data', '6', '0', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'zen_cfg_select_option(array(\'Test\', \'Production\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'Credit Card', 'Transaction method used for processing orders', '6', '0', 'zen_cfg_select_option(array(\'Credit Card\', \'eCheck\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net e-mail a receipt to the customer?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
    }

    function remove() {
      global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_AUTHORIZENET_STATUS', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'MODULE_PAYMENT_AUTHORIZENET_ZONE', 'MODULE_PAYMENT_AUTHORIZENET_ORDER_STATUS_ID', 'MODULE_PAYMENT_AUTHORIZENET_SORT_ORDER');
    }
  }
?>

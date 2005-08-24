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
// $Id: cc.php,v 1.2 2005/08/24 02:53:52 lsces Exp $
//

  class cc {
    var $code, $title, $description, $enabled;

// class constructor
    function cc() {
      global $order;

      $this->code = 'cc';
      $this->title = MODULE_PAYMENT_CC_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_CC_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_CC_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_CC_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_CC_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_CC_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order, $db;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CC_ZONE > 0) ) {
        $check_flag = false;
        $check = $db->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_CC_ZONE . "' and `zone_country_id` = '" . $order->billing['country']['id'] . "' order by `zone_id`");
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
            '    var cc_owner = document.checkout_payment.cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.cc_number.value;' . "\n";

      if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
        $js .= '    var cc_cvv = document.checkout_payment.cc_cvv.value;' . "\n";
      }

      $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER . '";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n" .
             '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
             '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER . '";' . "\n" .
             '      error = 1;' . "\n" .
             '    }' . "\n";

      if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
        $js .= '    if (cc_cvv == "" || cc_cvv.length < ' . CC_CVV_MIN_LENGTH . ') {' . "\n" .
               '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '";' . "\n" .
               '      error = 1;' . "\n" .
               '    }' . "\n";
      }

      $js .= '  }' . "\n";


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

      if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
       $selection['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
                                                 'field' => zen_draw_input_field('cc_cvv'));
      }
      return $selection;
    }

    function pre_confirmation_check() {
      global $_POST;

      include(DIR_WS_CLASSES . 'cc_validation.php');

      $cc_validation = new cc_validation();
      $result = $cc_validation->validate($_POST['cc_number'], $_POST['cc_expires_month'], $_POST['cc_expires_year']);

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
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&cc_owner=' . urlencode($_POST['cc_owner']) . '&cc_expires_month=' . $_POST['cc_expires_month'] . '&cc_expires_year=' . $_POST['cc_expires_year'];

        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $this->cc_card_type = $cc_validation->cc_type;
      $this->cc_card_number = $cc_validation->cc_number;
    }

    function confirmation() {
      global $_POST;

      $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['cc_owner']),
                                              array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['cc_expires_month'], 1, '20' . $_POST['cc_expires_year'])))));

      if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
       $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
                                                 'field' => $_POST['cc_cvv']);
      }
      return $confirmation;
    }

    function process_button() {
      global $_POST;

      $process_button_string = zen_draw_hidden_field('cc_owner', $_POST['cc_owner']) .
                               zen_draw_hidden_field('cc_expires', $_POST['cc_expires_month'] . $_POST['cc_expires_year']) .
                               zen_draw_hidden_field('cc_type', $this->cc_card_type) .
                               zen_draw_hidden_field('cc_number', $this->cc_card_number);
      if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
        $process_button_string .= zen_draw_hidden_field('cc_cvv', $_POST['cc_cvv']);
      }

      return $process_button_string;
    }

    function before_process() {
      global $_POST, $order;

        if (MODULES_PAYMENT_CC_STORE_NUMBER == 'True') {
           $order->info['cc_number'] = $_POST['cc_number'];
        }
        $order->info['cc_expires'] = $_POST['cc_expires'];
        $order->info['cc_type'] = $_POST['cc_type'];
        $order->info['cc_owner'] = $_POST['cc_owner'];
        $order->info['cc_cvv'] = $_POST['cc_cvv'];

        if (MODULE_PAYMENT_CC_STORE_NUMBER == 'True') {
           $order->info['cc_number'] = $_POST['cc_number'];
        }
        $order->info['cc_expires'] = $_POST['cc_expires'];
        $order->info['cc_type'] = $_POST['cc_type'];
        $order->info['cc_owner'] = $_POST['cc_owner'];
        $order->info['cc_cvv'] = $_POST['cc_cvv'];

      if ( (defined('MODULE_PAYMENT_CC_EMAIL')) && (zen_validate_email(MODULE_PAYMENT_CC_EMAIL)) ) {
        $len = strlen($_POST['cc_number']);

        $this->cc_middle = substr($_POST['cc_number'], 4, ($len-8));
        $order->info['cc_number'] = substr($_POST['cc_number'], 0, 4) . str_repeat('X', (strlen($_POST['cc_number']) - 8)) . substr($_POST['cc_number'], -4);
        $order->info['cc_expires'] = $_POST['cc_expires'];
        $order->info['cc_type'] = $_POST['cc_type'];
        $order->info['cc_owner'] = $_POST['cc_owner'];
        $order->info['cc_cvv'] = $_POST['cc_cvv'];
      }
    }

    function after_process() {
      global $insert_id;

      if ( (defined('MODULE_PAYMENT_CC_EMAIL')) && (zen_validate_email(MODULE_PAYMENT_CC_EMAIL)) ) {
        $message = 'Order #' . $insert_id . "\n\n" . 'Middle: ' . $this->cc_middle . "\n\n";
		$html_msg['EMAIL_MESSAGE_HTML'] = str_replace("\n\n",'<br />',$message);
        zen_mail(MODULE_PAYMENT_CC_EMAIL, MODULE_PAYMENT_CC_EMAIL, SEND_EXTRA_CC_EMAILS_TO_SUBJECT . $insert_id, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'cc_middle_digs');
      }
    }

    function after_order_create($zf_order_id) {
      global $db, $order;
      if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
        $db->execute("update "  . TABLE_ORDERS . " set cc_cvv ='" . $order->info['cc_cvv'] . "' where orders_id = '" . $zf_order_id ."'");
      }
    }
    
    function get_error() {
      global $_GET;

      $error = array('title' => MODULE_PAYMENT_CC_TEXT_ERROR,
                     'error' => stripslashes(urldecode($_GET['error'])));

      return $error;
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_PAYMENT_CC_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
      global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Split Credit Card E-Mail Address', 'MODULE_PAYMENT_CC_EMAIL', '', 'If an e-mail address is entered, the middle digits of the credit card number will be sent to the e-mail address (the outside digits are stored in the database with the middle digits censored)', '6', '0', 'NOW')");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Collect & store the CVV number', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'True', 'Do you want to collect the CVV number. Note: If you do the CVV number will be stored in the database in an encoded format.', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Store the Credit Card Number', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'False', 'Do you want to store the Credit Card Number. Note: The Credit Card Number will be stored unenecrypted, and as such may represent a security problem', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', 'NOW')");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , 'NOW')");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function``, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function``, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
    }

    function remove() {
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_CC_STATUS', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'MODULE_PAYMENT_CC_EMAIL', 'MODULE_PAYMENT_CC_ZONE', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', 'MODULE_PAYMENT_CC_SORT_ORDER');
    }
  }
?>
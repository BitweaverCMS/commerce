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
// |                                                                      |
// |   DevosC, Developing open source Code                                |
// |   Copyright (c) 2004 DevosC.com                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: paypal.php,v 1.1 2005/07/05 05:59:10 bitweaver Exp $
//

// Note this is temporary
DEFINE('MODULE_PAYMENT_PAYPAL_RM', '2');

 class paypal {
   var $code, $title, $description, $enabled;

// class constructor
   function paypal($paypal_ipn_id = '') {
     global $order;
       $this->code = 'paypal';
    if ($_GET['main_page'] != '') {
       $this->title = MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_TITLE; // Payment Module title in Catalog
    } else {
       $this->title = MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE; // Payment Module title in Admin
    }
       $this->description = MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION;
       $this->sort_order = MODULE_PAYMENT_PAYPAL_SORT_ORDER;
       $this->enabled = ((MODULE_PAYMENT_PAYPAL_STATUS == 'True') ? true : false);
       if ((int)MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID > 0) {
         $this->order_status = MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID;
       }
       if (is_object($order)) $this->update_status();
       $this->form_action_url = 'https://' . MODULE_PAYMENT_PAYPAL_HANDLER;

   }

// class methods
   function update_status() {
     global $order, $db;

     if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_ZONE > 0) ) {
       $check_flag = false;
       $check_query = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
       while (!$check_query->EOF) {
         if ($check_query->fields['zone_id'] < 1) {
           $check_flag = true;
           break;
         } elseif ($check_query->fields['zone_id'] == $order->billing['zone_id']) {
           $check_flag = true;
           break;
         }
                 $check_query->MoveNext();
       }

       if ($check_flag == false) {
         $this->enabled = false;
       }
     }
   }

   function javascript_validation() {
     return false;
   }

   function selection() {
     return array('id' => $this->code,
                  'module' => $this->title);
   }

   function pre_confirmation_check() {
     return false;
   }

   function confirmation() {
     return false;
   }

   function process_button() {
     global $db, $order, $currencies, $currency;

     // save the session stuff permanently in case paypal loses the session
     $db->Execute("delete from " . TABLE_PAYPAL_SESSION . " where session_id = '" . session_id() . "'");

     $sql = "insert into " . TABLE_PAYPAL_SESSION . " (session_id, saved_session, expiry) values (
            '" . session_id() . "',
            '" . base64_encode(serialize($_SESSION)) . "',
            '" . (time() + (1*60*60*24*2)) . "')";

     $db->Execute($sql);


     if (MODULE_PAYMENT_PAYPAL_CURRENCY == 'Selected Currency') {
       $my_currency = $_SESSION['currency'];
     } else {
       $my_currency = substr(MODULE_PAYMENT_PAYPAL_CURRENCY, 5);
     }
     if (!in_array($my_currency, array('CAD', 'EUR', 'GBP', 'JPY', 'USD', 'AUD'))) {
       $my_currency = 'USD';
     }
     $telephone = preg_replace('/\D/', '', $order->customer['telephone']);
     $process_button_string = zen_draw_hidden_field('business', MODULE_PAYMENT_PAYPAL_BUSINESS_ID) .
                              zen_draw_hidden_field('cmd', '_ext-enter') .
                              zen_draw_hidden_field('return', zen_href_link(FILENAME_CHECKOUT_PROCESS, 'referer=paypal', 'SSL')) .
                              zen_draw_hidden_field('cancel_return', zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')) .
                              zen_draw_hidden_field('notify_url', zen_href_link('ipn_main_handler.php', '', 'NONSSL',false,false,true)) .
                              zen_draw_hidden_field('rm', MODULE_PAYMENT_PAYPAL_RM) .
                              zen_draw_hidden_field('currency_code', $my_currency) .
//                              zen_draw_hidden_field('address_override', MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE) .
//                              zen_draw_hidden_field('no_shipping', MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED) .
                              zen_draw_hidden_field('bn', 'zencart') .
                              zen_draw_hidden_field('mrb', 'R-6C7952342H795591R') .
                              zen_draw_hidden_field('pal', '9E82WJBKKGPLQ') .
                              zen_draw_hidden_field('cbt', MODULE_PAYMENT_PAYPAL_CBT) .
//                              zen_draw_hidden_field('handling', MODULE_PAYMENT_PAYPAL_HANDLING) .
                              zen_draw_hidden_field('image_url', MODULE_PAYMENT_PAYPAL_IMAGE_URL) .
                              zen_draw_hidden_field('page_style', MODULE_PAYMENT_PAYPAL_PAGE_STYLE) .
                              zen_draw_hidden_field('item_name', STORE_NAME) .
                              zen_draw_hidden_field('item_number', '1') .
//                               zen_draw_hidden_field('invoice', '') .
//                               zen_draw_hidden_field('num_cart_items', '') .
                              zen_draw_hidden_field('lc', $order->customer['country']['iso_code_2']) .
//                               zen_draw_hidden_field('amount', number_format(($order->info['total'] - $order->info['shipping_cost']) * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency))) .
//                               zen_draw_hidden_field('shipping', number_format($order->info['shipping_cost'] * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency))) .
                              zen_draw_hidden_field('amount', number_format(($order->info['total']) * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency))) .
                              zen_draw_hidden_field('shipping', '0.00') .
                              zen_draw_hidden_field('custom', zen_session_name() . '=' . zen_session_id() ) .
                              zen_draw_hidden_field('upload', sizeof($order->products) ) .
                              zen_draw_hidden_field('redirect_cmd', '_xclick') .
                              zen_draw_hidden_field('first_name', $order->customer['firstname']) .
                              zen_draw_hidden_field('last_name', $order->customer['lastname']) .
                              zen_draw_hidden_field('address1', $order->customer['street_address']) .
//                              zen_draw_hidden_field('address2', '') .
                              zen_draw_hidden_field('city', $order->customer['city']) .
                              zen_draw_hidden_field('state',strtoupper(substr($order->customer['state'],0,2))) .
                              zen_draw_hidden_field('zip', $order->customer['postcode']) .
                              zen_draw_hidden_field('country', $order->customer['country']['iso_code_2']) .
                              zen_draw_hidden_field('email', $order->customer['email_address']) .
                              zen_draw_hidden_field('night_phone_a',substr($telephone,0,3)) .
                              zen_draw_hidden_field('night_phone_b',substr($telephone,3,3)) .
                              zen_draw_hidden_field('night_phone_c',substr($telephone,6,4)) .
                              zen_draw_hidden_field('day_phone_a',substr($telephone,0,3)) .
                              zen_draw_hidden_field('day_phone_b',substr($telephone,3,3)) .
                              zen_draw_hidden_field('day_phone_c',substr($telephone,6,4)) .
                              zen_draw_hidden_field('paypal_order_id', $paypal_order_id)
                            ;

     return $process_button_string;
   }

   function before_process() {
      global $order_total_modules;
    // now just need to check here whether we are here because of IPN or auto-return, we cn use the referer variable for that
    // If we have come from auto return, check to see wether the order has been created by IPN and if not create it now.
    if ($_GET['referer'] == 'paypal') {
      $_SESSION['cart']->reset(true);
      unset($_SESSION['sendto']);
      unset($_SESSION['billto']);
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
      unset($_SESSION['comments']);
      $order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    } else {
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
   }

   function check_referrer($zf_domain) {
     return true;
   }

   function admin_notification($zf_order_id) {
     global $db;

     $sql = "select * from " . TABLE_PAYPAL . " where zen_order_id = '" . $zf_order_id . "'";
     $ipn = $db->Execute($sql);
     require(DIR_FS_CATALOG. DIR_WS_MODULES . 'payment/paypal/paypal_admin_notification.php');
     return $output;
   }

   function after_process() {
     $_SESSION['order_created'] = '';
     return false;
   }

   function output_error() {
     return false;
   }

   function check() {
     global $db;
     if (!isset($this->_check)) {
       $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_STATUS'");
       $this->_check = $check_query->RecordCount();
     }
     return $this->_check;
   }

   function install() {
     global $db;
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayPal Module', 'MODULE_PAYMENT_PAYPAL_STATUS', 'True', 'Do you want to accept PayPal payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Business ID', 'MODULE_PAYMENT_PAYPAL_BUSINESS_ID','".STORE_OWNER_EMAIL_ADDRESS."', 'Primary email address for your PayPal account', '6', '2', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PAYPAL_CURRENCY', 'Selected Currency', 'Choose the currency/currencies you want to accept', '6', '3', 'zen_cfg_select_option(array(\'Selected Currency\',\'Only USD\',\'Only CAD\',\'Only EUR\',\'Only GBP\',\'Only JPY\',\'Only AUD\'), ', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYPAL_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '4', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Pending Notification Status', 'MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID', '" . DEFAULT_ORDERS_STATUS_ID .  "', 'Set the status of orders made with this payment module that are not yet completed to this value<br />(\'Pending\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID', '2', 'Set the status of orders made with this payment module that have completed payment to this value<br />(\'Processing\' recommended)', '6', '6', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Refund Order Status', 'MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID', '1', 'Set the status of orders that have been refunded made with this payment module to this value<br />(\'Pending\' recommended)', '6', '7', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYPAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '8', now())");
//     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Charge', 'MODULE_PAYMENT_PAYPAL_HANDLING', '0', 'The cost of handling. This is not quantity specific. The same handling will be charged regardless of the number of items purchased. If omitted or 0, no handling charges will be assessed.', '6', '15', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Address override', 'MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE', '', 'If set to 1 the address passed in via Zen Cart will override the users paypal-stored address. The user will be shown the Zen Cart address, but will not be able to edit it. If the address is not valid (i.e. missing required fields, including country) or not included, then no address will be shown.<br />Empty=No Override<br />1=Passed-in Address overrides users paypal-stored address', '6', '18', 'zen_cfg_select_option(array(\'\',\'1\'), ', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Address Options', 'MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED', '1', 'The buyers shipping address. If set to 0 your customer will be prompted to include a shipping address. If set to 1 your customer will not be asked for a shipping address. If set to 2 your customer will be required to provide a shipping address.<br />0=Prompt<br />1=Not Asked<br />2=Required<br /><br /><strong>NOTE: If you allow your customers to enter their own shipping address, then MAKE SURE you check the paypal confirmation details to verify the proper address when filling orders. Zen Cart does not know if they choose an alternate shipping address compared to the one entered when placing an order.</strong>', '6', '20', 'zen_cfg_select_option(array(\'0\',\'1\',\'2\'), ', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Continue Button Text', 'MODULE_PAYMENT_PAYPAL_CBT', '', 'Sets the text for the Continue button on the PayPal Payment Complete page. Requires Return URL to be set.', '6', '22', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Image URL', 'MODULE_PAYMENT_PAYPAL_IMAGE_URL', '', 'The internet URL of the 150x50-pixel image you would like to use as your logo. If omitted, the customer will see your Business name if you have a Business account, or your email address if you have premier account.', '6', '24', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Page Style', 'MODULE_PAYMENT_PAYPAL_PAGE_STYLE', 'paypal', 'Sets the Custom Payment Page Style for payment pages. The value of page_style is the same as the Page Style Name you chose when adding or editing the page style. You can add and edit Custom Payment Page Styles from the Profile subtab of the My Account tab on the paypal site. If you would like to always reference your Primary style, set this to \"primary.\" If you would like to reference the default PayPal page style, set this to \"paypal\".', '6', '25', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Email Notifications', 'MODULE_PAYMENT_PAYPAL_IPN_DEBUG', 'No', 'Enable debug email notifications', '6', '71', 'zen_cfg_select_option(array(\'Yes\',\'No\'), ', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug E-Mail Address', 'MODULE_PAYMENT_PAYPAL_DEBUG_EMAIL_ADDRESS','".STORE_OWNER_EMAIL_ADDRESS."', 'The e-mail address to use for paypal debugging', '6', '72', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Mode for PayPal web services<br /><br />Default:<br /><code>www.paypal.com/cgi-bin/webscr</code><br />or<br /><code>www.paypal.com/us/cgi-bin/webscr</code>', 'MODULE_PAYMENT_PAYPAL_HANDLER', 'www.paypal.com/cgi-bin/webscr', 'Choose the URL for PayPal live processing', '6', '73', '', now())");
     $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('<font color=red>NOTE: On www.paypal.com</font>,<br />set your PayPal IPN Return URL to:', 'MODULE_PAYMENT_PAYPAL_IPN_RETURN_URL','".zen_catalog_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL',false)."', '<font color=red><strong>DO NOT EDIT.</strong></font><br />This is the URL that PayPal needs to be configured to return to.', '6', '99', 'zen_cfg_select_option(array(\'".zen_catalog_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL',false)."\'), ',now())");
}

   function remove() {
     global $db;
     $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE  'MODULE_PAYMENT_PAYPAL%'");
   }

   function keys() {
     return array(
         'MODULE_PAYMENT_PAYPAL_STATUS',
         'MODULE_PAYMENT_PAYPAL_BUSINESS_ID',
         'MODULE_PAYMENT_PAYPAL_CURRENCY',
         'MODULE_PAYMENT_PAYPAL_ZONE',
         'MODULE_PAYMENT_PAYPAL_PROCESSING_STATUS_ID',
         'MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID',
         'MODULE_PAYMENT_PAYPAL_REFUND_ORDER_STATUS_ID',
         'MODULE_PAYMENT_PAYPAL_SORT_ORDER',
//         'MODULE_PAYMENT_PAYPAL_HANDLING' ,
//         'MODULE_PAYMENT_PAYPAL_ADDRESS_OVERRIDE' ,
//         'MODULE_PAYMENT_PAYPAL_ADDRESS_REQUIRED' ,
//         'MODULE_PAYMENT_PAYPAL_CBT' ,
//         'MODULE_PAYMENT_PAYPAL_IMAGE_URL' ,
//         'MODULE_PAYMENT_PAYPAL_PAGE_STYLE' ,

//         'MODULE_PAYMENT_PAYPAL_IPN_DEBUG',
//         'MODULE_PAYMENT_PAYPAL_DEBUG_EMAIL_ADDRESS',
         'MODULE_PAYMENT_PAYPAL_HANDLER',
         'MODULE_PAYMENT_PAYPAL_IPN_RETURN_URL'
         );

   }

 }
?>
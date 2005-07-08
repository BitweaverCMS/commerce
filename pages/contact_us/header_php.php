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
// $Id: header_php.php,v 1.2 2005/07/08 06:13:01 spiderr Exp $
//
  require(DIR_WS_MODULES . 'require_languages.php');

  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'send')) {
    $name = zen_db_prepare_input($_POST['name']);
    $email_address = zen_db_prepare_input($_POST['email']);
    $enquiry = zen_db_prepare_input(strip_tags($_POST['enquiry']));

    if (zen_validate_email($email_address)) {
// auto complete when logged in
      if($_SESSION['customer_id']) {
        $check_customer = $db->Execute("select customers_id, customers_firstname, customers_lastname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
        $customer_email= $check_customer->fields['customers_email_address'];
        $customer_name= $check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'];
      } else {
        $customer_email='Not logged in';
        $customer_name='Not logged in';
      }

// use contact us dropdown if defined
	if (CONTACT_US_LIST !=''){
		$send_to_array=explode("," ,CONTACT_US_LIST);
		preg_match('/\<[^>]+\>/', $send_to_array[$_POST['send_to']], $send_email_array);
		$send_to_email= eregi_replace (">", "", $send_email_array[0]);
		$send_to_email= eregi_replace ("<", "", $send_to_email);
		$send_to_name = preg_replace('/\<[^*]*/', '', $send_to_array[$_POST['send_to']]);
	} else {  //otherwise default to EMAIL_FROM and store name
		$send_to_email = EMAIL_FROM;
		$send_to_name =  STORE_NAME;
    }

// Prepare extra-info details
    $extra_info = email_collect_extra_info($name, $email_address, $customer_name, $customer_email);
// Prepare Text-only portion of message
	$text_message = OFFICE_FROM . "\t" . $name . "\n" .
                    OFFICE_EMAIL . "\t" . $email_address . "\n\n" .
                    '------------------------------------------------------' . "\n\n" .
                    strip_tags($_POST['enquiry']) .  "\n\n" .
                    '------------------------------------------------------' . "\n\n" .
					$extra_info['TEXT'];
// Prepare HTML-portion of message
     $html_msg['EMAIL_MESSAGE_HTML'] = strip_tags($_POST['enquiry']);
     $html_msg['CONTACT_US_OFFICE_FROM'] = OFFICE_FROM . ' ' . $name . '<br />' . OFFICE_EMAIL . '(' . $email_address . ')';
	 $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
// Send message
      zen_mail($send_to_name, $send_to_email, EMAIL_SUBJECT, $text_message, $name, $email_address, $html_msg,'contact_us');

      zen_redirect(zen_href_link(FILENAME_CONTACT_US, 'action=success'));
    } else {
      $error = true;

      $messageStack->add('contact', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }
  } // end action==send

// default email and name if customer is logged in
  if($_SESSION['customer_id']) {
      $check_customer = $db->Execute("select customers_id, customers_firstname, customers_lastname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_id = '" . $_SESSION['customer_id'] . "'");
      $email= $check_customer->fields['customers_email_address'];
      $name= $check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'];
  }

  if (CONTACT_US_LIST !=''){
    foreach(explode(",", CONTACT_US_LIST) as $k => $v) {
      $send_to_array[] = array('id' => $k, 'text' => preg_replace('/\<[^*]*/', '', $v));
    }
  }

// include template specific file name defines
  $define_contact_us = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/html_includes/', FILENAME_DEFINE_CONTACT_US, 'false');

  $breadcrumb->add(NAVBAR_TITLE);
?>

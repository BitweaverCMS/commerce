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
// $Id: header_php.php,v 1.2 2005/08/24 15:06:38 lsces Exp $
//
  require(DIR_WS_MODULES . 'require_languages.php');

  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $email_address = zen_db_prepare_input($_POST['email_address']);

    $check_customer_query = "select customers_firstname, customers_lastname, customers_password,
                                    customers_id from " . TABLE_CUSTOMERS . "
                             where customers_email_address = '" . zen_db_input($email_address) . "'";

    $check_customer = $db->Execute($check_customer_query);

    if ($check_customer->RecordCount() > 0) {

      $new_password = zen_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
      $crypted_password = zen_encrypt_password($new_password);

      $sql = "update " . TABLE_CUSTOMERS . "
              set `customers_password` = '" . zen_db_input($crypted_password) . "'
              where `customers_id` = '" . (int)$check_customer->fields['customers_id'] . "'";

      $db->Execute($sql);

      $html_msg['EMAIL_CUSTOMERS_NAME'] = $check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'];
      $html_msg['EMAIL_MESSAGE_HTML'] = sprintf(EMAIL_PASSWORD_REMINDER_BODY, $new_password);

	// send the email
      zen_mail($check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'], $email_address, EMAIL_PASSWORD_REMINDER_SUBJECT, sprintf(EMAIL_PASSWORD_REMINDER_BODY, $new_password), STORE_NAME, EMAIL_FROM, $html_msg,'password_forgotten');

      $messageStack->add_session('login', SUCCESS_PASSWORD_SENT, 'success');

      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    } else {
      $messageStack->add('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>
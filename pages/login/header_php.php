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
// $Id: header_php.php,v 1.4 2005/08/24 16:47:31 lsces Exp $
//
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
  if ($session_started == false) {
    zen_redirect(zen_href_link(FILENAME_COOKIE_USAGE));
  }

  require(DIR_WS_MODULES . 'require_languages.php');

  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $email_address = zen_db_prepare_input($_POST['email_address']);
    $password = zen_db_prepare_input($_POST['password']);

    if (DISPLAY_PRIVACY_CONDITIONS == 'true') {
      if (!isset($_POST['privacy_conditions']) || ($_POST['privacy_conditions'] != '1')) {
        $error = true;
        $messageStack->add('create_account', ERROR_PRIVACY_STATEMENT_NOT_ACCEPTED, 'error');
      }
    }

// Check if email exists
    $check_customer_query = "select `customers_id`, `customers_firstname`, `customers_password`,
                                    `customers_email_address`, `customers_default_address_id`,
                                    `customers_authorization`, `customers_referral`
                             from " . TABLE_CUSTOMERS . "
                             where `customers_email_address` = '" . zen_db_input($email_address) . "'";

    $check_customer = $db->Execute($check_customer_query);

    if (!$check_customer->RecordCount()) {
      $error = true;
    } else {
// Check that password is good
      if (!zen_validate_password($password, $check_customer->fields['customers_password'])) {
        $error = true;
      } else {
        if (SESSION_RECREATE == 'True') {
          zen_session_recreate();
        }

        $check_country_query = "select `entry_country_id`, `entry_zone_id`
                                from " . TABLE_ADDRESS_BOOK . "
                                where `customers_id` = '" . (int)$check_customer->fields['customers_id'] . "'
                                and `address_book_id` = '" . (int)$check_customer->fields['customers_default_address_id'] . "'";

        $check_country = $db->Execute($check_country_query);

        $_SESSION['customer_id'] = $check_customer->fields['customers_id'];
        $_SESSION['customer_default_address_id'] = $check_customer->fields['customers_default_address_id'];
        $_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];
        $_SESSION['customer_first_name'] = $check_customer->fields['customers_firstname'];
        $_SESSION['customer_country_id'] = $check_country->fields['entry_country_id'];
        $_SESSION['customer_zone_id'] = $check_country->fields['entry_zone_id'];

        $sql = "update " . TABLE_CUSTOMERS_INFO . "
                set `date_of_last_logon` = " . $db->mDb->sysTimeStamp . ",
                    `number_of_logons` = `number_of_logons`+1
                where `customers_info_id` = '" . (int)$_SESSION['customer_id'] . "'";

        $db->Execute($sql);

// restore cart contents
        $_SESSION['cart']->restore_contents();
/*
		if ($_SESSION['cart']->count_contents() > 0) {
		  zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING));
		}
*/
      if (sizeof($_SESSION['navigation']->snapshot) > 0) {
//    $back = sizeof($_SESSION['navigation']->path)-2;
//if (isset($_SESSION['navigation']->path[$back]['page'])) {
//    if (sizeof($_SESSION['navigation']->path)-2 > 0) {
          $origin_href = zen_href_link($_SESSION['navigation']->snapshot['page'], zen_array_to_string($_SESSION['navigation']->snapshot['get'], array(zen_session_name())), $_SESSION['navigation']->snapshot['mode']);
//            $origin_href = zen_back_link_only(true);
          $_SESSION['navigation']->clear_snapshot();
          zen_redirect($origin_href);
        } else {
          zen_redirect(zen_href_link(FILENAME_DEFAULT));
        }
      }
    }
  }

  if ($error == true) {
    $messageStack->add('login', TEXT_LOGIN_ERROR);
  }

  $breadcrumb->add(NAVBAR_TITLE);
?>
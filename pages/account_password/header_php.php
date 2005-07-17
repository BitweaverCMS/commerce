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
// $Id: header_php.php,v 1.2 2005/07/17 20:28:28 lsces Exp $
//
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_MODULES . 'require_languages.php');

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $password_current = zen_db_prepare_input($_POST['password_current']);
    $password_new = zen_db_prepare_input($_POST['password_new']);
    $password_confirmation = zen_db_prepare_input($_POST['password_confirmation']);

    $error = false;

    if (strlen($password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_CURRENT_ERROR);
    } elseif (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new != $password_confirmation) {
      $error = true;

      $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
      $check_customer_query = "select customers_password, customers_nick
                               from   " . TABLE_CUSTOMERS . "
                               where  customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $check_customer = $db->Execute($check_customer_query);

      if (zen_validate_password($password_current, $check_customer->fields['customers_password'])) {
        $nickname = $check_customer->fields['customers_nick'];
        $db->Execute("update " . TABLE_CUSTOMERS . " set customers_password = '" . zen_encrypt_password($password_new) . "' where customers_id = '" . (int)$_SESSION['customer_id'] . "'");

        $sql = "update " . TABLE_CUSTOMERS_INFO . "
                set    date_account_last_modified = now()
                where   customers_info_id = '" . (int)$_SESSION['customer_id'] . "'";

        $db->Execute($sql);

        if ($sniffer->phpBB['installed'] == true) {
          if (zen_not_null($nickname) && $nickname != '') {
//            require($sniffer->phpBB['phpbb_path'] . 'config.php');
            $db_phpbb = new queryFactory();
            $db_phpbb->connect($sniffer->phpBB['dbhost'], $sniffer->phpBB['dbuser'], $sniffer->phpBB['dbpasswd'], $sniffer->phpBB['dbname'], USE_PCONNECT, false);
            $sql = "update " . $sniffer->phpBB['users_table'] . " set user_password='" . MD5($password_new) . "'
                    where username = '" . $nickname . "'";
            $phpbb_users = $db_phpbb->Execute($sql);
	    $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);
          }
        }

        $messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');

        zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      } else {
        $error = true;

        $messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
      }
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>

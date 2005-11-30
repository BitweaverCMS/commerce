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
// $Id: header_php.php,v 1.4 2005/11/30 07:46:26 spiderr Exp $
//
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(FILENAME_LOGIN);
  }

  require_once(DIR_FS_MODULES . 'require_languages.php');

  $newsletter_query = "select customers_newsletter
                       from   " . TABLE_CUSTOMERS . "
                       where  customers_id = '" . (int)$_SESSION['customer_id'] . "'";

  $newsletter = $db->Execute($newsletter_query);

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    if (isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general'])) {
      $newsletter_general = zen_db_prepare_input($_POST['newsletter_general']);
    } else {
      $newsletter_general = '0';
    }

    if ($newsletter_general != $newsletter->fields['customers_newsletter']) {
      $newsletter_general = (($newsletter->fields['customers_newsletter'] == '1') ? '0' : '1');

      $sql = "update " . TABLE_CUSTOMERS . "
              set    customers_newsletter = '" . (int)$newsletter_general . "'
              where  customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $db->Execute($sql);
    }

    $messageStack->add_session('account', SUCCESS_NEWSLETTER_UPDATED, 'success');

    zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>
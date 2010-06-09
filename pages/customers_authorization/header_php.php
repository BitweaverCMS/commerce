<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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

  $sql = "select `customers_authorization` from " . TABLE_CUSTOMERS . " where `customers_id` ='" . $_SESSION['customer_id'] . "'";
  $check_customer = $gBitDb->Execute($sql);

  $_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];

  if ($_SESSION['customers_authorization'] != '1') {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
  }

  require_once(DIR_FS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);

  if (CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF == 'true') $flag_disable_right = true;
  if (CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF == 'true') $flag_disable_left = true;
  if (CUSTOMERS_AUTHORIZATION_FOOTER_OFF == 'true') $flag_disable_footer = true;
  if (CUSTOMERS_AUTHORIZATION_HEADER_OFF == 'true') $flag_disable_header = true;

?>
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
// $Id: header_php.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//
  require(DIR_WS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);

  if (DOWN_FOR_MAINTENANCE_COLUMN_RIGHT_OFF == 'true') $flag_disable_right = true;
  if (DOWN_FOR_MAINTENANCE_COLUMN_LEFT_OFF == 'true') $flag_disable_left = true;
  if (DOWN_FOR_MAINTENANCE_FOOTER_OFF == 'true') $flag_disable_footer = true;
  if (DOWN_FOR_MAINTENANCE_HEADER_OFF == 'true') $flag_disable_header = true;

  if (DOWN_FOR_MAINTENANCE == 'true') {
    $maintenance_on_at_time = $db->Execute("select last_modified from " . TABLE_CONFIGURATION . " WHERE configuration_key = 'DOWN_FOR_MAINTENANCE'");
    define('TEXT_DATE_TIME', $maintenance_on_at_time->fields['last_modified']);
  }

?>
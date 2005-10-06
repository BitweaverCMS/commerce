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
// $Id: main_template_vars.php,v 1.3 2005/10/06 19:33:29 spiderr Exp $
//
  if (file_exists(DIR_WS_MODULES . 'pages/' . $current_page_base . '/main_template_vars.php')) {
    $body_code = DIR_WS_MODULES . 'pages/' . $current_page_base . '/main_template_vars.php';
  } else {
    $body_code = DIR_FS_PAGES . $current_page_base . '/' . $_REQUEST['main_page'] . '.php';
  }
?>
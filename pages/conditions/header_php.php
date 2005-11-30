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
// $Id: header_php.php,v 1.6 2005/11/30 07:46:27 spiderr Exp $
//
  require_once(DIR_FS_MODULES . 'require_languages.php');

// include template specific file name defines
  $define_conditions = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/html_includes/', FILENAME_DEFINE_CONDITIONS, 'false');

  $breadcrumb->add(NAVBAR_TITLE);
?>

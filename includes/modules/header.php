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
// $Id: header.php,v 1.3 2006/12/19 00:11:33 spiderr Exp $
//

// check if the 'install' directory exists, and warn of its existence
  if (WARN_INSTALL_EXISTENCE == 'true') {
    if (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/zc_install')) {
      $messageStack->add('header', WARNING_INSTALL_DIRECTORY_EXISTS, 'warning');
    }
  }

// check if the configure.php file is writeable
  if (WARN_CONFIG_WRITEABLE == 'true') {
    if ( (file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) && (is_writeable(dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php')) ) {
      $messageStack->add('header', WARNING_CONFIG_FILE_WRITEABLE, 'warning');
    }
  }

// check if the session folder is writeable
  if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true') {
    if (STORE_SESSIONS == '') {
      if (!is_dir(zen_session_save_path())) {
        $messageStack->add('header', WARNING_SESSION_DIRECTORY_NON_EXISTENT, 'warning');
      } elseif (!is_writeable(zen_session_save_path())) {
        $messageStack->add('header', WARNING_SESSION_DIRECTORY_NOT_WRITEABLE, 'warning');
      }
    }
  }

// check if the sql cache folder is writeable
  if (WARN_SQL_CACHE_DIRECTORY_NOT_WRITEABLE == 'true' && strtolower(SQL_CACHE_METHOD) == 'file') {
    if (!is_dir(DIR_FS_SQL_CACHE)) {
      $messageStack->add('header', WARNING_SQL_CACHE_DIRECTORY_NON_EXISTENT, 'warning');
    } elseif (!is_writeable(DIR_FS_SQL_CACHE)) {
      $messageStack->add('header', WARNING_SQL_CACHE_DIRECTORY_NOT_WRITEABLE, 'warning');
      }
  }

// give the visitors a message that the website will be down at ... time
  if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'false') ) {
        $messageStack->add('header', TEXT_BEFORE_DOWN_FOR_MAINTENANCE . PERIOD_BEFORE_DOWN_FOR_MAINTENANCE);
  }

// this will let the admin know that the website is DOWN FOR MAINTENANCE to the public
  if ( (DOWN_FOR_MAINTENANCE == 'true') && (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) ) {
        $messageStack->add('header', TEXT_ADMIN_DOWN_FOR_MAINTENANCE, 'warning');
  }

// check session.auto_start is disabled
  if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
    if (ini_get('session.auto_start') == '1') {
      $messageStack->add('header', WARNING_SESSION_AUTO_START, 'warning');
    }
  }

  if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
    if (!is_dir(DIR_FS_DOWNLOAD)) {
      $messageStack->add('header', WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'warning');
    }
  }

// check database version against source code
  $zv_db_patch_ok = true; // we start with true
  if (WARN_DATABASE_VERSION_PROBLEM != 'false') {
    $result = $gBitDb->Execute("SELECT project_version_major, project_version_minor FROM " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Database'");
    $zv_db_patch_level_found = $result->fields['project_version_major']. '.' . $result->fields['project_version_minor'];
    $zv_db_patch_level_expected = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
    if ($zv_db_patch_level_expected=='.' || ($zv_db_patch_level_found < $zv_db_patch_level_expected) ) {
      $zv_db_patch_ok = false;
      $messageStack->add('header', WARNING_DATABASE_VERSION_OUT_OF_DATE, 'warning');
    }
  }

  if ($messageStack->size('header') > 0) {
    echo $messageStack->output('header');
  }
  require($template->get_template_dir('tpl_header.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_header.php');
?>
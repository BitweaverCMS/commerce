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
// $Id: extra_definitions.php,v 1.2 2005/07/08 06:12:59 spiderr Exp $
//

// Set current template
  $template_id= $template_dir;

// set directories to check for language files
  $languages_extra_definitions_directory = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/extra_definitions/';
  $languages_extra_definitions_directory_template = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/extra_definitions/' . $template_id . '/';

  $ws_languages_extra_definitions_directory = DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/extra_definitions/';
  $ws_languages_extra_definitions_directory_template = DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/extra_definitions/' . $template_id . '/';

// Check for new definitions in template directory

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir($languages_extra_definitions_directory_template)) {
    while ($file = $dir->read()) {
      if (!is_dir($languages_extra_definitions_directory_template . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    if (sizeof($directory_array)) {
      sort($directory_array);
    }
    $dir->close();
  }

// Check for new definitions in extra_definitions directory
  $dir_check= $directory_array;
  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));

  if ($dir = @dir($languages_extra_definitions_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($languages_extra_definitions_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          if (in_array($file, $dir_check, TRUE)) {
            // skip name exists
          } else {
            $directory_array[] = $file;
          }
        }
      }
    }
    if (sizeof($directory_array)) {
      sort($directory_array);
    }
    $dir->close();
  }

  $file_cnt=0;
  for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
    $file_cnt++;
    $file = $directory_array[$i];

    if (file_exists($ws_languages_extra_definitions_directory_template . $file)) {
//      echo 'LOADING: ' . $ws_languages_extra_definitions_directory_template . $file . ' ' . $file_cnt . '<br />';
      include($ws_languages_extra_definitions_directory_template . $file);
    } else {
//      echo 'LOADING: ' . $ws_languages_extra_definitions_directory . $file . ' ' . $file_cnt . '<br />';
      include($ws_languages_extra_definitions_directory . $file);
    }
  }
?>
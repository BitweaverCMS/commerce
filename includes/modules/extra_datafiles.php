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
// $Id$
//

// set directories to check for databases and filename files
  $extra_datafiles_directory = DIR_FS_CATALOG . DIR_WS_INCLUDES . 'extra_datafiles/';
  $ws_extra_datafiles_directory = DIR_WS_INCLUDES . 'extra_datafiles/';

// Check for new databases and filename in extra_datafiles directory
  $dir_check= $directory_array;
  $file_extension = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '.'));

  if ($dir = @dir($extra_datafiles_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($extra_datafiles_directory . $file)) {
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

  $file_cnt=0;
  for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
    $file_cnt++;
    $file = $directory_array[$i];

    if (file_exists($ws_extra_datafiles_directory . $file)) {
//      echo 'LOADING: ' . $ws_extra_datafiles_directory . $file . ' ' . $file_cnt . '<br />';
      include($ws_extra_datafiles_directory . $file);
    }
  }
?>

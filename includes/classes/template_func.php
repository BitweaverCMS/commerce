<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: template_func.php,v 1.5 2010/03/16 03:56:44 spiderr Exp $
//

  class template_func {

    function template_func($template_dir = 'default') {
      $this->info  = array();
    }

    function get_template_part($page_directory, $template_part, $file_extension = '.php') {
      $directory_array = array();
      if ($dir = @dir($page_directory)) {
        while ($file = $dir->read()) {
          if (!is_dir($page_directory . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension && preg_match($template_part, $file)) {
              $directory_array[] = $file;
            }
          }
        }

        sort($directory_array);
        $dir->close();
      }
      return $directory_array;
    }

    function get_template_dir($template_code, $current_template, $current_page, $template_dir, $debug=false) {

//	echo 'template_default/' . $template_dir . '=' . $template_code;
      if (template_func::file_exists($current_template . $current_page, $template_code)) {
        return $current_template . $current_page . '/';
      } elseif (template_func::file_exists(DIR_WS_TEMPLATES . 'template_default/' . $current_page, str_replace('/', '', $template_code), $debug)) {
        return DIR_WS_TEMPLATES . 'template_default/' . $current_page;
      } elseif (template_func::file_exists($current_template . $template_dir, str_replace('/', '', $template_code), $debug)) {
        return $current_template . $template_dir;
      } else {
        return DIR_WS_TEMPLATES . 'template_default/' . $template_dir;
//        return $current_template . $template_dir;
      }

    }
    function file_exists($file_dir, $file_pattern, $debug=false) {
      $file_found = false;
      if ($mydir = @dir($file_dir)) {
        while ($file = $mydir->read()) {
          if ( strstr($file, $file_pattern) ) {
            $file_found = true;
            break;
          }
        }
      }
      return $file_found;
    }
  }
?>

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
// $Id: category_row.php,v 1.2 2005/10/06 21:01:52 spiderr Exp $
//
    $rows = 0;
    while (!$categories->EOF) {
      $rows++;
      $cPath_new = zen_get_path($categories->fields['categories_id']);

      // strio out 0_ from top level
      $cPath_new = str_replace('=0_', '=', $cPath_new);

      $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
      $newrow = false;
      if ((($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) == floor($rows / MAX_DISPLAY_CATEGORIES_PER_ROW)) && ($rows != $number_of_categories))
      {
        $newrow = true;
      }
      if (!$categories->fields['categories_image']) !$categories->fields['categories_image'] = 'pixel_trans.gif';
      require($template->get_template_dir('tpl_index_category_row.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/'. 'tpl_index_category_row.php');
      $categories->MoveNext();
    }
?>
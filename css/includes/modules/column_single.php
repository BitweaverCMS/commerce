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
// $Id: column_single.php,v 1.1 2005/07/05 05:59:32 bitweaver Exp $
//
  // Check if there are boxes for the column
  $column_single_display= $db->Execute("select layout_box_name from " . TABLE_LAYOUT_BOXES . " where (layout_box_location=0 or layout_box_location=1) and layout_box_status_single=1 and layout_template ='" . $template_dir . "'" . ' order by LPAD(layout_box_sort_order_single,11,"0")');
  // safety row stop
  $box_cnt=0;
  while (!$column_single_display->EOF and $box_cnt < 100) {
    $box_cnt++;
    if ( file_exists(DIR_WS_MODULES . 'sideboxes/' . $column_single_display->fields['layout_box_name']) or file_exists(DIR_WS_MODULES . 'sideboxes/' . $template_dir . '/' . $column_single_display->fields['layout_box_name']) ) {
?>
<?php
    $column_box_spacer = 'column_box_spacer_single';
    if (defined('BOX_WIDTH_SINGLE')) {
      $column_width = BOX_WIDTH_SINGLE;
    } else {
      $column_width = BOX_WIDTH_LEFT;
    }
    if ( file_exists(DIR_WS_MODULES . 'sideboxes/' . $template_dir . '/' . $column_single_display->fields['layout_box_name']) ) {
      $box_id = zen_get_box_id($column_single_display->fields['layout_box_name']);
      require(DIR_WS_MODULES . 'sideboxes/' . $template_dir . '/' . $column_single_display->fields['layout_box_name']);
    } else {
      $box_id = zen_get_box_id($column_single_display->fields['layout_box_name']);
      require(DIR_WS_MODULES . 'sideboxes/' . $column_single_display->fields['layout_box_name']);
    }
   } // file_exists
   $column_single_display->MoveNext();
 } // while column_single
?>

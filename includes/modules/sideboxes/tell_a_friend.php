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
// $Id: tell_a_friend.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

// test if box should display
  $show_tell_a_friend= false;

  if (isset($_GET['products_id']) and zen_products_id_valid($_GET['products_id'])) {
    if (!($_GET['main_page']==FILENAME_TELL_A_FRIEND)) $show_tell_a_friend = true;
  }

  if ($show_tell_a_friend == true) {
    require($template->get_template_dir('tpl_tell_a_friend.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_tell_a_friend.php');
    $title =  BOX_HEADING_TELL_A_FRIEND;
    $left_corner = false;
    $right_corner = false;
    $right_arrow = false;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
?>

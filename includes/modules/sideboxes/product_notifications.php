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
// $Id: product_notifications.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

// test if box should show
  $show_product_notifications= false;

  if (isset($_GET['products_id']) and zen_products_id_valid($_GET['products_id'])) {
    if ($_SESSION['customer_id']) {
      $check_query = "select count(*) as count
                      from " . TABLE_CUSTOMERS_INFO . "
                      where customers_info_id = '" . (int)$_SESSION['customer_id'] . "'
                      and global_product_notifications = '1'";

      $check = $db->Execute($check_query);

      if ($check->fields['count'] <= 0) {
        $show_product_notifications= true;
      }
    } else {
      $show_product_notifications= true;
    }
  }

if ($show_product_notifications == true) {
  if (isset($_GET['products_id'])) {
    if ($_SESSION['customer_id']) {
      $check_query = "select count(*) as count
                      from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                      where products_id = '" . (int)$_GET['products_id'] . "'
                      and customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $check = $db->Execute($check_query);

      $notification_exists = (($check->fields['count'] > 0) ? true : false);
    } else {
      $notification_exists = false;
    }

    $info_box_contents = array();
    if ($notification_exists == true) {
      require($template->get_template_dir('tpl_yes_notifications.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_yes_notifications.php');
    } else {
      require($template->get_template_dir('tpl_no_notifications.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_no_notifications.php');
    }
    $title =  BOX_HEADING_NOTIFICATIONS;
    $box_id = productnotifications;
    $left_corner = false;
    $right_corner = false;
    $right_arrow = false;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
}
?>
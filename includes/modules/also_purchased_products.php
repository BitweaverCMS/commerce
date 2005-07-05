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
// $Id: also_purchased_products.php,v 1.1 2005/07/05 05:59:09 bitweaver Exp $
//
  if (isset($_GET['products_id']) && SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS > 0) {

    $orders = $db->Execute(sprintf(SQL_ALSO_PURCHASED, (int)$_GET['products_id'], (int)$_GET['products_id']));

    $num_products_ordered = $orders->RecordCount();

    if ($num_products_ordered > 0) {
//    if ($num_products_ordered >= MIN_DISPLAY_ALSO_PURCHASED) {
      $row = 0;
      $col = 0;
      $list_box_contents = '';
      if ($num_products_ordered < SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS) {
        $col_width = 100/$num_products_ordered;
      } else {
        $col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS;
      }
      $info_box_contents = array();
      while (!$orders->EOF) {
        $orders->fields['products_name'] = zen_get_products_name($orders->fields['products_id']);
        $list_box_contents[$row][$col] = array('align' => 'center',
                                               'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
                                               'text' => '<a href="' . zen_href_link(zen_get_info_page($orders->fields['products_id']), 'products_id=' . $orders->fields['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $orders->fields['products_image'], $orders->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . zen_href_link(zen_get_info_page($orders->fields['products_id']), 'products_id=' . $orders->fields['products_id']) . '">' . $orders->fields['products_name'] . '</a>');

        $col ++;
        if ($col > (SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS - 1)) {
          $col = 0;
          $row ++;
        }
        $orders->MoveNext();

        $title =  TEXT_ALSO_PURCHASED_PRODUCTS;
      }
      require($template->get_template_dir('tpl_modules_also_purchased_products.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_also_purchased_products.php');
    }
  }
?>

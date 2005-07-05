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
// $Id: new_products.php,v 1.1 2005/07/05 05:59:09 bitweaver Exp $
//

  $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B'));

// display limits
  switch (true) {
    case (SHOW_NEW_PRODUCTS_LIMIT == '0'):
      $display_limit = '';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '1'):
      $display_limit = " and date_format(p.products_date_added, '%Y%m') >= date_format(now(), '%Y%m')";
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '30'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 30';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '60'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 60';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '90'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 90';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '120'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 120';
      break;
  }

  if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
    $new_products_query = "select p.products_id, p.products_image, p.products_tax_class_id, p.products_price, p.products_date_added
                           from " . TABLE_PRODUCTS . " p
                           where p.products_status = '1' " . $display_limit;
  } else {
    $new_products_query = "select distinct p.products_id, p.products_image, p.products_tax_class_id, p.products_date_added,
                                           p.products_price
                           from " . TABLE_PRODUCTS . " p
                           left join " . TABLE_SPECIALS . " s
                           on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
                              TABLE_CATEGORIES . " c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = c.categories_id
                           and c.parent_id = '" . (int)$new_products_category_id . "'
                           and p.products_status = '1' " . $display_limit;
  }
  $new_products = $db->ExecuteRandomMulti($new_products_query, MAX_DISPLAY_NEW_PRODUCTS);
  $row = 0;
  $col = 0;
  $list_box_contents = '';

  $num_products_count = $new_products->RecordCount();

// show only when 1 or more
  if ($num_products_count > 0) {
    if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS) {
      $col_width = 100/$num_products_count;
    } else {
      $col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
    }

    while (!$new_products->EOF) {

      $products_price = zen_get_products_display_price($new_products->fields['products_id']);

      $new_products->fields['products_name'] = zen_get_products_name($new_products->fields['products_id']);
      $list_box_contents[$row][$col] = array('align' => 'center',
                                             'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
                                             'text' => '<a href="' . zen_href_link(zen_get_info_page($new_products->fields['products_id']), 'products_id=' . $new_products->fields['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $new_products->fields['products_image'], $new_products->fields['products_name'], IMAGE_PRODUCT_NEW_WIDTH, IMAGE_PRODUCT_NEW_HEIGHT) . '</a><br /><a href="' . zen_href_link(zen_get_info_page($new_products->fields['products_id']), 'products_id=' . $new_products->fields['products_id']) . '">' . $new_products->fields['products_name'] . '</a><br />' . $products_price);

      $col ++;
      if ($col > (SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS - 1)) {
        $col = 0;
        $row ++;
      }
      $new_products->MoveNextRandom();
    }

    if ($new_products->RecordCount() > 0) {
      if (isset($new_products_category_id)) {
        $category_title = zen_get_categories_name((int)$new_products_category_id);
        $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')) . ($category_title != '' ? ' - ' . $category_title : '' );
      } else {
        $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B'));
      }
      require($template->get_template_dir('tpl_modules_whats_new.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_whats_new.php');
    }
  }
?>
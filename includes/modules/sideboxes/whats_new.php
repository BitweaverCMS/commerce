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
// $Id: whats_new.php,v 1.2 2005/07/08 06:13:04 spiderr Exp $
//

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

  $random_whats_new_sidebox_product_query = "select p.products_id, p.products_image, p.products_tax_class_id, p.products_price
                           from " . TABLE_PRODUCTS . " p
                           where p.products_status = '1' " . $display_limit . "
                           limit " . MAX_RANDOM_SELECT_NEW;

  $random_whats_new_sidebox_product = zen_random_select($random_whats_new_sidebox_product_query);

  if ($random_whats_new_sidebox_product->RecordCount() > 0 ) {
  	$random_product = $random_whats_new_sidebox_product;
    $whats_new_price = zen_get_products_display_price($random_whats_new_sidebox_product->fields['products_id']);
    $random_whats_new_sidebox_product->fields['products_name'] = zen_get_products_name($random_whats_new_sidebox_product->fields['products_id']);
    $random_whats_new_sidebox_product->fields['specials_new_products_price'] = zen_get_products_special_price($random_whats_new_sidebox_product->fields['products_id']);
    require($template->get_template_dir('tpl_whats_new.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_whats_new.php');
    $title =  BOX_HEADING_WHATS_NEW;
    $left_corner = false;
    $right_corner = false;
    $right_arrow = false;
    $title_link = FILENAME_PRODUCTS_NEW;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
?>
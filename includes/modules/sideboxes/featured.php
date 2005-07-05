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
// $Id: featured.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

// test if box should display
  $show_featured= true;

  if ($show_featured == true) {
    $random_featured_products_query = "select p.products_id, p.products_image, pd.products_name
                           from " . TABLE_PRODUCTS . " p
                           left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id
                           where p.products_id = f.products_id and p.products_id = pd.products_id and p.products_status = '1' and f.status = '1' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                           order by pd.products_name desc
                           limit " . MAX_RANDOM_SELECT_FEATURED_PRODUCTS;

    $random_featured_product = zen_random_select($random_featured_products_query);

    if ($random_featured_product->RecordCount() > 0)  {
      $featured_box_price = zen_get_products_display_price($random_featured_product->fields['products_id']);

      require($template->get_template_dir('tpl_featured.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_featured.php');
      $title =  BOX_HEADING_FEATURED_PRODUCTS;
      $left_corner = false;
      $right_corner = false;
      $right_arrow = false;
      $title_link = FILENAME_FEATURED_PRODUCTS;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>
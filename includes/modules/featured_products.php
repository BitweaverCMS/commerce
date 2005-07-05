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
// $Id: featured_products.php,v 1.2 2005/07/05 16:44:06 spiderr Exp $
//


  $title = TABLE_HEADING_FEATURED_PRODUCTS;

  if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
    $featured_products_query = "select distinct p.products_id, p.products_image, pd.products_name
                           from " . TABLE_PRODUCTS . " p
                           left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id
                           where p.products_id = f.products_id and p.products_id = pd.products_id and p.products_status = '1' and f.status = '1' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  } else {
    $featured_products_query = "select distinct p.products_id, p.products_image, pd.products_name
                           from " . TABLE_PRODUCTS . " p
                           left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id, " .
                              TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
                              TABLE_CATEGORIES . " c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = c.categories_id
                           and c.parent_id = '" . (int)$new_products_category_id . "'
                           and p.products_id = f.products_id and p.products_id = pd.products_id and p.products_status = '1' and f.status = '1' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

  }
  //$featured_products = $db->ExecuteRandomMulti($featured_products_query, MAX_DISPLAY_SEARCH_RESULTS_FEATURED);
  $featured_products = $db->query( $featured_products_query.' ORDER BY '.$db->convert_sortmode( 'random' ), NULL, MAX_DISPLAY_SEARCH_RESULTS_FEATURED);
  $row = 0;
  $col = 0;
  $list_box_contents = '';

  $num_products_count = $featured_products->RecordCount();

// show only when 1 or more
  if ($num_products_count > 0) {
    if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS) {
      $col_width = 100/$num_products_count;
    } else {
      $col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS;
    }
    while (!$featured_products->EOF) {

      $products_price = zen_get_products_display_price($featured_products->fields['products_id']);

      $list_box_contents[$row][$col] = array('align' => 'center',
                                             'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
                                             'text' => '<a href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'products_id=' . $featured_products->fields['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $featured_products->fields['products_image'], $featured_products->fields['products_name'], IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH, IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT) . '</a><br /><a href="' . zen_href_link(zen_get_info_page($featured_products->fields['products_id']), 'products_id=' . $featured_products->fields['products_id']) . '">' . $featured_products->fields['products_name'] . '</a><br />' . $products_price);

      $col ++;
      if ($col > (SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS - 1)) {
        $col = 0;
        $row ++;
      }
      $featured_products->MoveNextRandom();
    }

    if ($featured_products->RecordCount() > 0) {
      if (isset($new_products_category_id)) {
        $category_title = zen_get_categories_name((int)$new_products_category_id);
        $title =  $title . ($category_title != '' ? ' - ' . $category_title : '');
      } else {
        $title = TABLE_HEADING_FEATURED_PRODUCTS;
      }
      require($template->get_template_dir('tpl_modules_featured_products.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_featured_products.php');
    }
  }
?>

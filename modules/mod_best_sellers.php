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
// $Id: mod_best_sellers.php,v 1.1 2005/07/30 15:08:15 spiderr Exp $
//
	global $db, $gBitProduct;

// test if box should display
  $show_best_sellers= false;

  if( $gBitUser->isValid() ) {
    if ($_SESSION['customer_id']) {
      $check_query = "select count(*) as count
                      from " . TABLE_CUSTOMERS_INFO . "
                      where customers_info_id = '" . (int)$_SESSION['customer_id'] . "'
                      and global_product_notifications = '1'";

      $check = $db->Execute($check_query);

      if ($check->fields['count'] > 0) {
        $show_best_sellers= true;
      }
    }
  } else {
    $show_best_sellers= true;
  }

  if ($show_best_sellers == true) {

    if (isset($current_category_id) && ($current_category_id > 0)) {
      $best_sellers_query = "select distinct p.products_id, pd.products_name, p.products_ordered
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                                    . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c
                             where p.products_status = '1'
                             and p.products_ordered > 0
                             and p.products_id = pd.products_id
                             and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             and p.products_id = p2c.products_id
                             and p2c.categories_id = c.categories_id
                             and '" . (int)$current_category_id . "' in (c.categories_id, c.parent_id)
                             order by p.products_ordered desc, pd.products_name
                             limit " . MAX_DISPLAY_BESTSELLERS;

      $best_sellers = $db->Execute($best_sellers_query);

    } else {
      $best_sellers_query = "select distinct p.products_id, pd.products_name, p.products_ordered
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                             where p.products_status = '1'
                             and p.products_ordered > 0
                             and p.products_id = pd.products_id
                             and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             order by p.products_ordered desc, pd.products_name
                             limit " . MAX_DISPLAY_BESTSELLERS;

      $best_sellers = $db->Execute($best_sellers_query);
    }

    if ($best_sellers->RecordCount() >= MIN_DISPLAY_BESTSELLERS) {
      $title =  BOX_HEADING_BESTSELLERS;
      $box_id =  bestsellers;
      $rows = 0;
      while (!$best_sellers->EOF) {
        $rows++;
        $bestsellers_list[$rows]['id'] = $best_sellers->fields['products_id'];
        $bestsellers_list[$rows]['name']  = $best_sellers->fields['products_name'];
        $best_sellers->MoveNext();
      }

      $left_corner = false;
      $right_corner = false;
      $right_arrow = false;
      $title_link = false;
  //	require($template->get_template_dir('tpl_best_sellers.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_best_sellers.php');
      $title =  BOX_HEADING_BESTSELLERS;
  //	require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>

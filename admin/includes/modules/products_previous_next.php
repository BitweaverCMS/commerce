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
//  $Id: products_previous_next.php,v 1.8 2006/12/19 00:11:30 spiderr Exp $
//

/////
// BOF PREVIOUS NEXT

  if( empty( $prev_next_list ) ) {
// calculate the previous and next

    $check_type = $gBitDb->query("select `products_type` from " . TABLE_PRODUCTS . " where `products_id` =?", array( $productsId ) );
	if( !defined( 'PRODUCT_INFO_PREVIOUS_NEXT_SORT' ) ) {
	    define('PRODUCT_INFO_PREVIOUS_NEXT_SORT', zen_get_configuration_key_value_layout('PRODUCT_INFO_PREVIOUS_NEXT_SORT', $check_type->fields['products_type']));
	}

    // sort order
    switch(PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
      case (0):
        $prev_next_order= ' order by LPAD(p.`products_id`,11,"0")';
        break;
      case (1):
        $prev_next_order= " order by pd.`products_name`";
        break;
      case (2):
        $prev_next_order= " order by p.`products_model`";
        break;
      case (3):
        $prev_next_order= " order by p.`products_price`, pd.`products_name`";
        break;
      case (4):
        $prev_next_order= " order by p.`products_price`, p.`products_model`";
        break;
      case (5):
        $prev_next_order= " order by pd.`products_name`, p.`products_model`";
        break;
      default:
        $prev_next_order= " order by pd.`products_name`";
        break;
      }


// set current category
    $current_category_id = (isset($_GET['current_category_id']) ? $_GET['current_category_id'] : $current_category_id);

    if (!$current_category_id) {
      $sql = "SELECT `categories_id`
              from   " . TABLE_PRODUCTS_TO_CATEGORIES . "
              where  `products_id` ='" .  $productsId . "'";

      $cPath_row = $gBitDb->Execute($sql);
      $current_category_id = $cPath_row->fields['categories_id'];
    }

    $sql = "select p.`products_id`, pd.`products_name`
            from   " . TABLE_PRODUCTS . " p, "
                     . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                     . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
            where  p.`products_id` = pd.`products_id` and pd.`language_id`= '" . $_SESSION['languages_id'] . "' and p.`products_id` = ptc.`products_id` and ptc.`categories_id` = '" . $current_category_id . "'" .
            $prev_next_order
            ;

    $products_ids = $gBitDb->Execute($sql);
  }

  while (!$products_ids->EOF) {
    $id_array[] = $products_ids->fields['products_id'];
    $products_ids->MoveNext();
  }

// if invalid product id skip
  if (is_array($id_array)) {
    reset ($id_array);
    $counter = 0;
    while (list($key, $value) = each ($id_array)) {
      if ($value == $productsId) {
        $position = $counter;
        if ($key == 0) {
          $previous = -1; // it was the first to be found
        } else {
          $previous = $id_array[$key - 1];
        }
        if( isset( $id_array[$key + 1] ) ) {
          $next_item = $id_array[$key + 1];
        } else {
          $next_item = $id_array[0];
        }
      }
      $last = $value;
      $counter++;
    }

    if ($previous == -1) $previous = $last;

    $sql = "select `categories_name`
            from   " . TABLE_CATEGORIES_DESCRIPTION . "
            where  `categories_id` = $current_category_id AND `language_id` = '" . $_SESSION['languages_id'] . "'";

    $category_name_row = $gBitDb->Execute($sql);
  } // if is_array

  if (strstr($PHP_SELF, FILENAME_PRODUCTS_PRICE_MANAGER)) {
    $curr_page = FILENAME_PRODUCTS_PRICE_MANAGER;
  } else {
    $curr_page = FILENAME_ATTRIBUTES_CONTROLLER;
  }
// to display use products_previous_next_display.php
?>

<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: main_template_vars.php,v 1.16 2009/08/18 20:32:09 spiderr Exp $
//

  $sql = "select count(*) as `total`
          from " . TABLE_PRODUCTS . " p, " .
                   TABLE_PRODUCTS_DESCRIPTION . " pd
          where    p.`products_status` = '1'
          and      p.`products_id` = '" . (int)$_GET['products_id'] . "'
          and      pd.`products_id` = p.`products_id`
          and      pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";


  $res = $gBitDb->Execute($sql);

  if ( $res->fields['total'] < 1 ) {

    $mid = 'bitpackage:bitcommerce/product_not_available.tpl';

  } else {

    $tpl_page_body = '/product_music_info_display.php';

    $sql = "select * from " . TABLE_PRODUCT_MUSIC_EXTRA . "
            where `products_id` = '" . (int)$_GET['products_id'] . "'";

    $music_extras = $gBitDb->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_ARTISTS . "
            where `artists_id` = '" . $music_extras->fields['artists_id'] . "'";

    $artist = $gBitDb->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_ARTISTS_INFO . "
            where `artists_id` = '" . $music_extras->fields['artists_id'] . "'
            and `languages_id` = '" . (int)$_SESSION['languages_id'] . "'";

    $artist_info = $gBitDb->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_COMPANY . "
            where `record_company_id` = '" . $music_extras->fields['record_company_id'] . "'";

    $record_company = $gBitDb->Execute($sql);

    $sql = "select * from " . TABLE_RECORD_COMPANY_INFO . "
            where record_company_id = '" . $music_extras->fields['record_company_id'] . "'
            and languages_id = '" . (int)$_SESSION['languages_id'] . "'";

    $record_company_info = $gBitDb->Execute($sql);


    $sql = "select * from " . TABLE_MUSIC_GENRE . "
            where music_genre_id = '" . $music_extras->fields['music_genre_id'] . "'";

    $music_genre = $gBitDb->Execute($sql);

    $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . "
            set        `products_viewed` = `products_viewed` + 1
            where      `products_id` = '" . (int)$_GET['products_id'] . "'
            and        `language_id` = '" . (int)$_SESSION['languages_id'] . "'";

    $res = $gBitDb->Execute($sql);

    $sql = "select p.`products_id`, pd.`products_name`,
                  pd.`products_description`, p.`products_model`,
                  p.`products_quantity`, p.`products_image`,
                  pd.`products_url`, p.`products_price`,
                  p.`products_tax_class_id`, p.`products_date_added`,
                  p.`products_date_available`, p.`manufacturers_id`, p.`products_quantity`,
                  p.`products_weight`, p.`products_priced_by_attribute`, p.`product_is_free`,
                  p.`products_qty_box_status`,
                  p.`products_quantity_order_max`,
                  p.`products_discount_type`, p.`products_discount_type_from`, p.`products_sort_order`, p.`products_price_sorter`
           from   " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
           where  p.`products_status` = '1'
           and    p.`products_id` = '" . (int)$_GET['products_id'] . "'
           and    pd.`products_id` = p.`products_id`
           and    pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";

    $product_info = $gBitDb->Execute($sql);

    $products_price_sorter = $product_info->fields['products_price_sorter'];

    $products_price = $currencies->display_price($product_info->fields['products_price'],
                      zen_get_tax_rate($product_info->fields['products_tax_class_id']));

    $manufacturers_name= zen_get_products_manufacturers_name((int)$_GET['products_id']);

    if ($new_price = $gBitProduct->getSalePrice()) {
      $specials_price = $currencies->display_price($new_price, zen_get_tax_rate($product_info->fields['products_tax_class_id']));
    }

// get attributes
    require(DIR_FS_PAGES . $current_page_base . '/main_template_vars_attributes.php');

// if review must be approved or disabled do not show review
    $review_status = " and r.`status` = '1'";

    $reviews_query = "select COUNT(*) from " . TABLE_REVIEWS . " r, "
                                                       . TABLE_REVIEWS_DESCRIPTION . " rd
                       where r.`products_id` = '" . (int)$_GET['products_id'] . "'
                       and r.`reviews_id` = rd.`reviews_id`
                       and rd.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'" .
                       $review_status;

    $reviews = $gBitDb->Execute($reviews_query);

  }

// bof: previous next
if (PRODUCT_INFO_PREVIOUS_NEXT != 0) {
// calculate the previous and next
  if ($prev_next_list=='') {

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
        $prev_next_order= " order by p.`products_price_sorter`, pd.`products_name`";
        break;
      case (4):
        $prev_next_order= " order by p.`products_price_sorter`, p.`products_model`";
        break;
      case (5):
        $prev_next_order= " order by pd.`products_name`, p.`products_model`";
        break;
      case (6):
        $prev_next_order= ' order by LPAD(p.`products_sort_order`,11,"0"), pd.`products_name`';
        break;
      default:
        $prev_next_order= " order by pd.`products_name`";
        break;
    }

    if (!$current_category_id) {
      $sql = "SELECT `categories_id`
              from   " . TABLE_PRODUCTS_TO_CATEGORIES . "
              where  `products_id` ='" .  (int)$_GET['products_id']
              . "'";

      $cPath_row = $gBitDb->Execute($sql);
      $current_category_id = $cPath_row->fields['categories_id'];
    }

    $sql = "select p.`products_id`, p.`products_model`, p.`products_price_sorter`, pd.`products_name`, p.`products_sort_order`
            from   " . TABLE_PRODUCTS . " p, "
                     . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                     . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
            where  p.`products_status` = '1' and p.`products_id` = pd.`products_id` and pd.`language_id`= '" . $_SESSION['languages_id'] . "' and p.`products_id` = ptc.`products_id` and ptc.`categories_id` = '" . $current_category_id . "'" .
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
      if ($value == (int)$_GET['products_id']) {
        $position = $counter;
        if ($key == 0) {
          $previous = -1; // it was the first to be found
        } else {
          $previous = $id_array[$key - 1];
        }
        if ($id_array[$key + 1]) {
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
            where  `categories_id` = $current_category_id AND `language_id` = '" . $_SESSION['languages_id']
            . "'";

    $category_name_row = $gBitDb->Execute($sql);
  } // if is_array

// previous_next button and product image settings
// include products_image status 0 = off 1= on
// 0 = button only 1= button and product image 2= product image only
  $previous_button = zen_image_button(BUTTON_IMAGE_PREVIOUS, BUTTON_PREVIOUS_ALT);
  $next_item_button = zen_image_button(BUTTON_IMAGE_NEXT, BUTTON_NEXT_ALT);
  $previous_image = zen_get_products_image($previous, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT) . '<br />';
  $next_item_image = zen_get_products_image($next_item, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT) . '<br />';
  if (SHOW_PREVIOUS_NEXT_STATUS == 0) {
    $previous_image = '';
    $next_item_image = '';
  } else {
    if (SHOW_PREVIOUS_NEXT_IMAGES >= 1) {
      if (SHOW_PREVIOUS_NEXT_IMAGES == 2) {
        $previous_button = '';
        $next_item_button = '';
      }
      if ($previous == $next_item) {
        $previous_image = '';
        $next_item_image = '';
      }
    } else {
      $previous_image = '';
      $next_item_image = '';
    }
  }
}
// eof: previous next

  $products_artist_name = $artist->fields['artists_name'];
  $products_artist_url = $artist_info->fields['artists_url'];
  $products_record_company_name = $record_company->fields['record_company_name'];
  $products_record_company_url = $record_company_info->fields['record_company_url'];
  $products_music_genre_name = $music_genre->fields['music_genre_name'];
  if (!empty($products_artist_url)) $products_artist_name = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($products_artist_url), 'NONSSL', true, false) . '" target="_BLANK">'.$products_artist_name.'</a>';
  if (!empty($products_record_companyt_url)) $products_record_company_name = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($products_record_companyt_url), 'NONSSL', true, false) . '" target="_BLANK">'.$products_record_company_name.'</a>';

  $products_name = $product_info->fields['products_name'];
  $products_model = $product_info->fields['products_model'];
  $products_description = $product_info->fields['products_description'];

  if ($product_info->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  } else {
    $products_image = $product_info->fields['products_image'];
  }

  $products_url = $product_info->fields['products_url'];
  $products_date_available = $product_info->fields['products_date_available'];
  $products_date_added = $product_info->fields['products_date_added'];
  $products_manufacturer = $product_info->fields['manufacturers_name'];
  $products_weight = $product_info->fields['products_weight'];
  $products_quantity = $product_info->fields['products_quantity'];

  $products_qty_box_status = $product_info->fields['products_qty_box_status'];
  $products_quantity_order_max = $product_info->fields['products_quantity_order_max'];

  $products_base_price = $currencies->display_price( $gBitProduct->getBasePrice(), zen_get_tax_rate($product_info->fields['products_tax_class_id']));

  $product_is_free = $product_info->fields['product_is_free'];

  $products_tax_class_id = $product_info->fields['products_tax_class_id'];

  $module_show_categories = PRODUCT_INFO_CATEGORIES;
  $module_next_previous = PRODUCT_INFO_PREVIOUS_NEXT;

  $products_id_current = (int)$_GET['products_id'];
  $products_discount_type = $product_info->fields['products_discount_type'];
  $products_discount_type_from = $product_info->fields['products_discount_type_from'];

  if (is_dir(DIR_WS_TEMPLATE . $current_page_base . '/extra_main_template_vars')) {
    if ($za_dir = @dir(DIR_WS_TEMPLATE . $current_page_base. '/extra_main_template_vars')) {
      while ($zv_file = $za_dir->read()) {
        if (!strstr($zv_file, '*.php') ) {
          require(DIR_FS_TEMPLATE . $current_page_base . '/extra_main_template_vars/' . $zv_file);
        }
      }
    }
  }

	if( !empty( $mid ) ) {
		print $gBitSmarty->fetch( $mid );
	} else {
		require( DIR_FS_PAGES . $current_page_base . '/' . $tpl_page_body);
	}

  require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_ALSO_PURCHASED_PRODUCTS));
?>

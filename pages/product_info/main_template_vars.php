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
// $Id: main_template_vars.php,v 1.12 2005/09/27 22:33:57 spiderr Exp $
//

  $sql = "select count(*) as `total`
          from " . TABLE_PRODUCTS . " p, " .
                   TABLE_PRODUCTS_DESCRIPTION . " pd
          where    p.`products_status` = '1'
          and      p.`products_id` = '" . (int)$_GET['products_id'] . "'
          and      pd.`products_id` = p.`products_id`
          and      pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";


  $res = $db->Execute($sql);

  if ( $res->fields['total'] < 1 ) {

    $mid = 'bitpackage:bitcommerce/product_not_available.tpl';

  } else {

    $tpl_page_body = 'tpl_product_info_display.php';

    $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . "
            set        products_viewed = products_viewed+1
            where      products_id = '" . (int)$_GET['products_id'] . "'
            and        language_id = '" . (int)$_SESSION['languages_id'] . "'";

    $res = $db->Execute($sql);

    $sql = "select p.`products_id`, pd.`products_name`,
                  pd.`products_description`, p.`products_model`,
                  p.`products_quantity`, p.`products_image`,
                  pd.`products_url`, p.`products_price`,
                  p.`products_tax_class_id`, p.`products_date_added`,
                  p.`products_date_available`, p.`manufacturers_id`, p.`products_quantity`,
                  p.`products_weight`, p.`products_priced_by_attribute`, p.`product_is_free`,
                  p.`products_qty_box_status`,
                  p.`products_quantity_order_max`,
                  p.`products_discount_type`, p.`products_discount_type_from`, p.`products_sort_order`, p.`products_price_sorter`, m.manufacturers_name
           from   " . TABLE_PRODUCTS . " p LEFT OUTER JOIN " . TABLE_MANUFACTURERS ." m ON (p.`manufacturers_id`=m.`manufacturers_id`), " . TABLE_PRODUCTS_DESCRIPTION . " pd
           where  p.`products_status` = '1'
           and    p.`products_id` = '" . (int)$_GET['products_id'] . "'
           and    pd.`products_id` = p.`products_id`
           and    pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";

    $product_info = $db->Execute($sql);

    $products_price_sorter = $product_info->fields['products_price_sorter'];

    $products_price = $currencies->display_price($product_info->fields['products_price'],
                      zen_get_tax_rate($product_info->fields['products_tax_class_id']));

    $manufacturers_name= zen_get_products_manufacturers_name((int)$_GET['products_id']);

    if ($new_price = zen_get_products_special_price($product_info->fields['products_id'])) {

      $specials_price = $currencies->display_price($new_price,
                        zen_get_tax_rate($product_info->fields['products_tax_class_id']));

    }

// get attributes
    require(DIR_FS_MODULES . 'pages/' . $current_page_base . '/main_template_vars_attributes.php');

// if review must be approved or disabled do not show review
    $review_status = " and r.status = '1'";

    $reviews_query = "select count(*) as count from " . TABLE_REVIEWS . " r, "
                                                       . TABLE_REVIEWS_DESCRIPTION . " rd
                       where r.`products_id` = '" . (int)$_GET['products_id'] . "'
                       and r.`reviews_id` = rd.`reviews_id`
                       and rd.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'" .
                       $review_status;

    $reviews = $db->Execute($reviews_query);

  }

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

  $products_base_price = $currencies->display_price(zen_get_products_base_price((int)$_GET['products_id']),
                      zen_get_tax_rate($product_info->fields['products_tax_class_id']));

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
        if (strstr($zv_file, '*.php') ) {
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

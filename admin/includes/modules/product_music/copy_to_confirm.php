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
//  $Id$

        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
          $products_id = zen_db_prepare_input($_POST['products_id']);
          $categories_id = zen_db_prepare_input($_POST['categories_id']);

// Copy attributes to duplicate product
          $products_id_from=$products_id;

          if ($_POST['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $check = $gBitDb->Execute("select count(*) as `total`
                                     from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                     where `products_id` = '" . (int)$products_id . "'
                                     and `categories_id` = '" . (int)$categories_id . "'");
              if ($check->fields['total'] < '1') {
                $gBitDb->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                          (`products_id`, `categories_id`)
                              values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($_POST['copy_as'] == 'duplicate') {
            $old_products_id = (int)$products_id;
            $product = $gBitDb->Execute("select `products_type`, `products_quantity`, `products_model`, `products_image`,
                                            `products_price`, `products_virtual`, `products_date_available`, `products_weight`,
                                            `products_tax_class_id`, `manufacturers_id`,
                                            `products_quantity_order_min`, `products_quantity_order_units`, `products_priced_by_attribute`,
                                            `product_is_free`, `product_is_call`, `products_quantity_mixed`,
                                            `product_is_always_free_ship`, `products_qty_box_status`, `products_quantity_order_max`, `products_sort_order`,
                                            `lowest_purchase_price`, `master_categories_id`
                                     from " . TABLE_PRODUCTS . "
                                     where `products_id` = '" . (int)$products_id . "'");

            $gBitDb->Execute("insert into " . TABLE_PRODUCTS . "
                                      (`products_type`, `products_quantity`, `products_model`, `products_image`,
                                       `products_price`, `products_virtual`, `products_date_added`, `products_date_available`,
                                       `products_weight`, `products_status`, `products_tax_class_id`,
                                       `manufacturers_id`,
                                       `products_quantity_order_min`, `products_quantity_order_units`, `products_priced_by_attribute`,
                                       `product_is_free`, product_is_call`, `products_quantity_mixed`,
                                       `product_is_always_free_ship`, `products_qty_box_status`, `products_quantity_order_max`, `products_sort_order`,
                                       `lowest_purchase_price`, `master_categories_id`
                                       )
                          values ('" . zen_db_input($product->fields['products_type']) . "',
                '" . zen_db_input($product->fields['products_quantity']) . "',
                                  '" . zen_db_input($product->fields['products_model']) . "',
                                  '" . zen_db_input($product->fields['products_image']) . "',
                                  '" . zen_db_input($product->fields['products_price']) . "',
                                  '" . zen_db_input($product->fields['products_virtual']) . "',
                                  now(),
                                  '" . zen_db_input($product->fields['products_date_available']) . "',
                                  '" . zen_db_input($product->fields['products_weight']) . "', '0',
                                  '" . (int)$product->fields['products_tax_class_id'] . "',
                                  '" . (int)$product->fields['manufacturers_id'] . "',
                                  '" . zen_db_input($product->fields['products_quantity_order_min']) . "',
                                  '" . zen_db_input($product->fields['products_quantity_order_units']) . "',
                                  '" . zen_db_input($product->fields['products_priced_by_attribute']) . "',
                                  '" . (int)$product->fields['product_is_free'] . "',
                                  '" . (int)$product->fields['product_is_call'] . "',
                                  '" . (int)$product->fields['products_quantity_mixed'] . "',
                                  '" . zen_db_input($product->fields['product_is_always_free_ship']) . "',
                                  '" . zen_db_input($product->fields['products_qty_box_status']) . "',
                                  '" . zen_db_input($product->fields['products_quantity_order_max']) . "',
                                  '" . zen_db_input($product->fields['products_sort_order']) . "',
                                  '" . zen_db_input($product->fields['lowest_purchase_price']) . "',
                                  '" . zen_db_input($product->fields['master_categories_id']) .
                                  "')");

            $dup_products_id = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );

            if (isset($_POST['copy_media']) && $_POST['copy_media'] == 'on') {
              $product_media = $gBitDb->Execute("select `media_id` from " . TABLE_MEDIA_TO_PRODUCTS . "
                                             where `product_id` = '" . (int)$products_id . "'");
              while (!$product_media->EOF) {
                $gBitDb->Execute("insert into " . TABLE_MEDIA_TO_PRODUCTS . "
                              (`media_id`, `product_id`)
                              values (
                              '" . $product_media->fields['media_id'] . "',
                              '" . $dup_products_id . "')");
                $product_media->MoveNext();
              }
            }

            $music_extra = $gBitDb->Execute("select `artists_id`, `record_company_id`, `music_genre_id` from " . TABLE_PRODUCT_MUSIC_EXTRA .
            		" where `products_id` = '" . (int)$products_id . "'");

            $gBitDb->Execute("insert into " . TABLE_PRODUCT_MUSIC_EXTRA . "
                          (`products_id`, `artists_id`, `record_company_id`, `music_genre_id`)
                          values (
                         '" . (int)$dup_products_id . "',
                         '" . zen_db_input($music_extra->fields['artists_id']) . "',
                         '" . zen_db_input($music_extra->fields['record_company_id']) . "',
                         '" . zen_db_input($music_extra->fields['music_genre_id']) . "')");


            $description = $gBitDb->Execute("select `language_id`, `products_name`, `products_description`,
                                                             `products_url`
                                         from " . TABLE_PRODUCTS_DESCRIPTION . "
                                         where `products_id` = '" . (int)$products_id . "'");
            while (!$description->EOF) {
              $gBitDb->Execute("insert into " . TABLE_PRODUCTS_DESCRIPTION . "
                                        (`products_id`, `language_id`, `products_name`, `products_description`,
                                         `products_url`, `products_viewed`)
                            values ('" . (int)$dup_products_id . "',
                                    '" . (int)$description->fields['language_id'] . "',
                                    '" . zen_db_input($description->fields['products_name']) . "',
                                    '" . zen_db_input($description->fields['products_description']) . "',
                                    '" . zen_db_input($description->fields['products_url']) . "', '0')");
              $description->MoveNext();
            }

            $gBitDb->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                      (`products_id`, `categories_id`)
                          values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
            $products_id = $dup_products_id;
            $description->MoveNext();
// FIX HERE
/////////////////////////////////////////////////////////////////////////////////////////////
// Copy attributes to duplicate product
// moved above            $products_id_from=zen_db_input($products_id);
            $products_id_to= $dup_products_id;
            $products_id = $dup_products_id;

if ( $_POST['copy_attributes']=='copy_attributes_yes' and $_POST['copy_as'] == 'duplicate' ) {
  // $products_id_to= $copy_to_products_id;
  // $products_id_from = $products_id;
//            $copy_attributes_delete_first='1';
//            $copy_attributes_duplicates_skipped='1';
//            $copy_attributes_duplicates_overwrite='0';

            if (DOWNLOAD_ENABLED == 'true') {
              $copy_attributes_include_downloads='1';
              $copy_attributes_include_filename='1';
            } else {
              $copy_attributes_include_downloads='0';
              $copy_attributes_include_filename='0';
            }

            zen_copy_products_attributes($products_id_from, $products_id_to);
}
// EOF: Attributes Copy on non-linked
/////////////////////////////////////////////////////////////////////

            // copy product discounts to duplicate
            zen_copy_discounts_to_product($old_products_id, (int)$dup_products_id);

          }

          // reset lowest_purchase_price for searches etc.
          zen_update_lowest_purchase_price($products_id);

        }
        zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&products_id=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
?>

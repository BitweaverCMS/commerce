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
//  $Id: copy_to_confirm.php,v 1.9 2006/11/01 19:15:30 lsces Exp $

        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
          $products_id = zen_db_prepare_input($_POST['products_id']);
          $categories_id = zen_db_prepare_input($_POST['categories_id']);

// Copy attributes to duplicate product
          $products_id_from=$products_id;

          if ($_POST['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $check = $db->Execute("select count(*) as `total`
                                     from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                     where `products_id` = '" . (int)$products_id . "'
                                     and `categories_id` = '" . (int)$categories_id . "'");
              if ($check->fields['total'] < '1') {
                $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                          (`products_id`, `categories_id`)
                              values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($_POST['copy_as'] == 'duplicate') {
            $old_products_id = (int)$products_id;
            $product = $db->Execute("select `products_type`, `products_quantity`, `products_model`, `products_image`,
                                            `products_price`, `products_virtual`, `products_date_available`, `products_weight`,
                                            `products_tax_class_id`, `manufacturers_id`,
                                            `products_quantity_order_min`, `products_quantity_order_units`, `products_priced_by_attribute`,
                                            `product_is_free`, `product_is_call`, `products_quantity_mixed`,
                                            `product_is_always_free_ship`, `products_qty_box_status`, `products_quantity_order_max`, `products_sort_order`,
                                            `products_price_sorter`, `master_categories_id`
                                     from " . TABLE_PRODUCTS . "
                                     where `products_id` = '" . (int)$products_id . "'");
            $db->Execute("insert into " . TABLE_PRODUCTS . "
                                      (`products_type`, `products_quantity`, `products_model`, `products_image`,
                                       `products_price`, `products_virtual`, `products_date_added`, `products_date_available`,
                                       `products_weight`, `products_status`, `products_tax_class_id`,
                                       `manufacturers_id`,
                                       `products_quantity_order_min`, `products_quantity_order_units`, `products_priced_by_attribute`,
                                       `product_is_free`, `product_is_call`, `products_quantity_mixed`,
                                       `product_is_always_free_ship`, `products_qty_box_status`, `products_quantity_order_max`, `products_sort_order`,
                                       `products_price_sorter`, `master_categories_id`
                                       )
                          values ('" . zen_db_input($product->fields['products_type']) . "',
                                  '" . zen_db_input($product->fields['products_quantity']) . "',
                                  '" . zen_db_input($product->fields['products_model']) . "',
                                  '" . zen_db_input($product->fields['products_image']) . "',
                                  '" . zen_db_input($product->fields['products_price']) . "',
                                  '" . zen_db_input($product->fields['products_virtual']) . "',
                                  ".$db->qtNOW().",
                                  '" . zen_db_input($product->fields['products_date_available']) . "',
                                  '" . (int)zen_db_input($product->fields['products_weight']) . "', '0',
                                  '" . (int)$product->fields['products_tax_class_id'] . "',
                                  '" . (int)$product->fields['manufacturers_id'] . "',
                                  '" . (int)zen_db_input($product->fields['products_quantity_order_min']) . "',
                                  '" . (int)zen_db_input($product->fields['products_quantity_order_units']) . "',
                                  '" . (int)zen_db_input($product->fields['products_priced_by_attribute']) . "',
                                  '" . (int)$product->fields['product_is_free'] . "',
                                  '" . (int)$product->fields['product_is_call'] . "',
                                  '" . (int)$product->fields['products_quantity_mixed'] . "',
                                  '" . (int)zen_db_input($product->fields['product_is_always_free_ship']) . "',
                                  '" . (int)zen_db_input($product->fields['products_qty_box_status']) . "',
                                  '" . (int)zen_db_input($product->fields['products_quantity_order_max']) . "',
                                  '" . (int)zen_db_input($product->fields['products_sort_order']) . "',
                                  '" . (int)zen_db_input($product->fields['products_price_sorter']) . "',
                                  '" . (int)zen_db_input($categories_id) .
                                  "')");

            $dup_products_id = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );

            $description = $db->Execute("select `language_id`, `products_name`, `products_description`,
                                                             `products_url`
                                         from " . TABLE_PRODUCTS_DESCRIPTION . "
                                         where `products_id` = '" . (int)$products_id . "'");
            while (!$description->EOF) {
              $db->Execute("insert into " . TABLE_PRODUCTS_DESCRIPTION . "
                                        (`products_id`, `language_id`, `products_name`, `products_description`,
                                         `products_url`, `products_viewed`)
                            values ('" . (int)$dup_products_id . "',
                                    '" . (int)$description->fields['language_id'] . "',
                                    '" . zen_db_input($description->fields['products_name']) . "',
                                    '" . zen_db_input($description->fields['products_description']) . "',
                                    '" . zen_db_input($description->fields['products_url']) . "', '0')");
              $description->MoveNext();
            }

            $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
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
  // $products_id_from = $pID;
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

          // reset products_price_sorter for searches etc.
          zen_update_products_price_sorter($products_id);

        }
        zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
?>
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
//  $Id: update_product.php,v 1.4 2005/08/03 15:35:14 spiderr Exp $
//
        if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
          $action = 'new_product';
        } else {
          if (isset($_GET['pID'])) $products_id = zen_db_prepare_input($_GET['pID']);
          $products_date_available = zen_db_prepare_input($_POST['products_date_available']);

          $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

          $sql_data_array = array('products_quantity' => zen_db_prepare_input($_POST['products_quantity']),
                                  'products_type' => zen_db_prepare_input($_GET['product_type']),
                                  'products_model' => zen_db_prepare_input($_POST['products_model']),
                                  'products_price' => zen_db_prepare_input($_POST['products_price']),
                                  'products_date_available' => $products_date_available,
                                  'products_weight' => zen_db_prepare_input($_POST['products_weight']),
                                  'products_status' => zen_db_prepare_input($_POST['products_status']),
                                  'products_virtual' => zen_db_prepare_input($_POST['products_virtual']),
                                  'products_tax_class_id' => zen_db_prepare_input($_POST['products_tax_class_id']),
                                  'products_quantity_order_min' => zen_db_prepare_input($_POST['products_quantity_order_min']),
                                  'products_quantity_order_units' => zen_db_prepare_input($_POST['products_quantity_order_units']),
                                  'products_priced_by_attribute' => zen_db_prepare_input($_POST['products_priced_by_attribute']),
                                  'product_is_free' => zen_db_prepare_input($_POST['product_is_free']),
                                  'product_is_call' => zen_db_prepare_input($_POST['product_is_call']),
                                  'products_quantity_mixed' => zen_db_prepare_input($_POST['products_quantity_mixed']),
                                  'product_is_always_free_ship' => zen_db_prepare_input($_POST['product_is_always_free_ship']),
                                  'products_qty_box_status' => zen_db_prepare_input($_POST['products_qty_box_status']),
                                  'products_quantity_order_max' => zen_db_prepare_input($_POST['products_quantity_order_max']),
                                  'products_sort_order' => zen_db_prepare_input($_POST['products_sort_order']),
                                  'products_discount_type' => zen_db_prepare_input($_POST['products_discount_type']),
                                  'products_discount_type_from' => zen_db_prepare_input($_POST['products_discount_type_from']),
                                  'products_price_sorter' => zen_db_prepare_input($_POST['products_price_sorter'])
                                  );

// when set to none remove from database
//          if (isset($_POST['products_image']) && zen_not_null($_POST['products_image']) && ($_POST['products_image'] != 'none')) {
          if (isset($_POST['products_image']) && zen_not_null($_POST['products_image']) && (!is_numeric(strpos($_POST['products_image'],'none'))) ) {
            $sql_data_array['products_image'] = zen_db_prepare_input($_POST['products_image']);
            $new_image= 'true';
          } else {
            $sql_data_array['products_image'] = '';
            $new_image= 'false';
          }

          if ($action == 'insert_product') {
            $insert_sql_data = array( 'products_date_added' => 'now()',
                                      'master_categories_id' => (int)$current_category_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $db->associateInsert(TABLE_PRODUCTS, $sql_data_array);
            $products_id = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );

            // reset products_price_sorter for searches etc.
            zen_update_products_price_sorter($products_id);

            $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                      (products_id, categories_id)
                          values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");


            $sql_data_array = array('products_id' => $products_id,
                                    'artists_id' => zen_db_prepare_input($_POST['artists_id']),
                                    'record_company_id' => zen_db_prepare_input($_POST['record_company_id']),
                                    'music_genre_id' => zen_db_prepare_input($_POST['music_genre_id']));

            $db->associateInsert(TABLE_PRODUCT_MUSIC_EXTRA, $sql_data_array);

          } elseif ($action == 'update_product') {
            $update_sql_data = array( 'products_last_modified' => 'now()',
                                      'master_categories_id' => ($_POST['master_category'] > 0 ? zen_db_prepare_input($_POST['master_category']) : zen_db_prepare_input($_POST['master_categories_id'])));

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);

            $db->associateInsert(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");

            // reset products_price_sorter for searches etc.
            zen_update_products_price_sorter((int)$products_id);

            $sql_data_array = array('artists_id' => zen_db_prepare_input($_POST['artists_id']),
                                    'record_company_id' => zen_db_prepare_input($_POST['record_company_id']),
                                    'music_genre_id' => zen_db_prepare_input($_POST['music_genre_id']));

            $db->associateInsert(TABLE_PRODUCT_MUSIC_EXTRA, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
          }

          $languages = zen_get_languages();
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array('products_name' => zen_db_prepare_input($_POST['products_name'][$language_id]),
                                    'products_description' => zen_db_prepare_input($_POST['products_description'][$language_id]),
                                    'products_url' => zen_db_prepare_input($_POST['products_url'][$language_id]));

            if ($action == 'insert_product') {
              $insert_sql_data = array('products_id' => $products_id,
                                       'language_id' => $language_id);

              $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

              $db->associateInsert(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
            } elseif ($action == 'update_product') {
              $db->associateInsert(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
            }
          }

// add meta tags
          $languages = zen_get_languages();
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array('metatags_title' => zen_db_prepare_input($_POST['metatags_title'][$language_id]),
                                    'metatags_keywords' => zen_db_prepare_input($_POST['metatags_keywords'][$language_id]),
                                    'metatags_description' => zen_db_prepare_input($_POST['metatags_description'][$language_id]));

            if ($action == 'insert_product_meta_tags') {

              $insert_sql_data = array('products_id' => $products_id,
                                       'language_id' => $language_id);

              $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

              $db->associateInsert(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array);
            } elseif ($action == 'update_product_meta_tags') {
              $db->associateInsert(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
            }
          }


// future image handler code
define('IMAGE_MANAGER_HANDLER', 0);
          if ($new_image == 'true' and IMAGE_MANAGER_HANDLER >= 1) {
            $src= CommerceProduct::getImageUrl( $products_id );
            $filename_small= $src;
            preg_match("/.*\/(.*)\.(\w*)$/", $src, $fname);
            list($oiwidth, $oiheight, $oitype) = getimagesize($src);

            define('DIR_IMAGEMAGICK', '');
            $small_width= SMALL_IMAGE_WIDTH;
            $small_height= SMALL_IMAGE_HEIGHT;
            $medium_width= MEDIUM_IMAGE_WIDTH;
            $medium_height= MEDIUM_IMAGE_HEIGHT;
            $large_width= LARGE_IMAGE_WIDTH;
            $large_height= LARGE_IMAGE_HEIGHT;

            $k = max($oiheight / $small_height, $oiwidth / $small_width); //use smallest size
            $small_width = round($oiwidth / $k);
            $small_height = round($oiheight / $k);

            $k = max($oiheight / $medium_height, $oiwidth / $medium_width); //use smallest size
            $medium_width = round($oiwidth / $k);
            $medium_height = round($oiheight / $k);

            $large_width= $oiwidth;
            $large_height= $oiheight;

            $products_image = CommerceProduct::getImageUrl( $products_id );
            $products_image_extention = substr($products_image, strrpos($products_image, '.'));
            $products_image_base = ereg_replace($products_image_extention, '', $products_image);

            $filename_medium = DIR_FS_CATALOG . DIR_WS_IMAGES . 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . '.' . $fname[2];
            $filename_large = DIR_FS_CATALOG . DIR_WS_IMAGES . 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . '.' . $fname[2];

// ImageMagick
            if (IMAGE_MANAGER_HANDLER == '1') {
              copy($src, $filename_large);
              copy($src, $filename_medium);
              exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $large_width . " " . $filename_large);
              exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $medium_width . " " . $filename_medium);
              exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $small_width . " " . $filename_small);
            }
          }

          zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
        }
?>

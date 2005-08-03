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
//  $Id: attributes_controller.php,v 1.4 2005/08/03 15:35:06 spiderr Exp $
//

  require('includes/application_top.php');

  // verify option names, values, products
  $chk_option_names = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . " where language_id='" . $_SESSION['languages_id'] . "' limit 1");
  if ($chk_option_names->RecordCount() < 1) {
    $messageStack->add_session(ERROR_DEFINE_OPTION_NAMES, 'caution');
    zen_redirect(zen_href_link_admin(FILENAME_OPTIONS_NAME_MANAGER));
  }
  $chk_option_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id != '0' and language_id='" . $_SESSION['languages_id'] . "' limit 1");
  if ($chk_option_values->RecordCount() < 1) {
    $messageStack->add_session(ERROR_DEFINE_OPTION_VALUES, 'caution');
    zen_redirect(zen_href_link_admin(FILENAME_OPTIONS_VALUES_MANAGER));
  }
  $chk_products = $db->Execute("select * from " . TABLE_PRODUCTS . " limit 1");
  if ($chk_products->RecordCount() < 1) {
    $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
    zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES));
  }

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $languages = zen_get_languages();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $products_filter = (isset($_GET['products_filter']) ? $_GET['products_filter'] : $products_filter);

  $current_category_id = (isset($_GET['current_category_id']) ? $_GET['current_category_id'] : $current_category_id);

  if ($action == 'new_cat') {
    $current_category_id = (isset($_GET['current_category_id']) ? $_GET['current_category_id'] : $current_category_id);
    $new_product_query = $db->Execute("select ptc.* from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' where ptc.categories_id='" . $current_category_id . "' order by pd.products_name");
    $products_filter = $new_product_query->fields['products_id'];
    zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
  }

// set categories and products if not set
  if ($products_filter == '' and $current_category_id != '') {
    $new_product_query = $db->Execute("select ptc.* from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' where ptc.categories_id='" . $current_category_id . "' order by pd.products_name");
    $products_filter = $new_product_query->fields['products_id'];
    if ($products_filter != '') {
      zen_redirect(zen_href_link_admin(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
    }
  } else {
    if ($products_filter == '' and $current_category_id == '') {
      $reset_categories_id = zen_get_category_tree('', '', '0', '', '', true);
      $current_category_id = $reset_categories_id[0]['id'];
      $new_product_query = $db->Execute("select ptc.* from " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on ptc.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' where ptc.categories_id='" . $current_category_id . "' order by pd.products_name");
      $products_filter = $new_product_query->fields['products_id'];
      $_GET['products_filter'] = $products_filter;
    }
  }

  require(DIR_WS_MODULES . FILENAME_PREV_NEXT);

  if (zen_not_null($action)) {
    $_SESSION['page_info'] = '';
    if (isset($_GET['option_page'])) $_SESSION['page_info'] .= 'option_page=' . $_GET['option_page'] . '&';
    if (isset($_GET['value_page'])) $_SESSION['page_info'] .= 'value_page=' . $_GET['value_page'] . '&';
    if (isset($_GET['attribute_page'])) $_SESSION['page_info'] .= 'attribute_page=' . $_GET['attribute_page'] . '&';
    if (isset($_GET['products_filter'])) $_SESSION['page_info'] .= 'products_filter=' . $_GET['products_filter'] . '&';
    if (isset($_GET['current_category_id'])) $_SESSION['page_info'] .= 'current_category_id=' . $_GET['current_category_id'] . '&';

    if (zen_not_null($_SESSION['page_info'])) {
      $_SESSION['page_info'] = substr($_SESSION['page_info'], 0, -1);
    }

    switch ($action) {
/////////////////////////////////////////
//// BOF OF FLAGS
      case 'set_flag_attributes_display_only':
        $action='';
        $new_flag= $db->Execute("select products_attributes_id, products_id, attributes_display_only from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        if ($new_flag->fields['attributes_display_only'] == '0') {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_display_only='1' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        } else {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_display_only='0' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        }
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;

      case 'set_flag_product_attribute_is_free':
        $action='';
        $new_flag= $db->Execute("select products_attributes_id, products_id, product_attribute_is_free from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        if ($new_flag->fields['product_attribute_is_free'] == '0') {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set product_attribute_is_free='1' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        } else {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set product_attribute_is_free='0' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        }
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;

      case 'set_flag_attributes_default':
        $action='';
        $new_flag= $db->Execute("select products_attributes_id, products_id, attributes_default from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        if ($new_flag->fields['attributes_default'] == '0') {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_default='1' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        } else {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_default='0' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        }
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;

      case 'set_flag_attributes_discounted':
        $action='';
        $new_flag= $db->Execute("select products_attributes_id, products_id, attributes_discounted from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        if ($new_flag->fields['attributes_discounted'] == '0') {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_discounted='1' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        } else {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_discounted='0' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        }

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($_GET['products_filter']);

        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;

      case 'set_flag_attributes_price_base_included':
        $action='';
        $new_flag= $db->Execute("select products_attributes_id, products_id, attributes_price_base_included from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        if ($new_flag->fields['attributes_price_base_included'] == '0') {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_price_base_included='1' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        } else {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_price_base_included='0' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        }

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($_GET['products_filter']);

        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;

      case 'set_flag_attributes_required':
        $action='';
        $new_flag= $db->Execute("select products_attributes_id, products_id, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        if ($new_flag->fields['attributes_required'] == '0') {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_required='1' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        } else {
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set attributes_required='0' where products_id='" . $_GET['products_filter'] . "' and products_attributes_id='" . $_GET['attributes_id'] . "'");
        }
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;

//// EOF OF FLAGS
/////////////////////////////////////////

      case 'set_products_filter':
        $_GET['products_filter'] = $_POST['products_filter'];
        $action='';
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $_GET['products_filter'] . '&current_category_id=' . $_POST['current_category_id']));
        break;
// update by product
      case ('update_product'):
        if (!zen_has_product_attributes($products_filter, 'false')) {
          $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT_NONE . $products_filter . ' ' . zen_get_products_name($products_filter, $_SESSION['languages_id']), 'error');
        } else {
          $messageStack->add_session(SUCCESS_PRODUCT_UPDATE_SORT . $products_filter . ' ' . zen_get_products_name($products_filter, $_SESSION['languages_id']), 'success');
          zen_update_attributes_products_option_values_sort_order($products_filter);
        }
        $action='';
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_GET['current_category_id']));
        break;
      case 'add_product_attributes':
// check for duplicate and block them
        $check_duplicate = $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                         where products_id ='" . $_POST['products_id'] . "'
                                         and options_id = '" . $_POST['options_id'] . "'
                                         and options_values_id = '" . $_POST['values_id'] . "'");

        if ($check_duplicate->RecordCount() > 0) {
          // do not add duplicates give a warning
          $messageStack->add_session(ATTRIBUTE_WARNING_DUPLICATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
        } else {
          // Validate options_id and options_value_id
          if (!zen_validate_options_to_options_value($_POST['options_id'], $_POST['values_id'])) {
            // do not add invalid match
            $messageStack->add_session(ATTRIBUTE_WARNING_INVALID_MATCH . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
          } else {
// iii 030811 added:  For TEXT and FILE option types, ignore option value
// entered by administrator and use PRODUCTS_OPTIONS_VALUES_TEXT instead.
        $products_options_array = $db->Execute("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $_POST['options_id'] . "'");
        $values_id = zen_db_prepare_input((($products_options_array->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT) or ($products_options_array->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUES_TEXT_ID : $_POST['values_id']);

            $products_id = zen_db_prepare_input($_POST['products_id']);
            $options_id = zen_db_prepare_input($_POST['options_id']);
//            $values_id = zen_db_prepare_input($_POST['values_id']);
            $value_price = zen_db_prepare_input($_POST['value_price']);
            $price_prefix = zen_db_prepare_input($_POST['price_prefix']);

            $products_options_sort_order = zen_db_prepare_input($_POST['products_options_sort_order']);
            $product_attribute_is_free = zen_db_prepare_input($_POST['product_attribute_is_free']);
            $products_attributes_wt = zen_db_prepare_input($_POST['products_attributes_wt']);
            $products_attributes_wt_pfix = zen_db_prepare_input($_POST['products_attributes_wt_pfix']);
            $attributes_display_only = zen_db_prepare_input($_POST['attributes_display_only']);
            $attributes_default = zen_db_prepare_input($_POST['attributes_default']);
            $attributes_discounted = zen_db_prepare_input($_POST['attributes_discounted']);
            $attributes_price_base_included = zen_db_prepare_input($_POST['attributes_price_base_included']);

            $attributes_price_onetime = zen_db_prepare_input($_POST['attributes_price_onetime']);
            $attributes_price_factor = zen_db_prepare_input($_POST['attributes_price_factor']);
            $attributes_pf_offset = zen_db_prepare_input($_POST['attributes_pf_offset']);
            $attributes_pf_onetime = zen_db_prepare_input($_POST['attributes_pf_onetime']);
            $attributes_pf_onetime_offset = zen_db_prepare_input($_POST['attributes_pf_onetime_offset']);
            $attributes_qty_prices = zen_db_prepare_input($_POST['attributes_qty_prices']);
            $attributes_qty_prices_onetime = zen_db_prepare_input($_POST['attributes_qty_prices_onetime']);

            $attributes_price_words = zen_db_prepare_input($_POST['attributes_price_words']);
            $attributes_price_words_free = zen_db_prepare_input($_POST['attributes_price_words_free']);
            $attributes_price_letters = zen_db_prepare_input($_POST['attributes_price_letters']);
            $attributes_price_letters_free = zen_db_prepare_input($_POST['attributes_price_letters_free']);
            $attributes_required = zen_db_prepare_input($_POST['attributes_required']);

// add - update as record exists
// attributes images
// when set to none remove from database
          if (isset($_POST['attributes_image']) && zen_not_null($_POST['attributes_image']) && ($_POST['attributes_image'] != 'none')) {
            $attributes_image = zen_db_prepare_input($_POST['attributes_image']);
          } else {
            $attributes_image = '';
          }

          $attributes_image = new upload('attributes_image');
          $attributes_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
          if ($attributes_image->parse() && $attributes_image->save($_POST['overwrite'])) {
            $attributes_image_name = $_POST['img_dir'] . $attributes_image->filename;
          } else {
            $attributes_image_name = (isset($_POST['attributes_previous_image']) ? $_POST['attributes_previous_image'] : '');
          }

            $db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES . "
                          values ('',
                                  '" . (int)$products_id . "',
                                  '" . (int)$options_id . "',
                                  '" . (int)$values_id . "',
                                  '" . zen_db_input($value_price) . "',
                                  '" . zen_db_input($price_prefix) . "',
                                  '" . zen_db_input($products_options_sort_order) . "',
                                  '" . zen_db_input($product_attribute_is_free) . "',
                                  '" . zen_db_input($products_attributes_wt) . "',
                                  '" . zen_db_input($products_attributes_wt_pfix) . "',
                                  '" . zen_db_input($attributes_display_only) . "',
                                  '" . zen_db_input($attributes_default) . "',
                                  '" . zen_db_input($attributes_discounted) . "',
                                  '" . zen_db_input($attributes_image_name) . "',
                                  '" . zen_db_input($attributes_price_base_included) . "',
                                  '" . zen_db_input($attributes_price_onetime) . "',
                                  '" . zen_db_input($attributes_price_factor) . "',
                                  '" . zen_db_input($attributes_pf_offset) . "',
                                  '" . zen_db_input($attributes_pf_onetime) . "',
                                  '" . zen_db_input($attributes_pf_onetime_offset) . "',
                                  '" . zen_db_input($attributes_qty_prices) . "',
                                  '" . zen_db_input($attributes_qty_prices_onetime) . "',
                                  '" . zen_db_input($attributes_price_words) . "',
                                  '" . zen_db_input($attributes_price_words_free) . "',
                                  '" . zen_db_input($attributes_price_letters) . "',
                                  '" . zen_db_input($attributes_price_letters_free) . "',
                                  '" . zen_db_input($attributes_required) . "')");

            if (DOWNLOAD_ENABLED == 'true') {
              $products_attributes_id = zen_db_insert_id( TABLE_PRODUCTS_ATTRIBUTES, 'products_attributes_id' );

              $products_attributes_filename = zen_db_prepare_input($_POST['products_attributes_filename']);
              $products_attributes_maxdays = zen_db_prepare_input($_POST['products_attributes_maxdays']);
              $products_attributes_maxcount = zen_db_prepare_input($_POST['products_attributes_maxcount']);

//die( 'I am adding ' . strlen($_POST['products_attributes_filename']) . ' vs ' . strlen(trim($_POST['products_attributes_filename'])) . ' vs ' . strlen(zen_db_prepare_input($_POST['products_attributes_filename'])) . ' vs ' . strlen(zen_db_input($products_attributes_filename)) );
              if (zen_not_null($products_attributes_filename)) {
                $db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                              values (" . (int)$products_attributes_id . ",
                                      '" . zen_db_input($products_attributes_filename) . "',
                                      '" . zen_db_input($products_attributes_maxdays) . "',
                                      '" . zen_db_input($products_attributes_maxcount) . "')");
              }
            }
          }
        }

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($_POST['products_id']);

        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&products_filter=' . $_POST['products_id'] . '&current_category_id=' . $_POST['current_category_id']));
        break;
      case 'update_product_attribute':
        $check_duplicate = $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                         where products_id ='" . $_POST['products_id'] . "'
                                         and options_id = '" . $_POST['options_id'] . "'
                                         and options_values_id = '" . $_POST['values_id'] . "'
                                         and products_attributes_id != '" . $_POST['attribute_id'] . "'");

        if ($check_duplicate->RecordCount() > 0) {
          // do not add duplicates give a warning
          $messageStack->add_session(ATTRIBUTE_WARNING_DUPLICATE_UPDATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
        } else {
          // Validate options_id and options_value_id
          if (!zen_validate_options_to_options_value($_POST['options_id'], $_POST['values_id'])) {
            // do not add invalid match
            $messageStack->add_session(ATTRIBUTE_WARNING_INVALID_MATCH_UPDATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
          } else {
            // add the new attribute
// iii 030811 added:  Enforce rule that TEXT and FILE Options use value PRODUCTS_OPTIONS_VALUES_TEXT_ID
        $products_options_query = $db->Execute("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $_POST['options_id'] . "'");
        switch ($products_options_array->fields['products_options_type']) {
          case PRODUCTS_OPTIONS_TYPE_TEXT:
          case PRODUCTS_OPTIONS_TYPE_FILE:
            $values_id = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
            break;
          default:
          $values_id = zen_db_prepare_input($_POST['values_id']);
        }
// iii 030811 added END

            $products_id = zen_db_prepare_input($_POST['products_id']);
            $options_id = zen_db_prepare_input($_POST['options_id']);
//            $values_id = zen_db_prepare_input($_POST['values_id']);
            $value_price = zen_db_prepare_input($_POST['value_price']);
            $price_prefix = zen_db_prepare_input($_POST['price_prefix']);

            $products_options_sort_order = zen_db_prepare_input($_POST['products_options_sort_order']);
            $product_attribute_is_free = zen_db_prepare_input($_POST['product_attribute_is_free']);
            $products_attributes_wt = zen_db_prepare_input($_POST['products_attributes_wt']);
            $products_attributes_wt_pfix = zen_db_prepare_input($_POST['products_attributes_wt_pfix']);
            $attributes_display_only = zen_db_prepare_input($_POST['attributes_display_only']);
            $attributes_default = zen_db_prepare_input($_POST['attributes_default']);
            $attributes_discounted = zen_db_prepare_input($_POST['attributes_discounted']);
            $attributes_price_base_included = zen_db_prepare_input($_POST['attributes_price_base_included']);

            $attributes_price_onetime = zen_db_prepare_input($_POST['attributes_price_onetime']);
            $attributes_price_factor = zen_db_prepare_input($_POST['attributes_price_factor']);
            $attributes_pf_offset = zen_db_prepare_input($_POST['attributes_pf_offset']);
            $attributes_pf_onetime = zen_db_prepare_input($_POST['attributes_pf_onetime']);
            $attributes_pf_onetime_offset = zen_db_prepare_input($_POST['attributes_pf_onetime_offset']);
            $attributes_qty_prices = zen_db_prepare_input($_POST['attributes_qty_prices']);
            $attributes_qty_prices_onetime = zen_db_prepare_input($_POST['attributes_qty_prices_onetime']);

            $attributes_price_words = zen_db_prepare_input($_POST['attributes_price_words']);
            $attributes_price_words_free = zen_db_prepare_input($_POST['attributes_price_words_free']);
            $attributes_price_letters = zen_db_prepare_input($_POST['attributes_price_letters']);
            $attributes_price_letters_free = zen_db_prepare_input($_POST['attributes_price_letters_free']);
            $attributes_required = zen_db_prepare_input($_POST['attributes_required']);

            $attribute_id = zen_db_prepare_input($_POST['attribute_id']);

// edit
// attributes images
// when set to none remove from database
          if (isset($_POST['attributes_image']) && zen_not_null($_POST['attributes_image']) && ($_POST['attributes_image'] != 'none')) {
            $attributes_image = zen_db_prepare_input($_POST['attributes_image']);
            $attributes_image_none = false;
          } else {
            $attributes_image = '';
            $attributes_image_none = true;
          }

          $attributes_image = new upload('attributes_image');
          $attributes_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
          if ($attributes_image->parse() && $attributes_image->save($_POST['overwrite'])) {
            $attributes_image_name = ($attributes_image->filename != 'none' ? ($_POST['img_dir'] . $attributes_image->filename) : '');
          } else {
            $attributes_image_name = ((isset($_POST['attributes_previous_image']) and $_POST['attributes_image'] != 'none') ? $_POST['attributes_previous_image'] : '');
          }

// turned off until working
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . "
                        set attributes_image = '" .  $attributes_image_name . "'
                        where products_attributes_id = '" . (int)$attribute_id . "'");

            $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . "
                          set products_id = '" . (int)$products_id . "',
                              options_id = '" . (int)$options_id . "',
                              options_values_id = '" . (int)$values_id . "',
                              options_values_price = '" . zen_db_input($value_price) . "',
                              price_prefix = '" . zen_db_input($price_prefix) . "',
                              products_options_sort_order = '" . zen_db_input($products_options_sort_order) . "',
                              product_attribute_is_free = '" . zen_db_input($product_attribute_is_free) . "',
                              products_attributes_wt = '" . zen_db_input($products_attributes_wt) . "',
                              products_attributes_wt_pfix = '" . zen_db_input($products_attributes_wt_pfix) . "',
                              attributes_display_only = '" . zen_db_input($attributes_display_only) . "',
                              attributes_default = '" . zen_db_input($attributes_default) . "',
                              attributes_discounted = '" . zen_db_input($attributes_discounted) . "',
                              attributes_price_base_included = '" . zen_db_input($attributes_price_base_included) . "',
                              attributes_price_onetime = '" . zen_db_input($attributes_price_onetime) . "',
                              attributes_price_factor = '" . zen_db_input($attributes_price_factor) . "',
                              attributes_pf_offset = '" . zen_db_input($attributes_pf_offset) . "',
                              attributes_pf_onetime = '" . zen_db_input($attributes_pf_onetime) . "',
                              attributes_pf_onetime_offset = '" . zen_db_input($attributes_pf_onetime_offset) . "',
                              attributes_qty_prices = '" . zen_db_input($attributes_qty_prices) . "',
                              attributes_qty_prices_onetime = '" . zen_db_input($attributes_qty_prices_onetime) . "',
                              attributes_price_words = '" . zen_db_input($attributes_price_words) . "',
                              attributes_price_words_free = '" . zen_db_input($attributes_price_words_free) . "',
                              attributes_price_letters = '" . zen_db_input($attributes_price_letters) . "',
                              attributes_price_letters_free = '" . zen_db_input($attributes_price_letters_free) . "',
                              attributes_required = '" . zen_db_input($attributes_required) . "'
                          where products_attributes_id = '" . (int)$attribute_id . "'");

            if (DOWNLOAD_ENABLED == 'true') {
              $products_attributes_filename = zen_db_prepare_input($_POST['products_attributes_filename']);
              $products_attributes_maxdays = zen_db_prepare_input($_POST['products_attributes_maxdays']);
              $products_attributes_maxcount = zen_db_prepare_input($_POST['products_attributes_maxcount']);

              if (zen_not_null($products_attributes_filename)) {
                $db->Execute("replace into " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                              set products_attributes_id = '" . (int)$attribute_id . "',
                                  products_attributes_filename = '" . zen_db_input($products_attributes_filename) . "',
                                  products_attributes_maxdays = '" . zen_db_input($products_attributes_maxdays) . "',
                                  products_attributes_maxcount = '" . zen_db_input($products_attributes_maxcount) . "'");
              }
            }
          }
        }

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($_POST['products_id']);

        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&current_category_id=' . $_POST['current_category_id']));
        break;
      case 'delete_attribute':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&current_category_id=' . $_POST['current_category_id']));
        }
        $attribute_id = zen_db_prepare_input($_GET['attribute_id']);

        $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . "
                      where products_attributes_id = '" . (int)$attribute_id . "'");

// added for DOWNLOAD_ENABLED. Always try to remove attributes, even if downloads are no longer enabled
        $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                      where products_attributes_id = '" . (int)$attribute_id . "'");

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($products_filter);

//        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info']));
        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, $_SESSION['page_info'] . '&current_category_id=' . $current_category_id));
        break;
// delete all attributes
      case 'delete_all_attributes':
        zen_delete_products_attributes($_POST['products_filter']);
        $messageStack->add_session(SUCCESS_ATTRIBUTES_DELETED . ' ID#' . $products_filter, 'success');
        $action='';
        $products_filter = $_POST['products_filter'];

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($products_filter);

        zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
        break;

// attributes copy to product
    case 'update_attributes_copy_to_product':
      $copy_attributes_delete_first = ($_POST['copy_attributes'] == 'copy_attributes_delete' ? '1' : '0');
      $copy_attributes_duplicates_skipped = ($_POST['copy_attributes'] == 'copy_attributes_ignore' ? '1' : '0');
      $copy_attributes_duplicates_overwrite = ($_POST['copy_attributes'] == 'copy_attributes_update' ? '1' : '0');
      zen_copy_products_attributes($_POST['products_filter'], $_POST['products_update_id']);
      $_GET['action']= '';
      $products_filter = $_POST['products_update_id'];
      zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
      break;

// attributes copy to category
    case 'update_attributes_copy_to_category':
      $copy_attributes_delete_first = ($_POST['copy_attributes'] == 'copy_attributes_delete' ? '1' : '0');
      $copy_attributes_duplicates_skipped = ($_POST['copy_attributes'] == 'copy_attributes_ignore' ? '1' : '0');
      $copy_attributes_duplicates_overwrite = ($_POST['copy_attributes'] == 'copy_attributes_update' ? '1' : '0');
      if ($_POST['categories_update_id'] == '') {
        $messageStack->add_session(WARNING_PRODUCT_COPY_TO_CATEGORY_NONE . ' ID#' . $_POST['products_filter'], 'warning');
      } else {
        $copy_to_category = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . $_POST['categories_update_id'] . "'");
        while (!$copy_to_category->EOF) {
          zen_copy_products_attributes($_POST['products_filter'], $copy_to_category->fields['products_id']);
          $copy_to_category->MoveNext();
        }
      }
      $_GET['action']= '';
      $products_filter = $_POST['products_filter'];
      zen_redirect(zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $_POST['current_category_id']));
      break;

    }
  }

//iii 031103 added to get results from database option type query
  $products_options_types_list = array();
//  $products_options_type_array = $db->Execute("select products_options_types_id, products_options_types_name from " . TABLE_PRODUCTS_OPTIONS_TYPES . " where language_id='" . $_SESSION['languages_id'] . "' order by products_options_types_id");
  $products_options_type_array = $db->Execute("select products_options_types_id, products_options_types_name from " . TABLE_PRODUCTS_OPTIONS_TYPES . " order by products_options_types_id");
  while (!$products_options_type_array->EOF) {
    $products_options_types_list[$products_options_type_array->fields['products_options_types_id']] = $products_options_type_array->fields['products_options_types_name'];
    $products_options_type_array->MoveNext();
  }

//CLR 030312 add function to draw pulldown list of option types
// Draw a pulldown for Option Types
//iii 031103 modified to use results of database option type query from above
function draw_optiontype_pulldown($name, $default = '') {
  global $products_options_types_list;
  $values = array();
  foreach ($products_options_types_list as $id => $text) {
    $values[] = array('id' => $id, 'text' => $text);
  }
  return zen_draw_pull_down_menu($name, $values, $default);
}

//CLR 030312 add function to translate type_id to name
// Translate option_type_values to english string
//iii 031103 modified to use results of database option type query from above
function translate_type_to_name($opt_type) {
  global $products_options_types_list;
  return $products_options_types_list[$opt_type];
  return 'Error ' . $opt_type;
}

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script language="javascript"><!--
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=600,height=460,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<!-- <body onload="init()"> -->
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
    echo zen_draw_form('search', FILENAME_CATEGORIES, '', 'get');
// show reset search
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
    }
    echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
      echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
    }
    echo '</form>';
?>
                </td>
              </tr>

      <tr>
        <td width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
<!-- products_attributes //-->
      <tr>
        <td width="100%"><table border="0" cellspacing="2" cellpadding="2">
          <tr>
  	        <td height="40" valign="bottom"><a href="<?php echo  zen_href_link_admin(FILENAME_OPTIONS_NAME_MANAGER, '', 'NONSSL') ?>"><?php echo zen_image_button('button_option_names.gif', IMAGE_OPTION_NAMES); ?></a></td>
  	        <td height="40" valign="bottom"><a href="<?php echo  zen_href_link_admin(FILENAME_OPTIONS_VALUES_MANAGER, '', 'NONSSL') ?>"><?php echo zen_image_button('button_option_values.gif', IMAGE_OPTION_VALUES); ?></a></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading">&nbsp;<?php echo HEADING_TITLE_ATRIB; ?>&nbsp;</td>
          </tr>
        </table></td>
      </tr>

<?php
if ($action == 'attributes_preview') {
  // don't show anything from here down
?>

<tr><td><table align="center"><tr>
            <td class="pageHeading" align="center"><?php echo TEXT_ATTRIBUTES_PREVIEW_DISPLAY . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></td>
</tr></table></td></tr>

<?php
} else {
  // show the attributes
?>
<?php
  if ($products_filter != '' and $action != 'attribute_features_copy_to_product' and $action != 'attribute_features_copy_to_category' and $action != 'delete_all_attributes_confirm') {
?>
      <tr>
        <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

      <tr>
        <td colspan="2"><table border="1" cellspacing="2" cellpadding="4" align="left">
          <tr>
            <td colspan="7" class="main" align="center">
              <?php echo TEXT_PRODUCTS_LISTING . TEXT_PRODUCTS_ID . $products_filter .  TEXT_PRODUCT_IN_CATEGORY_NAME . zen_get_category_name(zen_get_products_category_id($products_filter), (int)$_SESSION['languages_id']) . '<br />' . zen_get_products_name($products_filter); ?>
            </td>
          </tr>
          <tr>
            <td class="smallText" align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'action=new_product' . '&cPath=' . $current_category_id . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)) . '">' . zen_image_button('button_edit_product.gif', IMAGE_EDIT_PRODUCT) . '<br />' . TEXT_PRODUCT_EDIT . '</a>'; ?></td>
            <td class="smallText" align="center">
              <?php
                if ($zc_products->get_allow_add_to_cart($products_filter) == "Y") {
                  echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCTS_PRICE_MANAGER, '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_products_price_manager.gif', IMAGE_PRODUCTS_PRICE_MANAGER) . '<br />' . TEXT_PRODUCTS_PRICE_MANAGER . '</a>';
                } else {
                  echo TEXT_INFO_ALLOW_ADD_TO_CART_NO;
                }
              ?>
            </td>
<?php
  if (zen_has_product_attributes($products_filter, 'false')) {
?>
            <td class="smallText" align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, '&action=update_product' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_update_sort.gif', IMAGE_UPDATE_SORT) . '<br />' . TEXT_ATTRIBUTES_UPDATE_SORT_ORDER . '</a>'; ?></td>
            <td class="smallText" align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attributes_preview' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_preview.gif', IMAGE_PREVIEW) . '<br />' . TEXT_ATTRIBUTES_PREVIEW . '</a>'; ?></td>
            <td class="smallText" align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, '&action=delete_all_attributes_confirm' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '<br />' . TEXT_ATTRIBUTES_DELETE . '</a>'; ?></td>
            <td class="smallText" align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attribute_features_copy_to_product' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY) . '<br />' . TEXT_ATTRIBUTES_COPY_TO_PRODUCTS . '</a>'; ?></td>
            <td class="smallText" align="center"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attribute_features_copy_to_category' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY) . '<br />' . TEXT_ATTRIBUTES_COPY_TO_CATEGORY . '</a>'; ?></td>
<?php
} else {
?>
            <td class="main" align="center" width="200"><?php echo TEXT_NO_ATTRIBUTES_DEFINED . $products_filter; ?></td>
<?php
}
?>
          </tr>
          <tr>
            <td class="smallText" align="center" colspan="7"><?php echo '<a href="' . zen_href_link_admin(FILENAME_PRODUCTS_TO_CATEGORIES, '&products_filter=' . $products_filter) . '">' . IMAGE_PRODUCTS_TO_CATEGORIES . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
      <tr>
        <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
}
?>
<?php
} // eof: attributes_preview
?>

<?php
  if ($action == 'delete_all_attributes_confirm') {
?>
      <tr><form name="delete_all"<?php echo 'action="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_all_attributes') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?><?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
        <td colspan="2"><table border="2" cellspacing="2" cellpadding="4">
          <tr>
            <td><table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="alert" align="center"><?php echo TEXT_DELETE_ALL_ATTRIBUTES . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></td>
                <td class="main" align="center"><?php echo zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;&nbsp;' . '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
              </tr>
            </table></td>
          </table></td>
        </tr>
      </form></tr>
<?php
}
?>

<?php
  if ($action == 'attribute_features_copy_to_product') {
    $_GET['products_update_id'] = '';
    // excluded current product from the pull down menu of products
    $products_exclude_array = array();
    $products_exclude_array[] = $products_filter;
?>
      <tr><form name="product_copy_to_product"<?php echo 'action="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attributes_copy_to_product') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']) . zen_draw_hidden_field('products_id', $_GET['products_filter']) . zen_draw_hidden_field('products_update_id', $_GET['products_update_id']) . zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']); ?>
        <td colspan="2"><table border="2" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="2" cellpadding="4">
              <tr>
                <td class="main" align="center"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></td>
                <td class="main" align="left"><?php echo TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . ' ' . TEXT_COPY_ATTRIBUTES_DELETE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE; ?></td>
              </tr>
              <tr>
                <td class="alert" align="center"><?php echo TEXT_INFO_ATTRIBUTES_FEATURE_COPY_TO . '<br />' . zen_draw_products_pull_down('products_update_id', 'size="5"', $products_exclude_array, true, '', true); ?></td>
                <td class="main" align="center"><?php echo zen_image_submit('button_copy.gif', IMAGE_COPY) . '&nbsp;&nbsp;' . '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
              </tr>
            </table></td>
          </table></td>
        </tr>
      </form></tr>
<?php
}
?>

<?php
  if ($action == 'attribute_features_copy_to_category') {
?>
      <tr><form name="product_copy_to_category"<?php echo 'action="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attributes_copy_to_category') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']) . zen_draw_hidden_field('products_id', $_GET['products_filter']) . zen_draw_hidden_field('products_update_id', $_GET['products_update_id']) . zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']) . zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
        <td colspan="2"><table border="2" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="2" cellpadding="4">
              <tr>
                <td class="main" align="center"><?php echo TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY . $products_filter . '<br />' . zen_get_products_name($products_filter); ?></td>
                <td class="main" align="left"><?php echo TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . ' ' . TEXT_COPY_ATTRIBUTES_DELETE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE; ?></td>
              </tr>
              <tr>
                <td class="alert" align="center"><?php echo TEXT_INFO_ATTRIBUTES_FEATURE_CATEGORIES_COPY_TO . '<br />' . zen_draw_products_pull_down_categories('categories_update_id', 'size="5"', '', true, true); ?></td>
                <td class="main" align="center"><?php echo zen_image_submit('button_copy.gif', IMAGE_COPY) . '&nbsp;&nbsp;' . '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
              </tr>
            </table></td>
          </table></td>
        </tr>
      </form></tr>

<?php
}
?>

<?php
// fix here
// preview shot of attributes
if ($action == 'attributes_preview') {
  $_GET['products_id'] = $products_filter;
  $pInfo->products_id = $products_filter;

  include(DIR_WS_INCLUDES . 'attributes_preview.php');
?>
      <tr>
        <td colspan="2" class="main" align="center" height= "40" valign="middle"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
      <tr>
        <td colspan="2"><table border="1" cellspacing="0" cellpadding="2" align="center" class="main_page">
          <tr>
            <td class="main">
<?php
//            where    patrib.products_id='" . $pInfo->products_id . "'
//            where    patrib.products_id='" . (int)$_GET['products_id'] . "'
  $check_template = $db->Execute("select template_dir from " . TABLE_TEMPLATE_SELECT);
  echo '<link rel="stylesheet" type="text/css" href="' . DIR_WS_CATALOG_TEMPLATE . $check_template->fields['template_dir'] . '/css/stylesheet.css' . '" />';
?>

  <tr>
    <td colspan="2" class="main" align="center">
<?php
  if ($pr_attr->fields['total'] > 0) {
?>
      <table border="0" width="90%" cellspacing="0" cellpadding="2">
        <tr>
          <td colspan="2" class="main" align="left"><?php echo TEXT_PRODUCT_OPTIONS; ?></td>
        </tr>
<?php
    for($i=0;$i<sizeof($options_name);$i++) {
?>
<?php
  if ($options_comment[$i] != '' and $options_comment_position[$i] == '0') {
?>
        <tr>
          <td colspan="2" class="ProductInfoComments" align="left" valign="bottom"><?php echo $options_comment[$i]; ?></td>
        </tr>
<?php
  }
?>
        <tr>
          <td class="main" align="left" valign="top"><?php echo $options_name[$i] . ':'; ?></td>
          <td class="main" align="left" valign="top" width="75%"><?php echo $options_menu[$i]; ?></td>
        </tr>
<?php if ($options_comment[$i] != '' and $options_comment_position[$i] == '1') { ?>
        <tr>
          <td colspan="2" class="ProductInfoComments" align="left" valign="top"><?php echo $options_comment[$i]; ?></td>
        </tr>
<?php } ?>

<?php
if ($options_attributes_image[$i] != '') {
?>
        <tr><td colspan="2"><table border= "0" align="center" valign="top" cellpadding="2" cellspacing="2"><tr>
          <?php echo $options_attributes_image[$i]; ?>
        </tr></table></td></tr>
<?php
}
?>
<?php
    }
?>
      </table>
<?php
  }
?>
    </td>
  </tr>
            </td>
          </tr>
        </table></td>
      </tr>

      <tr>
        <td colspan="2" class="main" align="center" height= "40" valign="middle"><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
<?php
} // eof: attributes preview
?>

      <tr>
        <td colspan="3" class="main" height="20" align="center"><?php echo zen_draw_separator('pixel_black.gif', '90%', '2'); ?></td>
      </tr>

<?php
if ($action == 'attributes_preview') {
  // don't show anything from here down
} else {
  // show the attributes
?>
<?php
  if ($action == '') {
?>
      <tr>
        <td colspan="2"><table>
          <?php require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY); ?>
        </table></td>
      </tr>

      <tr><form name="set_products_filter_id" <?php echo 'action="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_products_filter') . '"'; ?> method="post"><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); ?><?php echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']); ?>
        <td colspan="2"><table border="0" cellspacing="0" cellpadding="2">

<?php
    if ($_GET['products_filter'] != '') {
?>
          <tr>
            <td class="main" width="200" align="left" valign="top">&nbsp;</td>
            <td colspan="2" class="main"><?php echo TEXT_PRODUCT_TO_VIEW; ?></td>
          </tr>
          <tr>
            <td class="main" width="200" align="center" valign="top">
<?php
  $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($_GET['products_filter']);
  echo ($display_priced_by_attributes ? '<span class="alert">' . TEXT_PRICED_BY_ATTRIBUTES . '</span>' . '<br />' : '');
  echo zen_get_products_display_price($_GET['products_filter']) . '<br /><br />';
  echo zen_get_products_quantity_min_units_display($_GET['products_filter'], $include_break = true);
?>
            </td>
            <td class="attributes-even" align="center"><?php echo zen_draw_products_pull_down('products_filter', 'size="5"', '', true, $_GET['products_filter'], true, true); ?></td>
            <td class="main" align="right" valign="top"><?php echo zen_image_submit('button_display.gif', IMAGE_DISPLAY); ?></td>
          </tr>
        </table></td>
      </form></tr>

<?php
    } // product dropdown
?>
<?php
  } // $action == ''
?>
<?php
// start of attributes display
if ($_GET['products_filter'] == '') {
?>
      <tr>
        <td colspan="2" class="pageHeading" align="center" valign="middle" height="200"><?php echo HEADING_TITLE_ATRIB_SELECT; ?></td>
      </tr>
<?php
} else {
////
// attribute listings and add

  if ($action == 'update_attribute') {
    $form_action = 'update_product_attribute';
  } else {
    $form_action = 'add_product_attributes';
  }

  if (!isset($_GET['attribute_page'])) {
    $_GET['attribute_page'] = 1;
  }
  $prev_attribute_page = $_GET['attribute_page'] - 1;
  $next_attribute_page = $_GET['attribute_page'] + 1;
?>

<?php
if ($action == '') {
?>
<tr>
  <td>
    <table border="2" align="left" cellpadding="2" cellspacing="2">
      <tr>
        <td class="smallText" align="right"><?php echo LEGEND_BOX; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTES_DISPLAY_ONLY; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTES_IS_FREE; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTES_DEFAULT; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTE_IS_DISCOUNTED; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTES_REQUIRED; ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTES_IMAGES ?></td>
        <td class="smallText" align="center"><?php echo LEGEND_ATTRIBUTES_DOWNLOAD ?></td>
      </tr>
      <tr>
        <td class="smallText" align="right"><?php echo LEGEND_KEYS; ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_yellow_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_pink_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_pink_on.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_purple_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_purple_on.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_red_off.gif') . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif'); ?></td>
        <td class="smallText" align="center"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif'); ?></td>
      </tr>
    </table>
  </td>
<?php } ?>
<?php
// fix here border width
?>
      <tr>
        <td><form name="attributes" action="<?php echo zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=' . $form_action . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter ); ?>" method="post", enctype="multipart/form-data"><table border="0" cellspacing="0" cellpadding="2">

          <tr>
            <td colspan="10" class="smallText">
<?php
  $per_page = MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER;
  $attributes = "select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pa.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.options_id = po.products_options_id and po.language_id = '" . (int)$_SESSION['languages_id'] . "'" . " where pa.products_id ='" . $products_filter . "' order by pd.products_name, LPAD(po.products_options_sort_order,11,'0'), LPAD(pa.options_id,11,'0'), LPAD(pa.products_options_sort_order,11,'0')";
  $attribute_query = $db->Execute($attributes);

  $attribute_page_start = ($per_page * $_GET['attribute_page']) - $per_page;
  $num_rows = $attribute_query->RecordCount();

  if ($num_rows <= $per_page) {
     $num_pages = 1;
  } else if (($num_rows % $per_page) == 0) {
     $num_pages = ($num_rows / $per_page);
  } else {
     $num_pages = ($num_rows / $per_page) + 1;
  }
  $num_pages = (int) $num_pages;

// fix limit error on some versions
    if ($attribute_page_start < 0) { $attribute_page_start = 0; }

  $attributes = $attributes . " LIMIT $attribute_page_start, $per_page";

  // Previous
  if ($prev_attribute_page) {
    echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $prev_attribute_page . '&products_filter=' . $products_filter) . '"> &lt;&lt; </a> | ';
  }

  for ($i = 1; $i <= $num_pages; $i++) {
    if ($i != $_GET['attribute_page']) {
      echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $i  . '&products_filter=' . $products_filter) . '">' . $i . '</a> | ';
    } else {
      echo '<b><font color="red">' . $i . '</font></b> | ';
    }
  }

  // Next
  if ($_GET['attribute_page'] != $num_pages) {
    echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'attribute_page=' . $next_attribute_page . '&products_filter=' . $products_filter) . '"> &gt;&gt; </a>';
  }
?>
            </td>
          </tr>
          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php // echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
            <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPT_PRICE_PREFIX; ?>&nbsp;<?php echo TABLE_HEADING_OPT_PRICE; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPT_WEIGHT_PREFIX; ?>&nbsp;<?php echo TABLE_HEADING_OPT_WEIGHT; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_OPT_SORT_ORDER; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="center"><?php echo LEGEND_BOX; ?></td>
            <td class="dataTableHeadingContent" align="right">&nbsp;<?php echo TABLE_HEADING_PRICE_TOTAL; ?>&nbsp;</td>
            <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>

<?php
  $next_id = 1;
  $attributes_values = $db->Execute($attributes);

  if ($attributes_values->RecordCount() == 0) {
?>
          <tr class="attributeBoxContent">
            <td colspan="10" class="dataTableHeadingContent">&nbsp;</td>
          </tr>
          <tr class="attributes-even">
            <td colspan="10" class="pageHeading" align="center">
              <?php echo ($products_filter == '' ? TEXT_NO_PRODUCTS_SELECTED : TEXT_NO_ATTRIBUTES_DEFINED . $products_filter . ' ' . zen_get_products_model($products_filter) . ' - ' . zen_get_products_name($products_filter)); ?>
            </td>
          </tr>
          <tr class="dataTableHeadingRow">
            <td colspan="10" class="dataTableHeadingContent">&nbsp;</td>
          </tr>

<?php
} else {
?>
          <tr class="attributeBoxContent">
            <td colspan="10" class="dataTableHeadingContent">&nbsp;</td>
          </tr>
          <tr class="attributes-even">
            <td colspan="10" class="pageHeading" align="center">
              <?php echo TEXT_INFO_ID . $products_filter . ' ' . zen_get_products_model($products_filter) . ' - ' . zen_get_products_name($products_filter); ?>
            </td>
          </tr>
          <tr class="attributeBoxContent">
            <td colspan="10" class="dataTableHeadingContent">&nbsp;</td>
          </tr>
<?php } ?>
<?php
  while (!$attributes_values->EOF) {
    $current_attributes_products_id = $attributes_values->fields['products_id'];
    $current_attributes_options_id = $attributes_values->fields['options_id'];

    $products_name_only = zen_get_products_name($attributes_values->fields['products_id']);
    $options_name = zen_options_name($attributes_values->fields['options_id']);
    $values_name = zen_values_name($attributes_values->fields['options_values_id']);
    $rows++;
?>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
<?php
    if (($action == 'update_attribute') && ($_GET['attribute_id'] == $attributes_values->fields['products_attributes_id'])) {
?>
          <tr>
            <td colspan="10"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>

<tr><td colspan="10" class="attributeBoxContent"><table border="0" width="100%">

<tr><td class="pageHeading"><?php echo PRODUCTS_ATTRIBUTES_EDITING; ?>

<?php // fix here edit ?>
<tr><td class="attributeBoxContent">
<table border="0" cellpadding="4" cellspacing="2">

<tr>
            <td class="smallText" valign="top" width="40">&nbsp;<?php echo $attributes_values->fields['products_attributes_id']; ?><input type="hidden" name="attribute_id" value="<?php echo $attributes_values->fields['products_attributes_id']; ?>">&nbsp;</td>
            <td class="smallText" valign="top">&nbsp;
      	    <td class="pageHeading" valign="top">&nbsp;
              <input type="hidden" name="products_id" value="<?php echo $products_filter; ?>">
              <input type="hidden" name="current_category_id" value="<?php echo $current_category_id; ?>">
      	      <?php
      	        $show_model = zen_get_products_model($products_filter);
      	        if(!empty($show_model)) {
      	          $show_model = " - (" . $show_model . ")";
      	        }
      	        echo zen_clean_html(zen_get_products_name($products_filter)) . $show_model;
      	      ?>
      	    </td>

          </tr></table></td></tr>
          <tr class="attributeBoxContent"><td><table><tr>
            <td class="smallText" valign="top" width="40">&nbsp;</td>
            <td class="pageHeading">&nbsp;
              <input type="hidden" name="options_id" value="<?php echo $attributes_values->fields['options_id']; ?>">
              <?php echo zen_get_option_name_language($attributes_values->fields['options_id'], $_SESSION['languages_id']); ?>:
            </td>
            <td class="smallText">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE . '<br />'; ?><select name="values_id" size="5">
<?php
// FIX HERE 2
      $values_values = $db->Execute("select pov.* from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov left join " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo on pov.products_options_values_id = povtpo.products_options_values_id
                                     where pov.language_id ='" . $_SESSION['languages_id'] . "' and povtpo.products_options_id='" . $attributes_values->fields['options_id'] . "'
                                     order by pov.products_options_values_name");

      while(!$values_values->EOF) {
        $show_option_name= '&nbsp;&nbsp;&nbsp;[' . strtoupper(zen_get_products_options_name_from_value($values_values->fields['products_options_values_id'])) . ']';
        if ($attributes_values->fields['options_values_id'] == $values_values->fields['products_options_values_id']) {
          echo "\n" . '<option name="' . $values_values->fields['products_options_values_name'] . '" value="' . $values_values->fields['products_options_values_id'] . '" SELECTED>' . $values_values->fields['products_options_values_name'] . $show_option_name . '</option>';
        } else {
          echo "\n" . '<option name="' . $values_values->fields['products_options_values_name'] . '" value="' . $values_values->fields['products_options_values_id'] . '">' . $values_values->fields['products_options_values_name'] . $show_option_name . '</option>';
        }
        $values_values->MoveNext();
      }
?>
<?php
// set radio values attributes_display_only
    switch ($attributes_values->fields['attributes_display_only']) {
      case '0': $on_attributes_display_only = false; $off_attributes_display_only = true; break;
      case '1': $on_attributes_display_only = true; $off_attributes_display_only = false; break;
      default: $on_attributes_display_only = false; $off_attributes_display_only = true;
    }
// set radio values attributes_default
    switch ($attributes_values->fields['product_attribute_is_free']) {
      case '0': $on_product_attribute_is_free = false; $off_product_attribute_is_free = true; break;
      case '1': $on_product_attribute_is_free = true; $off_product_attribute_is_free = false; break;
      default: $on_product_attribute_is_free = false; $off_product_attribute_is_free = true;
    }
// set radio values attributes_default
    switch ($attributes_values->fields['attributes_default']) {
      case '0': $on_attributes_default = false; $off_attributes_default = true; break;
      case '1': $on_attributes_default = true; $off_attributes_default = false; break;
      default: $on_attributes_default = false; $off_attributes_default = true;
    }
// set radio values attributes_discounted
    switch ($attributes_values->fields['attributes_discounted']) {
      case '0': $on_attributes_discounted = false; $off_attributes_discounted = true; break;
      case '1': $on_attributes_discounted = true; $off_attributes_discounted = false; break;
      default: $on_attributes_discounted = false; $off_attributes_discounted = true;
    }
// set radio values attributes_price_base_included
    switch ($attributes_values->fields['attributes_price_base_included']) {
      case '0': $on_attributes_price_base_included = false; $off_attributes_price_base_included = true; break;
      case '1': $on_attributes_price_base_included = true; $off_attributes_price_base_included = false; break;
      default: $on_attributes_price_base_included = false; $off_attributes_price_base_included = true;
    }
// set radio values attributes_required
    switch ($attributes_values->fields['attributes_required']) {
      case '0': $on_attributes_required = false; $off_attributes_required = true; break;
      case '1': $on_attributes_required = true; $off_attributes_required = false; break;
      default: $on_attributes_required = false; $off_attributes_required = true;
    }
// set image overwrite
  $on_overwrite = true;
  $off_overwrite = false;
?>
            </select>&nbsp;</td>

</table></td></tr>

<!-- bof: Edit Prices -->
<tr>
  <td class="attributeBoxContent">
    <table border="1" cellpadding="4" cellspacing="2" align="left" width="100%">
      <tr>
        <td align="right" class="pageHeading"><?php echo TEXT_SAVE_CHANGES . '&nbsp;&nbsp;' . zen_image_submit('button_update.gif', IMAGE_UPDATE); ?>&nbsp;<?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a>&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>

<tr><td>
  <table border="0">
    <tr>
      <td colspan="3" class="pageHeading"><?php echo TEXT_PRICES_AND_WEIGHTS; ?></td>
    </tr>
    <tr>
      <td class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2" align="left">
          <tr>
            <td align="center" class="smallText">&nbsp;<?php echo TABLE_HEADING_OPT_PRICE; ?><br /><input type="text" name="price_prefix" value="<?php echo $attributes_values->fields['price_prefix']; ?>" size="2">&nbsp;<input type="text" name="value_price" value="<?php echo $attributes_values->fields['options_values_price']; ?>" size="6">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo TABLE_HEADING_OPT_WEIGHT; ?><br /><input type="text" name="products_attributes_wt_pfix" value="<?php echo $attributes_values->fields['products_attributes_wt_pfix']; ?>" size="2">&nbsp;<input type="text" name="products_attributes_wt" value="<?php echo $attributes_values->fields['products_attributes_wt']; ?>" size="6">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo TABLE_HEADING_OPT_SORT_ORDER; ?><br /><input type="text" name="products_options_sort_order" value="<?php echo $attributes_values->fields['products_options_sort_order']; ?>" size="4">&nbsp;</td>
            <td align="center" class="smallText">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_ONETIME; ?><br />&nbsp;<input type="text" name="attributes_price_onetime" value="<?php echo $attributes_values->fields['attributes_price_onetime']; ?>" size="6">&nbsp;</td>
          </tr>
        </table>
      </td>

<?php if (ATTRIBUTES_ENABLED_PRICE_FACTOR == 'true') { ?>
      <td class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2" align="left">
          <tr>
            <td align="center" class="smallText" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR . ' ' . TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET; ?><br />&nbsp;<input type="text" name="attributes_price_factor" value="<?php echo $attributes_values->fields['attributes_price_factor']; ?>" size="6">&nbsp;&nbsp;<input type="text" name="attributes_pf_offset" value="<?php echo $attributes_values->fields['attributes_pf_offset']; ?>" size="6">&nbsp;</td>
            <td align="center" class="smallText" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_ONETIME . ' ' . TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET_ONETIME; ?><br />&nbsp;<input type="text" name="attributes_pf_onetime" value="<?php echo $attributes_values->fields['attributes_pf_onetime']; ?>" size="6">&nbsp;&nbsp;<input type="text" name="attributes_pf_onetime_offset" value="<?php echo $attributes_values->fields['attributes_pf_onetime_offset']; ?>" size="6">&nbsp;</td>
          </tr>
        </table>
      </td>
<?php
    } else {
      echo zen_draw_hidden_field('attributes_price_factor', $attributes_values->fields['attributes_price_factor']);
      echo zen_draw_hidden_field('attributes_pf_offset', $attributes_values->fields['attributes_pf_offset']);
      echo zen_draw_hidden_field('attributes_pf_onetime', $attributes_values->fields['attributes_pf_onetime']);
      echo zen_draw_hidden_field('attributes_pf_onetime_offset', $attributes_values->fields['attributes_pf_onetime_offset']);
    } // ATTRIBUTES_ENABLED_PRICE_FACTOR
?>

    </tr>
  </table>
</td></tr>

<?php if (ATTRIBUTES_ENABLED_QTY_PRICES == 'true') { ?>
    <tr>
      <td class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2" align="left">
          <tr>
            <td align="center" class="smallText" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_QTY_PRICES; ?><br />&nbsp;<input type="text" name="attributes_qty_prices" value="<?php echo $attributes_values->fields['attributes_qty_prices']; ?>" size="60">&nbsp;</td>
            <td align="center" class="smallText" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_QTY_PRICES_ONETIME; ?><br />&nbsp;<input type="text" name="attributes_qty_prices_onetime" value="<?php echo $attributes_values->fields['attributes_qty_prices_onetime']; ?>" size="60">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
<?php
    } else {
      echo zen_draw_hidden_field('attributes_qty_prices', $attributes_values->fields['attributes_qty_prices']);
      echo zen_draw_hidden_field('attributes_qty_prices_onetime', $attributes_values->fields['attributes_qty_prices_onetime']);
    } // ATTRIBUTES_ENABLED_QTY_PRICES
?>

<?php if (ATTRIBUTES_ENABLED_TEXT_PRICES == 'true') { ?>
    <tr>
      <td class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2" align="left">
          <tr>
            <td align="center" class="smallText" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_WORDS . ' ' . TABLE_HEADING_ATTRIBUTES_PRICE_WORDS_FREE; ?><br />&nbsp;<input type="text" name="attributes_price_words" value="<?php echo $attributes_values->fields['attributes_price_words']; ?>" size="6">&nbsp;&nbsp;<input type="text" name="attributes_price_words_free" value="<?php echo $attributes_values->fields['attributes_price_words_free']; ?>" size="6">&nbsp;</td>
            <td align="center" class="smallText" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS . ' ' . TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS_FREE; ?><br />&nbsp;<input type="text" name="attributes_price_letters" value="<?php echo $attributes_values->fields['attributes_price_letters']; ?>" size="6">&nbsp;&nbsp;<input type="text" name="attributes_price_letters_free" value="<?php echo $attributes_values->fields['attributes_price_letters_free']; ?>" size="6">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
<?php
    } else {
      echo zen_draw_hidden_field('attributes_price_words', $attributes_values->fields['attributes_price_words']);
      echo zen_draw_hidden_field('attributes_price_words_free', $attributes_values->fields['attributes_price_words_free']);
      echo zen_draw_hidden_field('attributes_price_letters', $attributes_values->fields['attributes_price_letters']);
      echo zen_draw_hidden_field('attributes_price_letters_free', $attributes_values->fields['attributes_price_letters_free']);
    } // ATTRIBUTES_ENABLED_TEXT_PRICES
?>

<!-- eof: Edit Prices -->

<tr><td class="attributeBoxContent">
<table border="1" cellpadding="4" cellspacing="2">

              <tr >
                <td class="attributeBoxContent" align="center" width="50"><?php echo TEXT_ATTRIBUTES_FLAGS; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#ffff00"><?php echo TEXT_ATTRIBUTES_DISPLAY_ONLY . '<br>' . zen_draw_radio_field('attributes_display_only', '0', $off_attributes_display_only) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_display_only', '1', $on_attributes_display_only) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#0000ff"><?php echo TEXT_ATTRIBUTES_IS_FREE . '<br>' . zen_draw_radio_field('product_attribute_is_free', '0', $off_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('product_attribute_is_free', '1', $on_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#ffa346"><?php echo TEXT_ATTRIBUTES_DEFAULT . '<br>' . zen_draw_radio_field('attributes_default', '0', $off_attributes_default) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_default', '1', $on_attributes_default) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#ff00ff"><?php echo TEXT_ATTRIBUTE_IS_DISCOUNTED . '<br>' . zen_draw_radio_field('attributes_discounted', '0', $off_attributes_discounted) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_discounted', '1', $on_attributes_discounted) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#d200f0"><?php echo TEXT_ATTRIBUTE_PRICE_BASE_INCLUDED . '<br>' . zen_draw_radio_field('attributes_price_base_included', '0', $off_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_price_base_included', '1', $on_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td align="center" class="smallText" width="150" bgcolor="#fd0000"><?php echo TEXT_ATTRIBUTES_REQUIRED . '<br>' . zen_draw_radio_field('attributes_required', '0', $off_attributes_required) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_required', '1', $on_attributes_required) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
              </tr>

</table></td></tr>

<?php if (ATTRIBUTES_ENABLED_IMAGES == 'true') { ?>

<?php
// edit
// attributes images
  $dir = @dir(DIR_FS_CATALOG_IMAGES);
  $dir_info[] = array('id' => '', 'text' => "Main Directory");
  while ($file = $dir->read()) {
    if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
      $dir_info[] = array('id' => $file . '/', 'text' => $file);
    }
  }

  sort($dir_info);

  if ($attributes_values->fields['attributes_image'] != '') {
    $default_directory = substr( $attributes_values->fields['attributes_image'], 0,strpos( $attributes_values->fields['attributes_image'], '/')+1);
  } else {
    $default_directory = 'attributes/';
  }
?>
<tr><td class="attributeBoxContent">
<table border="0" cellpadding="4" cellspacing="2">

          <tr class="attributeBoxContent">
            <td>&nbsp;</td>
            <td colspan="5">
              <table>
                <tr class="attributeBoxContent">
                  <td class="main" valign="top">&nbsp;</td>
                  <td class="main" valign="top"><?php echo TEXT_ATTRIBUTES_IMAGE . '<br />' . zen_draw_file_field('attributes_image') . '<br />' . zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . $attributes_values->fields['attributes_image']  . zen_draw_hidden_field('attributes_previous_image', $attributes_values->fields['attributes_image']); ?></td>
                  <td class="main" valign="top"><?php echo TEXT_ATTRIBUTES_IMAGE_DIR . '<br />' . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory); ?></td>
                  <td class="main" valign="middle"><?php echo ($attributes_values->fields['attributes_image'] != '' ? zen_image(DIR_WS_CATALOG_IMAGES . $attributes_values->fields['attributes_image']) : ''); ?></td>
                  <td class="main" valign="top"><?php echo TEXT_IMAGES_OVERWRITE . '<br />' . zen_draw_radio_field('overwrite', '0', $off_overwrite) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('overwrite', '1', $on_overwrite) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                </tr>
              </table>
            </td>
            <td>&nbsp;</td>
          </tr>

</table></td></tr>
<?php
    } else {
      echo zen_draw_hidden_field('attributes_previous_image', $attributes_values->fields['attributes_image']);
      echo zen_draw_hidden_field('attributes_image', $attributes_values->fields['attributes_image']);
    } // ATTRIBUTES_ENABLED_IMAGES
?>

<?php
      if (DOWNLOAD_ENABLED == 'true') {
        $download_query_raw ="select products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount
                              from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                              where products_attributes_id='" . $attributes_values->fields['products_attributes_id'] . "'";
        $download = $db->Execute($download_query_raw);
        if ($download->RecordCount() > 0) {
          $products_attributes_filename = $download->fields['products_attributes_filename'];
          $products_attributes_maxdays  = $download->fields['products_attributes_maxdays'];
          $products_attributes_maxcount = $download->fields['products_attributes_maxcount'];
        }
?>
<tr><td class="attributeBoxContent">
<table border="0" cellpadding="4" cellspacing="2">
          <tr class="attributeBoxContent">
            <td colspan="5">
                <tr class="attributeBoxContent">
                  <td class="attributeBoxContent" valign="top"><?php echo TABLE_HEADING_DOWNLOAD; ?>&nbsp;</td>
                  <td class="attributeBoxContent"><?php echo TABLE_TEXT_FILENAME . '<br />' . zen_draw_input_field('products_attributes_filename', $products_attributes_filename, zen_set_field_length(TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename', 35)); ?>&nbsp;</td>
                  <td class="attributeBoxContent"><?php echo TABLE_TEXT_MAX_DAYS . '<br />' . zen_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5"'); ?>&nbsp;</td>
                  <td class="attributeBoxContent"><?php echo TABLE_TEXT_MAX_COUNT . '<br />' . zen_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5"'); ?>&nbsp;</td>
                </tr>

</table></td></tr>
</td></tr>

<?php
      }
?>

</td></tr>
</table></td></tr>

          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>
          <tr>
            <td colspan="10"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>

<?php
    } elseif (($action == 'delete_product_attribute') && ($_GET['attribute_id'] == $attributes_values->fields['products_attributes_id'])) {
?>
          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>
          <tr class="attributeBoxContent">
            <td align="left" colspan="6" class="pageHeading"><?php echo PRODUCTS_ATTRIBUTES_DELETE; ?></td><td colspan="3" align="center" class="pageHeading"><?php echo PRODUCTS_ATTRIBUTES_DELETE; ?></td>
            <td colspan="3" align="center" class="attributeBoxContent">&nbsp;</td>
          </tr>
          <tr>
            <td class="attributeBoxContent">&nbsp;<b><?php echo $attributes_values->fields["products_attributes_id"]; ?></b>&nbsp;</td>
            <td class="attributeBoxContent">&nbsp;<b><?php echo $products_name_only; ?></b>&nbsp;</td>
            <td class="attributeBoxContent">&nbsp;<b><?php echo $options_name; ?></b>&nbsp;</td>
            <td class="attributeBoxContent">&nbsp;<b><?php echo $values_name; ?></b>&nbsp;</td>
            <td align="right" class="attributeBoxContent">&nbsp;<b><?php echo $attributes_values->fields["options_values_price"]; ?></b>&nbsp;</td>
            <td align="center" class="attributeBoxContent">&nbsp;<b><?php echo $attributes_values->fields["price_prefix"]; ?></b>&nbsp;</td>
            <td colspan="3" align="center" class="attributeBoxContent">&nbsp;<b><?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_attribute&attribute_id=' . $_GET['attribute_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '">'; ?><?php echo zen_image_button('button_confirm.gif', IMAGE_CONFIRM); ?></a>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id ) . '">'; ?><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></a>&nbsp;</b></td>
            <td colspan="3" align="center" class="attributeBoxContent">&nbsp;</td>
          </tr>
          <tr class="attributeBoxContent">
            <td colspan="10" class="attributeBoxContent">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>
          <tr>
<?php
    } else {
// attributes display listing

// calculate current total attribute price
// $attributes_values
$attributes_price_final = zen_get_attributes_price_final($attributes_values->fields["products_attributes_id"], 1, $attributes_values, 'false');
$attributes_price_final_value = $attributes_price_final;
$attributes_price_final = $currencies->display_price($attributes_price_final, zen_get_tax_rate(1), 1);
$attributes_price_final_onetime = zen_get_attributes_price_final_onetime($attributes_values->fields["products_attributes_id"], 1, $attributes_values);
$attributes_price_final_onetime = $currencies->display_price($attributes_price_final_onetime, zen_get_tax_rate(1), 1);
?>
            <td class="smallText">&nbsp;<?php echo $attributes_values->fields["products_attributes_id"]; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php // echo $products_name_only; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo $options_name; ?>&nbsp;</td>
            <td class="smallText">&nbsp;<?php echo ($attributes_values->fields['attributes_image'] != '' ? zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . '&nbsp;' : '&nbsp;&nbsp;') . $values_name; ?>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<?php echo $attributes_values->fields["price_prefix"]; ?>&nbsp;<?php echo $attributes_values->fields["options_values_price"]; ?>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<?php echo $attributes_values->fields["products_attributes_wt_pfix"]; ?>&nbsp;<?php echo $attributes_values->fields["products_attributes_wt"]; ?>&nbsp;</td>
            <td align="right" class="smallText">&nbsp;<?php echo $attributes_values->fields["products_options_sort_order"]; ?>&nbsp;</td>
<?php
// '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_display_only' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' .
// $marker = '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">';
if ($action == '') {
?>
<td>
  <table border="0" align="center" cellpadding="2" cellspacing="2">
      <tr>
        <td class="smallText" align="center"><?php echo ($attributes_values->fields["attributes_display_only"] == '0' ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_display_only' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_off.gif', LEGEND_ATTRIBUTES_DISPLAY_ONLY) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_display_only' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', LEGEND_ATTRIBUTES_DISPLAY_ONLY)) . '</a>'; ?></td>
        <td class="smallText" align="center"><?php echo ($attributes_values->fields["product_attribute_is_free"] == '0' ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_product_attribute_is_free' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif', LEGEND_ATTRIBUTES_IS_FREE) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_product_attribute_is_free' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif', LEGEND_ATTRIBUTES_IS_FREE)) . '</a>'; ?></td>
        <td class="smallText" align="center"><?php echo ($attributes_values->fields["attributes_default"] == '0' ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_default' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif', LEGEND_ATTRIBUTES_DEFAULT) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_default' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif', LEGEND_ATTRIBUTES_DEFAULT)) . '</a>' ?></td>
        <td class="smallText" align="center"><?php echo ($attributes_values->fields["attributes_discounted"] == '0' ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_discounted' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_pink_off.gif', LEGEND_ATTRIBUTE_IS_DISCOUNTED) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_discounted' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_pink_on.gif', LEGEND_ATTRIBUTE_IS_DISCOUNTED)) . '</a>'; ?></td>
        <td class="smallText" align="center"><?php echo ($attributes_values->fields["attributes_price_base_included"] == '0' ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_price_base_included' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_purple_off.gif', LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_price_base_included' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_purple_on.gif', LEGEND_ATTRIBUTE_PRICE_BASE_INCLUDED)) . '</a>'; ?></td>
        <td class="smallText" align="center"><?php echo ($attributes_values->fields["attributes_required"] == '0' ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_required' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_off.gif', LEGEND_ATTRIBUTES_REQUIRED) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=set_flag_attributes_required' . '&attributes_id=' . $attributes_values->fields["products_attributes_id"] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', LEGEND_ATTRIBUTES_REQUIRED)) . '</a>'; ?></td>
      </tr>
    </table>
</td>
<?php } ?>
<?php
  $new_attributes_price= '';
  if ($attributes_values->fields["attributes_discounted"]) {
    $new_attributes_price = zen_get_attributes_price_final($attributes_values->fields["products_attributes_id"], 1, '', 'false');
    $new_attributes_price = zen_get_discount_calc($products_filter, true, $new_attributes_price);
    if ($new_attributes_price != $attributes_price_final_value) {
      $new_attributes_price = '|' . $currencies->display_price($new_attributes_price, zen_get_tax_rate(1), 1);
    } else {
      $new_attributes_price = '';
    }
  }
?>
            <td align="right" class="smallText"><?php echo $attributes_price_final . $new_attributes_price . ' ' . $attributes_price_final_onetime; ?></td>
<?php
  if ($action== 'update_attribute' or $action== 'delete_product_attribute') {
?>
            <td width='120' align="center" class="smallText">&nbsp;</td>
<?php
  } else {
?>
            <td align="center" class="smallText">&nbsp;<?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=update_attribute&attribute_id=' . $attributes_values->fields['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id ) . '">'; ?><?php echo zen_image_button('button_edit.gif', IMAGE_UPDATE); ?></a>&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'action=delete_product_attribute&attribute_id=' . $attributes_values->fields['products_attributes_id'] . (isset($_GET['option_page']) ? '&option_page=' . $_GET['option_page'] . '&' : '') . (isset($_GET['value_page']) ? '&value_page=' . $_GET['value_page'] . '&' : '') . (isset($_GET['attribute_page']) ? '&attribute_page=' . $_GET['attribute_page'] : '') . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id ) , '">'; ?><?php echo zen_image_button('button_delete.gif', IMAGE_DELETE); ?></a>&nbsp;</td>
<?php
  }
?>
<?php
// bof: show filename if it exists
      if (DOWNLOAD_ENABLED == 'true') {
        $download_display_query_raw ="select products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount
                              from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                              where products_attributes_id='" . $attributes_values->fields['products_attributes_id'] . "'";
        $download_display = $db->Execute($download_display_query_raw);
        if ($download_display->RecordCount() > 0) {

// Could go into /admin/includes/configure.php
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  $filename_is_missing='';
  if ( !file_exists(DIR_FS_DOWNLOAD . $download_display->fields['products_attributes_filename']) ) {
    $filename_is_missing = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
  } else {
    $filename_is_missing = zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
  }
?>
          </tr>

          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
            <td colspan="3" class="smallText">&nbsp;</td>
            <td colspan="4"><table>
              <tr>
                <td class="smallText"><?php echo $filename_is_missing . '&nbsp;' . TABLE_TEXT_FILENAME; ?></td>
                <td class="smallText">&nbsp;&nbsp;<?php echo $download_display->fields['products_attributes_filename']; ?>&nbsp;</td>
                <td class="smallText">&nbsp;&nbsp;<?php echo TABLE_TEXT_MAX_DAYS_SHORT; ?></td>
                <td class="smallText">&nbsp;&nbsp;<?php echo $download_display->fields['products_attributes_maxdays']; ?>&nbsp;</td>
                <td class="smallText">&nbsp;&nbsp;<?php echo TABLE_TEXT_MAX_COUNT_SHORT; ?></td>
                <td class="smallText">&nbsp;&nbsp;<?php echo $download_display->fields['products_attributes_maxcount']; ?>&nbsp;</td>
              </tr>
            </table></td>


<?php
        } // show downloads
      }
// eof: show filename if it exists
?>
<?php
    }
    $max_attributes_id_values = $db->Execute("select max(products_attributes_id) + 1
                                              as next_id from " . TABLE_PRODUCTS_ATTRIBUTES);

    $next_id = $max_attributes_id_values->fields['next_id'];

//////////////////////////////////////////////////////////////
// BOF: Add dividers between Product Names and between Option Names
    $attributes_values->MoveNext();
    if (!$attributes_values->EOF) {
      if ($current_attributes_products_id != $attributes_values->fields['products_id']) {
?>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
            <td colspan="10"><table width="100%"><td>
              <tr>
                <td><?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?></td>
              </tr>
            </table></td></tr>
<?php
  } else {
    if ($current_attributes_options_id != $attributes_values->fields['options_id']) {
?>
          <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
            <td colspan="10"><table width="100%"><td>
              <tr>
                <td><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
              </tr>
            </table></td></tr>
<?php
    }
  }
}
// EOF: Add dividers between Product Names and between Option Names
//////////////////////////////////////////////////////////////
?>
          </tr>

<?php
  }
  if (($action != 'update_attribute' and $action != 'delete_product_attribute')) {
?>
          <tr>
            <td colspan="10"><?php echo zen_black_line(); ?></td>
          </tr>

<!-- bof_adding -->
<tr class="attributeBoxContent"><td colspan="10"><table border="0" width="100%">

  <tr class="attributeBoxContent">
    <td class="pageHeading">
      <table border="0" width="100%">
        <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2" width="100%">

          <tr class="attributeBoxContent">
            <td class="pageHeading">&nbsp;<?php echo PRODUCTS_ATTRIBUTES_ADDING; ?></td>
            <td class="main"><?php echo TEXT_ATTRIBUTES_INSERT_INFO; ?></td>
              <td align="center" class="attributeBoxContent" height="30" valign="bottom">&nbsp;
              <?php
                if ($action == '') {
                  echo zen_image_submit('button_insert.gif', IMAGE_INSERT);
                } else {
                  // hide button
                }
              ?>
            &nbsp;
              </td>

          </tr>

        </table></td></tr>
      </table>
    </td>
  </tr>

<!-- bof Option Names and Values -->
  <tr class="attributeBoxContent">
    <td class="pageHeading">
      <table border='0' width="100%">
        <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">
          <tr class="attributeBoxContent"><td><table><tr>
            <td class="attributeBoxContent" width="40">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
      	    <td class="pageHeading" valign="top">&nbsp;
              <input type="hidden" name="products_id" value="<?php echo $products_filter; ?>">
              <input type="hidden" name="current_category_id" value="<?php echo $current_category_id; ?>">
      	      <?php
        	      $show_model = zen_get_products_model($products_filter);
        	      if(!empty($show_model)) {
        	        $show_model = " - (" . $show_model . ")";
        	      }
      	        echo zen_clean_html(zen_get_products_name($products_filter)) . $show_model;
      	      ?>
      	    </td>
          </tr></table></td></tr>
          <tr class="attributeBoxContent"><td><table><tr>
            <td class="attributeBoxContent" width="40">&nbsp;</td>
            <td class="attributeBoxContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME . '<br />'; ?><select name="options_id" size="<?php echo ($action != 'delete_attribute' ? "5" : "1"); ?>">
<?php
    $options_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS . "
                                    where language_id = '" . $_SESSION['languages_id'] . "'
                                    order by products_options_name");

    while (!$options_values->EOF) {
      echo '<option name="' . $options_values->fields['products_options_name'] . '" value="' . $options_values->fields['products_options_id'] . '">' . $options_values->fields['products_options_name'] . '&nbsp;&nbsp;&nbsp;[' . translate_type_to_name($options_values->fields['products_options_type']) . ']' . '</option>';
      $options_values->MoveNext();
    }
?>
            </select>&nbsp;</td>
            <td class="attributeBoxContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE . '<br />'; ?><select name="values_id" size="<?php echo ($action != 'delete_attribute' ? "5" : "1"); ?>">
<?php
    $values_values = $db->Execute("select * from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   where language_id = '" . $_SESSION['languages_id'] . "'
                                   order by products_options_values_name");
    while (!$values_values->EOF) {
      $show_option_name= '&nbsp;&nbsp;&nbsp;[' . strtoupper(zen_get_products_options_name_from_value($values_values->fields['products_options_values_id'])) . ']';
      echo '<option name="' . $values_values->fields['products_options_values_name'] . '" value="' . $values_values->fields['products_options_values_id'] . '">' . $values_values->fields['products_options_values_name'] . $show_option_name . '</option>';
      $values_values->MoveNext();
    }
?>
<?php


// set defaults for adding attributes
$on_product_attribute_is_free = false;
$off_product_attribute_is_free = true;
$on_attributes_display_only = false;
$off_attributes_display_only = true;
$on_attributes_default = false;
$off_attributes_default = true;
$on_attributes_discounted = true;
$off_attributes_discounted = false;
$on_attributes_price_base_included = true;
$off_attributes_price_base_included = false;
$on_attributes_required = false;
$off_attributes_required = true;
$on_overwrite = true;
$off_overwrite = false;
?>
            </select>&nbsp;</td>
          </tr></table></td></tr>

<?php // split here ?>
        </table></td></tr>
      </table>
    </td>
  </tr>
<!-- eof Option Name and Value -->

<!-- bof Prices and Weight -->
  <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">
    <tr>
      <td colspan="2" class="pageHeading"><?php echo TEXT_PRICES_AND_WEIGHTS; ?></td>
    </tr>
    <tr>
      <td class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2">
          <tr>
            <td align="center" class="attributeBoxContent">&nbsp;<?php echo TABLE_HEADING_OPT_PRICE . '<br />'; ?><input type="text" name="price_prefix" size="2" value="+">&nbsp;<input type="text" name="value_price" size="6">&nbsp;</td>
            <td align="center" class="attributeBoxContent">&nbsp;<?php echo TABLE_HEADING_OPT_WEIGHT . '<br />'; ?><input type="text" name="products_attributes_wt_pfix" size="2" value="+">&nbsp;<input type="text" name="products_attributes_wt" size="6">&nbsp;</td>
            <td align="center" class="attributeBoxContent">&nbsp;<?php echo TABLE_HEADING_OPT_SORT_ORDER; ?><br /><input type="text" name="products_options_sort_order" value="" size="4">&nbsp;</td>
            <td align="center" class="attributeBoxContent">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_ONETIME . '<br />'; ?><input type="text" name="attributes_price_onetime" size="6">&nbsp;</td>
          </tr>
        </table>
      </td>

<?php if (ATTRIBUTES_ENABLED_PRICE_FACTOR == 'true') { ?>
      <td class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2">
          <tr>
            <td align="center" class="attributeBoxContent" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR . '&nbsp;&nbsp;' . TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET . '<br />'; ?><input type="text" name="attributes_price_factor" size="6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="attributes_pf_offset" size="6">&nbsp;</td>
            <td align="center" class="attributeBoxContent" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_ONETIME . '&nbsp;&nbsp;' . TABLE_HEADING_ATTRIBUTES_PRICE_FACTOR_OFFSET_ONETIME . '<br />'; ?><input type="text" name="attributes_pf_onetime" size="6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="attributes_pf_onetime_offset" size="6">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
<?php
    } // ATTRIBUTES_ENABLED_PRICE_FACTOR
?>

<?php if (ATTRIBUTES_ENABLED_QTY_PRICES == 'true') { ?>
    <tr>
      <td colspan="2" class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2">
          <tr>
            <td align="center" class="attributeBoxContent" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_QTY_PRICES . '<br />'; ?><input type="text" name="attributes_qty_prices" size="60">&nbsp;</td>
            <td align="center" class="attributeBoxContent" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_QTY_PRICES_ONETIME . '<br />'; ?><input type="text" name="attributes_qty_prices_onetime" size="60">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
<?php } // ATTRIBUTES_ENABLED_QTY_PRICES ?>

<?php if (ATTRIBUTES_ENABLED_TEXT_PRICES == 'true') { ?>
    <tr>
      <td colspan="2" class="attributeBoxContent">
        <table border="1" cellpadding="4" cellspacing="2">
          <tr>
            <td align="center" class="attributeBoxContent" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_WORDS . '&nbsp;&nbsp;' . TABLE_HEADING_ATTRIBUTES_PRICE_WORDS_FREE . '<br />'; ?><input type="text" name="attributes_price_words" size="6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="attributes_price_words_free" size="6">&nbsp;</td>
            <td align="center" class="attributeBoxContent" nowrap="nowrap">&nbsp;<?php echo TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS . '&nbsp;&nbsp;' . TABLE_HEADING_ATTRIBUTES_PRICE_LETTERS_FREE . '<br />'; ?><input type="text" name="attributes_price_letters" size="6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="attributes_price_letters_free" size="6">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
<?php } // ATTRIBUTES_ENABLED_TEXT_PRICES ?>

  </table></td></tr>

<!-- eof Option Name and Value -->

<!-- bof Attribute Flags -->
<tr class="attributeBoxContent">
  <td class="pageHeading">
    <table border='0' width="100%">
      <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">

            <tr><td class="attributeBoxContent"><table border="1" cellpadding="4" cellspacing="2">

              <tr >
                <td class="smallText" align="center" width="50"><?php echo TEXT_ATTRIBUTES_FLAGS; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#ffff00"><?php echo TEXT_ATTRIBUTES_DISPLAY_ONLY . '<br>' . zen_draw_radio_field('attributes_display_only', '0', $off_attributes_display_only) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_display_only', '1', $on_attributes_display_only) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#0000ff"><?php echo TEXT_ATTRIBUTES_IS_FREE . '<br>' . zen_draw_radio_field('product_attribute_is_free', '0', $off_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('product_attribute_is_free', '1', $on_product_attribute_is_free) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#ffa346"><?php echo TEXT_ATTRIBUTES_DEFAULT . '<br>' . zen_draw_radio_field('attributes_default', '0', $off_attributes_default) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_default', '1', $on_attributes_default) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#ff00ff"><?php echo TEXT_ATTRIBUTE_IS_DISCOUNTED . '<br>' . zen_draw_radio_field('attributes_discounted', '0', $off_attributes_discounted) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_discounted', '1', $on_attributes_discounted) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td class="smallText" align="center" width="150" bgcolor="#d200f0"><?php echo TEXT_ATTRIBUTE_PRICE_BASE_INCLUDED . '<br>' . zen_draw_radio_field('attributes_price_base_included', '0', $off_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_price_base_included', '1', $on_attributes_price_base_included) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
                <td align="center" class="smallText" width="150" bgcolor="#fd0000"><?php echo TEXT_ATTRIBUTES_REQUIRED . '<br>' . zen_draw_radio_field('attributes_required', '0', $off_attributes_required) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('attributes_required', '1', $on_attributes_required) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
              </tr>

</table></td></tr>

      </table></td></tr>
    </table>
  </td>
</tr>
<!-- eof Attribute Flags -->

<?php if (ATTRIBUTES_ENABLED_IMAGES == 'true') { ?>
<?php
// add
// attributes images
  $dir = @dir(DIR_FS_CATALOG_IMAGES);
  $dir_info[] = array('id' => '', 'text' => "Main Directory");
  while ($file = $dir->read()) {
    if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
      $dir_info[] = array('id' => $file . '/', 'text' => $file);
    }
  }
  sort($dir_info);

  $default_directory = 'attributes/';
?>

<!-- bof Attribute Images -->
<tr class="attributeBoxContent">
  <td class="pageHeading">
    <table border='0' width="100%">
      <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2" width="100%">

        <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">
          <tr class="attributeBoxContent">
            <td>&nbsp;</td>
            <td class="main" valign="top">&nbsp;</td>

<!--
            <td class="main" valign="top">xxx<?php echo TEXT_ATTRIBUTES_IMAGE . '<br />' . zen_draw_file_field('attributes_image') . '<br />' . zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . $attributes_values->fields['attributes_image'] . zen_draw_hidden_field('attributes_image', $attributes_values->fields['attributes_image']); ?></td>
-->

            <td class="main" valign="top"><?php echo TEXT_ATTRIBUTES_IMAGE . '<br />' . zen_draw_file_field('attributes_image'); ?></td>

            <td class="main" valign="top"><?php echo TEXT_ATTRIBUTES_IMAGE_DIR . '<br />' . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory); ?></td>
            <td class="main" valign="top"><?php echo TEXT_IMAGES_OVERWRITE . '<br />' . zen_draw_radio_field('overwrite', '0', $off_overwrite) . '&nbsp;' . TABLE_HEADING_NO . ' ' . zen_draw_radio_field('overwrite', '1', $on_overwrite) . '&nbsp;' . TABLE_HEADING_YES; ?></td>
          </tr>
        </table></td>

        <td width="200">
          <table align="right">
            <tr>
              <td align="center" class="attributeBoxContent" height="30" valign="bottom">&nbsp;
              <?php
                if ($action == '') {
                  echo zen_image_submit('button_insert.gif', IMAGE_INSERT);
                } else {
                  // hide button
                }
              ?>
            &nbsp;
              </td>
            </tr>
          </table>

        </td></tr>

      </table></td></tr>
    </table>
  </td>
</tr>
<!-- eof Attribute Images -->
<?php } // ATTRIBUTES_ENABLED_IMAGES ?>

<?php
      if (DOWNLOAD_ENABLED == 'true') {
        $products_attributes_maxdays  = DOWNLOAD_MAX_DAYS;
        $products_attributes_maxcount = DOWNLOAD_MAX_COUNT;
?>
<!-- bof Down loads ON -->
<tr class="attributeBoxContent">
  <td class="pageHeading">
    <table border='0' width="100%">
      <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">

        <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">
          <tr class="attributeBoxContent">
            <td class="attributeBoxContent" valign="top"><?php echo TABLE_HEADING_DOWNLOAD; ?>&nbsp;</td>
            <td class="attributeBoxContent"><?php echo TABLE_TEXT_FILENAME . '<br />' . zen_draw_input_field('products_attributes_filename', $products_attributes_filename, zen_set_field_length(TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD, 'products_attributes_filename', 35)); ?>&nbsp;</td>
            <td class="attributeBoxContent"><?php echo TABLE_TEXT_MAX_DAYS . '<br />' . zen_draw_input_field('products_attributes_maxdays', $products_attributes_maxdays, 'size="5"'); ?>&nbsp;</td>
            <td class="attributeBoxContent"><?php echo TABLE_TEXT_MAX_COUNT . '<br />' . zen_draw_input_field('products_attributes_maxcount', $products_attributes_maxcount, 'size="5"'); ?>&nbsp;</td>
          </tr>
        </table></td></tr>

      </table></td></tr>
    </table>
  </td>
</tr>
<!-- eof Downloads ON -->

<?php
      } else {
?>
<!-- bof Down loads OFF -->
<tr class="attributeBoxContent">
  <td class="pageHeading">
    <table border='0' width="100%">
      <tr><td class="attributeBoxContent"><table border="1" cellpadding="4" cellspacing="2">

        <tr><td class="attributeBoxContent"><table border="0" cellpadding="4" cellspacing="2">
          <tr class="attributeBoxContent">
            <td class="attributeBoxContent"><?php echo TEXT_DOWNLOADS_DISABLED; ?>&nbsp;</td>
          </tr>
        </table></td></tr>

      </table></td></tr>
    </table>
  </td>
</tr>
<!-- eof Downloads OFF -->
<?php
      } // end of DOWNLOAD_ENABLED section
?>
<?php
  }
?>
        </table></form></td>
<?php
} // EOF: attributes preview
?>
      </tr>
<?php
} // end of attributes
?>
    </table></td>

</table></td></tr>
<!-- eof_adding -->

<!-- products_attributes_eof //-->
  </tr>
</table>
<!-- body_text_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>

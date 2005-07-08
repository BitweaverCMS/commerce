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
// $Id: functions_lookups.php,v 1.3 2005/07/08 06:12:28 spiderr Exp $
//
//
/**
 * Returns an array with countries
 *
 * @param int If set limits to a single country
 * @param boolean If true adds the iso codes to the array
*/
  function zen_get_countries($countries_id = '', $with_iso_codes = false) {
    global $db;
    $countries_array = array();
    if (zen_not_null($countries_id)) {
      if ($with_iso_codes == true) {
        $countries = "select countries_name, countries_iso_code_2, countries_iso_code_3
                      from " . TABLE_COUNTRIES . "
                      where countries_id = '" . (int)$countries_id . "'
                      order by countries_name";

        $countries_values = $db->Execute($countries);

        $countries_array = array('countries_name' => $countries_values->fields['countries_name'],
                                 'countries_iso_code_2' => $countries_values->fields['countries_iso_code_2'],
                                 'countries_iso_code_3' => $countries_values->fields['countries_iso_code_3']);
      } else {
        $countries = "select countries_name
                      from " . TABLE_COUNTRIES . "
                      where countries_id = '" . (int)$countries_id . "'";

        $countries_values = $db->Execute($countries);

        $countries_array = array('countries_name' => $countries_values->fields['countries_name']);
      }
    } else {
      $countries = "select countries_id, countries_name
                    from " . TABLE_COUNTRIES . "
                    order by countries_name";

      $countries_values = $db->Execute($countries);

      while (!$countries_values->EOF) {
        $countries_array[] = array('countries_id' => $countries_values->fields['countries_id'],
                                   'countries_name' => $countries_values->fields['countries_name']);

        $countries_values->MoveNext();
      }
    }

    return $countries_array;
  }

////
// Alias function to zen_get_countries()
  function zen_get_country_name($country_id) {
    $country_array = zen_get_countries($country_id);

    return $country_array['countries_name'];
  }

/**
 * Alias function to zen_get_countries, which also returns the countries iso codes
 *
 * @param int If set limits to a single country
*/
  function zen_get_countries_with_iso_codes($countries_id) {
    return zen_get_countries($countries_id, true);
  }

////
// Returns the zone (State/Province) name
// TABLES: zones
  function zen_get_zone_name($country_id, $zone_id, $default_zone) {
    global $db;
    $zone_query = "select zone_name
                   from " . TABLE_ZONES . "
                   where zone_country_id = '" . (int)$country_id . "'
                   and zone_id = '" . (int)$zone_id . "'";

    $zone = $db->Execute($zone_query);

    if ($zone->RecordCount()) {
      return $zone->fields['zone_name'];
    } else {
      return $default_zone;
    }
  }

////
// Returns the zone (State/Province) code
// TABLES: zones
  function zen_get_zone_code($country_id, $zone_id, $default_zone) {
    global $db;
    $zone_query = "select zone_code
                   from " . TABLE_ZONES . "
                   where zone_country_id = '" . (int)$country_id . "'
                   and zone_id = '" . (int)$zone_id . "'";

    $zone = $db->Execute($zone_query);

    if ($zone->RecordCount() > 0) {
      return $zone->fields['zone_code'];
    } else {
      return $default_zone;
    }
  }


////
// validate products_id
  function zen_products_id_valid($valid_id) {
    global $db;
    $check_valid = $db->Execute("select p.products_id
                                 from " . TABLE_PRODUCTS . " p
                                 where products_id='" . $valid_id . "' limit 1");
    if ($check_valid->EOF) {
      return false;
    } else {
      return true;
    }
  }

/**
 * Return a product's name.
 *
 * @param int The product id of the product who's name we want
 * @param int The language id to use. If this is not set then the current language is used
*/
  function zen_get_products_name($product_id, $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select products_name
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where products_id = '" . (int)$product_id . "'
                      and language_id = '" . (int)$language . "'";

    $product = $db->Execute($product_query);

    return $product->fields['products_name'];
  }


/**
 * Return a product's stock count.
 *
 * @param int The product id of the product who's stock we want
*/
  function zen_get_products_stock($products_id) {
    global $db;
    $products_id = zen_get_prid($products_id);
    $stock_query = "select products_quantity
                    from " . TABLE_PRODUCTS . "
                    where products_id = '" . (int)$products_id . "'";

    $stock_values = $db->Execute($stock_query);

    return $stock_values->fields['products_quantity'];
  }

/**
 * Check if the required stock is available.
 *
 * If insufficent stock is available return an out of stock message
 *
 * @param int The product id of the product whos's stock is to be checked
 * @param int Is this amount of stock available
 *
 * @TODO naughty html in a function
*/
  function zen_check_stock($products_id, $products_quantity) {
    $stock_left = zen_get_products_stock($products_id) - $products_quantity;
    $out_of_stock = '';

    if ($stock_left < 0) {
      $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
    }

    return $out_of_stock;
  }

  function zen_get_manufacturers($manufacturers_array = '') {
    global $db;
    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $manufacturers_query = "select manufacturers_id, manufacturers_name
                            from " . TABLE_MANUFACTURERS . " order by manufacturers_name";

    $manufacturers = $db->Execute($manufacturers_query);

    while (!$manufacturers->EOF) {
      $manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'], 'text' => $manufacturers->fields['manufacturers_name']);
      $manufacturers->MoveNext();
    }

    return $manufacturers_array;
  }

////
// Check if product has attributes
  function zen_has_product_attributes($products_id, $not_readonly = 'true') {
    global $db;

    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true') {
      // don't include READONLY attributes to determin if attributes must be selected to add to cart
      $attributes_query = "select pa.products_attributes_id
                           from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.options_id = po.products_options_id
                           where pa.products_id = '" . (int)$products_id . "' and po.products_options_type != '" . PRODUCTS_OPTIONS_TYPE_READONLY . "' limit 1";
    } else {
      // regardless of READONLY attributes no add to cart buttons
      $attributes_query = "select pa.products_attributes_id
                           from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                           where pa.products_id = '" . (int)$products_id . "' limit 1";
    }

    $attributes = $db->Execute($attributes_query);

    if ($attributes->fields['products_attributes_id'] > 0) {
      return true;
    } else {
      return false;
    }
  }

///
// Check if product has attributes values
  function zen_has_product_attributes_values($products_id) {
    global $db;
    $attributes_query = "select sum(options_values_price) as total
                         from " . TABLE_PRODUCTS_ATTRIBUTES . "
                         where products_id = '" . (int)$products_id . "'";

    $attributes = $db->Execute($attributes_query);

    if ($attributes->fields['total'] != 0) {
      return true;
    } else {
      return false;
    }
  }

  function zen_get_category_name($category_id, $fn_language_id) {
    global $db;
    $category_query = "select categories_name
                       from " . TABLE_CATEGORIES_DESCRIPTION . "
                       where categories_id = '" . $category_id . "'
                       and language_id = '" . $fn_language_id . "'";

    $category = $db->Execute($category_query);

    return $category->fields['categories_name'];
  }


  function zen_get_category_description($category_id, $fn_language_id) {
    global $db;
    $category_query = "select categories_description
                       from " . TABLE_CATEGORIES_DESCRIPTION . "
                       where categories_id = '" . $category_id . "'
                       and language_id = '" . $fn_language_id . "'";

    $category = $db->Execute($category_query);

    return $category->fields['categories_description'];
  }

////
// Return a product's category
// TABLES: products_to_categories
  function zen_get_products_category_id($products_id) {
    global $db;

    $the_products_category_query = "select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "'" . " order by products_id,categories_id";
    $the_products_category = $db->Execute($the_products_category_query);

    return $the_products_category->fields['categories_id'];
  }

////
// TABLES: categories
  function zen_get_categories_image($what_am_i) {
    global $db;

    $the_categories_image_query= "select categories_image from " . TABLE_CATEGORIES . " where categories_id= '" . $what_am_i . "'";
    $the_products_category = $db->Execute($the_categories_image_query);

    return $the_products_category->fields['categories_image'];
  }

////
// TABLES: categories_description
  function zen_get_categories_name($who_am_i) {
    global $db;
    $the_categories_name_query= "select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id= '" . $who_am_i . "' and language_id= '" . $_SESSION['languages_id'] . "'";

    $the_categories_name = $db->Execute($the_categories_name_query);

    return $the_categories_name->fields['categories_name'];
  }

////
// Return a product's manufacturer's name
// TABLES: products, manufacturers
  function zen_get_products_manufacturers_name($product_id) {
    global $db;

    $product_query = "select m.manufacturers_name
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.products_id = '" . (int)$product_id . "'
                      and p.manufacturers_id = m.manufacturers_id";

    $product =$db->Execute($product_query);

    return $product->fields['manufacturers_name'];
  }

////
// Return a product's manufacturer's image
// TABLES: products, manufacturers
  function zen_get_products_manufacturers_image($product_id) {
    global $db;

    $product_query = "select m.manufacturers_image
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.products_id = '" . (int)$product_id . "'
                      and p.manufacturers_id = m.manufacturers_id";

    $product =$db->Execute($product_query);

    return $product->fields['manufacturers_image'];
  }


////
// return attributes products_options_sort_order - PRODUCTS_ATTRIBUTES
  function zen_get_attributes_sort_order($products_id, $options_id, $options_values_id) {
    global $db;
      $check = $db->Execute("select products_options_sort_order
                             from " . TABLE_PRODUCTS_ATTRIBUTES . "
                             where products_id = '" . $products_id . "'
                             and options_id = '" . $options_id . "'
                             and options_values_id = '" . $options_values_id . "' limit 1");

      return $check->fields['products_options_sort_order'];
  }

////
// return attributes products_options_sort_order - PRODUCTS_OPTIONS
  function zen_get_attributes_options_sort_order($products_id, $options_id, $options_values_id) {
    global $db;
      $check = $db->Execute("select products_options_sort_order
                             from " . TABLE_PRODUCTS_OPTIONS . "
                             where products_options_id = '" . $options_id . "' limit 1");

      $check_options_id = $db->Execute("select products_id, options_id, options_values_id, products_options_sort_order
                             from " . TABLE_PRODUCTS_ATTRIBUTES . "
                             where products_id='" . $products_id . "'
                             and options_id='" . $options_id . "'
                             and options_values_id = '" . $options_values_id . "' limit 1");


      return $check->fields['products_options_sort_order'] . '.' . str_pad($check_options_id->fields['products_options_sort_order'],5,'0',STR_PAD_LEFT);
  }

////
// check if attribute is display only
  function zen_get_attributes_valid($product_id, $option, $value) {
    global $db;

// regular attribute validation
    $check_attributes = $db->Execute("select attributes_display_only, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $product_id . "' and options_id='" . $option . "' and options_values_id='" . $value . "'");

    $check_valid = true;

// display only cannot be selected
    if ($check_attributes->fields['attributes_display_only'] == '1') {
      $check_valid = false;
    }

// text required validation
    if (ereg('^txt_', $option)) {
      $check_attributes = $db->Execute("select attributes_display_only, attributes_required from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . $product_id . "' and options_id='" . ereg_replace('txt_', '', $option) . "' and options_values_id='0'");
// text cannot be blank
      if ($check_attributes->fields['attributes_required'] == '1' and empty($value)) {
        $check_valid = false;
      }
    }

    return $check_valid;
  }

  function zen_options_name($options_id) {
    global $db;

    $options_id = str_replace('txt_','',$options_id);

    $options_values = $db->Execute("select products_options_name
                                    from " . TABLE_PRODUCTS_OPTIONS . "
                                    where products_options_id = '" . (int)$options_id . "'
                                    and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    return $options_values->fields['products_options_name'];
  }

  function zen_values_name($values_id) {
    global $db;

    $values_values = $db->Execute("select products_options_values_name
                                   from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   where products_options_values_id = '" . (int)$values_id . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    return $values_values->fields['products_options_values_name'];
  }

////
// configuration key value lookup
  function zen_get_configuration_key_value($lookup) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . $lookup . "'");
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<font color="FF0000">' . $lookup . '</font>';
    }
    return $lookup_value;
  }

  function zen_get_products_description($product_id, $language = '') {
    global $db;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select products_description
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where products_id = '" . (int)$product_id . "'
                      and language_id = '" . (int)$language . "'";

    $product = $db->Execute($product_query);

    return $product->fields['products_description'];
  }

////
// look up the product type from product_id and return an info page name
  function zen_get_info_page($zf_product_id) {
    global $db;
    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id = '" . (int)$zf_product_id . "'";
    $zp_type = $db->Execute($sql);
    if ($zp_type->RecordCount() == 0) {
      return 'products_general_info';
    } else {
      $zp_product_type = $zp_type->fields['products_type'];
      $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . $zp_product_type . "'";
      $zp_handler = $db->Execute($sql);
      return $zp_handler->fields['type_handler'] . '_info';
    }
  }

////
// Get accepted credit cards
// There needs to be a define on the accepted credit card in the language file credit_cards.php example: TEXT_CC_ENABLED_VISA
  function zen_get_cc_enabled($text_image = 'TEXT_', $cc_seperate = ' ', $cc_make_columns = 0) {
    global $db;
    $cc_check_accepted_query = $db->Execute(SQL_CC_ENABLED);
    $cc_check_accepted = '';
    $cc_counter = 0;
    if ($cc_make_columns == 0) {
      while (!$cc_check_accepted_query->EOF) {
        $check_it = $text_image . $cc_check_accepted_query->fields['configuration_key'];
        if (defined($check_it)) {
          $cc_check_accepted .= constant($check_it) . $cc_seperate;
        }
        $cc_check_accepted_query->MoveNext();
      }
    } else {
      // build a table
      $cc_check_accepted = '<table class="ccenabled">' . "\n";
      $cc_check_accepted .= '<tr class="ccenabled">' . "\n";
      while (!$cc_check_accepted_query->EOF) {
        $check_it = $text_image . $cc_check_accepted_query->fields['configuration_key'];
        if (defined($check_it)) {
          $cc_check_accepted .= '<td class="ccenabled">' . constant($check_it) . '</td>' . "\n";
        }
        $cc_check_accepted_query->MoveNext();
        $cc_counter++;
        if ($cc_counter >= $cc_make_columns) {
          $cc_check_accepted .= '</tr>' . "\n" . '<tr class="ccenabled">' . "\n";
          $cc_counter = 0;
        }
      }
      $cc_check_accepted .= '</tr>' . "\n" . '</table>' . "\n";
    }
    return $cc_check_accepted;
  }

////
// TABLES: categories_name from products_id
  function zen_get_categories_name_from_product($product_id) {
    global $db;

    $check_products_category= $db->Execute("select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $product_id . "' limit 1");
    $the_categories_name= $db->Execute("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id= '" . $check_products_category->fields['categories_id'] . "' and language_id= '" . $_SESSION['languages_id'] . "'");

    return $the_categories_name->fields['categories_name'];
  }

////
// configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
  function zen_get_configuration_key_value_layout($lookup, $type=1) {
    global $db;
    $configuration_query= $db->Execute("select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . $lookup . "' and product_type_id='". $type . "'");
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<font color="FF0000">' . $lookup . '</font>';
    }
    return $lookup_value;
  }

////
// look up a products image and send back the image
  function zen_get_products_image($product_id, $width = SMALL_IMAGE_WIDTH, $height = SMALL_IMAGE_HEIGHT) {
    global $db;

    $sql = "select p.products_image from " . TABLE_PRODUCTS . " p  where products_id=?";
    $look_up = $db->query( $sql, array($product_id) );

    return zen_image(STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/images'. $look_up->fields['products_image'], zen_get_products_name($product_id), $width, $height, 'hspace="5" vspace="5"');
  }

////
// look up a product is virtual
  function zen_get_products_virtual($lookup) {
    global $db;

    $sql = "select p.products_virtual from " . TABLE_PRODUCTS . " p  where p.products_id='" . $lookup . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->fields['products_virtual'] == '1') {
      return true;
    } else {
      return false;
    }
  }

  function zen_get_products_allow_add_to_cart($lookup) {
    global $db;

    $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id='" . $lookup . "'";
    $type_lookup = $db->Execute($sql);

    $sql = "select allow_add_to_cart from " . TABLE_PRODUCT_TYPES . " where type_id = '" . $type_lookup->fields['products_type'] . "'";
    $allow_add_to_cart = $db->Execute($sql);

    return $allow_add_to_cart->fields['allow_add_to_cart'];
  }

    function zen_get_show_product_switch($lookup, $field, $suffix= 'SHOW_', $prefix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $db;

      $sql = "select products_type from " . TABLE_PRODUCTS . " where products_id='" . $lookup . "'";
      $type_lookup = $db->Execute($sql);

      $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . " where type_id = '" . $type_lookup->fields['products_type'] . "'";
      $show_key = $db->Execute($sql);


      $zv_key = strtoupper($suffix . $show_key->fields['type_handler'] . $prefix . $field_prefix . $field . $field_suffix);

      $sql = "select configuration_key, configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . " where configuration_key='" . $zv_key . "'";
      $zv_key_value = $db->Execute($sql);

      if ($zv_key_value->RecordCount() > 0) {
        return $zv_key_value->fields['configuration_value'];
      } else {
        $sql = "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . $zv_key . "'";
        $zv_key_value = $db->Execute($sql);
        if ($zv_key_value->RecordCount() > 0) {
          return $zv_key_value->fields['configuration_value'];
        } else {
          return $zv_key_value->fields['configuration_value'];
        }
      }
    }

////
// look up a product is always free shipping
  function zen_get_product_is_always_free_shipping($lookup) {
    global $db;

    $sql = "select p.product_is_always_free_shipping from " . TABLE_PRODUCTS . " p  where p.products_id='" . $lookup . "'";
    $look_up = $db->Execute($sql);

    if ($look_up->fields['product_is_always_free_shipping'] == '1') {
      return true;
    } else {
      return false;
    }
  }

////
// stop regular behavior based on customer/store settings
  function zen_run_normal() {
    $zc_run = 'false';
    switch (true) {
      case (DOWN_FOR_MAINTENANCE == 'true'):
      // down for maintenance
        $zc_run = 'false';
        break;
      case (STORE_STATUS >= 1):
      // showcase no prices
        $zc_run = 'false';
        break;
      case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
      // customer must be logged in to browse
        $zc_run = 'false';
        break;
      case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
      // show room only
      // customer may browse but no prices
        $zc_run = 'false';
        break;
      case (CUSTOMERS_APPROVAL == '3'):
      // show room only
        $zc_run = 'false';
        break;
      case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customer_id'] == ''):
      // customer must be logged in to browse
        $zc_run = 'false';
        break;
      case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customers_authorization'] > '0'):
      // customer must be logged in to browse
        $zc_run = 'false';
        break;
      default:
      // proceed normally
        $zc_run = 'true';
        break;
    }
    return $zc_run;
  }

///
// no price check
  function zen_check_show_prices() {
    if (!(CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == '') and !((CUSTOMERS_APPROVAL_AUTHORIZATION > 0 and CUSTOMERS_APPROVAL_AUTHORIZATION < 3) and ($_SESSION['customers_authorization'] > '0' or $_SESSION['customer_id'] == '')) and STORE_STATUS != 1) {
      return 'true';
    } else {
      return 'false';
    }
  }
?>

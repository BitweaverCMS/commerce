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
// $Id$
//
//
  function zen_get_order_status_name($order_status_id, $language_id = '') {
    global $gBitDb;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (!is_numeric($language_id)) $language_id = $_SESSION['languages_id'];

    $status = $gBitDb->Execute("SELECT `orders_status_name`
                            FROM " . TABLE_ORDERS_STATUS . "
                            WHERE `orders_status_id` = '" . (int)$order_status_id . "'
                            and `language_id` = '" . (int)$language_id . "'");

    return $status->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
  }


	function commerce_get_statuses( $pHash=FALSE ) {
		global $gBitDb;
		$ret = array();
		$orders_status = $gBitDb->query("select `orders_status_id`, `orders_status_name`
									 from " . TABLE_ORDERS_STATUS . "
									 where `language_id` = '" . (int)$_SESSION['languages_id'] . "' ORDER BY `orders_status_id`");

		while (!$orders_status->EOF) {
			if( $pHash ) {
				$ret[$orders_status->fields['orders_status_id']] = '[' . $orders_status->fields['orders_status_id'] . '] '.$orders_status->fields['orders_status_name'];
			} else {
				$ret[] = array( 'id' => $orders_status->fields['orders_status_id'],
							    'text' => '[' . $orders_status->fields['orders_status_id'] . '] '.$orders_status->fields['orders_status_name'] );
			}
			$orders_status->MoveNext();
		}
		return $ret;
	}

/**
 * Returns an array with zones for a given country
 *
 * @param int determines country list
*/
function zen_get_country_zones( $pCountryId ) {
	global $gBitDb;
	$ret = array();
	if( BitBase::verifyId( $pCountryId ) ) {
		$sql = "SELECT `zone_id` AS `hash_key`, `zone_id`, `zone_name` 
				FROM " . TABLE_ZONES . " 
				WHERE `zone_country_id` = ? 
				ORDER BY `zone_name`";
		$bindVars = array( $pCountryId );
		$ret = $gBitDb->getAssoc( $sql, $bindVars );
	}
	return $ret;
}

/**
 * Returns an array with countries
 *
 * @param int If set limits to a single country
 * @param boolean If true adds the iso codes to the array
*/
function zen_get_countries( $pCountryMixed = '', $with_iso_codes = false) {
    global $gBitDb;

    $ret = array();
	$whereSql = '';
	$bindVars = array();

    if (zen_not_null($pCountryMixed)) {
    	if( is_numeric( $pCountryMixed ) ) {
    		$whereSql = ' WHERE `countries_id` = ? ';
    	} else {
    		$pCountryMixed = strtoupper( $pCountryMixed );
    		$whereSql = ' WHERE UPPER( `countries_name` ) = ? ';
    	}
		$bindVars = array( $pCountryMixed );
	}

	$countries = "SELECT *
				  FROM " . TABLE_COUNTRIES . "
				  $whereSql
				  ORDER BY `countries_name`";
	if( $rs = $gBitDb->query( $countries, $bindVars ) ) {
		while( !$rs->EOF ) {
			$row = array( 'countries_id' => $rs->fields['countries_id'],
						  'countries_name' => $rs->fields['countries_name'],
						  'address_format_id' => $rs->fields['address_format_id']
						);

			if( $with_iso_codes == true ) {
				$row['countries_iso_code_2'] = $rs->fields['countries_iso_code_2'];
				$row['countries_iso_code_3'] = $rs->fields['countries_iso_code_3'];
			}
			if( $rs->RecordCount() == 1 ) {
				$ret = $row;
			} else {
				$ret[] = $row;
			}
			$rs->MoveNext();
		}
	}

    return $ret;
}


	function zen_get_country_id( $pCountryName ) {
		global $gBitDb;
		$length = strlen( $pCountryName );
		if( $length > 3 ) {
			$column = 'countries_name';
		} elseif( $length == 3 ) {
			$column = 'countries_iso_code_3';
		} else {
			$column = 'countries_iso_code_2';
		}

		$sql = "SELECT countries_id
				FROM " . TABLE_COUNTRIES . "
				WHERE LOWER(`$column`) LIKE ?";
		return( $gBitDb->getOne( $sql, array( '%'.strtolower($pCountryName).'%' ) ) );
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


function zen_get_zone_by_name( $pCountryId, $pName ) {
	global $gBitDb;
	$sql = "SELECT *
			FROM " . TABLE_ZONES . "
			WHERE `zone_country_id` = ? AND (`zone_name` LIKE ? OR `zone_code` LIKE ?)";
	return( $gBitDb->getRow( $sql, array( $pCountryId, $pName.'%', '%'.$pName.'%' ) ) );
}


////
// Returns the zone (State/Province) name given the code
// TABLES: zones
function zen_get_zone_name_by_code( $pCountryId, $pZoneCode ) {
	global $gBitDb;
	$ret = $pZoneCode;
	if( $zoneName = $gBitDb->getOne( "select `zone_name` from " . TABLE_ZONES . " where `zone_country_id` = ? and `zone_code` = ?", array( $pCountryId, $pZoneCode ) ) ) {
		$ret = $zoneName;
	}
	return $ret;
}

////
// Returns the zone (State/Province) name
// TABLES: zones
  function zen_get_zone_name($country_id, $zone_id, $default_zone) {
    global $gBitDb;
    $zone_query = "select `zone_name` from " . TABLE_ZONES . " where `zone_country_id` = ? and `zone_id` = ?";
    $zone = $gBitDb->query( $zone_query, array( $country_id, $zone_id ) );

    if ($zone->RecordCount()) {
      return $zone->fields['zone_name'];
    } else {
      return $default_zone;
    }
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : zen_get_zone_code
  //
  // Arguments   : country           country code string
  //               zone              state/province zone_id
  //               def_state         default string if zone==0
  //
  // Return      : state_prov_code   state/province code
  //
  // Description : Function to retrieve the state/province code (as in FL for Florida etc)
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
  function zen_get_zone_code( $pCountryId, $pZoneMixed, $pDefaultZone ) {
	global $gBitDb;

	if( is_numeric( $pZoneMixed ) ) {
		$whereSql = ' AND `zone_id`=?';
	} else {
		$whereSql = ' AND `zone_name`=?';
	}

	if( !($zone = $gBitDb->getOne( "SELECT `zone_code` FROM " . TABLE_ZONES . " WHERE `zone_country_id` = ? ".$whereSql, array( (int)$pCountryId, $pZoneMixed ) ) ) ) {
		$zone = $pDefaultZone;
	}
	return $zone;
  }


/**
 * Return a zone's id
 *
 * @param int the id of the country
 * @param string the name of the zone
*/
  function zen_get_zone_id( $pCountryId, $pZoneName ) {
	global $gBitDb;

	return $gBitDb->getOne( "SELECT `zone_id` FROM " . TABLE_ZONES . " WHERE `zone_country_id` = ? AND `zone_name`=?", array( (int)$pCountryId, $pZoneName ) );
  }


/**
 * Return a product's name.
 *
 * @param int The product id of the product who's name we want
 * @param int The language id to use. If this is not set then the current language is used
*/
  function zen_get_products_name($product_id, $language = '') {
    global $gBitDb;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select `products_name`
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where `products_id` = '" . (int)$product_id . "'
                      and `language_id` = '" . (int)$language . "'";

    $product = $gBitDb->Execute($product_query);

    return $product->fields['products_name'];
  }


  function zen_get_manufacturers($manufacturers_array = '') {
    global $gBitDb;
    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $manufacturers_query = "select `manufacturers_id`, `manufacturers_name`
                            from " . TABLE_MANUFACTURERS . " order by `manufacturers_name`";

    $manufacturers = $gBitDb->Execute($manufacturers_query);

    while (!$manufacturers->EOF) {
      $manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'], 'text' => $manufacturers->fields['manufacturers_name']);
      $manufacturers->MoveNext();
    }

    return $manufacturers_array;
  }

////
// Check if product has attributes
  function zen_has_product_attributes($products_id, $not_readonly = 'true') {
    global $gBitDb;

    if (PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true') {
      // don't include READONLY attributes to determin if attributes must be selected to add to cart
      $attributes_query = "SELECT pa.`products_attributes_id`
                           FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
						 	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id`) 
						   	LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po on pa.`products_options_id` = po.`products_options_id`
                           WHERE pom.`products_id`=? and po.`products_options_type` != '" . PRODUCTS_OPTIONS_TYPE_READONLY . "'";
    } else {
      // regardless of READONLY attributes no add to cart buttons
      $attributes_query = "SELECT pa.`products_attributes_id`
                           FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
						 	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id`) 
                           WHERE pom.`products_id`=?";
		
    }
	$bindVars = array( (int)$products_id );

    $attributes = $gBitDb->getOne($attributes_query, $bindVars );

    return( !empty( $attributes ) );
  }

///
// Check if product has attributes values
  function zen_has_product_attributes_values($products_id) {
    global $gBitDb;
    $attributes_query = "SELECT SUM(`options_values_price`) as `total`
                         FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
						 	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`products_options_values_id`=pom.`products_options_values_id`) 
                         WHERE pom.`products_id` = ?";
    $hasAttributes = $gBitDb->getOne( $attributes_query, array( (int)$products_id ) );
	return $hasAttributes;
  }

  function zen_get_category_name($category_id, $fn_language_id) {
    global $gBitDb;
    $category_query = "select `categories_name`
                       from " . TABLE_CATEGORIES_DESCRIPTION . "
                       where `categories_id` = '" . $category_id . "'
                       and `language_id` = '" . $fn_language_id . "'";

    $category = $gBitDb->Execute($category_query);

    return $category->fields['categories_name'];
  }


  function zen_get_category_description($category_id, $fn_language_id) {
    global $gBitDb;
    if ( !$category_id ) return "";
    $category_query = "select `categories_description` from " . TABLE_CATEGORIES_DESCRIPTION . " where `categories_id` = ? and `language_id` = ?";
    $category = $gBitDb->query($category_query, array( $category_id, $fn_language_id) );
    return $category->fields['categories_description'];
  }

////
// Return a product's category
// TABLES: products_to_categories
  function zen_get_products_category_id($products_id) {
    global $gBitDb;

    $the_products_category_query = "select `products_id`, `categories_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `products_id` = '" . (int)$products_id . "'" . " order by `products_id`, `categories_id`";
    $the_products_category = $gBitDb->Execute($the_products_category_query);

    return $the_products_category->fields['categories_id'];
  }

////
// TABLES: categories
  function zen_get_categories_image($what_am_i) {
    global $gBitDb;

    $the_categories_image_query= "select `categories_image` from " . TABLE_CATEGORIES . " where `categories_id` = '" . $what_am_i . "'";
    $the_products_category = $gBitDb->Execute($the_categories_image_query);

    return $the_products_category->fields['categories_image'];
  }

////
// TABLES: categories_description
  function zen_get_categories_name($who_am_i) {
    global $gBitDb;
    $the_categories_name_query= "select `categories_name` from " . TABLE_CATEGORIES_DESCRIPTION . " where `categories_id` = '" . $who_am_i . "' and `language_id` = '" . $_SESSION['languages_id'] . "'";

    $the_categories_name = $gBitDb->Execute($the_categories_name_query);

    return $the_categories_name->fields['categories_name'];
  }

////
// Return a product's manufacturer's name
// TABLES: products, manufacturers
  function zen_get_products_manufacturers_name($product_id) {
    global $gBitDb;

    $product_query = "select m.`manufacturers_name`
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.`products_id` = '" . (int)$product_id . "'
                      and p.`manufacturers_id` = m.`manufacturers_id`";

    $product =$gBitDb->Execute($product_query);

    return $product->fields['manufacturers_name'];
  }

////
// Return a product's manufacturer's image
// TABLES: products, manufacturers
  function zen_get_products_manufacturers_image($product_id) {
    global $gBitDb;

    $product_query = "select m.`manufacturers_image`
                      from " . TABLE_PRODUCTS . " p, " .
                            TABLE_MANUFACTURERS . " m
                      where p.`products_id` = '" . (int)$product_id . "'
                      and p.`manufacturers_id` = m.`manufacturers_id`";

    $product =$gBitDb->Execute($product_query);

    return $product->fields['manufacturers_image'];
  }


////
// return attributes products_options_sort_order - PRODUCTS_ATTRIBUTES
function zen_get_attributes_sort_order($products_id, $options_id, $options_values_id) {
	global $gBitDb;
	$check = $gBitDb->getOne("SELECT `products_options_sort_order`
								FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
									INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id`) 
								WHERE `products_id` = ? AND `products_options_id` = ? AND pa.`products_options_values_id` = ?", array( $products_id, $options_id, $options_values_id ) );

	return $check;
}

////
// return attributes products_options_sort_order - PRODUCTS_OPTIONS
  function zen_get_attributes_options_sort_order($products_id, $options_id, $options_values_id) {
    global $gBitDb;
      $sortOrder = $gBitDb->getOne("select `products_options_sort_order`
                             from " . TABLE_PRODUCTS_OPTIONS . "
                             where `products_options_id` = '" . $options_id . "'");

      $attSortOrder = $gBitDb->getOne("select `products_id`, `products_options_id`, pa.`products_options_values_id`, `products_options_sort_order`
                             FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
							  	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )
                             WHERE `products_id` = ?  and `products_options_id` = ? and pa.`products_options_values_id` = ?", array( $products_id, $options_id, $options_values_id ) );
      return $sortOrder . '.' . str_pad( $attSortOrder ,5 ,'0' , STR_PAD_LEFT );
  }

////
// check if attribute is display only
	function zen_get_attributes_valid($product_id, $option, $value) {
		global $gBitDb;
		$check_valid = true;

		// text required validation
		if (preg_match('/^txt_/', $option)) {
			$query = "SELECT `attributes_display_only`, `attributes_required` 
					  FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
					  	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )
					  WHERE `products_id`=? and `products_options_id`=? and pa.`products_options_values_id`=?";
		  $check_attributes = $gBitDb->query( $query, array( $product_id, str_replace('txt_', '', $option), '0'  ) );
		// text cannot be blank
		  if ($check_attributes->fields['attributes_required'] == '1' and empty($value)) {
			$check_valid = false;
		  }
		} else {
			// regular attribute validation
			$query = "SELECT `attributes_display_only`, `attributes_required` 
					  FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
					  	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )
					  WHERE `products_id`=? and `products_options_id`=? and pa.`products_options_values_id`=?";
			$check_attributes = $gBitDb->query( $query, array( (int)$product_id, (int)$option, (int)$value ) );

			// display only cannot be selected
			if ($check_attributes->fields['attributes_display_only'] == '1') {
			  $check_valid = false;
			}

		}

	    return $check_valid;
	}

  function zen_options_name($options_id) {
    global $gBitDb;

    $options_id = str_replace('txt_','',$options_id);

    $options_values = $gBitDb->Execute("select `products_options_name`
                                    from " . TABLE_PRODUCTS_OPTIONS . "
                                    where `products_options_id` = '" . (int)$options_id . "'
                                    and `language_id` = '" . (int)$_SESSION['languages_id'] . "'");

    return $options_values->fields['products_options_name'];
  }

////
// configuration key value lookup
  function zen_get_configuration_key_value($lookup) {
    global $gBitDb;
    $configuration_query= $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` ='" . $lookup . "'");
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<font color="FF0000">' . $lookup . '</font>';
    }
    return $lookup_value;
  }

  function zen_get_products_description($product_id, $language = '') {
    global $gBitDb;

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "select `products_description`
                      from " . TABLE_PRODUCTS_DESCRIPTION . "
                      where `products_id` = '" . (int)$product_id . "'
                      and `language_id` = '" . (int)$language . "'";

    $product = $gBitDb->Execute($product_query);

    return $product->fields['products_description'];
  }

////
// look up the product type from product_id and return an info page name
function zen_get_info_page($zf_product_id) {
	$ret = 'products_general_info';
	if( BitBase::verifyId( $zf_product_id ) ) {
		global $gBitDb;
		//		return '';
		$sql = "SELECT cpt.`type_handler` FROM " . TABLE_PRODUCTS . " cp 
					INNER JOIN " . TABLE_PRODUCT_TYPES . " cpt ON (cp.`products_type`=cpt.`type_id`)
				WHERE cp.`products_id` = ?";
		if( $zp_type = $gBitDb->getOne( $sql, array( $zf_product_id ) ) ) {
			$ret = $zp_type . '_info';
		}
	}
	return $ret;
}

////
// Get accepted credit cards
// There needs to be a define on the accepted credit card in the language file credit_cards.php example: TEXT_CC_ENABLED_VISA
  function zen_get_cc_enabled($text_image = 'TEXT_', $cc_seperate = ' ', $cc_make_columns = 0) {
    global $gBitDb;
    $cc_check_accepted_query = $gBitDb->Execute(SQL_CC_ENABLED);
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
    global $gBitDb;

    $check_products_category= $gBitDb->getOne("select `products_id`, `categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `products_id`='" . $product_id . "'");
    $the_categories_name= $gBitDb->Execute("select `categories_name` from " . TABLE_CATEGORIES_DESCRIPTION . " where `categories_id`= '" . $check_products_category->fields['categories_id'] . "' and `language_id`= '" . $_SESSION['languages_id'] . "'");

    return $the_categories_name->fields['categories_name'];
  }

////
// configuration key value lookup in TABLE_PRODUCT_TYPE_LAYOUT
  function zen_get_configuration_key_value_layout($lookup, $type=1) {
    global $gBitDb;
    $configuration_query= $gBitDb->query("select `configuration_value` from " . TABLE_PRODUCT_TYPE_LAYOUT . " where `configuration_key`=? and `product_type_id`=?", array( $lookup, $type ) );
    $lookup_value= $configuration_query->fields['configuration_value'];
    if ( !($lookup_value) ) {
      $lookup_value='<font color="FF0000">' . $lookup . '</font>';
    }
    return $lookup_value;
  }

////
// look up a products image and send back the image
  function zen_get_products_image($product_id, $width = SMALL_IMAGE_WIDTH, $height = SMALL_IMAGE_HEIGHT) {
    global $gBitDb;
    return zen_image( CommerceProduct::getImageUrlFromHash( $product_id ), zen_get_products_name($product_id), $width, $height, 'hspace="5" vspace="5"');
  }

////
// look up a product is virtual
  function zen_get_products_virtual($lookup) {
    global $gBitDb;

    $sql = "select p.`products_virtual` from " . TABLE_PRODUCTS . " p  where p.`products_id`='" . $lookup . "'";
    $look_up = $gBitDb->Execute($sql);

    if ($look_up->fields['products_virtual'] == '1') {
      return true;
    } else {
      return false;
    }
  }

  function zen_get_products_allow_add_to_cart($lookup) {
    global $gBitDb;

    $sql = "select `products_type` from " . TABLE_PRODUCTS . " where `products_id`=?";
    $type_lookup = $gBitDb->query($sql, array( $lookup ) );

    $sql = "select `allow_add_to_cart` from " . TABLE_PRODUCT_TYPES . " where `type_id` = ?";
    $allow_add_to_cart = $gBitDb->query( $sql, array( $type_lookup->fields['products_type'] ) );

    return $allow_add_to_cart->fields['allow_add_to_cart'];
  }

// build configuration_key based on product type and return its value
// example: To get the settings for metatags_products_name_status for a product use:
// zen_get_show_product_switch($_GET['pID'], 'metatags_products_name_status')
// the product is looked up for the products_type which then builds the configuration_key example:
// SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS
// the value of the configuration_key is then returned
// NOTE: keys are looked up first in the product_type_layout table and if not found looked up in the configuration table.
    function zen_get_show_product_switch($lookup, $field, $suffix= 'SHOW_', $prefix= '_INFO', $field_prefix= '_', $field_suffix='') {
      global $gBitDb;

      $sql = "select `products_type` from " . TABLE_PRODUCTS . " where `products_id`='" . $lookup . "'";
      $type_lookup = $gBitDb->Execute($sql);

      $sql = "select `type_handler` from " . TABLE_PRODUCT_TYPES . " where `type_id` = '" . $type_lookup->fields['products_type'] . "'";
      $show_key = $gBitDb->Execute($sql);


      $zv_key = strtoupper($suffix . $show_key->fields['type_handler'] . $prefix . $field_prefix . $field . $field_suffix);

      $sql = "select `configuration_key`, `configuration_value` from " . TABLE_PRODUCT_TYPE_LAYOUT . " where `configuration_key` ='" . $zv_key . "'";
      $zv_key_value = $gBitDb->Execute($sql);

      if ($zv_key_value->RecordCount() > 0) {
        return $zv_key_value->fields['configuration_value'];
      } else {
        $sql = "select `configuration_key`, `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` ='" . $zv_key . "'";
        $zv_key_value = $gBitDb->Execute($sql);
        if ($zv_key_value->RecordCount() > 0) {
          return $zv_key_value->fields['configuration_value'];
        } else {
          return $zv_key_value->fields['configuration_value'];
        }
      }
    }

////
// look up a product is always free shipping
  function zen_get_product_is_always_free_ship($lookup) {
    global $gBitDb;

    $sql = "select p.`product_is_always_free_ship` from " . TABLE_PRODUCTS . " p  where p.`products_id`='" . $lookup . "'";
    $look_up = $gBitDb->Execute($sql);

    if ($look_up->fields['product_is_always_free_ship'] == '1') {
      return true;
    } else {
      return false;
    }
  }

////
// stop regular behavior based on customer/store settings
function zen_run_normal() {
	$ret = 'false';

	if( defined( 'DOWN_FOR_MAINTENANCE' ) && DOWN_FOR_MAINTENANCE == 'true') {
		// down for maintenance
		$ret = 'false';
	} elseif( defined( 'STORE_STATUS' ) && STORE_STATUS >= 1 ) {
		// showcase no prices
		$ret = 'false';
	} elseif( defined( 'CUSTOMERS_APPROVAL' ) && CUSTOMERS_APPROVAL == '1' && empty( $_SESSION['customer_id'] ) ) {
		// customer must be logged in to browse
		$ret = 'false';
	} elseif( defined( 'CUSTOMERS_APPROVAL' ) && CUSTOMERS_APPROVAL == '2' && empty( $_SESSION['customer_id'] ) ) {
		// show room only: customer may browse but no prices
		$ret = 'false';
	} elseif( defined( 'CUSTOMERS_APPROVAL' ) && CUSTOMERS_APPROVAL == '3' ) {
		// show room only
		$ret = 'false';
	} elseif( defined( 'CUSTOMERS_APPROVAL_AUTHORIZATION' ) && CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && empty( $_SESSION['customer_id'] ) ) {
		$ret = 'false';
	} elseif( defined( 'CUSTOMERS_APPROVAL_AUTHORIZATION' ) && CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && !empty( $_SESSION['customers_authorization'] ) ) {
		// customer must be logged in to browse
		$ret = 'false';
	} else {
		// proceed normally
		$ret = 'true';
	}
	return $ret;
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

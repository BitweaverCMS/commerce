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

	if (!is_numeric($language_id)) {
		$language_id = $_SESSION['languages_id'];
	}

	return $gBitDb->getOne("SELECT `orders_status_name` FROM " . TABLE_ORDERS_STATUS . " WHERE `orders_status_id` = ? and `language_id` = ?", array( (int)$order_status_id, (int)$language_id ) );
}


function commerce_get_statuses( $pHash=FALSE ) {
	global $gBitDb;
	$ret = array();
	$ordersStatus = $gBitDb->query("select `orders_status_id`, `orders_status_name`
								 from " . TABLE_ORDERS_STATUS . "
								 where `language_id` = '" . (int)$_SESSION['languages_id'] . "' ORDER BY `orders_status_id`");

	while (!$ordersStatus->EOF) {
		if( $pHash ) {
			$ret[$ordersStatus->fields['orders_status_id']] = '[' . $ordersStatus->fields['orders_status_id'] . '] '.$ordersStatus->fields['orders_status_name'];
		} else {
			$ret[] = array( 'id' => $ordersStatus->fields['orders_status_id'],
							'text' => '[' . $ordersStatus->fields['orders_status_id'] . '] '.$ordersStatus->fields['orders_status_name'] );
		}
		$ordersStatus->MoveNext();
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
		$sql = "SELECT `zone_id` AS `hash_key`, `zone_id`, `zone_name` FROM " . TABLE_ZONES . " WHERE `zone_country_id` = ?  ORDER BY `zone_name`";
		$bindVars = array( $pCountryId );
		$ret = $gBitDb->getAssoc( $sql, $bindVars, FALSE, FALSE, BIT_QUERY_CACHE_TIME );
	}
	return $ret;
}

/**
 * Returns an array with countries
 *
 * @param int If set limits to a single country
 * @param boolean If true adds the iso codes to the array
*/
function zen_get_countries( $pCountryMixed = '' ) {
    global $gBitDb;

    $ret = array();
	$whereSql = '';
	$bindVars = array();

    if (zen_not_null($pCountryMixed)) {
    	if( is_numeric( $pCountryMixed ) ) {
    		$whereSql = ' WHERE `countries_id` = ? ';
		} elseif( is_string( $pCountryMixed ) ) {
			$length = strlen( $pCountryMixed );
			if( $length == 3 ) {
				$pCountryMixed = strtoupper( $pCountryMixed );
				$whereSql = ' WHERE UPPER( `countries_iso_code_3` ) = ? ';
			} elseif( $length == 2 ) {
				$pCountryMixed = strtoupper( $pCountryMixed );
				$whereSql = ' WHERE UPPER( `countries_iso_code_2` ) = ? ';
			} else {
				$pCountryMixed = strtoupper( $pCountryMixed );
				$whereSql = ' WHERE UPPER( `countries_name` ) = ? ';
			}
		}
		if( !empty( $whereSql ) ) {
			$bindVars = array( $pCountryMixed );
		}
	}

	$countries = "SELECT * FROM " . TABLE_COUNTRIES . " $whereSql ORDER BY `countries_name`";
	if( $rs = $gBitDb->query( $countries, $bindVars, FALSE, FALSE, BIT_QUERY_CACHE_TIME ) ) {
		while( !$rs->EOF ) {
			$row = array( 'countries_id' => $rs->fields['countries_id'],
						  'countries_name' => $rs->fields['countries_name'],
						  'address_format_id' => $rs->fields['address_format_id'],
						  'countries_iso_code_2' => $rs->fields['countries_iso_code_2'],
						  'countries_iso_code_3' => $rs->fields['countries_iso_code_3']
						);

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
	if( $country_array = zen_get_countries($country_id) ) {
		return $country_array['countries_name'];
	}
}

function zen_get_zone_by_id( $pCountryId, $pZoneId ) {
	global $gBitDb;
	$sql = "SELECT * FROM " . TABLE_ZONES . " WHERE `zone_country_id` = ? AND `zone_id` = ?";
	return( $gBitDb->getRow( $sql, array( $pCountryId, $pZoneId ), BIT_QUERY_CACHE_TIME ) );
}

function zen_get_zone_by_name( $pCountryId, $pZoneMixed ) {
	global $gBitDb;
	$pZoneMixed = strtoupper( $pZoneMixed );
	$sql = "SELECT * FROM " . TABLE_ZONES . " WHERE `zone_country_id` = ? AND (UPPER(`zone_name`) = ? OR `zone_code` = ?)";
	return( $gBitDb->getRow( $sql, array( $pCountryId, $pZoneMixed, $pZoneMixed), BIT_QUERY_CACHE_TIME ) );
}


////
// Returns the zone (State/Province) name given the code
// TABLES: zones
function zen_get_zone_name_by_code( $pCountryId, $pZoneCode ) {
	global $gBitDb;
	$ret = $pZoneCode;
	if( $zoneName = $gBitDb->getOne( "select `zone_name` from " . TABLE_ZONES . " where `zone_country_id` = ? and `zone_code` = ?", array( $pCountryId, $pZoneCode ), FALSE, FALSE, BIT_QUERY_CACHE_TIME ) ) {
		$ret = $zoneName;
	}
	return $ret;
}

////
// Returns the zone (State/Province) name
// TABLES: zones
	function zen_get_zone_name( $pCountryId, $pZoneId, $pDefaultZone ) {
		global $gBitDb;

		$ret = $pDefaultZone;
		if( BitBase::verifyId( $pCountryId ) && BitBase::verifyId( $pZoneId ) ) {
			$query = "SELECT `zone_name` FROM " . TABLE_ZONES . " where `zone_country_id` = ? and `zone_id` = ?";
			$ret = $gBitDb->getOne( $query, array( $pCountryId, $pZoneId ) );
		}
		return $ret;
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
	function zen_get_zone_id( $pCountryId, $pZoneMixed ) {
		global $gBitDb;

		$pZoneMixed = strtoupper( $pZoneMixed );
		return $gBitDb->getOne( "SELECT `zone_id` FROM " . TABLE_ZONES . " WHERE `zone_country_id` = ? AND (UPPER(`zone_name`=?) OR UPPER(`zone_code`)=?)", array( (int)$pCountryId, $pZoneMixed, $pZoneMixed ) );
	}


/**
 * Return a product's name.
 *
 * @param int The product id of the product who's name we want
 * @param int The language id to use. If this is not set then the current language is used
*/
	function zen_get_products_name($pProductsId, $pLanguageId = '') {
		global $gBitDb;

		if( empty( $pLanguageId ) ) {
			$pLanguageId = $_SESSION['languages_id'];
		}

		$ret = "";
		if( BitBase::verifyId( $pProductsId ) && BitBase::verifyId( $pLanguageId ) ) {
			$query = "select `products_name` from " . TABLE_PRODUCTS_DESCRIPTION . " where `products_id` = ? and `language_id` = ?";
			$ret = $gBitDb->getOne($query, array( $pProductsId, $pLanguageId ) );
		}

		return $ret;
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
	function zen_has_product_attributes_values( $pProductsId ) {
		global $gBitDb;
		$ret = false;
		if( BitBase::verifyId( $pProductsId ) ) {
			$attributes_query = "SELECT SUM(`options_values_price`) as `total`
								 FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
									INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`products_options_values_id`=pom.`products_options_values_id`) 
								 WHERE pom.`products_id` = ?";
			$ret = $gBitDb->getOne( $attributes_query, array( (int)$products_id ) );
		}
		return $ret;
	}

	function zen_get_category_name( $pCategoryId, $pLanguageId ) {
		global $gBitDb;
		$ret = '';

		if( BitBase::verifyId( $pCategoryId ) && BitBase::verifyId( $pLanguageId ) ) {
			$query = "SELECT `categories_name` FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE `categories_id` = ? and `language_id` = ?"; 
			$ret = $gBitDb->GetOne( $query, array( $pCategoryId, $pLanguageId) );
		}

		return $ret;
	}


	function zen_get_category_description( $pCategoryId, $pLanguageId ) {
		global $gBitDb;
		$ret = '';

		if( BitBase::verifyId( $pCategoryId ) && BitBase::verifyId( $pLanguageId ) ) {
			$query = "select `categories_description` from " . TABLE_CATEGORIES_DESCRIPTION . " WHERE `categories_id` = ? and `language_id` = ?"; 
			$ret = $gBitDb->GetOne( $query, array( $pCategoryId, $pLanguageId) );
		}

		return $ret;
	}

	////
	// Return a product's category
	// TABLES: products_to_categories
	function zen_get_products_category_id( $pProductsId ) {
		global $gBitDb;
		$ret = '';

		if( BitBase::verifyId( $pProductsId ) ) {
			$query = "SELECT `categories_id` FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE `products_id` = ? ORDER BY `products_id`, `categories_id`";
			$ret = $gBitDb->GetOne( $query, array( $pProductsId ) );
		}

		return $ret;
	}

////
// TABLES: categories
	function zen_get_categories_image( $pCategoryId ) {
		global $gBitDb;

		$ret = "";
		if( BitBase::verifyId( $pCategoryId ) ) {
			$query= "SELECT `categories_image` FROM " . TABLE_CATEGORIES . " WHERE `categories_id` = ?";
			$ret = $gBitDb->getOne( $query, array( $pCategoryId ) );
		}
		return $ret;
	}

////
// TABLES: categories_description
	function zen_get_categories_name( $pCategoryId ) {
		global $gBitDb;
		$ret = "";
		if( BitBase::verifyId( $pCategoryId ) ) {
			$query= "SELECT `categories_name` from " . TABLE_CATEGORIES_DESCRIPTION . " WHERE `categories_id` = ? and `language_id` = ?";
			$ret = $gBitDb->getOne( $query, array( $pCategoryId, $_SESSION['languages_id'] ) );
		}
		return $ret;
	}

////
// Return a product's manufacturer's name
// TABLES: products, manufacturers
	function zen_get_products_manufacturers_name( $pProductsId ) {
		global $gBitDb;

		$ret = NULL;
		if( BitBase::verifyId( $pProductsId ) ) {
			$query = "SELECT m.`manufacturers_name` from " . TABLE_PRODUCTS . " p, " .  TABLE_MANUFACTURERS . " m where p.`products_id` = ? AND p.`manufacturers_id` = m.`manufacturers_id`";
			$ret = $gBitDb->getOne( $query, array( $pProductsId ) );
		}
		return $ret;
	}

////
// Return a product's manufacturer's image
// TABLES: products, manufacturers
	function zen_get_products_manufacturers_image( $pProductsId ) {
		global $gBitDb;

		$ret = NULL;
		if( BitBase::verifyId( $pProductsId ) ) {
			$query = "SELECT m.`manufacturers_image` FROM " . TABLE_PRODUCTS . " p, " .  TABLE_MANUFACTURERS . " m WHERE p.`products_id` = ? AND p.`manufacturers_id` = m.`manufacturers_id`";
			$ret = $gBitDb->getOne( $query, array( $pProductsId ) );
		}
		return $ret;
  }


////
// return attributes products_options_sort_order - PRODUCTS_ATTRIBUTES
function zen_get_attributes_sort_order($products_id, $options_id, $options_values_id) {
	global $gBitDb;
	$check = $gBitDb->getOne("SELECT `products_options_sort_order`
								FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
									INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id`) 
								WHERE `products_id` = ? AND `products_options_id` = ? AND pa.`products_options_values_id` = ?", array( $products_id, $options_id, $options_values_id ), FALSE, FALSE, BIT_QUERY_CACHE_TIME );

	return $check;
}

////
// return attributes products_options_sort_order - PRODUCTS_OPTIONS
  function zen_get_attributes_options_sort_order($products_id, $options_id, $options_values_id) {
    global $gBitDb;
      $sortOrder = $gBitDb->getOne("SELECT `products_options_sort_order` from " . TABLE_PRODUCTS_OPTIONS . " where `products_options_id` = ?", array( $options_id ), FALSE, FALSE, BIT_QUERY_CACHE_TIME );
      $attSortOrder = $gBitDb->getOne("SELECT `products_id`, `products_options_id`, pa.`products_options_values_id`, `products_options_sort_order`
                             FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
							  	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )
                             WHERE `products_id` = ?  and `products_options_id` = ? and pa.`products_options_values_id` = ?", array( $products_id, $options_id, $options_values_id ), FALSE, FALSE, BIT_QUERY_CACHE_TIME );
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
			$check_attributes = $gBitDb->query( $query, array( $product_id, str_replace('txt_', '', $option), '0' ) );
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
			if( $check_attributes = $gBitDb->query( $query, array( (int)$product_id, (int)$option, (int)$value ) ) ) {
				// display only cannot be selected
				if( !empty( $check_attributes->fields ) && $check_attributes->fields['attributes_display_only'] == '1') {
					$check_valid = false;
				}
			}

		}

		return $check_valid;
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

	function zen_get_products_description( $pProductsId, $pLanguageId = '' ) {
		global $gBitDb;

		if( empty( $pLanguageId ) ) {
			$pLanguageId = $_SESSION['languages_id'];
		}

		$ret = "";
		if( BitBase::verifyId( $pProductsId ) && BitBase::verifyId( $pLanguageId ) ) {
			$query = "SELECT `products_description` FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ? AND `language_id` = ?";
			$ret = $gBitDb->getOne($query, array( $pProductsId, $pLanguageId ) );
		}

		return $ret;
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
// zen_get_show_product_switch($_GET['products_id'], 'metatags_products_name_status')
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

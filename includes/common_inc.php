<?php

// {{{ -=-=-=-=-=-=-=-=-= OUTPUT FUNCITONS

/**
 * Returns a string with conversions for security.
 *
 * Simply calls the zen_ouput_string function
 * with parameters that run htmlspecialchars over the string
 * and converts quotes to html entities
 *
 * @param string The string to be parsed
*/
  function zen_output_string_protected($string) {
    return zen_output_string($string, false, true);
  }


/**
 * Returns a string with conversions for security.
 * @param string The string to be parsed
 * @param string contains a string to be translated, otherwise just quote is translated
 * @param boolean Do we run htmlspecialchars over the string
*/
  function zen_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string);
    } else {
      if ($translate == false) {
        return zen_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return zen_parse_input_field_data($string, $translate);
      }
    }
  }

/**
 * Returns a string with conversions for security.
 *
 * @param string The string to be parsed
*/

  function zen_sanitize_string($string) {
    $string = preg_replace('/ +/', ' ', $string);
    return preg_replace("/[<>]/", '_', $string);
  }


////
  function zen_html_entity_decode($given_html, $quote_style = ENT_QUOTES) {
    $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
    $trans_table['&#39;'] = "'";
    return ( strtr( $given_html, $trans_table ) );
  }

////
//CLR 030228 Add function zen_decode_specialchars
// Decode string encoded with htmlspecialchars()
  function zen_decode_specialchars($string){
    $string=str_replace('&gt;', '>', $string);
    $string=str_replace('&lt;', '<', $string);
    $string=str_replace('&#039;', "'", $string);
    $string=str_replace('&quot;', "\"", $string);
    $string=str_replace('&amp;', '&', $string);

    return $string;
  }

////
// remove common HTML from text for display as paragraph
  function zen_clean_html($clean_it) {

    $clean_it = preg_replace('/\r/', ' ', $clean_it);
    $clean_it = preg_replace('/\t/', ' ', $clean_it);
    $clean_it = preg_replace('/\n/', ' ', $clean_it);

    $clean_it= nl2br($clean_it);

// update breaks with a space for text displays in all listings with descriptions
    while (strstr($clean_it, '<br>')) $clean_it = str_replace('<br>', ' ', $clean_it);
    while (strstr($clean_it, '<br />')) $clean_it = str_replace('<br />', ' ', $clean_it);
    while (strstr($clean_it, '<br/>')) $clean_it = str_replace('<br/>', ' ', $clean_it);
    while (strstr($clean_it, '<p>')) $clean_it = str_replace('<p>', ' ', $clean_it);
    while (strstr($clean_it, '</p>')) $clean_it = str_replace('</p>', ' ', $clean_it);

// temporary fix more for reviews than anything else
    while (strstr($clean_it, '<span class="smallText">')) $clean_it = str_replace('<span class="smallText">', ' ', $clean_it);
    while (strstr($clean_it, '</span>')) $clean_it = str_replace('</span>', ' ', $clean_it);

    while (strstr($clean_it, '  ')) $clean_it = str_replace('  ', ' ', $clean_it);

// remove other html code to prevent problems on display of text
    $clean_it = strip_tags($clean_it);
    return $clean_it;
  }


////
// The HTML href link wrapper function
  function zen_href_link($page = '', $parameters = '', $connection = 'SSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
    global $gBitSystem, $request_type, $session_started, $http_domain, $https_domain;

	$link = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];

    if ($use_dir_ws_catalog) $link .= DIR_WS_CATALOG;

	if( !empty( $page ) ) {
		$page = $page;
	}

    if (!$static) {
      $link .= $page;
      if (zen_not_null($parameters)) {
        $link .= '?' . zen_output_string($parameters);
      }
    } else {
      if (zen_not_null($parameters)) {
        $link .= $page . "&" . zen_output_string($parameters);
      } else {
        $link .= $page;
      }
    }

    $separator = '&';

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (!defined( 'SESSION_FORCE_COOKIE_USE' ) || SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (defined('SID') && zen_not_null(SID)) {
        $sid = SID;
//      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL_ADMIN == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if ($http_domain != $https_domain) {
          $sid = session_name() . '=' . session_id();
        }
      }
    }

// clean up the link before processing
    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

    if ( 0 &&( $gBitSystem->isFeatureActive( 'pretty_urls' )) && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('&amp;', '/', $link);
      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);

      $separator = '?';
    }

    if (isset($sid)) {
      $link .= $separator . $sid;
    }

// clean up the link after processing
    while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

    $link = preg_replace('/&/', '&amp;', $link);

    return $link;
  }



/**
 * Parse the data used in the html tags to ensure the tags will not break.
 * Basically just an extension to the php strstr function
 * @param string The string to be parsed
 * @param string The needle to find
*/
// Parse the data used in the html tags to ensure the tags will not break
  function zen_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

////
// Output a form input field
  function zen_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true, $required = false) {
    $field = '<input  class="form-control" type="' . zen_output_string($type) . '" name="' . zen_output_string($name) . '"';

    if (isset($GLOBALS[$name]) && ($reinsert_value == true) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Output a form filefield
  function zen_draw_file_field($name, $required = false) {
    return zen_draw_input_field($name, '', ' size="50" ', 'file', true, $required );
  }

////
// Output a form hidden field
  function zen_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . zen_output_string($name) . '"';
    if( !empty( $value ) ) {
      $field .= ' value="' . zen_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (zen_not_null($parameters)) {
		$field .= ' ' . $parameters;
	}

    $field .= ' />';

    return $field;
  }

////
// Output a form password field
  function zen_draw_password_field($name, $value = '', $required = false) {
    return zen_draw_input_field($name, $value, 'maxlength="40"', 'password', false, $required);
  }


////
// Output a form pull down menu
  function zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false, $blank = false ) {
    $field = '<select class="form-control" name="' . zen_output_string($name) . '"';

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';
	if( $blank ) {
	    $field .= '<option value=""></option>';
	}

    if( empty($default) && isset($GLOBALS[$name] ) && is_string( $GLOBALS[$name] ) ) {
		$default = stripslashes( $GLOBALS[$name] );
	}

    foreach( array_keys( $values ) as $i ) {
      $field .= '<option value="' . zen_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . zen_output_string( strip_tags( $values[$i]['text'] ), array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;') ) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }


// function to return field type
// uses $tbl = table name, $fld = field name

  function zen_field_type($tbl, $fld) {
    global $gBitDb;
    $rs = $gBitDb->MetaColumns($tbl);
    $type = $rs[strtoupper($fld)]->type;
    return $type;
  }

// function to return field length
// uses $tbl = table name, $fld = field name
  function zen_field_length($tbl, $fld) {
    global $gBitDb;
	$length = NULL;
    if( $rs = $gBitDb->MetaColumns($tbl) ) {
	    $length = $rs[strtoupper($fld)]->max_length;
	}
    return $length;
  }

////
// return the size and maxlength settings in the form size="blah" maxlength="blah" based on maximum size being 70
// uses $tbl = table name, $fld = field name
// example: zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name')
  function zen_set_field_length($tbl, $fld, $max=70) {
    $field_length= zen_field_length($tbl, $fld);
    switch (true) {
      case ($field_length > $max):
        $length= 'size = "' . ($max+1) . '" maxlength= "' . $field_length . '"';
        break;
      default:
        $length= 'size = "' . ($field_length+1) . '" maxlength = "' . $field_length . '"';
        break;
    }
    return $length;
  }


////
// Output a function button in the selected language
	function zen_image_button($image, $alt = '', $parameters = '') {
		return '<span class="btn btn-default btn-sm">'.$alt.'</span>';
	}


////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function zen_image_submit($image, $alt = '', $parameters = '') {
	return '<input type="submit" class="btn btn-primary btn-sm" name="'.$alt.'" value="'.$alt.'" />';
  }

  function bit_get_images_dir( $pDir ) {
	if( !is_dir( $pDir ) ) {
		mkdir_p( $pDir );
	}
	$ret = is_writeable( $pDir ) ? dir( $pDir ) : NULL;
    return( $ret );
  }



// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
  function zen_date_long($raw_date) {
    if ( ($raw_date == '0001-01-01 00:00:00') || empty( $raw_date ) ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour,$minute,$second,$month,$day,$year));
  }


////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function zen_date_short($raw_date) {
    if ( ($raw_date == '0001-01-01 00:00:00') || empty($raw_date) ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

// error on 1969 only allows for leap year
    if ($year != 1969 && @date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return preg_replace( '/2037$/', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }

// }}}
// {{{ -=-=-=-=-=-=-=-=-= DATABASE FUNCTIONS





////
  function zen_db_input($string) {
    return addslashes($string);
  }

  function zen_db_insert_id( $pTableName, $pIdColumn ) {
  	global $gBitDb;
  	return( $gBitDb->GetOne( "SELECT MAX(`$pIdColumn`) FROM $pTableName" ) );
  }

  function zen_db_offset_date( $pDays, $pColumn=NULL ) {
  	global $gBitDb;
  	return( $gBitDb->OffsetDate( $pDays, $pColumn ) );
  }


////
  function zen_db_output($string) {
    return htmlspecialchars($string);
  }


////
  function zen_db_prepare_input($string) {
	global $gBitUser;
  	if( empty( $string ) ) {
		return NULL;
    } elseif (is_string($string) && !$gBitUser->hasPermission( 'p_bitcommerce_admin' ) ) {
      return trim(zen_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = zen_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }


// }}}
// {{{ -=-=-=-=-=-=-=-=-= LANGUAGES FUNCTIONS


function zen_get_languages() {
	global $gBitDb;
	return $gBitDb->getAll( "SELECT `languages_id` AS `id`, `name`, `code`, `image`, `directory` FROM " . TABLE_LANGUAGES . " ORDER BY `sort_order`", FALSE, 3600 );
}

// }}}
// {{{ -=-=-=-=-=-=-=-=-= CUSTOMER FUNCITONS




////
// Return a formatted address
// TABLES: address_format
function zen_address_format( $address, $html, $boln, $eoln, $pAddressPrefix='') {
	global $gBitDb;

	$company = zen_output_string_protected($address[$pAddressPrefix.'company']);
	if ( !empty( $address[$pAddressPrefix.'firstname'] ) ) {
		$firstname = zen_output_string_protected($address[$pAddressPrefix.'firstname']);
		$lastname = zen_output_string_protected($address[$pAddressPrefix.'lastname']);
	} elseif( !empty( $address[$pAddressPrefix.'name'] ) ) {
		$firstname = zen_output_string_protected($address[$pAddressPrefix.'name']);
		$lastname = '';
	} else {
		$firstname = '';
		$lastname = '';
	}

	$street = zen_output_string_protected($address[$pAddressPrefix.'street_address']);
	$suburb = zen_output_string_protected($address[$pAddressPrefix.'suburb']);
	$city = zen_output_string_protected($address[$pAddressPrefix.'city']);
	$state = zen_output_string_protected($address[$pAddressPrefix.'state']);
	$telephone = (isset( $address[$pAddressPrefix.'telephone'] ) ? zen_output_string_protected($address[$pAddressPrefix.'telephone']) : NULL);
	if ( !empty( $address[$pAddressPrefix.'country_id'] ) ) {
		$country = zen_get_country_name($address[$pAddressPrefix.'country_id']);

		if ( !empty( $address[$pAddressPrefix.'zone_id'] ) ) {
			$state = zen_get_zone_code($address[$pAddressPrefix.'country_id'], $address[$pAddressPrefix.'zone_id'], $state);
		}
	} elseif( !empty( $address[$pAddressPrefix.'countries_name'])) {
		$country = zen_output_string_protected($address[$pAddressPrefix.'countries_name']);
	} elseif( !empty( $address[$pAddressPrefix.'country'])) {
		$country = zen_output_string_protected($address[$pAddressPrefix.'country']);
	} else {
		$country = '';
	}
	$postcode = zen_output_string_protected($address[$pAddressPrefix.'postcode']);
	$zip = $postcode;

	if ($html) {
// HTML Mode
		$HR = '<hr />';
		$hr = '<hr />';
		if ( empty( $boln ) && ($eoln == "\n") ) { // Values not specified, use rational defaults
			$CR = '<br />';
			$cr = '<br />';
			$eoln = $cr;
		} else { // Use values supplied
			$CR = $eoln . $boln;
			$cr = $CR;
		}
	} else {
// Text Mode
		$CR = $eoln;
		$cr = $CR;
		$HR = '----------------------------------------';
		$hr = '----------------------------------------';
	}

	$statecomma = '';
	$streets = $street;
	if ($suburb != '') $streets = $street . $cr . $suburb;
	if ($state != '') $statecomma = $state . ', ';

	$lookupColumn = 'caf.`address_format_id`';
	$lookupValue = 2;
	if( $formatId = (int)BitBase::getParameter( $address, $pAddressPrefix.'format_id' ) ) {
		$lookupValue = $formatId;
	} elseif( $countryLookup = BitBase::getParameter( $address, $pAddressPrefix.'countries_name', BitBase::getParameter( $address, $pAddressPrefix.'country' ) ) ) {
		if( strlen( $countryLookup ) == 2 ) {
			$lookupColumn = 'ccou.`countries_iso_code_2`';
			$lookupValue = strtoupper( $countryLookup );
		} elseif( strlen( $countryLookup ) == 3 ) {
			$lookupColumn = 'ccou.`countries_iso_code_3`';
			$lookupValue = strtoupper( $countryLookup );
		} else {
			$lookupColumn = 'UPPER( ccou.`countries_name` )';
			$lookupValue = strtoupper( $countryLookup );
		}
	}

	$addressFormat = $gBitDb->getOne( "SELECT `address_format` FROM " . TABLE_ADDRESS_FORMAT . " caf INNER JOIN " . TABLE_COUNTRIES . " ccou ON ( caf.`address_format_id` = ccou.`address_format_id` ) WHERE $lookupColumn = ?", array( $lookupValue ) );

	eval("\$address_out = \"$addressFormat\";");
	if( !empty( $telephone ) ) {
		$address_out .= $cr . $telephone;
	}

	if ( (ACCOUNT_COMPANY == 'true') && (zen_not_null($company)) ) {
	$address_out = preg_replace( "`$cr`", "$cr$company$cr", $address_out, 1 );
	}

	return $address_out;
}

// }}}

// {{{ -=-=-=-=-=-=-=-=-= PRICING FUNCITONS

////
// computes products_price + option groups lowest attributes price of each group when on
	function zen_get_products_base_price( $pProductsId ) {
		static $sBasePriceCache = array();
		if( empty( $sBasePriceCache[$pProductsId] ) ) {
			if( $product = bc_get_commerce_product( $pProductsId ) ) {
				$sBasePriceCache[$pProductsId] = $product->getBasePrice();
			}
		}

		return $sBasePriceCache[$pProductsId];
	}


////
// Is the product priced by attributes?
  function zen_get_products_price_is_priced_by_attributes($products_id) {
    global $gBitDb;
    $product_check = $gBitDb->getOne("select `products_priced_by_attribute` from " . TABLE_PRODUCTS .
			" where `products_id` = ?", array( zen_get_prid( $products_id ) ) );
    return( $product_check == '1' );
  }
 /* MOVED TO CommerceProduct:: 
////
// Return a product's minimum quantity
// TABLES: products
  function zen_get_products_quantity_order_min($product_id) {
    global $gBitDb;

    $the_products_quantity_order_min = $gBitDb->query("select `products_id`, `products_quantity_order_min` from " . TABLE_PRODUCTS . " where `products_id` = ?", array( $product_id ) );
    return $the_products_quantity_order_min->fields['products_quantity_order_min'];
  }


////
// Return a product's minimum unit order
// TABLES: products
  function zen_get_products_quantity_order_units($product_id) {
    global $gBitDb;

    $the_products_quantity_order_units = $gBitDb->query( "select `products_id`, `products_quantity_order_units` from " . TABLE_PRODUCTS . " where `products_id` = ?", array( $product_id ) );
    return $the_products_quantity_order_units->fields['products_quantity_order_units'];
  }

////
// Return a product's maximum quantity
// TABLES: products
  function zen_get_products_quantity_order_max($product_id) {
    global $gBitDb;

    $the_products_quantity_order_max = $gBitDb->query("select `products_id`, `products_quantity_order_max` from " . TABLE_PRODUCTS . " where `products_id` = ?", array( $product_id ) );
    return $the_products_quantity_order_max->fields['products_quantity_order_max'];
  }

////
// Return quantity buy now
  function zen_get_buy_now_qty($product_id) {
    global $gBitCustomer;
    $check_min = zen_get_products_quantity_order_min($product_id);
    $check_units = zen_get_products_quantity_order_units($product_id);
    $buy_now_qty=1;
// works on Mixed ON
    switch (true) {
      case ($gBitCustomer->mCart->in_cart_mixed($product_id) == 0 ):
        if ($check_min >= $check_units) {
          $buy_now_qty = $check_min;
        } else {
          $buy_now_qty = $check_units;
        }
        break;
      case ($gBitCustomer->mCart->in_cart_mixed($product_id) < $check_min):
        $buy_now_qty = $check_min - $gBitCustomer->mCart->in_cart_mixed($product_id);
        break;
      case ($gBitCustomer->mCart->in_cart_mixed($product_id) > $check_min):
      // set to units or difference in units to balance cart
        $new_units = $check_units - fmod($gBitCustomer->mCart->in_cart_mixed($product_id), $check_units);
//echo 'Cart: ' . $gBitCustomer->mCart->in_cart_mixed($product_id) . ' Min: ' . $check_min . ' Units: ' . $check_units . ' fmod: ' . fmod($gBitCustomer->mCart->in_cart_mixed($product_id), $check_units) . '<br />';
        $buy_now_qty = ($new_units > 0 ? $new_units : $check_units);
        break;
      default:
        $buy_now_qty = $check_units;
        break;
    }
    if ($buy_now_qty <= 0) {
      $buy_now_qty = 1;
    }
    return $buy_now_qty;
  }


////
// Find quantity discount quantity mixed and not mixed
  function zen_get_products_quantity_discount_mixed($product_id, $qty) {
    global $gBitDb;
    global $gBitCustomer;

    $product_discounts = $gBitDb->query("select `products_price`, `products_quantity_mixed`, `product_is_free` from " . TABLE_PRODUCTS . " where `products_id` = '" . $product_id . "'");

    if ($product_discounts->fields['products_quantity_mixed']) {
      if ($new_qty = $gBitCustomer->mCart->count_contents_qty($product_id)) {
        $qty = $new_qty;
      }
    }
    return $qty;
  }

////
// Return a product mixed setting
// TABLES: products
  function zen_get_products_quantity_mixed($product_id) {
    global $gBitDb;

    $the_products_quantity_mixed = $gBitDb->query("select `products_id`, `products_quantity_mixed` from " . TABLE_PRODUCTS . " where `products_id` = '" . $product_id . "'");
    if ($the_products_quantity_mixed->fields['products_quantity_mixed'] == '1') {
      $look_up = true;
    } else {
      $look_up = false;
    }
    return $look_up;
  }

////
// Return a product's quantity box status
// TABLES: products
  function zen_get_products_qty_box_status($product_id) {
    global $gBitDb;

    $the_products_qty_box_status = $gBitDb->query("select `products_id`, `products_qty_box_status`  from " . TABLE_PRODUCTS . " where `products_id` = '" . (int)$product_id . "'");
    return $the_products_qty_box_status->fields['products_qty_box_status'];
  }


*/



////
// are there discount quanties
  function zen_get_discount_qty($product_id, $check_qty) {
    global $gBitDb;

    $product_id = (int)$product_id;

    $discounts_qty_query = $gBitDb->query("select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where `products_id` = '" . $product_id . "' and `discount_qty` != 0");
//echo 'zen_get_discount_qty: ' . $product_id . ' - ' . $check_qty . '<br />';
    if ($discounts_qty_query->RecordCount() > 0 and $check_qty > 0) {
      return true;
    } else {
      return false;
    }
  }

////
// salemaker categories array
  function zen_parse_salemaker_categories($clist) {
    $clist_array = explode(',', $clist);

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($clist_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($clist_array[$i], $tmp_array)) {
        $tmp_array[] = $clist_array[$i];
      }
    }
    return $tmp_array;
  }

////
// update salemaker product prices per category per product
  function zen_update_salemaker_product_prices($salemaker_id) {
    global $gBitDb;
    $zv_categories = $gBitDb->Execute("select `sale_categories_selected` from " . TABLE_SALEMAKER_SALES . " where `sale_id` = '" . $salemaker_id . "'");

    $za_salemaker_categories = zen_parse_salemaker_categories($zv_categories->fields['sale_categories_selected']);
    $n = sizeof($za_salemaker_categories);
    for ($i=0; $i<$n; $i++) {
      $update_products_price = $gBitDb->Execute("select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id`='" . $za_salemaker_categories[$i] . "'");
      while (!$update_products_price->EOF) {
        zen_update_lowest_purchase_price($update_products_price->fields['products_id']);
        $update_products_price->MoveNext();
      }
    }
  }

////
// Return a product ID from a product ID with attributes
  function zen_get_prid( $uprid ) {
	$ret = 0;
	if( !empty( $uprid ) ) {
		$pieces = explode(':', $uprid);
		$ret = $pieces[0];
	}
	return $ret;
  }

////
// Return an option ID from a option ID with value_id
  function zen_get_options_id( $pOptionsId ) {
	$ret = 0;
	if( strpos( $pOptionsId, '_' ) ) {
		$pieces = explode('_', $pOptionsId);
		$ret = $pieces[0];
	} else {
		$ret = $pOptionsId;
	}
	return $ret;
  }


////
// return attributes_price_factor
  function zen_get_attributes_price_factor($price, $special, $factor, $offset) {
    if( defined( 'ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL' ) && ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL =='1' and $special) {
      // calculate from specials_new_products_price
      $calculated_price = $special * ($factor - $offset);
    } else {
      // calculate from products_price
      $calculated_price = $price * ($factor - $offset);
    }
//    return '$price ' . $price . ' $special ' . $special . ' $factor ' . $factor . ' $offset ' . $offset;
    return $calculated_price;
  }


////
// return attributes_qty_prices or attributes_qty_prices_onetime based on qty
  function zen_get_attributes_qty_prices_onetime($string, $qty) {
      $attribute_qty = preg_split("/[:,]/" , $string);
      $size = sizeof($attribute_qty);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        $new_price = isset( $attribute_qty[$i+1] ) ? $attribute_qty[$i+1] : 0;
        if( !empty( $attribute_qty[$i] ) && $qty <= $attribute_qty[$i]) {
          $new_price = $attribute_qty[$i+1];
          break;
        }
      }
      return $new_price;
}


////
// Check specific attributes_qty_prices or attributes_qty_prices_onetime for a given quantity price
  function zen_get_attributes_quantity_price($check_what, $check_for) {
// $check_what='1:3.00,5:2.50,10:2.25,20:2.00';
// $check_for=50;
      $attribute_table_cost = preg_split("/[:,]/" , $check_what);
      $size = sizeof($attribute_table_cost);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        if ($check_for >= $attribute_table_cost[$i]) {
          $attribute_quantity_check = $attribute_table_cost[$i];
          $attribute_quantity_price = $attribute_table_cost[$i+1];
        }
      }
//          echo '<br>Cost ' . $check_for . ' - '  .  $attribute_quantity_check . ' x ' . $attribute_quantity_price;
     return $attribute_quantity_price;
  }


////
// get attributes type
  function zen_get_attributes_type($check_attribute) {
    global $gBitDb;
	$query = "SELECT po.`products_options_type` 
			  FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
				INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON( po.`products_options_id`=pa.`products_options_id` )
			  WHERE pa.`products_attributes_id` = ?";
    return $gBitDb->getOne( $query, array( $check_attribute ) );
  }



////
// get option values
// $pOptionId not used as it is a foreign key
function zen_get_option_value( $pOptionId, $pValueId ) {
	global $gBitDb;

	$ret = array();
	if( BitBase::verifyId( $pOptionId ) && BitBase::verifyId( $pValueId ) ) {
		$query = "SELECT popt.*, pa.*, pad.`products_attributes_filename`, pad.`products_attributes_maxdays`, pad.`products_attributes_maxcount`
				  FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
					INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " popt ON(pa.`products_options_id` = popt.`products_options_id`)
					LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on(pa.`products_options_values_id` = pad.`products_options_values_id`)
				  WHERE pa.`products_options_values_id` = ?  AND popt.`language_id` = ? ";
		$ret = $gBitDb->getRow( $query, array( $pValueId, (int)$_SESSION['languages_id'] ) );
	}
	return $ret;
}

////
// calculate words
  function zen_get_word_count($string, $free=0) {
    if ($string != '') {
      while (strstr($string, '  ')) $string = str_replace('  ', ' ', $string);
      $string = trim($string);
      $word_count = substr_count($string, ' ');
      return (($word_count+1) - $free);
    } else {
      // nothing to count
      return 0;
    }
  }


////
// calculate words price
  function zen_get_word_count_price($string, $free=0, $price) {
    $word_count = zen_get_word_count($string, $free);
    if ($word_count >= 1) {
      return ($word_count * $price);
    } else {
      return 0;
    }
  }


////
// calculate letters
  function zen_get_letters_count($string, $free=0) {
    while (strstr($string, '  ')) $string = str_replace('  ', ' ', $string);
    $string = trim($string);
    if (TEXT_SPACES_FREE == '1') {
      $letters_count = strlen(str_replace(' ', '', $string));
    } else {
      $letters_count = strlen($string);
    }
    if ($letters_count - $free >= 1) {
      return ($letters_count - $free);
    } else {
      return 0;
    }
  }


////
// calculate letters price
  function zen_get_letters_count_price($string, $free=0, $price) {
      $letters_price = zen_get_letters_count($string, $free) * $price;
      if ($letters_price <= 0) {
        return 0;
      } else {
        return $letters_price;
      }
  }


////
// Add tax to a products price
  function zen_add_tax($price, $tax) {
    global $currencies;

    if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true') {
      return zen_round($price, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']) + zen_calculate_tax($price, $tax);
    } else {
      return zen_round($price, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
    }
  }

// Calculates Tax rounding the result
  function zen_calculate_tax($price, $tax) {
    global $currencies;

    return zen_round($price * $tax / 100, $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']);
  }

////
// set the lowest_purchase_price
function zen_update_lowest_purchase_price($product_id) {
    if( $product = bc_get_commerce_product( array( 'products_id' => $_REQUEST['products_id'] ) ) ) {
		$product->getLowestPrice();
	}
}

////
// enable shipping
  function zen_get_shipping_enabled($shipping_module) {
    global $order;

	$ret = true;

    // for admin always true if installed
    if (strstr($_SERVER['SCRIPT_NAME'], FILENAME_MODULES)) {
      return true;
    }

//	} elseif( !empty( $order ) ) {
//		$checkCart = &$order;
	if( !empty( $gBitCustomer->mCart ) ) {
		$checkCart = &$gBitCustomer->mCart;
	}

	if( !empty( $checkCart ) && is_object( $checkCart ) ) {
		$check_cart_free = $checkCart->in_cart_check('product_is_always_free_ship','1');
		$check_cart_cnt = $checkCart->count_contents();
		$check_cart_weight = $checkCart->show_weight();


		if( ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module != 'freeshipper') ) {
			$ret = false;
		} elseif( ($check_cart_free == $check_cart_cnt) and $shipping_module != 'freeshipper' ) {
			$ret = false;
		} elseif( ($check_cart_free != $check_cart_cnt) and $shipping_module == 'freeshipper' ) {
			$ret = false;
		}
	}

	return $ret;
  }


// }}}
// {{{ -=-=-=-=-=-=-=-=-= STOREFRONT FUNCITONS





////
// Return true if the category has subcategories
// TABLES: categories
  function zen_has_category_subcategories($category_id) {
    global $gBitDb;
    $child_category_query = "select count(*) as `ccount`
                             from " . TABLE_CATEGORIES . "
                             where `parent_id` = '" . (int)$category_id . "'";

    $child_category = $gBitDb->query($child_category_query);

    if ($child_category->fields['ccount'] > 0) {
      return true;
    } else {
      return false;
    }
  }


function reset_bitcommerce_layout() {
	require_once( THEMES_PKG_CLASS_PATH.'BitThemes.php' );
	global $gBitThemes;

	$modules = array(
		'l' => array(
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_whats_new.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_manufacturers.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_reviews.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_featured.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_information.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_categories.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_commerce_information.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_banner_box.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_document_categories.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_order_history.tpl' ),
		),

		'r' => array(
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_banner_box_all.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_search.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_banner_box2.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_shopping_cart.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_best_sellers.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_manufacturer_info.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_specials.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_product_notifications.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_tell_a_friend.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_languages.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_currencies.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_whos_online.tpl' ),
		)
	);
	$i = 1;
	$gBitThemes->expungeLayout( BITCOMMERCE_PKG_NAME );
	foreach( array_keys( $modules ) as $col ) {
		foreach( $modules[$col] as $moduleHash ) {
			$moduleHash['fPackage'] = BITCOMMERCE_PKG_NAME;
			$gBitThemes->storeModule( $moduleHash );
			$moduleHash['user_id'] = ROOT_USER_ID;
			$moduleHash['pos'] = $col;
			$moduleHash['ord'] = $i++;
			$moduleHash['layout'] = BITCOMMERCE_PKG_NAME;
			$gBitThemes->storeModule( $moduleHash );
		}
		$i = 1;
	}
}

////
// find template or default file
	function zen_get_file_directory($check_directory, $check_file, $dir_only = 'false') {
		global $gCommerceSystem;

		$zv_filename = $check_file;
		if (!strstr($zv_filename, '.php')) $zv_filename .= '.php';

		if (file_exists($check_directory . $gCommerceSystem->mTemplateDir . '/' . $zv_filename)) {
			$zv_directory = $check_directory . $gCommerceSystem->mTemplateDir . '/';
		} else {
			$zv_directory = $check_directory;
		}

		if ($dir_only == 'true') {
			return $zv_directory;
		} else {
			return $zv_directory . $zv_filename;
		}
	}


/**
 * Get a link to a product or main_page, adjusting for pretty_url enabled
 *
 * @param string $pTarget A string, numeric is assumed to be a product_id, everything else is assumed to be a main_page
 */
function zen_get_page_url( $pTarget=NULL, $pParams=NULL ) {
	global $gBitSystem;

	$ret = BITCOMMERCE_PKG_URL;
	if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
		$ret .= $pTarget;
		if( !empty( $pParams ) ) {
			$ret .= '?';
		}
	} else {
		if( BitBase::verifyId( $pTarget ) ) {
			$ret .= 'index.php?products_id='.$pTarget;
		} else {
			$ret .= 'index.php?main_page='.$pTarget;
		}
		if( !empty( $pParams ) ) {
			$ret .= '&';
		}
	}
	if( is_array( $pParams ) ) {
		$ret .= implode( '&', $pParams );
	} else {
		$ret .= $pParams;
	}

	return $ret;
}

function zen_get_root_uri() {
	global $gCommerceSystem;
	return $gCommerceSystem->getConfig( 'STORE_URL', BIT_ROOT_URI );
}

function zen_get_page_uri( $pTarget=NULL, $pParams=NULL ) {
	return zen_get_root_uri().zen_get_page_url( $pTarget, $pParams );
}

////
  function zen_get_top_level_domain($url) {
    if (strpos($url, '://')) {
      $url = @parse_url($url);
      $url = $url['host'];
    }
    $domain_array = explode('.', $url);
    $domain_size = sizeof($domain_array);
    if ($domain_size > 1) {
      if (is_numeric($domain_array[$domain_size-2]) && is_numeric($domain_array[$domain_size-1])) {
        return false;
      } else {
        if ($domain_size > 3) {
          return $domain_array[$domain_size-3] . '.' . $domain_array[$domain_size-2] . '.' . $domain_array[$domain_size-1];
        } else {
          return $domain_array[$domain_size-2] . '.' . $domain_array[$domain_size-1];
        }
      }
    } else {
      return false;
    }
  }

////
// Wrapper function for round()
  function zen_round($number, $precision) {
	return round( $number, $precision );
  }


////
  function zen_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ( (is_float($value) || is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }






?>

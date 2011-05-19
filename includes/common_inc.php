<?php

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
  function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
    global $gBitSystem, $request_type, $session_started, $http_domain, $https_domain;

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == 'true') {
        $link = HTTPS_SERVER ;
      } else {
        $link = HTTP_SERVER;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><strong class="note">Error!<br /><br />Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
    }

    if ($use_dir_ws_catalog) $link .= DIR_WS_CATALOG;

	if( !empty( $page ) ) {
		$page = 'main_page='. $page . "&";
	}

    if (!$static) {
      if (zen_not_null($parameters)) {
        $link .= 'index.php?'. $page . zen_output_string($parameters);
      } else {
        $link .= 'index.php?' . $page;
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
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (defined('SID') && zen_not_null(SID)) {
        $sid = SID;
//      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL_ADMIN == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {


        if ($http_domain != $https_domain) {
          $sid = zen_session_name() . '=' . zen_session_id();
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
    $field = '<input type="' . zen_output_string($type) . '" name="' . zen_output_string($name) . '"';

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
    $field = '<select name="' . zen_output_string($name) . '"';

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';
	if( $blank ) {
	    $field .= '<option value=""></option>';
	}

    if( empty($default) && isset($GLOBALS[$name] ) && is_string( $GLOBALS[$name] ) ) {
		$default = stripslashes( $GLOBALS[$name] );
	}

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . zen_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . zen_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
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
		global $template, $current_page_base, $gBitCustomer;
		// return '<span class="button">'.$alt.'</span>';
		if( is_string( $alt ) ) {
			$ret = '<span class="button">'.$alt.'</span>';
		} elseif( $template ) {
			$ret = zen_image($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $gBitCustomer->getLanguage() . '/') . $image, $alt, '', '', $parameters);
		} else {
			$ret = zen_image(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/images/buttons/' . $image, $alt, '', '', $parameters);
		}
		return $ret;
	}


////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function zen_image_submit($image, $alt = '', $parameters = '') {
    global $template, $current_page_base, $gBitCustomer;

	if( is_string( $alt ) ) {
		$ret = '<input type="submit" class="button" name="'.$alt.'" value="'.$alt.'" />';
	} else {
		if( $template ) {
			$imgSrc = zen_output_string($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $gBitCustomer->getLanguage() . '/') . $image);
		} else {
		  $imgSrc = zen_output_string(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/images/buttons/' . $image);
		}
		$ret = '<input type="image" src="'.$imgSrc. '" alt="' . zen_output_string($alt) . '"';
	
		if (zen_not_null($alt)) { 
			$ret .= ' title=" ' . zen_output_string($alt) . ' "';
		}
	
		if (zen_not_null($parameters)) {
			$ret .= ' ' . $parameters;
		}
	
		$ret .= ' />';
	}
	
	return $ret;
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
      return ereg_replace('2037' . '$', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }



// -=-=-=-=-=-=-=-=-= DATABASE FUNCTIONS





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






// -=-=-=-=-=-=-=-=-= LANGUAGES FUNCTIONS





  function zen_get_languages() {
    global $gBitDb;
    $languages = $gBitDb->query("SELECT `languages_id`, `name`, `code`, `image`, `directory`
                               FROM " . TABLE_LANGUAGES . " ORDER BY `sort_order`");

    while (!$languages->EOF) {
      $languages_array[] = array('id' => $languages->fields['languages_id'],
                                 'name' => $languages->fields['name'],
                                 'code' => $languages->fields['code'],
                                 'image' => $languages->fields['image'],
                                 'directory' => $languages->fields['directory']);
      $languages->MoveNext();
    }

    return $languages_array;
  }








// -=-=-=-=-=-=-=-=-= CUSTOMER FUNCITONS








////
// Return a formatted address
// TABLES: address_format
  function zen_address_format($address_format_id, $address, $html, $boln, $eoln) {
    global $gBitDb;
    $address_format_query = "select `address_format` as `format`
                             from " . TABLE_ADDRESS_FORMAT . "
                             where `address_format_id` = '" . (int)$address_format_id . "'";

    $address_format = $gBitDb->query($address_format_query);
    $company = zen_output_string_protected($address['company']);
    if ( !empty( $address['firstname'] ) ) {
      $firstname = zen_output_string_protected($address['firstname']);
      $lastname = zen_output_string_protected($address['lastname']);
    } elseif( !empty( $address['name'] ) ) {
      $firstname = zen_output_string_protected($address['name']);
      $lastname = '';
    } else {
      $firstname = '';
      $lastname = '';
    }
    $street = zen_output_string_protected($address['street_address']);
    $suburb = zen_output_string_protected($address['suburb']);
    $city = zen_output_string_protected($address['city']);
    $state = zen_output_string_protected($address['state']);
    $telephone = (isset( $address['telephone'] ) ? zen_output_string_protected($address['telephone']) : NULL);
    if ( !empty( $address['country_id'] ) ) {
      $country = zen_get_country_name($address['country_id']);

      if ( !empty( $address['zone_id'] ) ) {
        $state = zen_get_zone_code($address['country_id'], $address['zone_id'], $state);
      }
    } elseif( !empty( $address['country'] ) ) {
      if (is_array($address['country'])) {
        $country = zen_output_string_protected($address['country']['countries_name']);
      } else {
      $country = zen_output_string_protected($address['country']);
      }
    } else {
      $country = '';
    }
    $postcode = zen_output_string_protected($address['postcode']);
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
    if ( empty( $country ) ) {
      if (is_array($address['country'])) {
        $country = zen_output_string_protected($address['country']['countries_name']);
      } else {
      $country = zen_output_string_protected($address['country']);
      }
    }
    if ($state != '') $statecomma = $state . ', ';

    $fmt = $address_format->fields['format'];
    eval("\$address_out = \"$fmt\";");
    if( !empty( $telephone ) ) {
    	$address_out .= $cr . $telephone;
    }

    if ( (ACCOUNT_COMPANY == 'true') && (zen_not_null($company)) ) {
      $address_out = $company . $cr . $address_out;
    }

    return $address_out;
  }






// -=-=-=-=-=-=-=-=-= PRICING FUNCITONS







////
// computes products_price + option groups lowest attributes price of each group when on
	function zen_get_products_base_price( $pProductsId ) {
		static $sBasePriceCache = array();
		if( empty( $sBasePriceCache[$pProductsId] ) ) {
			$product = bc_get_commerce_product( $pProductsId );
			$sBasePriceCache[$pProductsId] = $product->getBasePrice();
		}
bt(); die;
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
// Return a product's quantity box status
// TABLES: products
  function zen_get_products_qty_box_status($product_id) {
    global $gBitDb;

    $the_products_qty_box_status = $gBitDb->query("select `products_id`, `products_qty_box_status`  from " . TABLE_PRODUCTS . " where `products_id` = '" . (int)$product_id . "'");
    return $the_products_qty_box_status->fields['products_qty_box_status'];
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
function zen_get_option_value( $pOptionId, $pValueId ) {
	global $gBitDb;

	$query = "SELECT *
			  FROM " . TABLE_PRODUCTS_OPTIONS . " popt
				INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON(pa.`products_options_id` = popt.`products_options_id`)
				LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on(pa.`products_attributes_id` = pad.`products_attributes_id`)
			  WHERE pa.`products_options_id` = ?  AND pa.`products_options_values_id` = ?  AND popt.`language_id` = ? ";
	return( $gBitDb->getRow( $query, array( $pOptionId, $pValueId, (int)$_SESSION['languages_id'] ) ) );
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
    global $PHP_SELF, $order;

	$ret = true;

    // for admin always true if installed
    if (strstr($PHP_SELF, FILENAME_MODULES)) {
      return true;
    }

//	} elseif( !empty( $order ) ) {
//		$checkCart = &$order;
	if( !empty( $gBitCustomer->mCart ) ) {
		$checkCart = &$gBitCustomer->mCart;
	}

	if( is_object( $checkCart ) ) {
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
	require_once( THEMES_PKG_PATH.'BitThemes.php' );
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
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_music_genres.tpl' ),
			array( 'module_rsrc' => 'bitpackage:bitcommerce/mod_record_companies.tpl' ),
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
  function zen_get_top_level_domain($url) {
    if (strpos($url, '://')) {
      $url = @parse_url($url);
      $url = $url['host'];
    }
    $domain_array = explode('.', $url);
    $domain_size = sizeof($domain_array);
    if ($domain_size > 1) {
      if (SESSION_USE_FQDN == 'True') return $url;
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
    if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.')+1)) > $precision)) {
      $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

      if (substr($number, -1) >= 5) {
        if ($precision > 1) {
          $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');
        } elseif ($precision == 1) {
          $number = substr($number, 0, -1) + 0.1;
        } else {
          $number = substr($number, 0, -1) + 1;
        }
      } else {
        $number = substr($number, 0, -1);
      }
    }

    return $number;
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

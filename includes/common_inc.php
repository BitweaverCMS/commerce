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
    $string = ereg_replace(' +', ' ', $string);
    return preg_replace("/[<>]/", '_', $string);
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

    $link = ereg_replace('&', '&amp;', $link);
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

    if (zen_not_null($value)) {
      $field .= ' value="' . zen_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . zen_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (zen_not_null($parameters)) $field .= ' ' . $parameters;

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

    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

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
    $rs = $gBitDb->MetaColumns($tbl);
    $length = $rs[strtoupper($fld)]->max_length;
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
	if( $template ) {
	    return zen_image($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $gBitCustomer->getLanguage() . '/') . $image, $alt, '', '', $parameters);
	} else {
      return zen_image(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/images/buttons/' . $image, $alt, '', '', $params);
    }
  }


////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function zen_image_submit($image, $alt = '', $parameters = '') {
    global $template, $current_page_base, $gBitCustomer;

	if( $template ) {
		$imgSrc = zen_output_string($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $gBitCustomer->getLanguage() . '/') . $image);
	} else {
      $imgSrc = zen_output_string(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/images/buttons/' . $image);
    }
    $image_submit = '<input type="image" src="'.$imgSrc. '" alt="' . zen_output_string($alt) . '"';

    if (zen_not_null($alt)) $image_submit .= ' title=" ' . zen_output_string($alt) . ' "';

    if (zen_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= ' />';

    return $image_submit;
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
    if ( ($raw_date == '0001-01-01 00:00:00') || ($raw_date == '') ) return false;

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
    } elseif (is_string($string) && !$gBitUser->hasPermission( 'bit_p_commerce_admin' ) ) {
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
    $languages = $gBitDb->Execute("SELECT `languages_id`, `name`, `code`, `image`, `directory`
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
    global $db;
    $address_format_query = "select address_format as format
                             from " . TABLE_ADDRESS_FORMAT . "
                             where address_format_id = '" . (int)$address_format_id . "'";

    $address_format = $db->Execute($address_format_query);
    $company = zen_output_string_protected($address['company']);
    if (isset($address['firstname']) && zen_not_null($address['firstname'])) {
      $firstname = zen_output_string_protected($address['firstname']);
      $lastname = zen_output_string_protected($address['lastname']);
    } elseif (isset($address['name']) && zen_not_null($address['name'])) {
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
    if (isset($address['country_id']) && zen_not_null($address['country_id'])) {
      $country = zen_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && zen_not_null($address['zone_id'])) {
        $state = zen_get_zone_code($address['country_id'], $address['zone_id'], $state);
      }
    } elseif (isset($address['country']) && zen_not_null($address['country'])) {
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
      $HR = '<hr>';
      $hr = '<hr>';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
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
    if ($country == '') {
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
    	$address_out = $cr . $telephone;
    }

    if ( (ACCOUNT_COMPANY == 'true') && (zen_not_null($company)) ) {
      $address_out = $company . $cr . $address_out;
    }

    return $address_out;
  }






// -=-=-=-=-=-=-=-=-= PRICING FUNCITONS







////
// computes products_price + option groups lowest attributes price of each group when on
  function zen_get_products_base_price($products_id) {
    global $gBitDb;
      $product_check = $gBitDb->Execute("select `products_price`, `products_priced_by_attribute` from " . TABLE_PRODUCTS . " where `products_id` = '" . (int)$products_id . "'");

// is there a products_price to add to attributes
      $products_price = $product_check->fields['products_price'];

      // do not select display only attributes and attributes_price_base_inc is true
      $product_att_query = $gBitDb->Execute("select `options_id`, `price_prefix`, `options_values_price`, `attributes_display_only`, `attributes_price_base_inc` from " . TABLE_PRODUCTS_ATTRIBUTES . " where `products_id` = '" . (int)$products_id . "' and `attributes_display_only` != '1' and `attributes_price_base_inc` ='1'". " order by `options_id`, `price_prefix`, `options_values_price`");

      $the_options_id= 'x';
      $the_base_price= 0;
// add attributes price to price
      if ($product_check->fields['products_priced_by_attribute'] == '1' and $product_att_query->RecordCount() >= 1) {
        while (!$product_att_query->EOF) {
          if ( $the_options_id != $product_att_query->fields['options_id']) {
            $the_options_id = $product_att_query->fields['options_id'];
            $the_base_price += $product_att_query->fields['options_values_price'];
          }
          $product_att_query->MoveNext();
        }

        $the_base_price = $products_price + $the_base_price;
      } else {
        $the_base_price = $products_price;
      }
      return $the_base_price;
  }


////
// Display Price Retail
// Specials and Tax Included
  function zen_get_products_display_price($products_id) {
    global $gBitDb, $currencies;

// 0 = normal shopping
// 1 = Login to shop
// 2 = Can browse but no prices
    // verify display of prices
      switch (true) {
        case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        return '';
        break;
        case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
        // customer may browse but no prices
        return TEXT_LOGIN_FOR_PRICE_PRICE;
        break;
        case (CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != ''):
        // customer may browse but no prices
        return TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
        break;
        case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
        case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0'):
        // customer must be logged in to browse
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
        default:
        // proceed normally
        break;
      }

// show case only
    if (STORE_STATUS != '0') {
      if (STORE_STATUS == '1') {
        return '';
      }
    }

    // $new_fields = ', `product_is_free`, `product_is_call`, `product_is_showroom_only`';
    $product_check = $gBitDb->query( "select `products_tax_class_id`, `products_price`, `products_priced_by_attribute`, `product_is_free`, `product_is_call` from " . TABLE_PRODUCTS .
			" where `products_id` = ?", array( $products_id ) );

    $show_display_price = '';
    $display_normal_price = zen_get_products_base_price($products_id);
    $display_special_price = zen_get_products_special_price($products_id, true);
    $display_sale_price = zen_get_products_special_price($products_id, false);

    $show_sale_discount = '';
    if (SHOW_SALE_DISCOUNT_STATUS == '1' and ($display_special_price != 0 or $display_sale_price != 0)) {
      if ($display_sale_price) {
        if (SHOW_SALE_DISCOUNT == 1) {
          if ($display_normal_price != 0) {
            $show_discount_amount = number_format(100 - (($display_sale_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS);
          } else {
            $show_discount_amount = '';
          }
          $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . $show_discount_amount . PRODUCT_PRICE_DISCOUNT_PERCENTAGE . '</span>';

        } else {
          $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . $currencies->display_price(($display_normal_price - $display_sale_price), zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</span>';
        }
      } else {
        if (SHOW_SALE_DISCOUNT == 1) {
          $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . number_format(100 - (($display_special_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS) . PRODUCT_PRICE_DISCOUNT_PERCENTAGE . '</span>';
        } else {
          $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . $currencies->display_price(($display_normal_price - $display_special_price), zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</span>';
        }
      }
    }

    if ($display_special_price) {
      $show_normal_price = '<span class="normalprice">' . $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . ' </span>';
      if ($display_sale_price && $display_sale_price != $display_special_price) {
        $show_special_price = '&nbsp;' . '<span class="productSpecialPriceSale">' . $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
        if ($product_check->fields['product_is_free'] == '1') {
          $show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . '<s>' . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>' . '</span>';
        } else {
          $show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
        }
      } else {
        if ($product_check->fields['product_is_free'] == '1') {
          $show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . '<s>' . $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>' . '</span>';
        } else {
          $show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
        }
        $show_sale_price = '';
      }
    } else {
      if ($display_sale_price) {
        $show_normal_price = '<span class="normalprice">' . $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . ' </span>';
        $show_special_price = '';
        $show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
      } else {
        if ($product_check->fields['product_is_free'] == '1') {
          $show_normal_price = '<s>' . $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>';
        } else {
          $show_normal_price = $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        }
        $show_special_price = '';
        $show_sale_price = '';
      }
    }

    if ($display_normal_price == 0) {
      // don't show the $0.00
      $final_display_price = $show_special_price . $show_sale_price . $show_sale_discount;
    } else {
      $final_display_price = $show_normal_price . $show_special_price . $show_sale_price . $show_sale_discount;
    }

	$free_tag = '';
	$call_tag = '';
    // If Free, Show it
    if ($product_check->fields['product_is_free'] == '1') {
      if (OTHER_IMAGE_PRICE_IS_FREE_ON=='0') {
        $free_tag = '<br />' . PRODUCTS_PRICE_IS_FREE_TEXT;
      } else {
        $free_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_PRICE_IS_FREE, PRODUCTS_PRICE_IS_FREE_TEXT);
      }
    }

    // If Call for Price, Show it
    if ($product_check->fields['product_is_call']) {
      if (PRODUCTS_PRICE_IS_CALL_IMAGE_ON=='0') {
        $call_tag = '<br />' . PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT;
      } else {
        $call_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CALL_FOR_PRICE, PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT);
      }
    }

    return $final_display_price . $free_tag . $call_tag;
  }

////
// Is the product free?
  function zen_get_products_price_is_free($products_id) {
    global $gBitDb;
    $the_free_price = false;
	if( !empty( $products_id ) ) {
      $product_check = $gBitDb->getOne("select `product_is_free` from " . TABLE_PRODUCTS .
			" where `products_id` = ?", array( $products_id ) );
      if( $product_check == '1' ) {
        $the_free_price = true;
	  }
    }
    return $the_free_price;
  }

////
// Is the product call for price?
  function zen_get_products_price_is_call($products_id) {
    global $gBitDb;
    $the_call_price = false;
	if( !empty( $products_id ) ) {
      $product_check = $gBitDb->getOne("select `product_is_call` from " . TABLE_PRODUCTS .
			" where `products_id` = ?", array( $products_id ) );
      if ( $product_check == '1' ) {
        $the_call_price = true;
      }
    }
    return $the_call_price;
  }

////
// Is the product priced by attributes?
  function zen_get_products_price_is_priced_by_attributes($products_id) {
    global $gBitDb;
    $product_check = $gBitDb->getOne("select `products_priced_by_attribute` from " . TABLE_PRODUCTS .
			" where `products_id` = ?", array( $products_id ) );
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
    global $cart;

    $product_discounts = $gBitDb->Execute("select `products_price`, `products_quantity_mixed`, `product_is_free` from " . TABLE_PRODUCTS . " where `products_id` = '" . $product_id . "'");

    if ($product_discounts->fields['products_quantity_mixed']) {
      if ($new_qty = $_SESSION['cart']->count_contents_qty($product_id)) {
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

    $the_products_qty_box_status = $gBitDb->Execute("select `products_id`, `products_qty_box_status`  from " . TABLE_PRODUCTS . " where `products_id` = '" . (int)$product_id . "'");
    return $the_products_qty_box_status->fields['products_qty_box_status'];
  }

////
// Return a product mixed setting
// TABLES: products
  function zen_get_products_quantity_mixed($product_id) {
    global $gBitDb;

    $the_products_quantity_mixed = $gBitDb->Execute("select `products_id`, `products_quantity_mixed` from " . TABLE_PRODUCTS . " where `products_id` = '" . $product_id . "'");
    if ($the_products_quantity_mixed->fields['products_quantity_mixed'] == '1') {
      $look_up = true;
    } else {
      $look_up = false;
    }
    return $look_up;
  }


////
// look up discount in sale makers - attributes only can have discounts if set as percentages
// this gets the discount amount this does not determin when to apply the discount
  function zen_get_products_sale_discount_type($product_id = false, $categories_id = false, $return_value = false) {
    global $currencies;
    global $gBitDb;

/*

0 = flat amount off base price with a special
1 = Percentage off base price with a special
2 = New Price with a special

5 = No Sale or Skip Products with Special

special options + option * 10
0 = Ignore special and apply to Price
1 = Skip Products with Specials switch to 5
2 = Apply to Special Price

If a special exist * 10+9

0*100 + 0*10 = flat apply to price = 0 or 9
0*100 + 1*10 = flat skip Specials = 5 or 59
0*100 + 2*10 = flat apply to special = 20 or 209

1*100 + 0*10 = Percentage apply to price = 100 or 1009
1*100 + 1*10 = Percentage skip Specials = 110 or 1109 / 5 or 59
1*100 + 2*10 = Percentage apply to special = 120 or 1209

2*100 + 0*10 = New Price apply to price = 200 or 2009
2*100 + 1*10 = New Price skip Specials = 210 or 2109 / 5 or 59
2*100 + 2*10 = New Price apply to Special = 220 or 2209

*/

// get products category
    if ($categories_id == true) {
      $check_category = $categories_id;
    } else {
      $check_category = zen_get_products_category_id($product_id);
    }

    $deduction_type_array = array(array('id' => '0', 'text' => tra( 'Deduct amount' )),
                                  array('id' => '1', 'text' => tra( 'Percent' )),
                                  array('id' => '2', 'text' => tra( 'New Price' )));

    $sale_exists = 'false';
    $sale_maker_discount = '';
    $sale_maker_special_condition = '';
    $salemaker_sales = $gBitDb->Execute("select `sale_id`, `sale_status`, `sale_name`, `sale_categories_all`, `sale_deduction_value`, `sale_deduction_type`, `sale_pricerange_from`, `sale_pricerange_to`, `sale_specials_condition`, `sale_categories_selected`, `sale_date_start`, `sale_date_end`, `sale_date_added`, `sale_date_last_modified`, `sale_date_status_change` from " . TABLE_SALEMAKER_SALES . " where `sale_status`='1'");
    while (!$salemaker_sales->EOF) {
      $categories = explode(',', $salemaker_sales->fields['sale_categories_all']);
  	  while (list($key,$value) = each($categories)) {
	      if ($value == $check_category) {
          $sale_exists = 'true';
  	      $sale_maker_discount = $salemaker_sales->fields['sale_deduction_value'];
  	      $sale_maker_special_condition = $salemaker_sales->fields['sale_specials_condition'];
	        $sale_maker_discount_type = $salemaker_sales->fields['sale_deduction_type'];
	        break;
        }
      }
      $salemaker_sales->MoveNext();
    }

    $check_special = zen_get_products_special_price($product_id, true);

    if ($sale_exists == 'true' and $sale_maker_special_condition != 0) {
      $sale_maker_discount_type = (($sale_maker_discount_type * 100) + ($sale_maker_special_condition * 10));
    } else {
      $sale_maker_discount_type = 5;
    }

    if (!$check_special) {
      // do nothing
    } else {
      $sale_maker_discount_type = ($sale_maker_discount_type * 10) + 9;
    }

    switch (true) {
      case (!$return_value):
        return $sale_maker_discount_type;
        break;
      case ($return_value == 'amount'):
        return $sale_maker_discount;
        break;
      default:
        return 'Unknown Request';
        break;
    }
  }


////
// Return quantity buy now
  function zen_get_buy_now_qty($product_id) {
    global $cart;
    $check_min = zen_get_products_quantity_order_min($product_id);
    $check_units = zen_get_products_quantity_order_units($product_id);
    $buy_now_qty=1;
// works on Mixed ON
    switch (true) {
      case ($_SESSION['cart']->in_cart_mixed($product_id) == 0 ):
        if ($check_min >= $check_units) {
          $buy_now_qty = $check_min;
        } else {
          $buy_now_qty = $check_units;
        }
        break;
      case ($_SESSION['cart']->in_cart_mixed($product_id) < $check_min):
        $buy_now_qty = $check_min - $_SESSION['cart']->in_cart_mixed($product_id);
        break;
      case ($_SESSION['cart']->in_cart_mixed($product_id) > $check_min):
      // set to units or difference in units to balance cart
        $new_units = $check_units - fmod($_SESSION['cart']->in_cart_mixed($product_id), $check_units);
//echo 'Cart: ' . $_SESSION['cart']->in_cart_mixed($product_id) . ' Min: ' . $check_min . ' Units: ' . $check_units . ' fmod: ' . fmod($_SESSION['cart']->in_cart_mixed($product_id), $check_units) . '<br />';
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

    $discounts_qty_query = $gBitDb->Execute("select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where `products_id` = '" . $product_id . "' and `discount_qty` != 0");
//echo 'zen_get_discount_qty: ' . $product_id . ' - ' . $check_qty . '<br />';
    if ($discounts_qty_query->RecordCount() > 0 and $check_qty > 0) {
      return true;
    } else {
      return false;
    }
  }


////
// compute product discount to be applied to attributes or other values
  function zen_get_discount_calc($product_id, $attributes_id = false, $attributes_amount = false, $check_qty= false) {
    global $discount_type_id, $sale_maker_discount;
    global $cart;

    // no charge
    if ($attributes_id > 0 and $attributes_amount == 0) {
      return 0;
    }

    $new_products_price = zen_get_products_base_price($product_id);
    $new_special_price = zen_get_products_special_price($product_id, true);
    $new_sale_price = zen_get_products_special_price($product_id, false);

    $discount_type_id = zen_get_products_sale_discount_type($product_id);

    if ($new_products_price != 0) {
      $special_price_discount = ($new_special_price != 0 ? ($new_special_price/$new_products_price) : 1);
    } else {
      $special_price_discount = '';
    }
    $sale_maker_discount = zen_get_products_sale_discount_type($product_id, '', 'amount');

    // percentage adjustment of discount
    if (($discount_type_id == 120 or $discount_type_id == 1209) or ($discount_type_id == 110 or $discount_type_id == 1109)) {
      $sale_maker_discount = ($sale_maker_discount != 0 ? (100 - $sale_maker_discount)/100 : 1);
    }

   $qty = $check_qty;

// fix here
// BOF: percentage discounts apply to price
    switch (true) {
      case (zen_get_discount_qty($product_id, $qty) and !$attributes_id):
        // discount quanties exist and this is not an attribute
        // $this->contents[$products_id]['quantity']
        $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
//echo 'How much 1 ' . $qty . ' : ' . $attributes_amount . ' vs ' . $check_discount_qty_price . '<br />';
        return $check_discount_qty_price;
        break;

      case (zen_get_discount_qty($product_id, $qty) and zen_get_products_price_is_priced_by_attributes($product_id)):
        // discount quanties exist and this is not an attribute
        // $this->contents[$products_id]['quantity']
        $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
//echo 'How much 2 ' . $qty . ' : ' . $attributes_amount . ' vs ' . $check_discount_qty_price . '<br />';

        return $check_discount_qty_price;
        break;

      case ($discount_type_id == 5):
        // No Sale and No Special
//        $sale_maker_discount = 1;
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            if ($special_price_discount != 0) {
              $calc = ($attributes_amount * $special_price_discount);
            } else {
              $calc = $attributes_amount;
            }

            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
//echo 'How much 3 - ' . $qty . ' : ' . $product_id . ' : ' . $qty . ' x ' .  $attributes_amount . ' vs ' . $check_discount_qty_price . ' - ' . $sale_maker_discount . '<br />';
        break;
      case ($discount_type_id == 59):
        // No Sale and Special
//        $sale_maker_discount = $special_price_discount;
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discount apply to price

// BOF: percentage discounts apply to Sale
      case ($discount_type_id == 120):
        // percentage discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 1209):
        // percentage discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $calc2 = $calc - ($calc * $sale_maker_discount);
            $sale_maker_discount = $calc - $calc2;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discounts apply to Sale

// BOF: percentage discounts skip specials
      case ($discount_type_id == 110):
        // percentage discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
//            $sale_maker_discount = $sale_maker_discount;
            if ($attributes_amount != 0) {
//            $calc = ($attributes_amount * $special_price_discount);
//            $calc2 = $calc - ($calc * $sale_maker_discount);
//            $sale_maker_discount = $calc - $calc2;
              $calc = $attributes_amount - ($attributes_amount * $sale_maker_discount);
              $sale_maker_discount = $calc;
            } else {
              $sale_maker_discount = $sale_maker_discount;
            }
          }
        }
        break;
      case ($discount_type_id == 1109):
        // percentage discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//            $calc2 = $calc - ($calc * $sale_maker_discount);
//            $sale_maker_discount = $calc - $calc2;
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discounts skip specials

// BOF: flat amount discounts
      case ($discount_type_id == 20):
        // flat amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount - $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 209):
        // flat amount discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $calc2 = ($calc - $sale_maker_discount);
            $sale_maker_discount = $calc2;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: flat amount discounts

// BOF: flat amount discounts Skip Special
      case ($discount_type_id == 10):
        // flat amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount - $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 109):
        // flat amount discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = 1;
        } else {
          // compute attribute amount based on Special
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: flat amount discounts Skip Special

// BOF: New Price amount discounts
      case ($discount_type_id == 220):
        // New Price amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 2209):
        // New Price amount discount on Sale and Special with a special
        if (!$attributes_id) {
//          $sale_maker_discount = $sale_maker_discount;
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: New Price amount discounts

// BOF: New Price amount discounts - Skip Special
      case ($discount_type_id == 210):
        // New Price amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 2109):
        // New Price amount discount on Sale and Special with a special
        if (!$attributes_id) {
//          $sale_maker_discount = $sale_maker_discount;
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: New Price amount discounts - Skip Special

      case ($discount_type_id == 0 or $discount_type_id == 9):
      // flat discount
        return $sale_maker_discount;
        break;
      default:
        $sale_maker_discount = 7000;
        break;
    }

    return $sale_maker_discount;
  }


////
// Return a product ID from a product ID with attributes
  function zen_get_prid($uprid) {
    $ret = 0;
  	if( !empty( $uprid ) ) {
      $pieces = explode(':', $uprid);
	  $ret = $pieces[0];
	}
    return $ret;
  }


////
// Actual Price Retail
// Specials and Tax Included
  function zen_get_products_actual_price($products_id) {
    global $gBitDb, $currencies;
    $product_check = $gBitDb->query( "select `products_tax_class_id`, `products_price`, `products_priced_by_attribute`, `product_is_free`, `product_is_call` from " . TABLE_PRODUCTS .
			" where `products_id` = ?", array( $products_id ) );

    $show_display_price = '';
    $display_normal_price = zen_get_products_base_price($products_id);
    $display_special_price = zen_get_products_special_price($products_id, true);
    $display_sale_price = zen_get_products_special_price($products_id, false);

    $products_actual_price = $display_normal_price;

    if ($display_special_price) {
      $products_actual_price = $display_special_price;
    }
    if ($display_sale_price) {
      $products_actual_price = $display_sale_price;
    }

    // If Free, Show it
    if ($product_check->fields['product_is_free'] == '1') {
      $products_actual_price = 0;
    }

    return $products_actual_price;
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
      $attribute_qty = split("[:,]" , $string);
      $size = sizeof($attribute_qty);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        $new_price = isset( $attribute_qty[$i+1] ) ? $attribute_qty[$i+1] : 0;
        if ($qty <= $attribute_qty[$i]) {
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
      $attribute_table_cost = split("[:,]" , $check_what);
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
// attributes final price
  function zen_get_attributes_price_final($attribute, $qty = 1, $pre_selected, $include_onetime = 'false') {
    global $gBitDb;
    global $cart;

    if ($pre_selected == '' or $attribute != $pre_selected->fields["products_attributes_id"]) {
      $pre_selected = $gBitDb->Execute("select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.`products_attributes_id` = '" . $attribute . "'");
    } else {
      // use existing select
    }

	$attributes_price_final = 0;
    // normal attributes price
    if ($pre_selected->fields["price_prefix"] == '-') {
      $attributes_price_final -= $pre_selected->fields["options_values_price"];
    } else {
      $attributes_price_final += $pre_selected->fields["options_values_price"];
    }
    // qty discounts
    $attributes_price_final += zen_get_attributes_qty_prices_onetime($pre_selected->fields["attributes_qty_prices"], $qty);

    // price factor
    $display_normal_price = zen_get_products_actual_price($pre_selected->fields["products_id"]);
    $display_special_price = zen_get_products_special_price($pre_selected->fields["products_id"]);

    $attributes_price_final += zen_get_attributes_price_factor($display_normal_price, $display_special_price, $pre_selected->fields["attributes_price_factor"], $pre_selected->fields["attributes_pf_offset"]);

    // per word and letter charges
    if (zen_get_attributes_type($attribute) == PRODUCTS_OPTIONS_TYPE_TEXT) {
      // calc per word or per letter
    }

// onetime charges
    if ($include_onetime == 'true') {
      $pre_selected_onetime = $pre_selected;
      $attributes_price_final += zen_get_attributes_price_final_onetime($pre_selected->fields["products_attributes_id"], 1, $pre_selected_onetime);
    }

    return $attributes_price_final;
  }


////
// attributes final price onetime
  function zen_get_attributes_price_final_onetime($attribute, $qty= 1, $pre_selected_onetime) {
    global $gBitDb;
    global $cart;

    if ($pre_selected_onetime == '' or $attribute != $pre_selected_onetime->fields["products_attributes_id"]) {
      $pre_selected_onetime = $gBitDb->Execute("select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.`products_attributes_id` = '" . $attribute . "'");
    } else {
      // use existing select
    }

// one time charges
    // onetime charge
      $attributes_price_final_onetime += $pre_selected_onetime->fields["attributes_price_onetime"];

    // price factor
    $display_normal_price = zen_get_products_actual_price($pre_selected_onetime->fields["products_id"]);
    $display_special_price = zen_get_products_special_price($pre_selected_onetime->fields["products_id"]);

    // price factor one time
      $attributes_price_final_onetime += zen_get_attributes_price_factor($display_normal_price, $display_special_price, $pre_selected_onetime->fields["attributes_pf_onetime"], $pre_selected_onetime->fields["attributes_pf_onetime_offset"]);

    // onetime charge qty price
      $attributes_price_final_onetime += zen_get_attributes_qty_prices_onetime($pre_selected_onetime->fields["attributes_qty_prices_onetime"], 1);

      return $attributes_price_final_onetime;
    }


////
// get attributes type
  function zen_get_attributes_type($check_attribute) {
    global $gBitDb;
    $check_options_id_query = $gBitDb->query( "select `options_id` from " . TABLE_PRODUCTS_ATTRIBUTES . " where `products_attributes_id` =?", array( $check_attribute ) );
    $check_type_query = $gBitDb->query( "select `products_options_type` from " . TABLE_PRODUCTS_OPTIONS . " where `products_options_id` =?", array( $check_options_id_query->fields['options_id'] ) );
    return $check_type_query->fields['products_options_type'];
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

//get specials price or sale price
  function zen_get_products_special_price($product_id, $specials_price_only=false) {
    global $gBitDb;
    $product = $gBitDb->Execute("select `products_price`, `products_model`, `products_priced_by_attribute` from " . TABLE_PRODUCTS . " where `products_id` = '" . (int)$product_id . "'");

    if ($product->RecordCount() > 0) {
//  	  $product_price = $product->fields['products_price'];
  	  $product_price = zen_get_products_base_price($product_id);
    } else {
  	  return false;
    }

    $specials = $gBitDb->Execute("select `specials_new_products_price` from " . TABLE_SPECIALS . " where `products_id` = '" . (int)$product_id . "' and `status` ='1'");
    if ($specials->RecordCount() > 0) {
//      if ($product->fields['products_priced_by_attribute'] == 1) {
    	  $special_price = $specials->fields['specials_new_products_price'];
    } else {
  	  $special_price = false;
    }

    if(substr($product->fields['products_model'], 0, 4) == 'GIFT') {    //Never apply a salededuction to Ian Wilson's Giftvouchers
      if (zen_not_null($special_price)) {
        return $special_price;
      } else {
        return false;
      }
    }

// return special price only
    if ($specials_price_only==true) {
      if (zen_not_null($special_price)) {
        return $special_price;
      } else {
        return false;
      }
    } else {
// get sale price

// changed to use master_categories_id
//      $product_to_categories = $gBitDb->Execute("select `categories_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `products_id` = '" . (int)$product_id . "'");
//      $category = $product_to_categories->fields['categories_id'];

      $product_to_categories = $gBitDb->query("select `master_categories_id` from " . TABLE_PRODUCTS . " where `products_id`=?", array( zen_get_prid( $product_id ) ) );
      $category = $product_to_categories->fields['master_categories_id'];

      $sale = $gBitDb->query("select `sale_specials_condition`, `sale_deduction_value`, `sale_deduction_type` from " . TABLE_SALEMAKER_SALES . " where `sale_categories_all` like '%," . $category . ",%' and `sale_status` = '1' and (`sale_date_start` <= 'NOW' or `sale_date_start` = '0001-01-01') and (`sale_date_end` >= 'NOW' or `sale_date_end` = '0001-01-01') and (`sale_pricerange_from` <= ? or `sale_pricerange_from` = '0') and (`sale_pricerange_to` >= ? or `sale_pricerange_to` = '0')", array($product_price, $product_price) );
      if ($sale->RecordCount() < 1) {
         return $special_price;
      }

      if (!$special_price) {
        $tmp_special_price = $product_price;
      } else {
        $tmp_special_price = $special_price;
      }
      switch ($sale->fields['sale_deduction_type']) {
        case 0:
          $sale_product_price = $product_price - $sale->fields['sale_deduction_value'];
          $sale_special_price = $tmp_special_price - $sale->fields['sale_deduction_value'];
          break;
        case 1:
          $sale_product_price = $product_price - (($product_price * $sale->fields['sale_deduction_value']) / 100);
          $sale_special_price = $tmp_special_price - (($tmp_special_price * $sale->fields['sale_deduction_value']) / 100);
          break;
        case 2:
          $sale_product_price = $sale->fields['sale_deduction_value'];
          $sale_special_price = $sale->fields['sale_deduction_value'];
          break;
        default:
          return $special_price;
      }

      if ($sale_product_price < 0) {
        $sale_product_price = 0;
      }

      if ($sale_special_price < 0) {
        $sale_special_price = 0;
      }

      if (!$special_price) {
        return number_format($sale_product_price, 4, '.', '');
    	} else {
        switch($sale->fields['sale_specials_condition']){
          case 0:
            return number_format($sale_product_price, 4, '.', '');
            break;
          case 1:
            return number_format($special_price, 4, '.', '');
            break;
          case 2:
            return number_format($sale_special_price, 4, '.', '');
            break;
          default:
            return number_format($special_price, 4, '.', '');
        }
      }
    }
  }

////
// set the products_price_sorter
  function zen_update_products_price_sorter($product_id) {
    global $gBitDb;
    if( !($products_price_sorter = zen_get_products_actual_price($product_id) ) ) {
		$products_price_sorter = NULL;
	}
    $gBitDb->query("update " . TABLE_PRODUCTS . " set `products_price_sorter` = ? WHERE `products_id` = ?", array( $products_price_sorter, $product_id ) );
  }



////
// enable shipping
  function zen_get_shipping_enabled($shipping_module) {
    global $PHP_SELF, $cart, $order;

    // for admin always true if installed
    if (strstr($PHP_SELF, FILENAME_MODULES)) {
      return true;
    }

    $check_cart_free = $_SESSION['cart']->in_cart_check('product_is_always_free_ship','1');
    $check_cart_cnt = $_SESSION['cart']->count_contents();
    $check_cart_weight = $_SESSION['cart']->show_weight();

    switch(true) {
      // for admin always true if installed
      case (strstr($PHP_SELF, FILENAME_MODULES)):
        return true;
        break;
      // Free Shipping when 0 weight - enable freeshipper - ORDER_WEIGHT_ZERO_STATUS must be on
      case (ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module == 'freeshipper')):
        return true;
        break;
      // Free Shipping when 0 weight - disable everyone - ORDER_WEIGHT_ZERO_STATUS must be on
      case (ORDER_WEIGHT_ZERO_STATUS == '1' and ($check_cart_weight == 0 and $shipping_module != 'freeshipper')):
        return false;
        break;
      // Always free shipping only true - enable freeshipper
      case (($check_cart_free == $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return true;
        break;
      // Always free shipping only true - disable everyone
      case (($check_cart_free == $check_cart_cnt) and $shipping_module != 'freeshipper'):
        return false;
        break;
      // Always free shipping only is false - disable freeshipper
      case (($check_cart_free != $check_cart_cnt) and $shipping_module == 'freeshipper'):
        return false;
        break;
      default:
        return true;
        break;
    }
  }








////
// Return true if the category has subcategories
// TABLES: categories
  function zen_has_category_subcategories($category_id) {
    global $gBitDb;
    $child_category_query = "select count(*) as `count`
                             from " . TABLE_CATEGORIES . "
                             where `parent_id` = '" . (int)$category_id . "'";

    $child_category = $gBitDb->Execute($child_category_query);

    if ($child_category->fields['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }


function reset_bitcommerce_layout() {
	require_once( KERNEL_PKG_PATH.'mod_lib.php' );
	global $modlib;

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
	$modlib->removeAllLayoutModules( ROOT_USER_ID, BITCOMMERCE_PKG_NAME );
	foreach( array_keys( $modules ) as $col ) {
		foreach( $modules[$col] as $moduleHash ) {
			$moduleHash['fPackage'] = BITCOMMERCE_PKG_NAME;
			$modlib->storeModule( $moduleHash );
			$moduleHash['user_id'] = ROOT_USER_ID;
			$moduleHash['pos'] = $col;
			$moduleHash['ord'] = $i++;
			$moduleHash['layout'] = BITCOMMERCE_PKG_NAME;
			$modlib->storeLayout( $moduleHash );
		}
		$i = 1;
	}
}


////
  function zen_get_top_level_domain($url) {
    if (strpos($url, '://')) {
      $url = parse_url($url);
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
      if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }






?>

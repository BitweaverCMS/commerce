<?php
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
// Output a form password field
  function zen_draw_password_field($name, $value = '', $required = false) {
    return zen_draw_input_field($name, $value, 'maxlength="40"', 'password', false, $required);
  }



// function to return field type
// uses $tbl = table name, $fld = field name

  function zen_field_type($tbl, $fld) {
    global $db;
    $rs = $db->MetaColumns($tbl);
    $type = $rs[strtoupper($fld)]->type;
    return $type;
  }

// function to return field length
// uses $tbl = table name, $fld = field name
  function zen_field_length($tbl, $fld) {
    global $db;
    $rs = $db->MetaColumns($tbl);
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
    global $template, $current_page_base, $gBitLanguage;
	if( $template ) {
	    return zen_image($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $gBitLanguage->getLanguage() . '/') . $image, $alt, '', '', $parameters);
	} else {
      return zen_image(DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/images/buttons/' . $image, $alt, '', '', $params);
    }
  }


////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function zen_image_submit($image, $alt = '', $parameters = '') {
    global $template, $current_page_base, $gBitLanguage;

	if( $template ) {
		$imgSrc = zen_output_string($template->get_template_dir($image, DIR_WS_TEMPLATE, $current_page_base, 'buttons/' . $gBitLanguage->getLanguage() . '/') . $image);
	} else {
      $imgSrc = zen_output_string(DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/images/buttons/' . $image);
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
////
  function zen_db_input($string) {
    return addslashes($string);
  }

  function zen_db_insert_id( $pTableName, $pIdColumn ) {
  	global $db;
  	return( $db->GetOne( "SELECT MAX(`$pIdColumn`) FROM $pTableName" ) );
  }

  function zen_db_offset_date( $pDays, $pColumn=NULL ) {
  	global $db;
  	return( $db->OffsetDate( $pDays, $pColumn ) );
  }


// -=-=-=-=-=-=-=-=-= PRICING FUNCITONS
//get specials price or sale price
  function zen_get_products_special_price($product_id, $specials_price_only=false) {
    global $db;
    $product = $db->Execute("select products_price, products_model, products_priced_by_attribute from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");

    if ($product->RecordCount() > 0) {
//  	  $product_price = $product->fields['products_price'];
  	  $product_price = zen_get_products_base_price($product_id);
    } else {
  	  return false;
    }

    $specials = $db->Execute("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status='1'");
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
//      $product_to_categories = $db->Execute("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
//      $category = $product_to_categories->fields['categories_id'];

      $product_to_categories = $db->Execute("select master_categories_id from " . TABLE_PRODUCTS . " where products_id = '" . $product_id . "'");
      $category = $product_to_categories->fields['master_categories_id'];

      $sale = $db->query("select sale_specials_condition, sale_deduction_value, sale_deduction_type from " . TABLE_SALEMAKER_SALES . " where sale_categories_all like '%," . $category . ",%' and sale_status = '1' and (sale_date_start <= now() or sale_date_start = '0001-01-01') and (sale_date_end >= now() or sale_date_end = '0001-01-01') and (sale_pricerange_from <= ? or sale_pricerange_from = '0') and (sale_pricerange_to >= ? or sale_pricerange_to = '0')", array($product_price, $product_price) );
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

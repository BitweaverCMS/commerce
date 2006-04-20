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
// $Id: main_template_vars_attributes.php,v 1.17 2006/04/20 03:46:17 spiderr Exp $
//
//////////////////////////////////////////////////
//// BOF: attributes
//////////////////////////////////////////////////
// limit to 1 for larger tables

if ( $gBitProduct->loadAttributes() ) {
	$productSettings['zv_display_select_option'] = 0;
	$productSettings['show_attributes_qty_prices_description'] = 'false';
	$productSettings['show_onetime_charges_description'] = 'false';
	if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
		$options_order_by= ' ORDER BY popt.`products_options_sort_order`';
	} else {
		$options_order_by= ' ORDER BY popt.`products_options_name`';
	}

	$discount_type = zen_get_products_sale_discount_type((int)$_GET['products_id']);
	$discount_amount = zen_get_discount_calc((int)$_GET['products_id']);
	// iii 030813 added: initialize $number_of_uploads
	$number_of_uploads = 0;

	foreach ( array_keys( $gBitProduct->mOptions ) as $optionsId ) {
        $products_options_array = array();

/*
                          pa.options_values_price, pa.price_prefix,
                          pa.products_options_sort_order, pa.product_attribute_is_free, pa.products_attributes_wt, pa.products_attributes_wt_pfix,
                          pa.attributes_default, pa.attributes_discounted, pa.attributes_image
*/

        $products_options_value_id = '';
        $products_options_details = '';
        $products_options_details_noname = '';
        $tmp_radio = '';
        $tmp_checkbox = '';
        $tmp_html = '';
        $selected_attribute = false;

        $tmp_attributes_image = '';
        $tmp_attributes_image_row = 0;
        $productSettings['show_attributes_qty_prices_icon'] = 'false';
        foreach ( array_keys( $gBitProduct->mOptions[$optionsId]['values'] ) as $valId ) {
        	$vals = &$gBitProduct->mOptions[$optionsId]['values'][$valId];
			// reset
			$products_options_display_price='';
			$new_attributes_price= '';
			$price_onetime = '';

			$products_options_array[] = array('id' => $vals['products_options_values_id'],
												'text' => $vals['products_options_values_name']);

			if (((CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == '') or (STORE_STATUS == '1')) or (CUSTOMERS_APPROVAL_AUTHORIZATION >= 2 and $_SESSION['customers_authorization'] == '')) {
				$new_attributes_price = '';
				$new_options_values_price = 0;
				$products_options_display_price = '';
				$price_onetime = '';
			} else {
				// collect price information if it exists
				if ($vals['attributes_discounted'] == 1) {
					// apply product discount to attributes if discount is on
					//              $new_attributes_price = $vals['options_values_price'];
					$new_attributes_price = zen_get_attributes_price_final( $vals["products_attributes_id"], 1, '', 'false' );
					$new_attributes_price = zen_get_discount_calc((int)$_GET['products_id'], true, $new_attributes_price);
				} else {
					// discount is off do not apply
					$new_attributes_price = $vals['options_values_price'];
				}

				// reverse negative values for display
				if ($new_attributes_price < 0) {
					$new_attributes_price = -$new_attributes_price;
				}

				if( $vals['attributes_price_onetime'] != 0 || $vals['attributes_pf_onetime'] != 0) {
					$productSettings['show_onetime_charges_description'] = 'true';
					$new_onetime_charges = zen_get_attributes_price_final_onetime( $vals["products_attributes_id"], 1, '');
					$price_onetime = TEXT_ONETIME_CHARGE_SYMBOL . $currencies->display_price($new_onetime_charges,
					zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id']));
				} else {
					$price_onetime = '';
				}

				if ( !empty( $vals['attributes_qty_prices'] ) || !empty( $vals['attributes_qty_prices_onetime'] ) ) {
					$productSettings['show_attributes_qty_prices_description'] = 'true';
					$productSettings['show_attributes_qty_prices_icon'] = 'true';
				}

				if ( !empty( $vals['options_values_price'] ) && (empty( $vals['product_attribute_is_free'] ) && !$gBitProduct->isFree() ) ) {
					// show sale maker discount if a percentage
					$products_options_display_price= ' (' . $vals['price_prefix'] .
					$currencies->display_price($new_attributes_price,
					zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id'])) . ') ';
				} else {
					// if product_is_free and product_attribute_is_free
					if ( $vals['product_attribute_is_free'] == '1' && !$gBitProduct->isFree() ) {
						$products_options_display_price= TEXT_ATTRIBUTES_PRICE_WAS . $vals['price_prefix'] .
						$currencies->display_price($new_attributes_price,
						zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id'])) . TEXT_ATTRIBUTE_IS_FREE;
					} else {
						// normal price
						if ($new_attributes_price == 0) {
							$products_options_display_price= '';
						} else {
							$products_options_display_price= ' (' . $vals['price_prefix'] .
							$currencies->display_price($new_attributes_price,
							zen_get_tax_rate( $gBitProduct->mInfo['products_tax_class_id'] ) ) . ') ';
						}
					}
				}
				$products_options_display_price .= $price_onetime;
			} // approve
			$products_options_array[sizeof($products_options_array)-1]['text'] .= $products_options_display_price;

	// collect weight information if it exists
			if ((SHOW_PRODUCT_INFO_WEIGHT_ATTRIBUTES=='1' && !empty( $vals['products_attributes_wt'] ) )) {
				$products_options_display_weight = ' (' . $vals['products_attributes_wt_pfix'] . round( $vals['products_attributes_wt'], 2 ) . TEXT_PRODUCT_WEIGHT_UNIT . ')';
				$products_options_array[sizeof($products_options_array)-1]['text'] .= $products_options_display_weight;
			} else {
				// reset
				$products_options_display_weight='';
			}

	// prepare product options details
			$prod_id = $_GET['products_id'];
	//die($prod_id);
			if ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE or $gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT or $gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_CHECKBOX or $gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_RADIO or count( $gBitProduct->mOptions[$optionsId] ) == 1 or $gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_READONLY) {
				$products_options_value_id = $vals['products_options_values_id'];
				if ($gBitProduct->mOptions[$optionsId]['products_options_type'] != PRODUCTS_OPTIONS_TYPE_TEXT and $gBitProduct->mOptions[$optionsId]['products_options_type'] != PRODUCTS_OPTIONS_TYPE_FILE) {
					$products_options_details = $vals['products_options_values_name'];
				} else {
					// don't show option value name on TEXT or filename
					$products_options_details = '';
				}
				if ($gBitProduct->mOptions[$optionsId]['products_options_images_style'] >= 3) {
					$products_options_details .= $products_options_display_price . (!empty( $vals['products_attributes_wt'] ) ? '<br />' . $products_options_display_weight : '');
					$products_options_details_noname = $products_options_display_price . (!empty( $vals['products_attributes_wt'] ) ? '<br />' . $products_options_display_weight : '');
				} else {
					$products_options_details .= $products_options_display_price . (!empty( $vals['products_attributes_wt'] ) ? '&nbsp;' . $products_options_display_weight : '');
					$products_options_details_noname = $products_options_display_price . (!empty( $vals['products_attributes_wt'] ) ? '&nbsp;' . $products_options_display_weight : '');
				}
			}

	// radio buttons
			if ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_RADIO) {
				if ($_SESSION['cart']->in_cart($prod_id)) {
					if ($_SESSION['cart']->contents[$prod_id]['attributes'][$gBitProduct->mOptions[$optionsId]['products_options_id']] == $vals['products_options_values_id']) {
						$selected_attribute = $_SESSION['cart']->contents[$prod_id]['attributes'][$gBitProduct->mOptions[$optionsId]['products_options_id']];
					} else {
						$selected_attribute = false;
					}
				} else {
					// $selected_attribute = ($vals['attributes_default']=='1' ? true : false);
					// if an error, set to customer setting
					if ($_POST['id'] !='') {
						$selected_attribute= false;
						reset($_POST['id']);
						while(list($key,$value) = each($_POST['id'])) {
							if (($key == $gBitProduct->mOptions[$optionsId]['products_options_id'] and $value == $vals['products_options_values_id'])) {
								// zen_get_products_name($_POST['products_id']) .
								$selected_attribute = true;
								break;
							}
						}
					} else {
						$selected_attribute = $vals['attributes_default'] == '1';
					}
				}

				switch ($gBitProduct->mOptions[$optionsId]['products_options_images_style']) {
				case '1':
				$tmp_radio .= zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, $selected_attribute) . (!empty( $vals['attributes_image'] ) ? zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') . '&nbsp;' : '') . $products_options_details . '<br />';
				break;
				case '2':
				$tmp_radio .= zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, $selected_attribute) . $products_options_details .
								($vals['attributes_image'] != '' ? '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') : '') . '<br />';
				break;

				case '3':
					$tmp_attributes_image_row++;

	//                  if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
					if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
						$tmp_attributes_image .= '</tr><tr>';
						$tmp_attributes_image_row = 1;
					}

					if ($vals['attributes_image'] != '') {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, $selected_attribute) . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . $products_options_details_noname . '</td>';
					} else {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, $selected_attribute) . '<br />' . $vals['products_options_values_name'] . $products_options_details_noname . '</td>';
					}
				break;

				case '4':
					$tmp_attributes_image_row++;

	//                  if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
					if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
						$tmp_attributes_image .= '</tr><tr>';
						$tmp_attributes_image_row = 1;
					}

					if ($vals['attributes_image'] != '') {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '<br />' . zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
									$products_options_value_id, $selected_attribute) . '</td>';
					} else {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . $vals['products_options_values_name'] . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '<br />' . zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
									$products_options_value_id, $selected_attribute) . '</td>';
					}
				break;

				case '5':
					$tmp_attributes_image_row++;

	//                  if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
					if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
						$tmp_attributes_image .= '</tr><tr>';
						$tmp_attributes_image_row = 1;
					}

					if ($vals['attributes_image'] != '') {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, $selected_attribute) . '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '</td>';
					} else {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, $selected_attribute) . '<br />' . $vals['products_options_values_name'] . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '</td>';
					}
					break;
				case '0':
				default:
					$tmp_radio .= zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']', $products_options_value_id, $selected_attribute) . $products_options_details . '<br />';
					break;
				}
			}

	// checkboxes
			if ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_CHECKBOX) {
				$string = $gBitProduct->mOptions[$optionsId]['products_options_id'].'_chk'.$vals['products_options_values_id'];
				if ($_SESSION['cart']->in_cart($prod_id)) {
					if ($_SESSION['cart']->contents[$prod_id]['attributes'][$string] == $vals['products_options_values_id']) {
						$selected_attribute = true;
					} else {
						$selected_attribute = false;
					}
				} else {
	//              $selected_attribute = ($vals['attributes_default']=='1' ? true : false);
					// if an error, set to customer setting
					if( !empty( $_POST['id'] ) ) {
						$selected_attribute= false;
						reset($_POST['id']);
						while(list($key,$value) = each($_POST['id'])) {
						if (is_array($value)) {
							while(list($kkey,$vvalue) = each($value)) {
							if (($key == $gBitProduct->mOptions[$optionsId]['products_options_id'] and $vvalue == $vals['products_options_values_id'])) {
								$selected_attribute = true;
								break;
							}
							}
						} else {
							if (($key == $gBitProduct->mOptions[$optionsId]['products_options_id'] and $value == $vals['products_options_values_id'])) {
						// zen_get_products_name($_POST['products_id']) .
							$selected_attribute = true;
							break;
							}
						}
						}
					} else {
						$selected_attribute = ($vals['attributes_default']=='1' ? true : false);
					}
				}

	/*
				$tmp_checkbox .= zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']',
									$products_options_value_id, $selected_attribute) . $products_options_details .'<br />';
	*/
				switch ($gBitProduct->mOptions[$optionsId]['products_options_images_style']) {
				  case '1':
					$tmp_checkbox .= zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute) . ($vals['attributes_image'] != '' ? zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') . '&nbsp;' : '') . $products_options_details . '<br />';
					break;
				  case '2':
					$tmp_checkbox .= zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute) . $products_options_details .
								($vals['attributes_image'] != '' ? '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') : '') . '<br />';
					break;
				  case '3':
					$tmp_attributes_image_row++;

//                  if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
					if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
						$tmp_attributes_image .= '</tr><tr>';
						$tmp_attributes_image_row = 1;
					}

					if ($vals['attributes_image'] != '') {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']',
									$products_options_value_id, $selected_attribute) . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . $products_options_details_noname . '</td>';
					} else {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']',
									$products_options_value_id, $selected_attribute) . '<br />' . $vals['products_options_values_name'] . $products_options_details_noname . '</td>';
					}
					break;

				  case '4':
					$tmp_attributes_image_row++;

	//                  if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
					if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
						$tmp_attributes_image .= '</tr><tr>';
						$tmp_attributes_image_row = 1;
					}

					if ($vals['attributes_image'] != '') {
						$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">'
													. zen_image(DIR_WS_IMAGES . $vals['attributes_image'])
													. (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '')
													. ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '')
													. '<br />' . zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute) . '</td>';
					} else {
						$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . $vals['products_options_values_name'] . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '<br />' . zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']',
									$products_options_value_id, $selected_attribute) . '</td>';
					}
					break;

				  case '5':
					$tmp_attributes_image_row++;

	//                  if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
					if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
						$tmp_attributes_image .= '</tr><tr>';
						$tmp_attributes_image_row = 1;
					}

					if ($vals['attributes_image'] != '') {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']',
									$products_options_value_id, $selected_attribute) . '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '</td>';
					} else {
					$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']',
									$products_options_value_id, $selected_attribute) . '<br />' . $vals['products_options_values_name'] . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '</td>';
					}
					break;
				  case '0':
				  default:
					$tmp_checkbox .= zen_draw_checkbox_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute) . $products_options_details .'<br />';
					break;
				}
			}


	// text
			if (($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT)) {
				//CLR 030714 Add logic for text option
	//            $products_attribs_query = zen_db_query("select distinct patrib.options_values_price, patrib.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.`products_id`='" . (int)$_GET['products_id'] . "' and patrib.options_id = '" . $products_options_name['products_options_id'] . "'");
	//            $products_attribs_array = zen_db_fetch_array($products_attribs_query);
				if ( !empty( $_POST['id'] ) ) {
					reset($_POST['id']);
					while(list($key,$value) = each($_POST['id'])) {
	//echo ereg_replace('txt_', '', $key) . '#';
	//print_r($_POST['id']);
	//echo $gBitProduct->mOptions[$optionsId]['products_options_id'].'|';
	//echo $value.'|';
	//echo $vals['products_options_values_id'].'#';
						if ((ereg_replace('txt_', '', $key) == $gBitProduct->mOptions[$optionsId]['products_options_id'])) {
		//                  if ((ereg_replace('txt_', '', $key) == $gBitProduct->mOptions[$optionsId]['products_options_id'] and $value == $vals['products_options_values_id'])) {
							$tmp_html = '<input type="text" name ="id[' . TEXT_PREFIX . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']" size="' . $gBitProduct->mOptions[$optionsId]['products_options_size'] .'" maxlength="' . $gBitProduct->mOptions[$optionsId]['products_options_length'] . '" value="' . stripslashes($value) .'" />  ';
							$tmp_html .= $products_options_details;
							break;
						}
					}

				} else {
					$tmp_value = $_SESSION['cart']->contents[$_GET['products_id']]['attributes_values'][$gBitProduct->mOptions[$optionsId]['products_options_id']];
					$tmp_html = '<input type="text" name ="id[' . TEXT_PREFIX . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']" size="' . $gBitProduct->mOptions[$optionsId]['products_options_size'] .'" maxlength="' . $gBitProduct->mOptions[$optionsId]['products_options_length'] . '" value="' . htmlspecialchars($tmp_value) .'" />  ';
					$tmp_html .= $products_options_details;
					$tmp_word_cnt_string = '';
		// calculate word charges
					$tmp_word_cnt =0;
					$tmp_word_cnt_string = $_SESSION['cart']->contents[$_GET['products_id']]['attributes_values'][$gBitProduct->mOptions[$optionsId]['products_options_id']];
					$tmp_word_cnt = zen_get_word_count($tmp_word_cnt_string, $vals['attributes_price_words_free']);
					$tmp_word_price = zen_get_word_count_price($tmp_word_cnt_string, $vals['attributes_price_words_free'], $vals['attributes_price_words']);

					if ($vals['attributes_price_words'] != 0) {
						$tmp_html .= TEXT_PER_WORD . $currencies->display_price($vals['attributes_price_words'], zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id'])) . ($vals['attributes_price_words_free'] !=0 ? TEXT_WORDS_FREE . $vals['attributes_price_words_free'] : '');
					}
					if ($tmp_word_cnt != 0 and $tmp_word_price != 0) {
						$tmp_word_price = $currencies->display_price($tmp_word_price, zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id']));
						$tmp_html = $tmp_html . '<br />' . TEXT_CHARGES_WORD . ' ' . $tmp_word_cnt . ' = ' . $tmp_word_price;
					}
		// calculate letter charges
					$tmp_letters_cnt =0;
					$tmp_letters_cnt_string = $_SESSION['cart']->contents[$_GET['products_id']]['attributes_values'][$gBitProduct->mOptions[$optionsId]['products_options_id']];
					$tmp_letters_cnt = zen_get_letters_count($tmp_letters_cnt_string, $vals['attributes_price_letters_free']);
					$tmp_letters_price = zen_get_letters_count_price($tmp_letters_cnt_string, $vals['attributes_price_letters_free'], $vals['attributes_price_letters']);

					if ($vals['attributes_price_letters'] != 0) {
						$tmp_html .= TEXT_PER_LETTER . $currencies->display_price($vals['attributes_price_letters'], zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id'])) . ($vals['attributes_price_letters_free'] !=0 ? TEXT_LETTERS_FREE . $vals['attributes_price_letters_free'] : '');
					}
					if ($tmp_letters_cnt != 0 and $tmp_letters_price != 0) {
						$tmp_letters_price = $currencies->display_price($tmp_letters_price, zen_get_tax_rate($gBitProduct->mInfo['products_tax_class_id']));
						$tmp_html = $tmp_html . '<br />' . TEXT_CHARGES_LETTERS . ' ' . $tmp_letters_cnt . ' = ' . $tmp_letters_price;
					}

				}
			}

	// file uploads

	// iii 030813 added: support for file fields
			if ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE) {
				$number_of_uploads++;
	// $cart->contents[$_GET['products_id']]['attributes_values'][$products_options_name['products_options_id']]
				$tmp_html = '<input type="file" name="id[' . TEXT_PREFIX . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']" /><br />' .
							$_SESSION['cart']->contents[$prod_id]['attributes_values'][$gBitProduct->mOptions[$optionsId]['products_options_id']] .
							zen_draw_hidden_field(UPLOAD_PREFIX . $number_of_uploads, $gBitProduct->mOptions[$optionsId]['products_options_id']) .
							zen_draw_hidden_field(TEXT_PREFIX . UPLOAD_PREFIX . $number_of_uploads, $_SESSION['cart']->contents[$prod_id]['attributes_values'][$gBitProduct->mOptions[$optionsId]['products_options_id']]);
				$tmp_html  .= $products_options_details;
			}


	// collect attribute image if it exists and to draw in table below
			if ($gBitProduct->mOptions[$optionsId]['products_options_images_style'] == '0' or ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE or $gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT or $gBitProduct->mOptions[$optionsId]['products_options_type'] == '0') ) {
				if ($vals['attributes_image'] != '') {
				$tmp_attributes_image_row++;

	//              if ($tmp_attributes_image_row > PRODUCTS_IMAGES_ATTRIBUTES_PER_ROW) {
				if ($tmp_attributes_image_row > $gBitProduct->mOptions[$optionsId]['products_options_images_per_row']) {
					$tmp_attributes_image .= '</tr><tr>';
					$tmp_attributes_image_row = 1;
				}

				$tmp_attributes_image .= '<td class="smallText" align="center">' . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . '</td>';
				}
			}

	// Read Only - just for display purposes
			if ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_READONLY) {
	//            $tmp_html .= '<input type="hidden" name ="id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']"' . '" value="' . stripslashes($vals['products_options_values_name']) . ' SELECTED' . '" />  ' . $vals['products_options_values_name'];
				$tmp_html .= $products_options_details . '<br />';
			} else {
				$productSettings['zv_display_select_option']++;
			}


				// default
				// find default attribute if set to for default dropdown
				if ($vals['attributes_default']=='1') {
					$selected_attribute = $vals['products_options_values_id'];
				}
			}

			switch (true) {
			// text
			case ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT):
				if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
				$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $gBitProduct->mOptions[$optionsId]['products_options_name'];
				} else {
				$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				}
				$productOptions[$optionsId]['menu'] = $tmp_html;
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ($gBitProduct->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
			break;
			// checkbox
			case ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_CHECKBOX):
				if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
					$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $gBitProduct->mOptions[$optionsId]['products_options_name'];
				} else {
					$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				}
				$productOptions[$optionsId]['menu'] = $tmp_checkbox;
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ($gBitProduct->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
			break;
			// radio buttons
			case ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_RADIO):
				if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
				$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $gBitProduct->mOptions[$optionsId]['products_options_name'];
				} else {
				$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				}
				$productOptions[$optionsId]['menu'] = $tmp_radio;
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ($gBitProduct->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
			break;
			// file upload
			case ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE):
				if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
				$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $gBitProduct->mOptions[$optionsId]['products_options_name'];
				} else {
				$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				}
				$productOptions[$optionsId]['menu'] = $tmp_html;
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ($gBitProduct->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
			break;
			// READONLY
			case ($gBitProduct->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_READONLY):
				$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				$productOptions[$optionsId]['menu'] = $tmp_html;
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ($gBitProduct->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
			break;
			// dropdownmenu auto switch to selected radio button display
			case ( count( $gBitProduct->mOptions[$optionsId] ) == 1):
				if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
				$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $gBitProduct->mOptions[$optionsId]['products_options_name'];
				} else {
				$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				}
				$productOptions[$optionsId]['menu'] = zen_draw_radio_field('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
								$products_options_value_id, 'selected') . $products_options_details;
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ($gBitProduct->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
			break;
			default:
				// normal dropdown menu display
				if (isset($_SESSION['cart']->contents[$prod_id]['attributes'][$gBitProduct->mOptions[$optionsId]['products_options_id']])) {
				$selected_attribute = $_SESSION['cart']->contents[$prod_id]['attributes'][$gBitProduct->mOptions[$optionsId]['products_options_id']];
				} else {
				// selected set above
	//                echo 'Type ' . $gBitProduct->mOptions[$optionsId]['products_options_type'] . '<br />';
				}

				if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
				$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $gBitProduct->mOptions[$optionsId]['products_options_name'];
				} else {
				$productOptions[$optionsId]['name'] = $gBitProduct->mOptions[$optionsId]['products_options_name'];
				}


				$productOptions[$optionsId]['menu'] = zen_draw_pull_down_menu('id[' . $gBitProduct->mOptions[$optionsId]['products_options_id'] . ']',
									$products_options_array, $selected_attribute);
				$productOptions[$optionsId]['comment'] = $gBitProduct->mOptions[$optionsId]['products_options_comment'];
				$productOptions[$optionsId]['comment_position'] = ( !empty( $gBitProduct->mOptions[$optionsId]['products_options_comment_position'] ) ? '1' : '0');
			break;
			}
			// attributes images table
			$productOptions[$optionsId]['attributes_image'] = $tmp_attributes_image;
		}
      // manage filename uploads
      $_GET['number_of_uploads'] = $number_of_uploads;
//      zen_draw_hidden_field('number_of_uploads', $_GET['number_of_uploads']);
      zen_draw_hidden_field('number_of_uploads', $number_of_uploads);
      $gBitSmarty->assign( 'productSettings', $productSettings );
      $gBitSmarty->assign( 'productOptions', $productOptions );
    }

//////////////////////////////////////////////////
//// EOF: attributes
//////////////////////////////////////////////////

?>

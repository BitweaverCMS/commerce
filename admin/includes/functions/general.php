<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
//	$Id$
//

////
// Redirect to another page or site
	function zen_redirect($url) {
		global $logger;
// clean up URL before executing it
		while (strstr($url, '&&')) $url = str_replace('&&', '&', $url);
		while (strstr($url, '&amp;&amp;')) $url = str_replace('&amp;&amp;', '&amp;', $url);
		// header locates should not have the &amp; in the address it breaks things
		while (strstr($url, '&amp;')) $url = str_replace('&amp;', '&', $url);

		header('Location: ' . $url);

		if (STORE_PAGE_PARSE_TIME == 'true') {
			if (!is_object($logger)) $logger = new logger;
			$logger->timer_stop();
		}

		exit;
	}

	function zen_customers_name($customers_id) {
		global $gBitDb;
		$customers_values = $gBitDb->Execute("SELECT `customers_firstname`,` customers_lastname`
															 FROM " . TABLE_CUSTOMERS . "
															 WHERE `customers_id` = '" . (int)$customers_id . "'");

		return $customers_values->fields['customers_firstname'] . ' ' . $customers_values->fields['customers_lastname'];
	}

/*
	function zen_get_path($current_category_id = '') {
		global $cPath_array, $gBitDb;
// set to 0 if Top Level
		if ($current_category_id == '') {
			if (empty($cPath_array)) {
				$cPath_new= '';
			} else {
				$cPath_new = implode('_', $cPath_array);
			}
		} else {
			if (sizeof($cPath_array) == 0) {
				$cPath_new = $current_category_id;
			} else {
				$cPath_new = '';
				$last_category = $gBitDb->Execute("SELECT `parent_id`
																			 FROM " . TABLE_CATEGORIES . "
																			 WHERE `categories_id` = '" . (int)$cPath_array[(sizeof($cPath_array)-1)] . "'");

				$current_category = $gBitDb->Execute("SELECT `parent_id`
																					FROM " . TABLE_CATEGORIES . "
																					 WHERE `categories_id` = '" . (int)$current_category_id . "'");

				if ($last_category->fields['parent_id'] == $current_category->fields['parent_id']) {
					for ($i = 0, $n = sizeof($cPath_array) - 1; $i < $n; $i++) {
						$cPath_new .= '_' . $cPath_array[$i];
					}
				} else {
					for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {
						$cPath_new .= '_' . $cPath_array[$i];
					}
				}

				$cPath_new .= '_' . $current_category_id;

				if (substr($cPath_new, 0, 1) == '_') {
					$cPath_new = substr($cPath_new, 1);
				}
			}
		}

		return 'cPath=' . $cPath_new;
	}
*/

	function zen_get_all_get_params($exclude_array = '') {
		global $_GET;

		if ($exclude_array == '') $exclude_array = array();

		$get_url = '';

		reset($_GET);
		while (list($key, $value) = each($_GET)) {
			if (($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array))) $get_url .= $key . '=' . $value . '&';
		}

		return $get_url;
	}


	function zen_datetime_short($raw_datetime) {
		if ( ($raw_datetime == '0001-01-01 00:00:00') || ($raw_datetime == '') ) return false;

		$year = (int)substr($raw_datetime, 0, 4);
		$month = (int)substr($raw_datetime, 5, 2);
		$day = (int)substr($raw_datetime, 8, 2);
		$hour = (int)substr($raw_datetime, 11, 2);
		$minute = (int)substr($raw_datetime, 14, 2);
		$second = (int)substr($raw_datetime, 17, 2);

		return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
	}


	function zen_get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $category_has_products = false, $limit = false) {
		global $gBitDb;

		if ($limit) {
			$limit_count = 1;
		} else {
			$limit_count = 100;
		}

		if (!is_array($category_tree_array)) $category_tree_array = array();
		if ( (sizeof($category_tree_array) < 1) && ($exclude != '0') ) $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

		if ($include_itself) {
			$category = $gBitDb->Execute("SELECT cd.`categories_name`
																FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd
																WHERE cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
																and cd.`categories_id` = '" . (int)$parent_id . "'");

			$category_tree_array[] = array('id' => $parent_id, 'text' => $category->fields['categories_name']);
		}

		$categories = $gBitDb->Execute("SELECT c.`categories_id`, cd.`categories_name`, c.`parent_id`
																FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
																WHERE c.`categories_id` = cd.`categories_id`
																and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
																and c.`parent_id` = '" . (int)$parent_id . "'
																ORDER BY c.`sort_order`, cd.`categories_name`");

		while (!$categories->EOF) {
			if ($category_has_products == true and zen_products_in_category_count($categories->fields['categories_id'], '', false, true) >= 1) {
				$mark = '*';
			} else {
				$mark = '&nbsp;&nbsp;';
			}
			if ($exclude != $categories->fields['categories_id']) $category_tree_array[] = array('id' => $categories->fields['categories_id'], 'text' => $spacing . $categories->fields['categories_name'] . $mark);
			$category_tree_array = zen_get_category_tree($categories->fields['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, '', $category_has_products);
			$categories->MoveNext();
		}

		return $category_tree_array;
	}

/*
////
// products with name, model and price pulldown
	function zen_draw_products_pull_down($name, $parameters = '', $exclude = '', $show_id = false, $set_selected = false, $show_model = false, $show_current_category = false) {
		global $currencies, $gBitDb, $current_category_id;

		if ($exclude == '') {
			$exclude = array();
		}

		$select_string = '<SELECT name="' . $name . '"';

		if ($parameters) {
			$select_string .= ' ' . $parameters;
		}

		$select_string .= '>';

		if ($show_current_category) {
// only show $current_categories_id
			$products = $gBitDb->Execute("SELECT p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_model`, ptc.`categories_id`
																FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON( p.`products_id` = pd.`products_id` )
																	LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc on ptc.`products_id` = p.`products_id`
																WHERE pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
																and ptc.`categories_id` = '" . $current_category_id . "'
																ORDER BY `products_name`");
		} else {
			$products = $gBitDb->Execute("SELECT p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_model`
																FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
																WHERE p.`products_id` = pd.`products_id`
																and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
																ORDER BY `products_name`");
		}

		while (!$products->EOF) {
			if (!in_array($products->fields['products_id'], $exclude)) {
				$display_price = zen_get_products_base_price($products->fields['products_id']);
				$select_string .= '<option value="' . $products->fields['products_id'] . '"';
				if ($set_selected == $products->fields['products_id']) $select_string .= ' SELECTED';
				$select_string .= '>' . $products->fields['products_name'] . ' (' . $currencies->format($display_price) . ')' . ($show_model ? ' [' . $products->fields['products_model'] . '] ' : '') . ($show_id ? ' - ID# ' . $products->fields['products_id'] : '') . '</option>';
			}
			$products->MoveNext();
		}

		$select_string .= '</select>';

		return $select_string;
	}
*/

	function zen_info_image($image, $alt, $width = '', $height = '') {
		if (zen_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image)) ) {
			$image = zen_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height);
		} else {
			$image = tra( 'Image does not exist' );
		}

		return $image;
	}


	function zen_break_string($string, $len, $break_char = '-') {
		$l = 0;
		$output = '';
		for ($i=0, $n=strlen($string); $i<$n; $i++) {
			$char = substr($string, $i, 1);
			if ($char != ' ') {
				$l++;
			} else {
				$l = 0;
			}
			if ($l > $len) {
				$l = 1;
				$output .= $break_char;
			}
			$output .= $char;
		}

		return $output;
	}


	function zen_get_country_name_cfg() {
		global $gBitDb;
		$country = $gBitDb->Execute("SELECT `countries_name`
														 FROM " . TABLE_COUNTRIES . "
														 WHERE `countries_id` = '" . (int)$country_id . "'");

		if ($country->RecordCount() < 1) {
			return $country_id;
		} else {
			return $country->fields['countries_name'];
		}
	}


	function zen_browser_detect($component) {

		return stristr($_SERVER['HTTP_USER_AGENT'], $component);
	}


	function zen_tax_classes_pull_down($parameters, $selected = '') {
		global $gBitDb;
		$select_string = '<SELECT ' . $parameters . '>';
		$classes = $gBitDb->Execute("SELECT `tax_class_id`, `tax_class_title`
														 FROM " . TABLE_TAX_CLASS . "
														 ORDER BY `tax_class_title`");

		while (!$classes->EOF) {
			$select_string .= '<option value="' . $classes->fields['tax_class_id'] . '"';
			if ($selected == $classes->fields['tax_class_id']) $select_string .= ' SELECTED';
			$select_string .= '>' . $classes->fields['tax_class_title'] . '</option>';
			$classes->MoveNext();
		}
		$select_string .= '</select>';

		return $select_string;
	}


	function zen_geo_zones_pull_down($parameters, $selected = '') {
		global $gBitDb;
		$select_string = '<SELECT ' . $parameters . '>';
		$zones = $gBitDb->Execute("SELECT `geo_zone_id`, `geo_zone_name`
																 FROM " . TABLE_GEO_ZONES . "
																 ORDER BY `geo_zone_name`");

		while (!$zones->EOF) {
			$select_string .= '<option value="' . $zones->fields['geo_zone_id'] . '"';
			if ($selected == $zones->fields['geo_zone_id']) $select_string .= ' SELECTED';
			$select_string .= '>' . $zones->fields['geo_zone_name'] . '</option>';
			$zones->MoveNext();
		}
		$select_string .= '</select>';

		return $select_string;
	}


	function zen_get_geo_zone_name($geo_zone_id) {
		global $gBitDb;
		$zones = $gBitDb->Execute("SELECT `geo_zone_name`
													 FROM " . TABLE_GEO_ZONES . "
													 WHERE `geo_zone_id` = '" . (int)$geo_zone_id . "'");

		if ($zones->RecordCount() < 1) {
			$geo_zone_name = $geo_zone_id;
		} else {
			$geo_zone_name = $zones->fields['geo_zone_name'];
		}

		return $geo_zone_name;
	}


	function zen_get_orders_status_name($orders_status_id, $language_id = '') {
		global $gBitDb;

		if (!$language_id) $language_id = $_SESSION['languages_id'];
		$orders_status = $gBitDb->Execute("SELECT `orders_status_name`
																	 FROM " . TABLE_ORDERS_STATUS . "
																	 WHERE `orders_status_id` = '" . (int)$orders_status_id . "'
																	 and `language_id` = '" . (int)$language_id . "'");

		return $orders_status->fields['orders_status_name'];
	}


	function zen_get_orders_status() {
		global $gBitDb;

		$orders_status_array = array();
		$orders_status = $gBitDb->Execute("SELECT `orders_status_id`, `orders_status_name`
																	 FROM " . TABLE_ORDERS_STATUS . "
																	 WHERE `language_id` = '" . (int)$_SESSION['languages_id'] . "'
																	 ORDER BY `orders_status_id`");

		while (!$orders_status->EOF) {
			$orders_status_array[] = array('id' => $orders_status->fields['orders_status_id'],
																		 'text' => $orders_status->fields['orders_status_name']);
			$orders_status->MoveNext();
		}

		return $orders_status_array;
	}


	function zen_get_customers_stats( $pCustomersId ) {
		global $gBitDb;
		global $gCommerceSystem;
		$ret = array();
		if( BitBase::verifyId( $pCustomersId ) ) {	
			//  MAX(cgvc.`amount`) is a little hokie here since customer_id is PKEY and a row is hit for every order, however MAX works and keeps things clean
			$sql = "SELECT MAX(co.`date_purchased`) - MIN(co.`date_purchased`) AS `customers_age`, COUNT(co.`order_total`) AS `orders_count`, SUM(co.`order_total`) AS `customers_total`, SUM(cotgv.`orders_value`) AS `gifts_redeemed`, MAX(cgvc.`amount`) AS `gifts_balance`
					FROM " . TABLE_ORDERS . " co
						LEFT JOIN " . TABLE_ORDERS_TOTAL . " cotgv ON (cotgv.`orders_id`=co.`orders_id` AND cotgv.`class`='ot_gv')
						LEFT JOIN " . TABLE_COUPON_GV_CUSTOMER . " cgvc ON (cgvc.`customer_id`=co.`customers_id`)
					WHERE `orders_status` > 0 AND `customers_id`=?";
			if( $ret = $gBitDb->getRow( $sql, array( $pCustomersId ) ) ) {
				// get the natural log of the total divided by the natural log of the 10th root of the tier cieling.
				// This gives an exponentail number from 1 to 10, akin to Google PageRank for customers
				$ret['tier'] = log( $ret['customers_total'] ) / log( pow( $gCommerceSystem->getConfig( 'CUSTOMERS_TIER_CIELING', 100000 ), 1/10 ) );
				// if we have a space in the age, clean up trailing stuff
				if( strpos( $ret['customers_age'], ' ' ) ) {
					$ret['customers_age'] = substr( $ret['customers_age'], 0, strrpos( $ret['customers_age'], ' ' ));
				}
				$ret['commissions'] = $gBitDb->getOne( "SELECT SUM(cop.`products_quantity` * cop.`products_commission`) FROM " . TABLE_ORDERS_PRODUCTS . " cop INNER JOIN " . TABLE_PRODUCTS . " cp ON(cop.`products_id`=cp.`products_id`) INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(cp.`content_id`=lc.`content_id`) WHERE lc.`user_id`=?", array( $pCustomersId ) );
			}
			$sql = "SELECT `orders_status_name`, COUNT(`orders_id`) 
					FROM  " . TABLE_ORDERS . " co 
						INNER JOIN " . TABLE_ORDERS_STATUS . " cos ON (co.`orders_status`=cos.`orders_status_id`)
					WHERE `orders_status_id` < -10 AND `customers_id`=?
					GROUP BY `orders_status_name`";
			$ret['negative_orders'] = $gBitDb->getAssoc( $sql, array( $pCustomersId ) );
		}
		return $ret;
	}

	function zen_get_products_url($product_id, $language_id) {
		global $gBitDb;
		$product = $gBitDb->Execute("SELECT `products_url`
														 FROM " . TABLE_PRODUCTS_DESCRIPTION . "
														 WHERE `products_id` = '" . (int)$product_id . "'
														 and `language_id` = '" . (int)$language_id . "'");

		return $product->fields['products_url'];
	}


////
// Return the manufacturers URL in the needed language
// TABLES: manufacturers_info
	function zen_get_manufacturer_url($manufacturer_id, $language_id) {
		global $gBitDb;
		$manufacturer = $gBitDb->Execute("SELECT `manufacturers_url`
																	FROM " . TABLE_MANUFACTURERS_INFO . "
																	WHERE `manufacturers_id` = '" . (int)$manufacturer_id . "'
																	and `languages_id` = '" . (int)$language_id . "'");

		return $manufacturer->fields['manufacturers_url'];
	}

////
// Return the suppliers URL in the needed language
// TABLES: suppliers_info
	function zen_get_supplier_url($supplier_id, $language_id) {
		global $gBitDb;
		$supplier = $gBitDb->Execute("SELECT `suppliers_url`
																	FROM " . TABLE_SUPPLIERS_INFO . "
																	WHERE `suppliers_id` = '" . (int)$supplier_id . "'
																	and `languages_id` = '" . (int)$language_id . "'");

		return $supplier->fields['suppliers_url'];
	}


////
// Wrapper for class_exists() function
// This function is not available in all PHP versions so we test it before using it.
	function zen_class_exists($class_name) {
		if (function_exists('class_exists')) {
			return class_exists($class_name);
		} else {
			return true;
		}
	}


////
// Count how many products exist in a category
// TABLES: products, products_to_categories, categories
	function zen_products_in_category_count($categories_id, $include_deactivated = false, $include_child = true, $limit = false) {
		global $gBitDb;
		$products_count = 0;

		if ($limit) {
			$limit_count = 1;
		} else {
			$limit_count = 100;
		}

		if ($include_deactivated) {

			$products = $gBitDb->Execute("SELECT count(*) as `total`
																FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
																WHERE p.`products_id` = p2c.`products_id`
																and p2c.`categories_id` = '" . (int)$categories_id . "'", NULL, $limit_count);
		} else {
			$products = $gBitDb->Execute("SELECT count(*) as `total`
																FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
																WHERE p.`products_id` = p2c.`products_id`
																and p.`products_status` = '1'
																and p2c.`categories_id` = '" . (int)$categories_id . "'", NULL, $limit_count);

		}

		$products_count += $products->fields['total'];

		if ($include_child) {
			$childs = $gBitDb->Execute("SELECT `categories_id` FROM " . TABLE_CATEGORIES . "
															WHERE `parent_id` = '" . (int)$categories_id . "'");
			if ($childs->RecordCount() > 0 ) {
				while (!$childs->EOF) {
					$products_count += zen_products_in_category_count($childs->fields['categories_id'], $include_deactivated);
					$childs->MoveNext();
				}
			}
		}
		return $products_count;
	}


////
// Count how many subcategories exist in a category
// TABLES: categories
	function zen_childs_in_category_count($categories_id) {
		global $gBitDb;
		$categories_count = 0;

		$categories = $gBitDb->Execute("SELECT `categories_id`
																FROM " . TABLE_CATEGORIES . "
																WHERE `parent_id` = '" . (int)$categories_id . "'");

		while (!$categories->EOF) {
			$categories_count++;
			$categories_count += zen_childs_in_category_count($categories->fields['categories_id']);
			$categories->MoveNext();
		}

		return $categories_count;
	}


////
// Returns an array with countries
// TABLES: countries
	function zen_get_countries_admin($default = '') {
		global $gBitDb;
		$countries_array = array();
		if ($default) {
			$countries_array[] = array('id' => '',
																 'text' => $default);
		}
		$countries = $gBitDb->Execute("SELECT `countries_id`, `countries_name`
															 FROM " . TABLE_COUNTRIES . "
															 ORDER BY `countries_name`");

		while (!$countries->EOF) {
			$countries_array[] = array('id' => $countries->fields['countries_id'],
																 'text' => $countries->fields['countries_name']);
			$countries->MoveNext();
		}

		return $countries_array;
	}


	function zen_prepare_country_zones_pull_down($country_id = '') {
		if( $zones = zen_get_country_zones($country_id) ) {
			foreach( array_keys( $zones ) AS $key ) {
				$zones[$key]['id'] = $key;
				$zones[$key]['text'] = $zones[$key]['zone_name'];
			}
		}
		return $zones;
	}


////
// Get list of address_format_id's
	function zen_get_address_formats() {
		global $gBitDb;
		$address_format_values = $gBitDb->Execute("SELECT `address_format_id` FROM " . TABLE_ADDRESS_FORMAT . " ORDER BY `address_format_id`");

		$address_format_array = array();
		while (!$address_format_values->EOF) {
			$address_format_array[] = array('id' => $address_format_values->fields['address_format_id'], 'text' => $address_format_values->fields['address_format_id']);
			$address_format_values->MoveNext();
		}
		return $address_format_array;
	}


////
	function zen_cfg_select_coupon_id($coupon_id, $key = '') {
		global $gBitDb;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$coupons = $gBitDb->execute("SELECT cd.`coupon_name`, c.`coupon_id` FROM " . TABLE_COUPONS ." c, ". TABLE_COUPONS_DESCRIPTION . " cd WHERE cd.`coupon_id` = c.`coupon_id` and cd.`language_id` = '" . $_SESSION['languages_id'] . "'");
		$coupon_array[] = array('id' => '0', 'text' => 'None');

		while (!$coupons->EOF) {
			$coupon_array[] = array('id' => $coupons->fields['coupon_id'], 'text' => $coupons->fields['coupon_name']);
			$coupons->MoveNext();
		}
		return zen_draw_pull_down_menu($name, $coupon_array, $coupon_id);
	}


////
// Alias function for Store configuration values in the Administration Tool
	function zen_cfg_pull_down_country_list($country_id, $key = '') {
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		return zen_draw_pull_down_menu($name, zen_get_countries_admin(), $country_id);
	}


////
	function zen_cfg_pull_down_country_list_none($country_id, $key = '') {
		$country_array = zen_get_countries_admin('None');
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		return zen_draw_pull_down_menu($name, $country_array, $country_id);
	}


////
	function zen_cfg_pull_down_zone_list($zone_id, $key = '') {
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		$zones = zen_get_country_zones(STORE_COUNTRY);
		foreach( array_keys( $zones ) AS $key ) {
			$zones[$key]['id'] = $key;
			$zones[$key]['text'] = $zones[$key]['zone_name'];
		}
		return zen_draw_pull_down_menu($name, $zones, $zone_id);
	}


///
	function zen_cfg_pull_down_tax_classes($tax_class_id, $key = '') {
		global $gBitDb;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

		$tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
		$tax_class = $gBitDb->Execute("SELECT `tax_class_id`, `tax_class_title`
										 FROM " . TABLE_TAX_CLASS . "
										 ORDER BY `tax_class_title`");

		while (!$tax_class->EOF) {
			$tax_class_array[] = array('id' => $tax_class->fields['tax_class_id'], 'text' => $tax_class->fields['tax_class_title']);
			$tax_class->MoveNext();
		}

		return zen_draw_pull_down_menu($name, $tax_class_array, $tax_class_id);
	}


////
// Function to read in text area in admin
 function zen_cfg_textarea($text, $key = '') {
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		return zen_draw_textarea_field($name, false, 60, 5, $text);
	}


////
// Function to read in text area in admin
 function zen_cfg_textarea_small($text, $key = '') {
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
		return zen_draw_textarea_field($name, false, 35, 1, $text);
	}


	function zen_cfg_get_zone_name($zone_id) {
		global $gBitDb;
		$zone = $gBitDb->Execute("SELECT `zone_name`
													FROM " . TABLE_ZONES . "
													WHERE `zone_id` = '" . (int)$zone_id . "'");

		if ($zone->RecordCount() < 1) {
			return $zone_id;
		} else {
			return $zone->fields['zone_name'];
		}
	}


////
// Sets the status of a product
	function zen_set_product_status($products_id, $status) {
		global $gBitDb;
		if ($status == '1') {
			return $gBitDb->Execute("update " . TABLE_PRODUCTS . "
													 set `products_status` = '1', `products_last_modified` = ".$gBitDb->qtNOW()."
													 WHERE `products_id` = '" . (int)$products_id . "'");

		} elseif ($status == '0') {
			return $gBitDb->Execute("update " . TABLE_PRODUCTS . "
													 set `products_status` = '0', `products_last_modified` = ".$gBitDb->qtNOW()."
													 WHERE `products_id` = '" . (int)$products_id . "'");

		} else {
			return -1;
		}
	}


////
// Sets timeout for the current script.
// Cant be used in safe mode.
	function zen_set_time_limit($limit) {
		if (!get_cfg_var('safe_mode')) {
			@set_time_limit($limit);
		}
	}


////
// Alias function for Store configuration values in the Administration Tool
	function zen_cfg_select_option($select_array, $key_value, $key = '') {
		$string = '';

		for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
			$name = ((zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

			$string .= '<br><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

			if ($key_value == $select_array[$i]) $string .= ' CHECKED';

			$string .= '> ' . $select_array[$i];
		}

		return $string;
	}


	function zen_cfg_select_drop_down($select_array, $key_value, $key = '') {
		$string = '';

		$name = ((zen_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
		return zen_draw_pull_down_menu($name, $select_array, (int)$key_value);
	}

	function zen_cfg_groups_drop_down( $pValue ) {
		global $gBitUser;
		$listHash = array();
		$groupSelect = array();
		if( $groups = $gBitUser->getAllGroups( $listHash ) ) {
			$groupSelect[] = array( 'id' => NULL, 'text' => '' );
			foreach( $groups as $groupId => $groupHash ) {
				$groupSelect[] = array( 'id' => $groupId, 'text' => $groupHash['group_name'] );
			}
		}
		return zen_cfg_select_drop_down( $groupSelect, $pValue );
	}

////
// Alias function for module configuration keys
	function zen_mod_select_option($select_array, $key_name, $key_value) {
		reset($select_array);
		while (list($key, $value) = each($select_array)) {
			if (is_int($key)) $key = $value;
			$string .= '<br><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
			if ($key_value == $key) $string .= ' CHECKED';
			$string .= '> ' . $value;
		}

		return $string;
	}

////
// Retreive server information
	function zen_get_system_information() {
		global $gBitDb, $_SERVER, $gBitDbHost, $gBitDbType;

//		$gBitDb_query = $gBitDb->Execute("SELECT now() as datetime");
		list($system, $host, $kernel) = preg_split('/[\s,]+/', @exec('uname -a'), 5);

		return array('date' => zen_datetime_short(date('Y-m-d H:i:s')),
								 'system' => $system,
								 'kernel' => $kernel,
								 'host' => $host,
								 'ip' => gethostbyname($host),
								 'uptime' => (DISPLAY_SERVER_UPTIME == 'true' ? @exec('uptime') : ''),
								 'http_server' => $_SERVER['SERVER_SOFTWARE'],
								 'php' => PHP_VERSION,
								 'zend' => (function_exists('zend_version') ? zend_version() : ''),
								 'db_server' => $gBitDbHost,
								 'db_ip' => gethostbyname($gBitDbHost),
								 'db_version' => $gBitDbType,
								 'db_date' => zen_datetime_short($gBitDb_query->fields['datetime']));
	}

	function zen_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
		global $gBitDb;

		if (!is_array($categories_array)) $categories_array = array();

		if ($from == 'product') {
			$categories = $gBitDb->Execute("SELECT `categories_id`
																	FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
																	WHERE `products_id` = '" . (int)$id . "'");

			while (!$categories->EOF) {
				if ($categories->fields['categories_id'] == '0') {
					$categories_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
				} else {
					$category = $gBitDb->Execute("SELECT cd.`categories_name`, c.`parent_id`
																		FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
																		WHERE c.`categories_id` = '" . (int)$categories->fields['categories_id'] . "'
																		and c.`categories_id` = cd.`categories_id`
																		and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'");

					$categories_array[$index][] = array('id' => $categories->fields['categories_id'], 'text' => $category->fields['categories_name']);
					if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
					$categories_array[$index] = array_reverse($categories_array[$index]);
				}
				$index++;
				$categories->MoveNext();
			}
		} elseif ($from == 'category') {
			$category = $gBitDb->Execute("SELECT cd.`categories_name`, c.`parent_id`
																FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
																WHERE c.`categories_id` = '" . (int)$id . "'
																and c.`categories_id` = cd.`categories_id`
																and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'");

			$categories_array[$index][] = array('id' => $id, 'text' => $category->fields['categories_name']);
			if ( (zen_not_null($category->fields['parent_id'])) && ($category->fields['parent_id'] != '0') ) $categories_array = zen_generate_category_path($category->fields['parent_id'], 'category', $categories_array, $index);
		}

		return $categories_array;
	}

	function zen_output_generated_category_path($id, $from = 'category') {
		$calculated_category_path_string = '<ol class="breadcrumb"><li>'.TEXT_TOP.'</li>';
		$calculated_category_path = zen_generate_category_path($id, $from);
		for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
			for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
				$calculated_category_path_string .= '<li>'.$calculated_category_path[$i][$j]['text'] . '</li>';
			}
		}


		$calculated_category_path_string .= '</ol>';

		return $calculated_category_path_string;
	}

	function zen_get_generated_category_path_ids($id, $from = 'category') {
		global $gBitDb;
		$calculated_category_path_string = '';
		$calculated_category_path = zen_generate_category_path($id, $from);
		for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
			for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
				$calculated_category_path_string .= $calculated_category_path[$i][$j]['id'] . '_';
			}
			$calculated_category_path_string = substr($calculated_category_path_string, 0, -1) . '<br>';
		}
		$calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

		if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = TEXT_TOP;

		return $calculated_category_path_string;
	}

	function zen_remove_category($category_id) {
		global $gBitDb;
		$category_image = $gBitDb->Execute("SELECT `categories_image`
																		FROM " . TABLE_CATEGORIES . "
																		WHERE `categories_id` = '" . (int)$category_id . "'");

		$duplicate_image = $gBitDb->query("SELECT count(*) as `total`
																		 FROM " . TABLE_CATEGORIES . "
																		 WHERE `categories_image` = ?", array( $category_image->fields['categories_image'] ) );
		if ($duplicate_image->fields['total'] < 2) {
			if (file_exists(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image'])) {
				@unlink(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image']);
			}
		}

		$gBitDb->Execute("delete FROM " . TABLE_CATEGORIES_DESCRIPTION . "
									WHERE `categories_id` = '" . (int)$category_id . "'");

		$gBitDb->Execute("delete FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
									WHERE `categories_id` = '" . (int)$category_id . "'");

		$gBitDb->Execute("delete FROM " . TABLE_CATEGORIES . "
									WHERE `categories_id` = '" . (int)$category_id . "'");


	}

	function zen_remove_product($product_id, $ptc = 'true') {
vd( 'zen_remove_product is dead. Need to use CommerceProduct::expunge' );
bt(); die;
	}

/* TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD has been removed
	function zen_products_attributes_download_delete($product_id) {
		global $gBitDb;
	// remove downloads if they exist
		$remove_downloads= $gBitDb->Execute("SELECT `products_attributes_id` FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` = '" . $product_id . "'");
		while (!$remove_downloads->EOF) {
			$gBitDb->Execute("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE `products_attributes_id` = '" . $remove_downloads->fields['products_attributes_id'] . "'");
			$remove_downloads->MoveNext();
		}
	}
*/

	function zen_get_file_permissions($mode) {
// determine type
		if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
			$type = 's';
		} elseif ( ($mode & 0x4000) == 0x4000) { // directory
			$type = 'd';
		} elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
			$type = 'l';
		} elseif ( ($mode & 0x8000) == 0x8000) { // regular file
			$type = '-';
		} elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
			$type = 'b';
		} elseif ( ($mode & 0x2000) == 0x2000) { // character special file
			$type = 'c';
		} elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
			$type = 'p';
		} else { // unknown
			$type = '?';
		}

// determine permissions
		$owner['read']		= ($mode & 00400) ? 'r' : '-';
		$owner['write']	 = ($mode & 00200) ? 'w' : '-';
		$owner['execute'] = ($mode & 00100) ? 'x' : '-';
		$group['read']		= ($mode & 00040) ? 'r' : '-';
		$group['write']	 = ($mode & 00020) ? 'w' : '-';
		$group['execute'] = ($mode & 00010) ? 'x' : '-';
		$world['read']		= ($mode & 00004) ? 'r' : '-';
		$world['write']	 = ($mode & 00002) ? 'w' : '-';
		$world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
		if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
		if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
		if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

		return $type .
					 $owner['read'] . $owner['write'] . $owner['execute'] .
					 $group['read'] . $group['write'] . $group['execute'] .
					 $world['read'] . $world['write'] . $world['execute'];
	}

	function zen_remove($source) {
		global $messageStack, $zen_remove_error;

		if (isset($zen_remove_error)) $zen_remove_error = false;

		if (is_dir($source)) {
			$dir = dir($source);
			while ($file = $dir->read()) {
				if ( ($file != '.') && ($file != '..') ) {
					if (is_writeable($source . '/' . $file)) {
						zen_remove($source . '/' . $file);
					} else {
						$messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source . '/' . $file), 'error');
						$zen_remove_error = true;
					}
				}
			}
			$dir->close();

			if (is_writeable($source)) {
				rmdir($source);
			} else {
				$messageStack->add(sprintf(ERROR_DIRECTORY_NOT_REMOVEABLE, $source), 'error');
				$zen_remove_error = true;
			}
		} else {
			if (is_writeable($source)) {
				unlink($source);
			} else {
				$messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source), 'error');
				$zen_remove_error = true;
			}
		}
	}

//OLD FUNCTION:
	function legacy_zen_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {
		if (SEND_EMAILS != 'true') return false;

		// Instantiate a new mail object
		$message = new email(array('X-Mailer: Zen Cart Mailer'));

// bof: body of the email clean-up
// clean up &amp; and && FROM email text
		while (strstr($email_text, '&amp;&amp;')) $email_text = str_replace('&amp;&amp;', '&amp;', $email_text);
		while (strstr($email_text, '&amp;')) $email_text = str_replace('&amp;', '&', $email_text);
		while (strstr($email_text, '&&')) $email_text = str_replace('&&', '&', $email_text);

// clean up money &euro; to e
		while (strstr($email_text, '&euro;')) $email_text = str_replace('&euro;', 'e', $email_text);

// fix double quotes
		while (strstr($email_text, '&quot;')) $email_text = str_replace('&quot;', '"', $email_text);

// fix slashes
		$email_text = stripslashes($email_text);

// eof: body of the email clean-up

		// Build the text version
		$text = strip_tags($email_text);
		if (EMAIL_USE_HTML == 'true') {
			$message->add_html($email_text, $text);
		} else {
			$message->add_text($text);
		}

		// Send message
		$message->build_message();
		$message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
	}

	function zen_get_tax_class_title($tax_class_id) {
		global $gBitDb;
		if ($tax_class_id == '0') {
			return TEXT_NONE;
		} else {
			$classes = $gBitDb->Execute("SELECT `tax_class_title`
															 FROM " . TABLE_TAX_CLASS . "
															 WHERE `tax_class_id` = '" . (int)$tax_class_id . "'");

			return $classes->fields['tax_class_title'];
		}
	}

	function zen_banner_image_extension() {
		if (function_exists('imagetypes')) {
			if (imagetypes() & IMG_PNG) {
				return 'png';
			} elseif (imagetypes() & IMG_JPG) {
				return 'jpg';
			} elseif (imagetypes() & IMG_GIF) {
				return 'gif';
			}
		} elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
			return 'png';
		} elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
			return 'jpg';
		} elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
			return 'gif';
		}

		return false;
	}

////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
/*
	function zen_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
		global $gBitDb;
		global $customer_zone_id, $customer_country_id;

		if ( ($country_id == -1) && ($zone_id == -1) ) {
			if (!$_SESSION['customer_id']) {
				$country_id = STORE_COUNTRY;
				$zone_id = STORE_ZONE;
			} else {
				$country_id = $customer_country_id;
				$zone_id = $customer_zone_id;
			}
		}

		$tax = $gBitDb->Execute("SELECT SUM(tax_rate) as tax_rate
												 FROM " . TABLE_TAX_RATES . " tr
												 left join " . TABLE_ZONES_TO_GEO_ZONES . " za
												 ON tr.tax_zone_id = za.geo_zone_id
												 left join " . TABLE_GEO_ZONES . " tz ON tz.geo_zone_id = tr.tax_zone_id
												 WHERE (za.zone_country_id IS NULL
												 OR za.zone_country_id = '0'
												 OR za.zone_country_id = '" . (int)$country_id . "')
												 AND (za.zone_id IS NULL OR za.zone_id = '0'
												 OR za.zone_id = '" . (int)$zone_id . "')
												 AND tr.tax_class_id = '" . (int)$class_id . "'
												 GROUP BY tr.tax_priority");

		if ($tax->RecordCount() > 0) {
			$tax_multiplier = 0;
			while (!$tax->EOF) {
				$tax_multiplier += $tax->fields['tax_rate'];
		$tax->MoveNext();
			}
			return $tax_multiplier;
		} else {
			return 0;
		}
	}
*/


////
// Returns the tax rate for a tax class
// TABLES: tax_rates
	function zen_get_tax_rate_value($class_id) {
		global $gBitDb;
		$tax = $gBitDb->Execute("SELECT SUM(`tax_rate`) as `tax_rate`
												 FROM " . TABLE_TAX_RATES . "
												 WHERE `tax_class_id` = '" . (int)$class_id . "'
												 group by `tax_priority`");

		if ($tax->RecordCount() > 0) {
			$tax_multiplier = 0;
			while (!$tax->EOF) {
				$tax_multiplier += $tax->fields['tax_rate'];
				$tax->MoveNext();
			}
			return $tax_multiplier;
		} else {
			return 0;
		}
	}

	function zen_call_function($function, $parameter, $object = '') {
		if ($object == '') {
			return call_user_func($function, $parameter);
		} elseif (PHP_VERSION < 4) {
			return call_user_method($function, $object, $parameter);
		} else {
			return call_user_func(array($object, $function), $parameter);
		}
	}

	function zen_get_zone_class_title($zone_class_id) {
		global $gBitDb;
		if ($zone_class_id == '0') {
			return TEXT_NONE;
		} else {
			$classes = $gBitDb->Execute("SELECT `geo_zone_name`
															 FROM " . TABLE_GEO_ZONES . "
															 WHERE `geo_zone_id` = '" . (int)$zone_class_id . "'");

			return $classes->fields['geo_zone_name'];
		}
	}

////
	function zen_cfg_pull_down_zone_classes($zone_class_id, $key = '') {
		global $gBitDb;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

		$zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
		$zone_class = $gBitDb->Execute("SELECT `geo_zone_id`, `geo_zone_name`
																FROM " . TABLE_GEO_ZONES . "
																ORDER BY `geo_zone_name`");

		while (!$zone_class->EOF) {
			$zone_class_array[] = array('id' => $zone_class->fields['geo_zone_id'],
																	'text' => $zone_class->fields['geo_zone_name']);
			$zone_class->MoveNext();
		}

		return zen_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
	}


////
	function zen_cfg_pull_down_order_statuses($order_status_id, $key = '') {
		global $gBitDb;
		$name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

		$statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
		$statuses = $gBitDb->Execute("SELECT `orders_status_id`, `orders_status_name`
															FROM " . TABLE_ORDERS_STATUS . "
															WHERE `language_id` = '" . (int)$_SESSION['languages_id'] . "'
															ORDER BY `orders_status_name`");

		while (!$statuses->EOF) {
			$statuses_array[] = array('id' => $statuses->fields['orders_status_id'],
																'text' => $statuses->fields['orders_status_name'] . ' [' . $statuses->fields['orders_status_id'] . ']');
			$statuses->MoveNext();
		}

		return zen_draw_pull_down_menu($name, $statuses_array, $order_status_id);
	}

////
// Return a random value
	function zen_rand($min = null, $max = null) {
		static $seeded;

		if (!$seeded) {
			mt_srand((double)microtime()*1000000);
			$seeded = true;
		}

		if (isset($min) && isset($max)) {
			if ($min >= $max) {
				return $min;
			} else {
				return mt_rand($min, $max);
			}
		} else {
			return mt_rand();
		}
	}

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
	function zen_convert_linefeeds($from, $to, $string) {
		if ((PHP_VERSION < "4.0.5") && is_array($from)) {
			return ereg_replace('(' . implode('|', $from) . ')', $to, $string);
		} else {
			return str_replace($from, $to, $string);
		}
	}

	function zen_string_to_int($string) {
		return (int)$string;
	}

////
// Output a day/month/year dropdown selector
	function zen_draw_date_selector($prefix, $date=NULL) {
		$month_array = array();
		$month_array[1] =_JANUARY;
		$month_array[2] =_FEBRUARY;
		$month_array[3] =_MARCH;
		$month_array[4] =_APRIL;
		$month_array[5] =_MAY;
		$month_array[6] =_JUNE;
		$month_array[7] =_JULY;
		$month_array[8] =_AUGUST;
		$month_array[9] =_SEPTEMBER;
		$month_array[10] =_OCTOBER;
		$month_array[11] =_NOVEMBER;
		$month_array[12] =_DECEMBER;
		$usedate = getdate($date);
		$day = $usedate['mday'];
		$month = $usedate['mon'];
		$year = $usedate['year'];
		$date_selector = '<div class="controls controls-row"><SELECT class="col-md-1" name="'. $prefix .'_day">';
		for ($i=1;$i<32;$i++){
			$date_selector .= '<option value="' . $i . '"';
			if ($i==$day) $date_selector .= 'selected';
			$date_selector .= '>' . $i . '</option>';
		}
		$date_selector .= '</select>';
		$date_selector .= '<SELECT class="col-md-2" name="'. $prefix .'_month">';
		for ($i=1;$i<13;$i++){
			$date_selector .= '<option value="' . $i . '"';
			if ($i==$month) $date_selector .= 'selected';
			$date_selector .= '>' . $month_array[$i] . '</option>';
		}
		$date_selector .= '</select>';
		$date_selector .= '<SELECT class="col-md-2" name="'. $prefix .'_year">';
		$dateYear = date('Y', $date);
		$thisYear = date('Y');
		$startYear = ( $dateYear < $thisYear ? $dateYear : $thisYear );
		$endYear = ( $dateYear > $thisYear ? $dateYear : $thisYear );
		for ($i=($startYear-2);$i<$endYear+5;$i++){
			$date_selector .= '<option value="' . $i . '"';
			if ($i==$year) $date_selector .= 'selected';
			$date_selector .= '>' . $i . '</option>';
		}
		$date_selector .= '</select></div>';
		return $date_selector;
	}

////
// lookup attributes model
	function zen_get_products_model($products_id) {
		global $gBitDb;
		$check = $gBitDb->Execute("SELECT `products_model`
										FROM " . TABLE_PRODUCTS . "
										WHERE `products_id` ='" . $products_id . "'");

		return $check->fields['products_model'];
	}


////
// Check if product has attributes
	function zen_has_product_attributes_OLD($products_id) {
		global $gBitDb;
		$attributes = $gBitDb->Execute("SELECT count(*) as `acount`
												 FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
												 WHERE `products_id` = '" . (int)$products_id . "'");

		if ($attributes->fields['acount'] > 0) {
			return true;
		} else {
			return false;
		}
	}

/*

This is obsolete/really broken with the advent of normalized product options

function zen_copy_products_attributes($products_id_from, $products_id_to) {
	global $gBitDb;
	global $messageStack;
	global $copy_attributes_delete_first, $copy_attributes_duplicates_skipped, $copy_attributes_duplicates_overwrite, $copy_attributes_include_downloads, $copy_attributes_include_filename;

// Check for errors in copy request
	if ( (!zen_has_product_attributes($products_id_from, 'false') or !zen_products_id_valid($products_id_to)) or $products_id_to == $products_id_from ) {
		if ($products_id_to == $products_id_from) {
			// same products_id
			$messageStack->add_session('<b>WARNING: Cannot copy FROM Product ID #' . $products_id_from . ' to Product ID # ' . $products_id_to . ' ... No copy was made' . '</b>', 'caution');
		} else {
			if (!zen_has_product_attributes($products_id_from, 'false')) {
				// no attributes found to copy
				$messageStack->add_session('<b>WARNING: No Attributes to copy FROM Product ID #' . $products_id_from . ' for: ' . zen_get_products_name($products_id_from) . ' ... No copy was made' . '</b>', 'caution');
			} else {
				// invalid products_id
				$messageStack->add_session('<b>WARNING: There is no Product ID #' . $products_id_to . ' ... No copy was made' . '</b>', 'caution');
			}
		}
	} else {
// FIX HERE - remove once working

// check if product already has attributes
		$check_attributes = zen_has_product_attributes($products_id_to, 'false');

		if ($copy_attributes_delete_first=='1' and $check_attributes == true) {
// die('DELETE FIRST - Copying FROM ' . $products_id_from . ' to ' . $products_id_to . ' Do I delete first? ' . $copy_attributes_delete_first);
			// delete all attributes first FROM products_id_to
			zen_products_attributes_download_delete($products_id_to);
			$gBitDb->Execute("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` = '" . $products_id_to . "'");
		}

// get attributes to copy from
		$products_copy_from= $gBitDb->Execute("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id`='" . $products_id_from . "'" . " ORDER BY `products_attributes_id`");

		while ( !$products_copy_from->EOF ) {
// This must match the structure of your products_attributes table

			$update_attribute = false;
			$add_attribute = true;
			$check_duplicate = $gBitDb->Execute("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` ='" . $products_id_to . "'" . " and `products_options_id` = '" . $products_copy_from->fields['products_options_id'] . "' and options_values_id='" . $products_copy_from->fields['options_values_id'] .	"'");
			if ($check_attributes == true) {
				if ($check_duplicate->RecordCount() == 0) {
					$update_attribute = false;
					$add_attribute = true;
				} else {
					if ($check_duplicate->RecordCount() == 0) {
						$update_attribute = false;
						$add_attribute = true;
					} else {
						$update_attribute = true;
						$add_attribute = false;
					}
				}
			} else {
				$update_attribute = false;
				$add_attribute = true;
			}

// die('UPDATE/IGNORE - Checking Copying FROM ' . $products_id_from . ' to ' . $products_id_to . ' Do I delete first? ' . ($copy_attributes_delete_first == '1' ? 'Yes' : 'No') . ' Do I add? ' . ($add_attribute == true ? 'Yes' : 'No') . ' Do I Update? ' . ($update_attribute == true ? 'Yes' : 'No') . ' Do I skip it? ' . ($copy_attributes_duplicates_skipped=='1' ? 'Yes' : 'No') . ' Found attributes in From: ' . $check_duplicate->RecordCount());

			if ($copy_attributes_duplicates_skipped == '1' and $check_duplicate->RecordCount() != 0) {
				// skip it
					$messageStack->add_session(TEXT_ATTRIBUTE_COPY_SKIPPING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
			} else {
				if ($add_attribute == true) {
					// New attribute - insert it
					$gBitDb->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " values ('', '" . $products_id_to . "',
					'" . $products_copy_from->fields['products_options_id'] . "',
					'" . $products_copy_from->fields['options_values_id'] . "',
					'" . $products_copy_from->fields['options_values_price'] . "',
					'" . $products_copy_from->fields['price_prefix'] . "',
					'" . $products_copy_from->fields['products_options_sort_order'] . "',
					'" . $products_copy_from->fields['product_attribute_is_free'] . "',
					'" . $products_copy_from->fields['products_attributes_wt'] . "',
					'" . $products_copy_from->fields['attributes_display_only'] . "',
					'" . $products_copy_from->fields['attributes_default'] . "',
					'" . $products_copy_from->fields['attributes_discounted'] . "',
					'" . $products_copy_from->fields['attributes_image'] . "',
					'" . $products_copy_from->fields['attributes_price_base_inc'] . "',
					'" . $products_copy_from->fields['attributes_price_onetime'] . "',
					'" . $products_copy_from->fields['attributes_price_factor'] . "',
					'" . $products_copy_from->fields['attributes_pf_offset'] . "',
					'" . $products_copy_from->fields['attributes_pf_onetime'] . "',
					'" . $products_copy_from->fields['attributes_pf_onetime_offset'] . "',
					'" . $products_copy_from->fields['attributes_qty_prices'] . "',
					'" . $products_copy_from->fields['attributes_qty_prices_onetime'] . "',
					'" . $products_copy_from->fields['attributes_price_words'] . "',
					'" . $products_copy_from->fields['attributes_price_words_free'] . "',
					'" . $products_copy_from->fields['attributes_price_letters'] . "',
					'" . $products_copy_from->fields['attributes_price_letters_free'] . "',
					'" . $products_copy_from->fields['attributes_required'] . "')");
					$messageStack->add_session(TEXT_ATTRIBUTE_COPY_INSERTING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
				}
				if ($update_attribute == true) {
					// Update attribute - Just attribute settings not ids
					$gBitDb->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set
					`options_values_price` ='" . $products_copy_from->fields['options_values_price'] . "',
					`price_prefix` ='" . $products_copy_from->fields['price_prefix'] . "',
					`products_options_sort_order` ='" . $products_copy_from->fields['products_options_sort_order'] . "',
					`product_attribute_is_free` ='" . $products_copy_from->fields['product_attribute_is_free'] . "',
					`products_attributes_wt` ='" . $products_copy_from->fields['products_attributes_wt'] . "',
					`attributes_display_only` ='" . $products_copy_from->fields['attributes_display_only'] . "',
					`attributes_default` ='" . $products_copy_from->fields['attributes_default'] . "',
					`attributes_discounted` ='" . $products_copy_from->fields['attributes_discounted'] . "',
					`attributes_image` ='" . $products_copy_from->fields['attributes_image'] . "',
					`attributes_price_base_inc` ='" . $products_copy_from->fields['attributes_price_base_inc'] . "',
					`attributes_price_onetime` ='" . $products_copy_from->fields['attributes_price_onetime'] . "',
					`attributes_price_factor` ='" . $products_copy_from->fields['attributes_price_factor'] . "',
					`attributes_pf_offset` ='" . $products_copy_from->fields['attributes_pf_offset'] . "',
					`attributes_pf_onetime` ='" . $products_copy_from->fields['attributes_pf_onetime'] . "',
					`attributes_pf_onetime_offset` ='" . $products_copy_from->fields['attributes_pf_onetime_offset'] . "',
					`attributes_qty_prices` ='" . $products_copy_from->fields['attributes_qty_prices'] . "',
					`attributes_qty_prices_onetime` ='" . $products_copy_from->fields['attributes_qty_prices_onetime'] . "',
					`attributes_price_words` ='" . $products_copy_from->fields['attributes_price_words'] . "',
					`attributes_price_words_free` ='" . $products_copy_from->fields['attributes_price_words_free'] . "',
					`attributes_price_letters` ='" . $products_copy_from->fields['attributes_price_letters'] . "',
					`attributes_price_letters_free` ='" . $products_copy_from->fields['attributes_price_letters_free'] . "',
					`attributes_required` ='" . $products_copy_from->fields['attributes_required'] . "'"
					 . " WHERE `products_id` ='" . $products_id_to . "'" . " and `products_options_id` = '" . $products_copy_from->fields['products_options_id'] . "' and `options_values_id` ='" . $products_copy_from->fields['options_values_id'] . "'");
//					 . " WHERE `products_id` ='" . $products_id_to . "'" . " and `products_options_id` = '" . $products_copy_from->fields['products_options_id'] . "' and `options_values_id` ='" . $products_copy_from->fields['options_values_id'] . "' and attributes_image='" . $products_copy_from->fields['attributes_image'] . "' and attributes_price_base_inc='" . $products_copy_from->fields['attributes_price_base_inc'] .	"'");
					$messageStack->add_session(TEXT_ATTRIBUTE_COPY_UPDATING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
				}
			}

			$products_copy_from->MoveNext();
		} // end of products attributes while loop

		 // reset lowest_purchase_price for searches etc.
		 zen_update_lowest_purchase_price($products_id_to);
	} // end of no attributes or other errors
} // eof: zen_copy_products_attributes

*/

////
// warning message
	function zen_output_warning($warning) {
		new errorBox(array(array('text' => zen_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . ' ' . $warning)));
	}


////
// Lookup Languages Icon
	function zen_get_language_icon($lookup) {
		global $gBitDb;
		$languages_icon = $gBitDb->Execute("SELECT `directory`, `image` FROM " . TABLE_LANGUAGES . " WHERE `languages_id` = '" . $lookup . "'");
		$icon= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages_icon->fields['directory'] . '/images/' . $languages_icon->fields['image']);
		return $icon;
	}

////
// Get the Option Name for a particular language
	function zen_get_option_name_language($option, $language) {
		global $gBitDb;
		$lookup = $gBitDb->query("SELECT `products_options_id`, `products_options_name` FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE `products_options_id` = ? AND `language_id` = ?", array( $option, $language ) );
		return $lookup->fields['products_options_name'];
	}

////
// Get the Option Name for a particular language
	function zen_get_option_name_language_sort_order($option, $language) {
		global $gBitDb;
		$lookup = $gBitDb->Execute("SELECT `products_options_id`, `products_options_name`, `products_options_sort_order` FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE `products_options_id` = '" . $option . "' and `language_id` = '" . $language . "'");
		return $lookup->fields['products_options_sort_order'];
	}

////
// lookup attributes model
	function zen_get_language_name($lookup) {
		global $gBitDb;
		$check_language= $gBitDb->Execute("SELECT `directory` FROM " . TABLE_LANGUAGES . " WHERE `languages_id` = '" . $lookup . "'");
		return $check_language->fields['directory'];
	}


////
// Delete all product attributes
	function zen_delete_products_attributes($delete_product_id) {
		global $gBitDb;
		// delete associated downloads
	$sql = "SELECT pom.`products_id`, pad.`products_attributes_id` 
			FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
				INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`products_options_values_id`=pom.`products_options_values_id`)
				INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON(pad.`products_attributes_id` = pa.`products_attributes_id`)
			WHERE pom.`products_id`=?";
		$products_delete_from= $gBitDb->query( $sql, array( $delete_product_id ) );
		while (!$products_delete_from->EOF) {
			$gBitDb->Execute("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE `products_attributes_id` = '" . $products_delete_from['products_attributes_id'] . "'");
			$products_delete_from->MoveNext();
		}

		$gBitDb->Execute("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` = '" . $delete_product_id . "'");
}


////
// Check if a demo is active
	function zen_admin_demo() {
		global $gBitDb;
		if (ADMIN_DEMO == '1') {
			$admin_current = $gBitDb->Execute("SELECT `admin_level` FROM " . TABLE_ADMIN . " WHERE `admin_id` ='" . $_SESSION['admin_id'] . "'");
			if ($admin_current->fields['admin_level'] == '1') {
				$demo_on = false;
			} else {
				$demo_on = true;
			}
		} else {
			$demo_on = false;
		}
		return $demo_on;
	}

////
// Count how many subcategories exist in a category
// TABLES: categories
// old name zen_get_parent_category_name
	function zen_get_products_master_categories_name($categories_id) {
		global $gBitDb;

		$categories_lookup = $gBitDb->Execute("SELECT `parent_id`
																FROM " . TABLE_CATEGORIES . "
																WHERE `categories_id` = '" . (int)$categories_id . "'");

		$parent_name = zen_get_category_name($categories_lookup->fields['parent_id'], (int)$_SESSION['languages_id']);

		return $parent_name;
	}


	function zen_get_handler_from_type($product_type) {
		global $gBitDb;

		$sql = "SELECT `type_handler` FROM " . TABLE_PRODUCT_TYPES . " WHERE `type_id` = '" . $product_type . "'";
		$handler = $gBitDb->Execute($sql);
	return $handler->fields['type_handler'];
	}

/*
////
// Sets the status of a featured product
	function zen_set_featured_status($featured_id, $status) {
		global $gBitDb;
		if ($status == '1') {
			return $gBitDb->Execute("update " . TABLE_FEATURED . "
													 set status = '1', expires_date = NULL, date_status_change = NULL
													 WHERE featured_id = '" . (int)$featured_id . "'");

		} elseif ($status == '0') {
			return $gBitDb->Execute("update " . TABLE_FEATURED . "
													 set status = '0', date_status_change = now()
													 WHERE featured_id = '" . (int)$featured_id . "'");

		} else {
			return -1;
		}
	}
*/

////
// Sets the status of a product review
	function zen_set_reviews_status($review_id, $status) {
		global $gBitDb;
		if ($status == '1') {
			return $gBitDb->Execute("update " . TABLE_REVIEWS . "
													 set `status` = '1'
													 WHERE `reviews_id` = '" . (int)$review_id . "'");

		} elseif ($status == '0') {
			return $gBitDb->Execute("update " . TABLE_REVIEWS . "
													 set `status` = '0'
													 WHERE `reviews_id` = '" . (int)$review_id . "'");

		} else {
			return -1;
		}
	}


////
// Get the status of a category
	function zen_get_categories_status($categories_id) {
		global $gBitDb;
		$sql = "SELECT `categories_status` FROM " . TABLE_CATEGORIES . " WHERE `categories_id` ='" . (int)$categories_id . "'";
		$check_status = $gBitDb->Execute($sql);
		return $check_status->fields['categories_status'];
	}

////
// Get the status of a product
	function zen_get_products_status($product_id) {
		global $gBitDb;
		$sql = "SELECT `products_status` FROM " . TABLE_PRODUCTS . " WHERE `products_id` ='" . (int)$product_id . "'";
		$check_status = $gBitDb->Execute($sql);
		return $check_status->fields['products_status'];
	}

////
// check if linked
	function zen_get_product_is_linked($product_id, $show_count = 'false') {
		global $gBitDb;

		$sql = "SELECT * FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE `products_id`='" . (int)$product_id . "'";
		$check_linked = $gBitDb->Execute($sql);
		if ($check_linked->RecordCount() > 1) {
			if ($show_count == 'true') {
				return $check_linked->RecordCount();
			} else {
				return 'true';
			}
		} else {
			return 'false';
		}
	}

////
// Return the number of products in a category
// TABLES: products, products_to_categories, categories
// syntax for count: zen_get_products_to_categories($categories->fields['categories_id'], true)
// syntax for linked products: zen_get_products_to_categories($categories->fields['categories_id'], true, 'products_active')
	function zen_get_products_to_categories($category_id, $include_inactive = false, $counts_what = 'products') {
		global $gBitDb;

		$products_count = 0;
	$cat_products_count = 0;
		if ($include_inactive == true) {
			switch ($counts_what) {
				case ('products'):
				$cat_products_query = "SELECT count(*) as `total`
													 FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
													 WHERE p.`products_id` = p2c.`products_id`
													 and p2c.`categories_id` = '" . (int)$category_id . "'";
				break;
				case ('products_active'):
				$cat_products_query = "SELECT p.`products_id`
													 FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
													 WHERE p.`products_id` = p2c.`products_id`
													 and p2c.`categories_id` = '" . (int)$category_id . "'";
				break;
			}

		} else {
			switch ($counts_what) {
				case ('products'):
					$cat_products_query = "SELECT count(*) as `total`
														 FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
														 WHERE p.`products_id` = p2c.`products_id`
														 and p.`products_status` = '1'
														 and p2c.`categories_id` = '" . (int)$category_id . "'";
				break;
				case ('products_active'):
					$cat_products_query = "SELECT p.`products_id`
														 FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
														 WHERE p.`products_id` = p2c.`products_id`
														 and p.`products_status` = '1'
														 and p2c.`categories_id` = '" . (int)$category_id . "'";
				break;
			}

		}
		$cat_products = $gBitDb->Execute($cat_products_query);
			switch ($counts_what) {
				case ('products'):
					$cat_products_count += $cat_products->fields['total'];
					break;
				case ('products_active'):
				while (!$cat_products->EOF) {
					if (zen_get_product_is_linked($cat_products->fields['products_id']) == 'true') {
						return 'true';
					}
					$cat_products->MoveNext();
				}
					break;
			}

		$cat_child_categories_query = "SELECT `categories_id`
															 FROM " . TABLE_CATEGORIES . "
															 WHERE `parent_id` = '" . (int)$category_id . "'";

		$cat_child_categories = $gBitDb->Execute($cat_child_categories_query);

		if ($cat_child_categories->RecordCount() > 0) {
			while (!$cat_child_categories->EOF) {
			switch ($counts_what) {
				case ('products'):
					$cat_products_count += zen_get_products_to_categories($cat_child_categories->fields['categories_id'], $include_inactive);
					break;
				case ('products_active'):
					if (zen_get_products_to_categories($cat_child_categories->fields['categories_id'], true, 'products_active') == 'true') {
						return 'true';
					}
					break;
				}
				$cat_child_categories->MoveNext();
			}
		}


			switch ($counts_what) {
				case ('products'):
					return $cat_products_count;
					break;
				case ('products_active'):
					return 'true';
					break;
			}
	}

////
// master category selection
	function zen_get_master_categories_pulldown($product_id) {
		global $gBitDb;

		$master_category_array = array();

		$master_categories_query = $gBitDb->Execute("SELECT ptc.`products_id`, cd.`categories_name`, cd.`categories_id`
																		FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
																		left join " . TABLE_CATEGORIES_DESCRIPTION . " cd
																		on cd.`categories_id` = ptc.`categories_id`
																		WHERE ptc.`products_id`='" . $product_id . "'
																		and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
																		");

		$master_category_array[] = array('id' => '0', 'text' => TEXT_INFO_SET_MASTER_CATEGORIES_ID);
		while (!$master_categories_query->EOF) {
			$master_category_array[] = array('id' => $master_categories_query->fields['categories_id'], 'text' => $master_categories_query->fields['categories_name'] . TEXT_INFO_ID . $master_categories_query->fields['categories_id']);
			$master_categories_query->MoveNext();
		}

		return $master_category_array;
	}

////
// get products type
	function zen_get_products_type($product_id) {
		global $gBitDb;

		$check_products_type = $gBitDb->Execute("SELECT `products_type` FROM " . TABLE_PRODUCTS . " WHERE `products_id`='" . $product_id . "'");
		return $check_products_type->fields['products_type'];
	}

	function zen_draw_admin_box($zf_header, $zf_content) {
		$zp_boxes = '<li class="submenu">' . $zf_header['text'];
		$zp_boxes .= '<ul>' . "\n";
		foreach( array_keys( $zf_content ) as $i ) {
			$zp_boxes .= '<li><a href="' . $zf_content[$i]['link'] . '">' . $zf_content[$i]['text'] . '</a></li>' . "\n";
		}
		$zp_boxes .= '</ul>' . "\n";
		$zp_boxes .= '</li>' . "\n";
		return $zp_boxes;
	}







////
//	++++ modified for UPS Choice 1.8 and USPS Methods 2.5 by Brad Waite and Fritz Clapp ++++
//	++++ modified for USPS Methods 2.5 08/02/03 by Brad Waite and Fritz Clapp ++++
// USPS Methods 2.5
// Alias function for Store configuration values in the Administration Tool
	function zen_cfg_select_multioption($select_array, $key_value, $key = '') {
		$string = '';
		for ($i=0; $i<sizeof($select_array); $i++) {
			$name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
			$string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"';
			$key_values = explode( ", ", $key_value);
			if ( in_array($select_array[$i], $key_values) ) {
				$string .= ' CHECKED';
			}
			$string .= '> ' . $select_array[$i];
		}
		$string .= '<input type="hidden" name="' . $name . '" value="--none--">';
		return $string;
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

////
// Recursive algorithim to restrict all sub_categories to a rpoduct type
	function zen_restrict_sub_categories($zf_cat_id, $zf_type) {
		global $gBitDb;
		$zp_sql = "SELECT `categories_id` FROM " . TABLE_CATEGORIES . " WHERE `parent_id` = '" . $zf_cat_id . "'";
		$zq_sub_cats = $gBitDb->Execute($zp_sql);
		while (!$zq_sub_cats->EOF) {
			$zp_sql = "SELECT * FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
												 WHERE `category_id` = '" . $zq_sub_cats->fields['categories_id'] . "'
												 and `product_type_id` = '" . $zf_type . "'";

			$zq_type_to_cat = $gBitDb->Execute($zp_sql);

			if ($zq_type_to_cat->RecordCount() < 1) {
				$za_insert_sql_data = array('category_id' => $zq_sub_cats->fields['categories_id'],
																		'product_type_id' => $zf_type);
				$gBitDb->associateInsert(TABLE_PRODUCT_TYPES_TO_CATEGORY, $za_insert_sql_data);
			}
			zen_restrict_sub_categories($zq_sub_cats->fields['categories_id'], $zf_type);
			$zq_sub_cats->MoveNext();
		}
	}


////
// Recursive algorithim to restrict all sub_categories to a rpoduct type
	function zen_remove_restrict_sub_categories($zf_cat_id, $zf_type) {
		global $gBitDb;
		$zp_sql = "SELECT `categories_id` FROM " . TABLE_CATEGORIES . " WHERE `parent_id` = '" . $zf_cat_id . "'";
		$zq_sub_cats = $gBitDb->Execute($zp_sql);
		while (!$zq_sub_cats->EOF) {
				$sql = "delete FROM " .	TABLE_PRODUCT_TYPES_TO_CATEGORY . "
								WHERE `category_id` = '" . $zq_sub_cats->fields['categories_id'] . "'
								and `product_type_id` = '" . $zf_type . "'";

				$gBitDb->Execute($sql);
			zen_remove_restrict_sub_categories($zq_sub_cats->fields['categories_id'], $zf_type);
			$zq_sub_cats->MoveNext();
		}
	}


////
// compute the days between two dates
	function zen_date_diff($date1, $date2) {
	//$date1	today, or any other day
	//$date2	date to check against

		$d1 = explode("-", $date1);
		$y1 = $d1[0];
		$m1 = $d1[1];
		$d1 = $d1[2];

		$d2 = explode("-", $date2);
		$y2 = $d2[0];
		$m2 = $d2[1];
		$d2 = $d2[2];

		$date1_set = mktime(0,0,0, $m1, $d1, $y1);
		$date2_set = mktime(0,0,0, $m2, $d2, $y2);

		return(round(($date2_set-$date1_set)/(60*60*24)));
	}

////
// check that a download filename exists
	function zen_orders_products_downloads($check_filename) {
		global $gBitDb;

		$valid_downloads = true;
		// Could go into /admin/includes/configure.php
		// define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
		if (!file_exists(DIR_FS_CATALOG . 'download/' . $check_filename)) {
			$valid_downloads = false;
		// break;
		} else {
			$valid_downloads = true;
		}

		return $valid_downloads;
	}

////
// check if products has discounts
	function zen_has_product_discounts($look_up) {
		global $gBitDb;

		$check_discount_query = "SELECT `products_id` FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id`='" . $look_up . "'";
		$check_discount = $gBitDb->Execute($check_discount_query);

		if ($check_discount->RecordCount() > 0) {
			return 'true';
		} else {
			return 'false';
		}
	}

////
//copy discounts FROM product to another
	function zen_copy_discounts_to_product($copy_from, $copy_to) {
		global $gBitDb;

		$check_discount_type_query = "SELECT `products_discount_type`, `products_discount_type_from`, `products_mixed_discount_quantity` FROM " . TABLE_PRODUCTS . " WHERE `products_id`='" . $copy_from . "'";
		$check_discount_type = $gBitDb->Execute($check_discount_type_query);

		$gBitDb->query("update " . TABLE_PRODUCTS . " set `products_discount_type`=?, `products_discount_type_from`=?, `products_mixed_discount_quantity`=? WHERE `products_id` =?", array( $check_discount_type->fields['products_discount_type'], $check_discount_type->fields['products_discount_type_from'], $check_discount_type->fields['products_mixed_discount_quantity'], $copy_to ) );

		$check_discount_query = "SELECT * FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id` ='" . $copy_from . "' ORDER BY `discount_id`";
		$check_discount = $gBitDb->Execute($check_discount_query);
		$cnt_discount=1;
		while (!$check_discount->EOF) {
			$gBitDb->associateInsert( TABLE_PRODUCTS_DISCOUNT_QUANTITY, ( array( "discount_id" => $cnt_discount, "products_id" => $copy_to, "discount_qty" => $check_discount->fields['discount_qty'], "discount_price" => $check_discount->fields['discount_price'] ) ) );
			$cnt_discount++;
			$check_discount->MoveNext();
		}
	}

////
// meta tags
	function zen_get_metatags_title($product_id, $language_id) {
		global $gBitDb;
		$product = $gBitDb->Execute("SELECT `metatags_title`
														 FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
														 WHERE `products_id` = '" . (int)$product_id . "'
														 and `language_id` = '" . (int)$language_id . "'");

		return $product->fields['metatags_title'];
	}

	function zen_get_metatags_keywords($product_id, $language_id) {
		global $gBitDb;
		$product = $gBitDb->Execute("SELECT `metatags_keywords`
														 FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
														 WHERE `products_id` = '" . (int)$product_id . "'
														 and `language_id` = '" . (int)$language_id . "'");

		return $product->fields['metatags_keywords'];
	}

	function zen_get_metatags_description($product_id, $language_id) {
		global $gBitDb;
		$product = $gBitDb->Execute("SELECT `metatags_description`
														 FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
														 WHERE `products_id` = '" . (int)$product_id . "'
														 and `language_id` = '" . (int)$language_id . "'");

		return $product->fields['metatags_description'];
	}

////
// return products master_categories_id
// TABLES: categories
	function zen_get_parent_category_id($product_id) {
		global $gBitDb;

		$categories_lookup = $gBitDb->Execute("SELECT `master_categories_id`
																FROM " . TABLE_PRODUCTS . "
																WHERE `products_id` = '" . (int)$product_id . "'");

		$parent_id = $categories_lookup->fields['master_categories_id'];

		return $parent_id;
	}

?>

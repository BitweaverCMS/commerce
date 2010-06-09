<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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

function ep_get_uploaded_file($filename) {
	if (isset($_FILES[$filename])) {
		//global $_FILES;
		$uploaded_file = array('name' => $_FILES[$filename]['name'],
		'type' => $_FILES[$filename]['type'],
		'size' => $_FILES[$filename]['size'],
		'tmp_name' => $_FILES[$filename]['tmp_name']);
	} elseif (isset($_POST[$filename])) {
		$uploaded_file = array('name' => $_POST[$filename],
		);
	} elseif (isset($GLOBALS['HTTP_POST_FILES'][$filename])) {
		global $HTTP_POST_FILES;
		$uploaded_file = array('name' => $HTTP_POST_FILES[$filename]['name'],
		'type' => $HTTP_POST_FILES[$filename]['type'],
		'size' => $HTTP_POST_FILES[$filename]['size'],
		'tmp_name' => $HTTP_POST_FILES[$filename]['tmp_name']);
	} elseif (isset($GLOBALS['HTTP_POST_VARS'][$filename])) {
		global $HTTP_POST_VARS;
		$uploaded_file = array('name' => $HTTP_POST_VARS[$filename],
		);
	} else {
		$uploaded_file = array('name' => $GLOBALS[$filename . '_name'],
		'type' => $GLOBALS[$filename . '_type'],
		'size' => $GLOBALS[$filename . '_size'],
		'tmp_name' => $GLOBALS[$filename]);
	}
return $uploaded_file;
}

// the $filename parameter is an array with the following elements:
// name, type, size, tmp_name
function ep_copy_uploaded_file($filename, $target) {
	if (substr($target, -1) != '/') $target .= '/';
	$target .= $filename['name'];
	move_uploaded_file($filename['tmp_name'], $target);
}

function ep_get_tax_class_rate($tax_class_id) {
	$tax_multiplier = 0;
	$tax_query = mysql_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " WHERE  tax_class_id = '" . zen_db_input($tax_class_id) . "' GROUP BY tax_priority");
	if (mysql_num_rows($tax_query)) {
		while ($tax = mysql_fetch_array($tax_query)) {
			$tax_multiplier += $tax['tax_rate'];
		}
	}
	return $tax_multiplier;
}

function ep_get_tax_title_class_id($tax_class_title) {
	$classes_query = mysql_query("select tax_class_id from " . TABLE_TAX_CLASS . " WHERE tax_class_title = '" . zen_db_input($tax_class_title) . "'" );
	$tax_class_array = mysql_fetch_array($classes_query);
	$tax_class_id = $tax_class_array['tax_class_id'];
	return $tax_class_id ;
}

function print_el($item2) {
	//$output_display = " | " . substr(strip_tags($item2), 0, 10);
	$output_display = substr(strip_tags($item2), 0, 10) . " | ";
	return $output_display;
}

function print_el1($item2) {
	$output_display = sprintf("| %'.4s ", substr(strip_tags($item2), 0, 80));
	return $output_display;
}

function smart_tags($string,$tags,$crsub,$doit) {
	if ($doit == true) {
		foreach ($tags as $tag => $new) {
			$tag = '/('.$tag.')/';
			$string = preg_replace($tag,$new,$string);
		}
	}
	// we remove problem characters here anyway as they are not wanted..
	$string = preg_replace("/(\r\n|\n|\r)/", "", $string);
	// $crsub is redundant - may add it again later though..
	return $string;
}

function ep_field_name_exists($tbl,$fld) {
  if (zen_not_null(zen_field_type($tbl,$fld))) {
  	return true;
  } else {
  	return false;
  }
}

function ep_remove_product($product_model) {
  global $gBitDb;
  global $ep_debug_logging;
  global $ep_debug_logging_all;
  global $ep_stack_sql_error;
  
  $sql = "select products_id
                           from " . TABLE_PRODUCTS . "
                           where products_model = '" . zen_db_input($product_model) . "'";
  $products = $gBitDb->Execute($sql);
  
	if (mysql_errno()) {
		$ep_stack_sql_error = true;
		if ($ep_debug_logging == true) {
			// langer - will add time & date..
			$string = "MySQL error ".mysql_errno().": ".mysql_error()."\nWhen executing:\n$sql\n";
			write_debug_log($string);
		}
	} elseif ($ep_debug_logging_all == true) {
		$string = "MySQL PASSED\nWhen executing:\n$sql\n";
		write_debug_log($string);
	}
  
  while (!$products->EOF) {
    zen_remove_product($products->fields['products_id']);
    $products->MoveNext();
  }
  return;
}

function ep_purge_dross() {
	$dross = array();
	$dross = ep_get_dross();
	foreach ($dross as $products_id => $langer) {
		zen_remove_product($products_id);
	}
}

function ep_get_dross() {
	global $gBitDb;
	$target_tables = array(TABLE_PRODUCTS_DESCRIPTION,
												TABLE_SPECIALS,
												TABLE_PRODUCTS_TO_CATEGORIES,
												TABLE_PRODUCTS_ATTRIBUTES,
												TABLE_FEATURED,
												TABLE_CUSTOMERS_BASKET,
												TABLE_CUSTOMERS_BASKET_ATTRIBUTES,
												TABLE_PRODUCTS_DISCOUNT_QUANTITY);
												// can add others I guess, though this probably catches all data debris...
												// reviews uses reviews_id, but if it is in reviews, it is probably detected above anyway
												// This array needs to work with all versions - could break EP on older versions I think.. with each additional table, test on older versions
	
	$dross = array();
	foreach ($target_tables as $table) {
		//lets check the tables for deleted products
		$sql = "select distinct t.products_id from " . $table . " as t left join " . TABLE_PRODUCTS . " as p on t.products_id = p.products_id where p.products_id is NULL";
		$products = $gBitDb->Execute($sql);
		while (!$products->EOF) {
			$dross[$products->fields['products_id']] = 'dross';
			$products->MoveNext();
		}
	}
	// our array has product_id => "dross", so duplicate products simply over-write same in array
	//print_r($dross);
  return $dross;
}

function ep_update_cat_ids() {
  // reset products master categories ID
	global $gBitDb;
	
  $sql = "select products_id from " . TABLE_PRODUCTS;
  $check_products = $gBitDb->Execute($sql);
  while (!$check_products->EOF) {

    $sql = "select products_id, categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . $check_products->fields['products_id'] . "'";
    $check_category = $gBitDb->Execute($sql);

    $sql = "update " . TABLE_PRODUCTS . " set master_categories_id='" . $check_category->fields['categories_id'] . "' where products_id='" . $check_products->fields['products_id'] . "'";
    $update_viewed = $gBitDb->Execute($sql);

    $check_products->MoveNext();
  }
}

function ep_update_prices() {
	global $gBitDb;
	
  // reset lowest_purchase_price for searches etc.
  $sql = "select products_id from " . TABLE_PRODUCTS;
  $update_prices = $gBitDb->Execute($sql);

  while (!$update_prices->EOF) {
    zen_update_lowest_purchase_price($update_prices->fields['products_id']);
    $update_prices->MoveNext();
  }
}

function ep_update_attributes_sort_order() {
	global $gBitDb;
	$all_products_attributes= $gBitDb->Execute("select p.products_id, pa.products_attributes_id from " .
	TABLE_PRODUCTS . " p, " .
	TABLE_PRODUCTS_ATTRIBUTES . " pa " . "
	where p.products_id= pa.products_id"
	);
	while (!$all_products_attributes->EOF) {
	  $count++;
	  //$product_id_updated .= ' - ' . $all_products_attributes->fields['products_id'] . ':' . $all_products_attributes->fields['products_attributes_id'];
	  zen_update_attributes_products_option_values_sort_order($all_products_attributes->fields['products_id']);
	  $all_products_attributes->MoveNext();
	}
}

// langer - add update options/attributes sorter - call from end of attributes upload

function ep_datoriser($date_time) {
	global $ep_date_format; // d-m-y etc..
	global $ep_raw_time; // user's prefered time (eg for specials to start) if no time in upload
	
	$raw_date_exist = preg_match("/^([0-2]0[0-9]{2}-[0-1][0-9]-[0-3][0-9])( [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$)?/", $date_time);
	if (!$raw_date_exist) {
		// not raw... we can only assume it is an excel date..
		// separate dates from times
		$exist_time = preg_match("/^2?0?[0-9]?[0-9][\.\/-][0-3]?[0-9][\.\/-]2?0?[0-9]?[0-9] ([0-2]?[0-9]:[0-5][0-9]).*$/", $date_time, $excel_time); // no seconds..
		$exist_date = preg_match("/^(2?0?[0-9]?[0-9][\.\/-][0-3]?[0-9][\.\/-]2?0?[0-9]?[0-9]).*$/", $date_time, $excel_date);
		//echo $excel_time[1] . '<br >';
		//echo $excel_date[1] . '<br ><br />';
		// if (!zen_not_null($exist_date)) // we fail to get a date! error msg rqd, and/or substitute action??
		
		// check for which of 3 possible date separators we have..
		// this sucks, I know... but it works for now
		if (zen_not_null(strpos($excel_date[1], '-'))) $separator = '-';
		if (zen_not_null(strpos($excel_date[1], '.'))) $separator = '.';
		if (zen_not_null(strpos($excel_date[1], '/'))) $separator = '/';
		
		//echo 'separator is: ' . $separator . '<br />';
		$format_bits = explode('-', $ep_date_format);
		$date_bits = explode($separator, $excel_date[1]);
		foreach ($format_bits as $key => $bit) {
			$$bit = $date_bits[$key]; // $y = 05 or 2005, $m = 09 or 9, $d = 03 or 3 for eg. Can only work if d,m,y order from excel is same as config
			$$bit = strlen($$bit) < 2 ? '0' . $$bit : $$bit; // 4 is now 04 for eg. - expand this as a rudimentary check - should never occur on $y var
			$$bit = strlen($$bit) > 2 ? substr($$bit,-2, 2) : $$bit; // 2005 is now 05 - expand this as a rudimentary check - should only occur on $y var
			//echo $$bit . '<br />';
			// another rudimentary check could be for $m vals > 12 = error too!
		}
		// create default raw time... if user left space off, put it on..
		if (substr($ep_raw_time,0, 1) != ' ') $ep_raw_time = ' ' . $ep_raw_time;
		// is it really a raw time? if not, make it midnight..
		$exist_raw_time = preg_match("/ [0-2][0-9]:[0-5][0-9]:[0-5][0-9]/", $ep_raw_time); // true if is raw time
		$ep_raw_time = zen_not_null($exist_raw_time) ? $ep_raw_time : ' 00:00:00';
		
		// if time supplied from excel, use it instead..
		$ep_raw_time = zen_not_null($exist_time) ? ' ' . $excel_time[1] : $ep_raw_time;
		
		//echo '<br />'.$ep_raw_time . '<br />';
		$raw_date = '20' . $y . '-' . $m . '-' . $d . $ep_raw_time; // needs updating at the end of the century ;-)
		//echo $raw_date . '<br /><br />';
	} else {
		// the date is raw, so return it
		$raw_date = $date_time;
		//echo $date . ' is raw...<br />';
	}
	return $raw_date;
}

function write_debug_log($string) {
	global $ep_debug_log_path;
	$logFile = $ep_debug_log_path . 'ep_debug_log.txt';
  $fp = fopen($logFile,'ab');
  fwrite($fp, $string);
  fclose($fp);
  return;
}

function ep_query($query) {
	global $ep_debug_logging;
	global $ep_debug_logging_all;
	global $ep_stack_sql_error;
	$result = mysql_query($query);
	if (mysql_errno()) {
		$ep_stack_sql_error = true;
		if ($ep_debug_logging == true) {
			// langer - will add time & date..
			$string = "MySQL error ".mysql_errno().": ".mysql_error()."\nWhen executing:\n$query\n";
			write_debug_log($string);
		}
	} elseif ($ep_debug_logging_all == true) {
		$string = "MySQL PASSED\nWhen executing:\n$query\n";
		write_debug_log($string);
	}
	return $result;
}

function install_easypopulate() {
	global $gBitDb;
	$gBitDb->Execute("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " VALUES ('', 'Easy Populate', 'Config options for Easy Populate', '1', '1')");
	$group_id = mysql_insert_id();
	$gBitDb->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = " . $group_id . " WHERE configuration_group_id = " . $group_id);
	$gBitDb->Execute("INSERT INTO " . TABLE_CONFIGURATION . " VALUES 
		('', 'Uploads Directory', 'EASYPOPULATE_CONFIG_TEMP_DIR', 'temp/', 'Name of directory for your uploads (default: temp/).', " . $group_id . ", '0', NULL, now(), NULL, NULL),
		('', 'Upload File Date Format', 'EASYPOPULATE_CONFIG_FILE_DATE_FORMAT', 'm-d-y', 'Choose order of date values that corresponds to your uploads file, usually generated by MS Excel. Raw dates in your uploads file (Eg 2005-09-26 09:00:00) are not affected, and will upload as they are.', " . $group_id . ", '1', NULL, now(), NULL, 'zen_cfg_select_option(array(\"m-d-y\", \"d-m-y\", \"y-m-d\"),'),
		('', 'Default Raw Time', 'EASYPOPULATE_CONFIG_DEFAULT_RAW_TIME', '09:00:00', 'If no time value stipulated in upload file, use this value. Usefull for ensuring specials begin after a specific time of the day (default: 09:00:00)', " . $group_id . ", '2', NULL, now(), NULL, NULL),
		('', 'Split File On # Records', 'EASYPOPULATE_CONFIG_SPLIT_MAX', '300', 'Default number of records for split-file uploads. Used to avoid timeouts on large uploads (default: 300).', " . $group_id . ", '3', NULL, now(), NULL, NULL),
		('', 'Maximum Category Depth', 'EASYPOPULATE_CONFIG_MAX_CATEGORY_LEVELS', '7', 'Maximum depth of categories required for your store. Is the number of category columns in downloaded file (default: 7).', " . $group_id . ", '4', NULL, now(), NULL, NULL),
		('', 'Upload/Download Prices Include Tax', 'EASYPOPULATE_CONFIG_PRICE_INC_TAX', 'false', 'Choose to include or exclude tax, depending on how you manage prices outside of Zen Cart.', " . $group_id . ", '5', NULL, now(), NULL, 'zen_cfg_select_option(array(\"true\", \"false\"),'),
		('', 'Make Zero Qty Products Inactive', 'EASYPOPULATE_CONFIG_ZERO_QTY_INACTIVE', 'false', 'When uploading, make the status Inactive for products with zero qty (default: false).', " . $group_id . ", '6', NULL, now(), NULL, 'zen_cfg_select_option(array(\"true\", \"false\"),'),
		('', 'Smart Tags Replacement of Newlines', 'EASYPOPULATE_CONFIG_SMART_TAGS', 'true', 'Allows your description fields in your uploads file to have carriage returns and/or new-lines converted to HTML line-breaks on uploading, thus preserving some rudimentary formatting (default: true).', " . $group_id . ", '7', NULL, now(), NULL, 'zen_cfg_select_option(array(\"true\", \"false\"),'),
		('', 'Advanced Smart Tags', 'EASYPOPULATE_CONFIG_ADV_SMART_TAGS', 'false', 'Allow the use of complex regular expressions to format descriptions, making headings bold, add bullets, etc. Configuration is in ADMIN/easypopulate.php (default: false).', " . $group_id . ", '8', NULL, now(), NULL, 'zen_cfg_select_option(array(\"true\", \"false\"),'),
		('', 'Debug Logging', 'EASYPOPULATE_CONFIG_DEBUG_LOGGING', 'true', 'Allow Easy Populate to generate an error log on errors only (default: true)', " . $group_id . ", '9', NULL, now(), NULL, 'zen_cfg_select_option(array(\"true\", \"false\"),')
		");
}

function remove_easypopulate() {
	global $gBitDb;
	global $ep_keys;
	
	$sql = "SELECT
			configuration_group_id
		FROM
			" . TABLE_CONFIGURATION_GROUP . "
		WHERE
		configuration_group_title = 'Easy Populate'";
		
	$result = ep_query($sql);
	if (mysql_num_rows($result)) {
		// we have at least 1 EP group - let's delete it
		$ep_groups =  mysql_fetch_array($result);
		foreach ($ep_groups as $ep_group) {
			
	    $gBitDb->Execute("delete from " . TABLE_CONFIGURATION_GROUP . "
	             where configuration_group_id = '" . (int)$ep_group . "'");
	             
		}
	}
	// now delete any EP keys found in config
	foreach ($ep_keys as $ep_key) {
	  @$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . "
	           where configuration_key = '" . $ep_key . "'");
	}
}

function ep_chmod_check($tempdir) {
	global $messageStack;
	
	if (!@file_exists(DIR_FS_CATALOG . $tempdir . ".")) {
		// directory does not exist, or may be unwritable
		@chmod(DIR_FS_CATALOG . $tempdir, 0700); // attempt to make writable - supress error as dir may not exist..
		if (!@file_exists(DIR_FS_CATALOG . $tempdir . ".")) {
			// still can't see it, so it is probably not there..
			$messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_TEMP_FOLDER_MISSING, $tempdir, DIR_FS_CATALOG), 'warning');
			$chmod_check = false;
		} else {
			// we successfully changed to writable
			$messageStack->add(EASYPOPULATE_MSGSTACK_TEMP_FOLDER_PERMISSIONS_SUCCESS, 'success');
			$chmod_check = true;
		}
	} else {
		$chmod_check = true;
	}
	return $chmod_check;
}

/**
* The following functions are for testing purposes only
*/
// available zen functions of use..
/*
function zen_get_category_name($category_id, $language_id)
function zen_get_category_description($category_id, $language_id)
function zen_get_products_name($product_id, $language_id = 0)
function zen_get_products_description($product_id, $language_id)
function zen_get_products_model($products_id)
*/

function register_globals_vars_check () {
	echo phpversion();
	echo '<br>register_globals = ', ini_get('register_globals'), '<br>';
	print "_GET: "; print_r($_GET); echo '<br />';
	print "_POST: "; print_r($_POST); echo '<br />';
	print "_FILES: "; print_r($_FILES); echo '<br />';
	print "_COOKIE: "; print_r($_COOKIE); echo '<br />';
	print "GLOBALS: "; print_r($GLOBALS); echo '<br />';
	print "_REQUEST: "; print_r($_REQUEST); echo '<br /><br />';
	
	global $HTTP_POST_FILES;
	print "HTTP_POST_FILES: "; print_r($HTTP_POST_FILES); echo '<br />';
}
?>
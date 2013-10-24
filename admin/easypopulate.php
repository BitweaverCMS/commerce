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

//*******************************
//*******************************
// C O N F I G U R A T I O N
// V A R I A B L E S
//*******************************
//*******************************

/**
* Advanced Smart Tags - activated/de-activated in Zencart Admin
*/

// only activate advanced tags if you really know what you are doing, and understand regular expressions. Disable if things go awry.
// If you wish to add your own smart-tags below, please ensure that you understand the following:
// 1) ensure that the expressions you use avoid repetitive behaviour from one upload to the next using existing data, as you may end up with this sort of thing:
//   <b><b><b><b>thing</b></b></b></b> ...etc for each update. This is caused for each output that qualifies as an input for any expression..
// 2) remember to place the tags in the order that you want them to occur, as each is done in turn and may remove characters you rely on for a later tag
// 3) the $smart_tags array above is the last to be executed, so you have all of your carriage-returns and line-breaks to play with below
// 4) make sure you escape the following metacharacters if you are using them as string literals: ^  $  \  *  +  ?  (  )  |  .  [  ]  / etc..
// The following examples should get your blood going... comment out those you do not want after enabling $strip_advanced_smart_tags = true above
// for regex help see: http://www.quanetic.com/regex.php or http://www.regular-expressions.info
$advanced_smart_tags = array(
										// replaces "Description:" at beginning of new lines with <br /> and same in bold
										"\r\nDescription:|\rDescription:|\nDescription:" => '<br /><b>Description:</b>',
										
										// replaces at beginning of description fields "Description:" with same in bold
										"^Description:" => '<b>Description:</b>',
										
										// just make "Description:" bold wherever it is...must use both lines to prevent duplicates!
										//"<b>Description:<\/b>" => 'Description:',
										//"Description:" => '<b>Description:</b>',
										
										// replaces "Specification:" at beginning of new lines with <br /> and same in bold.
										"\r\nSpecifications:|\rSpecifications:|\nSpecifications:" => '<br /><b>Specifications:</b>',
										
										// replaces at beginning of descriptions "Specifications:" with same in bold
										"^Specifications:" => '<b>Specifications:</b>',
										
										// just make "Specifications:" bold wherever it is...must use both lines to prevent duplicates!
										//"<b>Specifications:<\/b>" => 'Specifications:',
										//"Specifications:" => '<b>Specifications:</b>',
										
										// replaces in descriptions any asterisk at beginning of new line with a <br /> and a bullet.
										"\r\n\*|\r\*|\n\*" => '<br />&bull;',
										
										// replaces in descriptions any asterisk at beginning of descriptions with a bullet.
										"^\*" => '&bull;',
										
										// returns/newlines in description fields replaced with space, rather than <br /> further below
										//"\r\n|\r|\n" => ' ',
										
										// the following should produce paragraphs between double breaks, and line breaks for returns/newlines
										"^<p>" => '', // this prevents duplicates
										"^" => '<p>',
										//"^<p style=\"desc-start\">" => '', // this prevents duplicates
										//"^" => '<p style="desc-start">',
										"<\/p>$" => '', // this prevents duplicates
										"$" => '</p>',
										"\r\n\r\n|\r\r|\n\n" => '</p><p>',
										// if not using the above 5(+2) lines, use the line below instead..
										//"\r\n\r\n|\r\r|\n\n" => '<br /><br />',
										"\r\n|\r|\n" => '<br />',
										
										// ensures "Description:" followed by single <br /> is fllowed by double <br />
										"<b>Description:<\/b><br \/>" => '<br /><b>Description:</b><br /><br />',
										);
										

//*******************************
//*******************************
// E N D
// C O N F I G U R A T I O N
// V A R I A B L E S
//*******************************
//*******************************


//*******************************
//*******************************
// S T A R T
// INITIALIZATION
//*******************************

require_once ('includes/application_top.php');

set_time_limit(300); // if possible, let's try for 5 minutes before timeouts

/**
* Config translation layer..
*/

$tempdir = EASYPOPULATE_CONFIG_TEMP_DIR;
$ep_date_format = EASYPOPULATE_CONFIG_FILE_DATE_FORMAT;
$ep_raw_time = EASYPOPULATE_CONFIG_DEFAULT_RAW_TIME;
$ep_debug_logging = EASYPOPULATE_CONFIG_DEBUG_LOGGING;
$maxrecs = EASYPOPULATE_CONFIG_SPLIT_MAX;
$price_with_tax = EASYPOPULATE_CONFIG_PRICE_INC_TAX;
$max_categories = EASYPOPULATE_CONFIG_MAX_CATEGORY_LEVELS;
$strip_smart_tags = EASYPOPULATE_CONFIG_SMART_TAGS;
// may make it optional for user to use their own names for these EP tasks..
//$active = 'Active';
//$inactive = 'Inactive';
//$deleteit = 'Delete';

// attributes array?

/**
* Test area start
*/
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);//test purposes only
//register_globals_vars_check ();// test purposes only
//$maxrecs = 4; // for testing
// usefull stuff: mysql_affected_rows(), mysql_num_rows().
$ep_debug_logging_all = false; // do not comment out.. make false instead
//$sql_fail_test == true; // used to cause an sql error on new product upload - tests error handling & logs
/*
* Test area end
**/

/**
* Initialise vars
*/

// Current EP Version
$curver = '1.2.5.4';

$display_output = '';
$ep_dltype = NULL;
$ep_dlmethod = NULL;
$chmod_check = true;
$ep_stack_sql_error = false; // function returns true on any 1 error, and notifies user of an error
$specials_print = EASYPOPULATE_SPECIALS_HEADING;
$replace_quotes = false; // langer - this is probably redundant now...retain here for now..
$products_with_attributes = false; // langer - this will be redundant after html renovation
// maybe below can go in array eg $ep_processed['attributes'] = true, etc.. cold skip all post-upload tasks on check if isset var $ep_processed.
$has_attributes == false;
$has_specials == false;

// define(EASYPOPULATE_CONFIG_COL_DELIMITER, "\t");
$separator = "\t"; // only tab allowed at present

// all mods go in this array as 'name' => 'true' if exist. eg $ep_supported_mods['psd'] => true means it exists.
// langer - scan array in future to reveal if any mods for inclusion in downloads
$ep_supported_mods = array();

// config keys array - must contain any expired keys to ensure they are deleted on install or removal
$ep_keys = array('EASYPOPULATE_CONFIG_TEMP_DIR',
								'EASYPOPULATE_CONFIG_FILE_DATE_FORMAT',
								'EASYPOPULATE_CONFIG_DEFAULT_RAW_TIME',
								'EASYPOPULATE_CONFIG_SPLIT_MAX',
								'EASYPOPULATE_CONFIG_MAX_CATEGORY_LEVELS',
								'EASYPOPULATE_CONFIG_PRICE_INC_TAX',
								'EASYPOPULATE_CONFIG_ZERO_QTY_INACTIVE',
								'EASYPOPULATE_CONFIG_SMART_TAGS',
								'EASYPOPULATE_CONFIG_ADV_SMART_TAGS',
								'EASYPOPULATE_CONFIG_DEBUG_LOGGING',
								);

// default smart-tags setting when enabled
$smart_tags = array("\r\n|\r|\n" => '<br />',
										);

if (substr($tempdir, -1) != '/') $tempdir .= '/';
if (substr($tempdir, 0, 1) == '/') $tempdir = substr($tempdir, 1);

$ep_debug_log_path = DIR_FS_CATALOG . $tempdir;

if ($ep_debug_logging_all == true) {
$fp = fopen($ep_debug_log_path . 'ep_debug_log.txt','w'); // new blank log file on each page impression for full testing log (too big otherwise!!)
fclose($fp);
}

/**
* Pre-flight checks start here
*/

// temp folder exists & permissions check & adjust if we can
// lets check our config is installed 1st..
// when installing, we skip these tests..
if (EASYPOPULATE_CONFIG_TEMP_DIR == 'EASYPOPULATE_CONFIG_TEMP_DIR' && ($_GET['langer'] != 'install' or $_GET['langer'] != 'installnew')) {
	// admin area config not installed
	$messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_INSTALL_KEYS_FAIL, '<a href="' . zen_href_link(FILENAME_EASYPOPULATE, 'langer=installnew') . '">', '</a>'), 'warning');
} elseif ($_GET['langer'] != 'install' or $_GET['langer'] != 'installnew') {
	ep_chmod_check ($tempdir);
}

// installation start
if ($_GET['langer'] == 'install' or $_GET['langer'] == 'installnew') {
	if ($_GET['langer'] == 'installnew') {
		// remove any old config..
		remove_easypopulate();
		// install new config
		install_easypopulate();
		zen_redirect(zen_href_link(FILENAME_EASYPOPULATE, 'langer=install'));
	}
	
	$chmod_check = ep_chmod_check($tempdir);
	if ($chmod_check == false) {
		// no temp dir, so template download wont work..
		$messageStack->add(EASYPOPULATE_MSGSTACK_INSTALL_CHMOD_FAIL, 'caution');
	} else {
		// chmod success
		if (defined('EASYPOPULATE_MSGSTACK_LANGER') && strpos(EASYPOPULATE_MSGSTACK_LANGER, 'paypal@portability.com.au') == true) {
			$messageStack->add(EASYPOPULATE_MSGSTACK_LANGER, 'caution');
		} else {
			$messageStack->add('EasyPopulate support & development by <b>langer</b>. Donations are always appreciated to support continuing development: paypal@portability.com.au', 'caution');
		}
		// lets do a full download to the temp file
		$ep_dltype = 'full';
		$ep_dlmethod = 'tempfile';
		$messageStack->add(EASYPOPULATE_MSGSTACK_INSTALL_CHMOD_SUCCESS, 'success');
	}
	//zen_redirect(zen_href_link(FILENAME_EASYPOPULATE));
	
	// attempt to delete redundant files from previous versions v1.2.5.2 and lower
	// delete easypopulate_functions from admin dir
	$return = @unlink('easypopulate_functions.php');
	if($return == true) $messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_INSTALL_DELETE_SUCCESS, 'easypopulate_functions.php', 'ADMIN'), 'success');
	$return = @unlink('includes/boxes/extra_boxes/populate_tools_dhtml.php');
	if($return == true) {
		$messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_INSTALL_DELETE_SUCCESS, 'populate_tools_dhtml.php', '/includes/boxes/extra_boxes/'), 'success');
	} else {
		// delete populate_tools_dhtml.php from extra boxes failed. Tell user to delete it, otherwise it shows in DHTML menu.
		if (@is_file(includes/boxes/extra_boxes/populate_tools_dhtml.php)) $messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_INSTALL_DELETE_FAIL, 'populate_tools_dhtml.php', '/includes/boxes/extra_boxes/'), 'caution');
	}
	
} elseif ($_GET['langer'] == 'remove') {
	remove_easypopulate();
	zen_redirect(zen_href_link(FILENAME_EASYPOPULATE));
}
// end installation/removal

/**
* START check for existance of various mods
*/

// $ep_supported_mods['psd'] = ep_field_name_exists(TABLE_PRODUCTS_DESCRIPTION,'products_short_desc') ? 'Product Short Descriptions' : NULL; // this will mean if isset, we have it, and the array has the name for html display
$ep_supported_mods['psd'] = ep_field_name_exists(TABLE_PRODUCTS_DESCRIPTION,'products_short_desc');

// others go here..

/**
* END check for existance of various mods
*/

if (EASYPOPULATE_CONFIG_ADV_SMART_TAGS == 'true') $smart_tags = array_merge($advanced_smart_tags,$smart_tags);

// maximum length for a category in this database
$category_strlen_max = zen_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name');

// model name length error handling
$model_varchar = zen_field_length(TABLE_PRODUCTS, 'products_model');
if (!isset($model_varchar)) {
	$messageStack->add(EASYPOPULATE_MSGSTACK_MODELSIZE_DETECT_FAIL, 'warning');
	$modelsize = 32;
} else {
	$modelsize = $model_varchar;
}
//echo $modelsize;

/**
* Pre-flight checks finish here
*/

// now to create the file layout for each download type..

// VJ product attributes begin
// this creates our attributes array
$attribute_options_array = array();

if (is_array($attribute_options_select) && (count($attribute_options_select) > 0)) {
	// this limits the size of files where there are many options/attributes
	// Maybe we can automatically creat multiple files where column count is likely to exceed 256?
	foreach ($attribute_options_select as $value) {
		$attribute_options_query = "select distinct products_options_id from " . TABLE_PRODUCTS_OPTIONS . " where products_options_name = '" . zen_db_input($value) . "'";
		$attribute_options_values = ep_query($attribute_options_query);

		if ($attribute_options = mysql_fetch_array($attribute_options_values)){
			$attribute_options_array[] = array('products_options_id' => $attribute_options['products_options_id']);
		}
	}
} else {
	$attribute_options_query = "select distinct products_options_id from " . TABLE_PRODUCTS_OPTIONS . " order by products_options_id";
	$attribute_options_values = ep_query($attribute_options_query);

	while ($attribute_options = mysql_fetch_array($attribute_options_values)){
		$attribute_options_array[] = array('products_options_id' => $attribute_options['products_options_id']);
	}
}
// VJ product attributes end


//elari check default language_id from configuration table DEFAULT_LANGUAGE
$epdlanguage_query = ep_query("select languages_id, name from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_LANGUAGE . "'");
if (mysql_num_rows($epdlanguage_query)) {
	$epdlanguage = mysql_fetch_array($epdlanguage_query);
	$epdlanguage_id   = $epdlanguage['languages_id'];
	$epdlanguage_name = $epdlanguage['name'];
} else {
	//$messageStack->add('', 'warning'); // langer - this will never occur..
	echo 'Strange but there is no default language to work... That may not happen, just in case...';
}

$langcode = array();
$languages_query = ep_query("select languages_id, code from " . TABLE_LANGUAGES . " order by sort_order");
// start array at one, the rest of the code expects it that way
$ll =1;
while ($ep_languages = mysql_fetch_array($languages_query)) {
	//will be used to return language_id en language code to report in product_name_code instead of product_name_id
	$ep_languages_array[$ll++] = array(
				'id' => $ep_languages['languages_id'],
				'code' => $ep_languages['code']
				);
}
$langcode = $ep_languages_array;

$ep_dltype = (isset($_GET['dltype'])) ? $_GET['dltype'] : $ep_dltype;

if (zen_not_null($ep_dltype)) {
	
	// if dltype is set, then create the filelayout.  Otherwise it gets read from the uploaded file
	// ep_create_filelayout($dltype); // get the right filelayout for this download. langer - redundant function call..

	// depending on the type of the download the user wanted, create a file layout for it.
	$fieldmap = array(); // default to no mapping to change internal field names to external.
	switch($ep_dltype){
	case 'full':
		// The file layout is dynamically made depending on the number of languages
		$iii = 0;
		$filelayout = array(
			'v_products_model'    => $iii++,
			'v_products_image'    => $iii++,
			);

		foreach ($langcode as $key => $lang){
			$l_id = $lang['id'];
			// uncomment the head_title, head_desc, and head_keywords to use
			// Linda's Header Tag Controller 2.0
			$filelayout  = array_merge($filelayout , array(
					'v_products_name_' . $l_id    => $iii++,
					'v_products_description_' . $l_id => $iii++,
					));
					// if short descriptions exist
					if ($ep_supported_mods['psd'] == true) {
						$filelayout  = array_merge($filelayout , array(
						'v_products_short_desc_' . $l_id  => $iii++,
						));
					}
					
					$filelayout  = array_merge($filelayout , array(
					'v_products_url_' . $l_id => $iii++,
					));
					
			//    'v_products_head_title_tag_'.$l_id  => $iii++,
			//    'v_products_head_desc_tag_'.$l_id => $iii++,
			//    'v_products_head_keywords_tag_'.$l_id => $iii++,
					
		}
		// uncomment the customer_price and customer_group to support multi-price per product contrib
		
		// langer - specials added below
		
			$header_array = array(
			'v_specials_price'    => $iii++,
			'v_specials_date_avail'     => $iii++,
			'v_specials_expires_date'     => $iii++,
			'v_products_price'    => $iii++,
			'v_products_weight'   => $iii++,
			'v_date_avail'      => $iii++,
			'v_date_added'      => $iii++,
			'v_products_quantity'   => $iii++,
			);
		
		if ($products_with_attributes == true) {
			//include attributes in full download if config is true
			// VJ product attribs begin

			$languages = zen_get_languages();

			$attribute_options_count = 1;
			foreach ($attribute_options_array as $attribute_options_values) {
				$key1 = 'v_attribute_options_id_' . $attribute_options_count;
				$header_array[$key1] = $iii++;

				for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
					$l_id = $languages[$i]['id'];

					$key2 = 'v_attribute_options_name_' . $attribute_options_count . '_' . $l_id;
					$header_array[$key2] = $iii++;
				}

				$attribute_values_query = "select products_options_values_id  from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$attribute_options_values['products_options_id'] . "' order by products_options_values_id";
				$attribute_values_values = ep_query($attribute_values_query);

				$attribute_values_count = 1;
				while ($attribute_values = mysql_fetch_array($attribute_values_values)) {
					$key3 = 'v_attribute_values_id_' . $attribute_options_count . '_' . $attribute_values_count;
					$header_array[$key3] = $iii++;

					$key4 = 'v_attribute_values_price_' . $attribute_options_count . '_' . $attribute_values_count;
					$header_array[$key4] = $iii++;

					for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
						$l_id = $languages[$i]['id'];

						$key5 = 'v_attribute_values_name_' . $attribute_options_count . '_' . $attribute_values_count . '_' . $l_id;
						$header_array[$key5] = $iii++;
					}

					$attribute_values_count++;
				}

				$attribute_options_count++;
		 }
		// VJ product attribs end
		}
		$header_array['v_manufacturers_name'] = $iii++;
		$filelayout = array_merge($filelayout, $header_array);
		
		// build the categories name section of the array based on the number of categores the user wants to have
		for($i=1;$i<$max_categories+1;$i++){
			$filelayout = array_merge($filelayout, array('v_categories_name_' . $i => $iii++));
		}

		$filelayout = array_merge($filelayout, array(
			'v_tax_class_title'   => $iii++,
			'v_status'      => $iii++,
			));

		$filelayout_sql = "SELECT
			p.products_id as v_products_id,
			p.products_model as v_products_model,
			p.products_image as v_products_image,
			p.products_price as v_products_price,
			p.products_weight as v_products_weight,
			p.products_date_available as v_date_avail,
			p.products_date_added as v_date_added,
			p.products_tax_class_id as v_tax_class_id,
			p.products_quantity as v_products_quantity,
			p.manufacturers_id as v_manufacturers_id,
			subc.categories_id as v_categories_id,
			p.products_status as v_status
			FROM
			".TABLE_PRODUCTS." as p,
			".TABLE_CATEGORIES." as subc,
			".TABLE_PRODUCTS_TO_CATEGORIES." as ptoc
			WHERE
			p.products_id = ptoc.products_id AND
			ptoc.categories_id = subc.categories_id
			";

		break;
	case 'priceqty':
		$iii = 0;
		// uncomment the customer_price and customer_group to support multi-price per product contrib
		$filelayout = array(
			'v_products_model'    => $iii++,
			'v_specials_price'    => $iii++,
			'v_specials_date_avail'     => $iii++,
			'v_specials_expires_date'     => $iii++,
			'v_products_price'    => $iii++,
			'v_products_quantity'   => $iii++,
			//'v_customer_price_1'    => $iii++,
			//'v_customer_group_id_1'   => $iii++,
			//'v_customer_price_2'    => $iii++,
			//'v_customer_group_id_2'   => $iii++,
			//'v_customer_price_3'    => $iii++,
			//'v_customer_group_id_3'   => $iii++,
			//'v_customer_price_4'    => $iii++,
			//'v_customer_group_id_4'   => $iii++,
				);
		$filelayout_sql = "SELECT
			p.products_id as v_products_id,
			p.products_model as v_products_model,
			p.products_price as v_products_price,
			p.products_tax_class_id as v_tax_class_id,
			p.products_quantity as v_products_quantity
			FROM
			".TABLE_PRODUCTS." as p
			";

		break;

	case 'category':
		// The file layout is dynamically made depending on the number of languages
		$iii = 0;
		$filelayout = array(
			'v_products_model'    => $iii++,
		);

		// build the categories name section of the array based on the number of categores the user wants to have
		for($i=1;$i<$max_categories+1;$i++){
			$filelayout = array_merge($filelayout, array('v_categories_name_' . $i => $iii++));
		}


		$filelayout_sql = "SELECT
			p.products_id as v_products_id,
			p.products_model as v_products_model,
			subc.categories_id as v_categories_id
			FROM
			".TABLE_PRODUCTS." as p,
			".TABLE_CATEGORIES." as subc,
			".TABLE_PRODUCTS_TO_CATEGORIES." as ptoc      
			WHERE
			p.products_id = ptoc.products_id AND
			ptoc.categories_id = subc.categories_id
			";
		break;

	case 'froogle':
		// this is going to be a little interesting because we need
		// a way to map from internal names to external names
		//
		// Before it didn't matter, but with froogle needing particular headers,
		// The file layout is dynamically made depending on the number of languages
		$iii = 0;
		$filelayout = array(
			'v_froogle_products_url_1'      => $iii++,
			);
		//
		// here we need to get the default language and put
		$l_id = 1; // dummy it in for now.
//    foreach ($langcode as $key => $lang){
//      $l_id = $lang['id'];
			$filelayout  = array_merge($filelayout , array(
					'v_froogle_products_name_' . $l_id    => $iii++,
					'v_froogle_products_description_' . $l_id => $iii++,
					));
//    }
		$filelayout  = array_merge($filelayout , array(
			'v_products_price'    => $iii++,
			'v_products_fullpath_image' => $iii++,
			'v_category_fullpath'   => $iii++,
			'v_froogle_offer_id'    => $iii++,
			'v_froogle_instock'   => $iii++,
			'v_froogle_ shipping'   => $iii++,
			'v_manufacturers_name'    => $iii++,
			'v_froogle_ upc'    => $iii++,
			'v_froogle_color'   => $iii++,
			'v_froogle_size'    => $iii++,
			'v_froogle_quantitylevel' => $iii++,
			'v_froogle_product_id'    => $iii++,
			'v_froogle_manufacturer_id' => $iii++,
			'v_froogle_exp_date'    => $iii++,
			'v_froogle_product_type'  => $iii++,
			'v_froogle_delete'    => $iii++,
			'v_froogle_currency'    => $iii++,
				));
		$iii=0;
		$fileheaders = array(
			'product_url'   => $iii++,
			'name'      => $iii++,
			'description'   => $iii++,
			'price'     => $iii++,
			'image_url'   => $iii++,
			'category'    => $iii++,
			'offer_id'    => $iii++,
			'instock'   => $iii++,
			'shipping'    => $iii++,
			'brand'     => $iii++,
			'upc'     => $iii++,
			'color'     => $iii++,
			'size'      => $iii++,
			'quantity'    => $iii++,
			'product_id'    => $iii++,
			'manufacturer_id' => $iii++,
			'exp_date'    => $iii++,
			'product_type'    => $iii++,
			'delete'    => $iii++,
			'currency'    => $iii++,
			);
		$filelayout_sql = "SELECT
			p.products_id as v_products_id,
			p.products_model as v_products_model,
			p.products_image as v_products_image,
			p.products_price as v_products_price,
			p.products_weight as v_products_weight,
			p.products_date_added as v_date_added,
			p.products_date_available as v_date_avail,
			p.products_tax_class_id as v_tax_class_id,
			p.products_quantity as v_products_quantity,
			p.manufacturers_id as v_manufacturers_id,
			subc.categories_id as v_categories_id
			FROM
			".TABLE_PRODUCTS." as p,
			".TABLE_CATEGORIES." as subc,
			".TABLE_PRODUCTS_TO_CATEGORIES." as ptoc
			WHERE
			p.products_id = ptoc.products_id AND
			ptoc.categories_id = subc.categories_id
			";
		break;

// VJ product attributes begin
	case 'attrib':
		$iii = 0;
		$filelayout = array(
			'v_products_model'    => $iii++
			);

		$header_array = array();

		$languages = zen_get_languages();

		$attribute_options_count = 1;
		foreach ($attribute_options_array as $attribute_options_values) {
			$key1 = 'v_attribute_options_id_' . $attribute_options_count;
			$header_array[$key1] = $iii++;

			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$l_id = $languages[$i]['id'];

				$key2 = 'v_attribute_options_name_' . $attribute_options_count . '_' . $l_id;
				$header_array[$key2] = $iii++;
			}

			$attribute_values_query = "select products_options_values_id  from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$attribute_options_values['products_options_id'] . "' order by products_options_values_id";
			$attribute_values_values = ep_query($attribute_values_query);

			$attribute_values_count = 1;
			while ($attribute_values = mysql_fetch_array($attribute_values_values)) {
				$key3 = 'v_attribute_values_id_' . $attribute_options_count . '_' . $attribute_values_count;
				$header_array[$key3] = $iii++;

				$key4 = 'v_attribute_values_price_' . $attribute_options_count . '_' . $attribute_values_count;
				$header_array[$key4] = $iii++;

				for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
					$l_id = $languages[$i]['id'];

					$key5 = 'v_attribute_values_name_' . $attribute_options_count . '_' . $attribute_values_count . '_' . $l_id;
					$header_array[$key5] = $iii++;
				}

				$attribute_values_count++;
			}

			$attribute_options_count++;
		}

		$filelayout = array_merge($filelayout, $header_array);

		$filelayout_sql = "SELECT
			p.products_id as v_products_id,
			p.products_model as v_products_model
			FROM
			".TABLE_PRODUCTS." as p
			";

		break;
// VJ product attributes end
	}
	$filelayout_count = count($filelayout);

}

//*******************************
//*******************************
// E N D
// INITIALIZATION
//*******************************
//*******************************

$ep_dlmethod = isset($_GET['download']) ? $_GET['download'] : $ep_dlmethod;
if ($ep_dlmethod == 'stream' or  $ep_dlmethod == 'tempfile'){
	//*******************************
	//*******************************
	// DOWNLOAD FILE
	//*******************************
	//*******************************
	$filestring = ""; // this holds the csv file we want to download

	//if ($_GET['dltype']=='froogle'){
		// set the things froogle wants at the top of the file
//    $filestring .= " html_escaped=YES\n";
//    $filestring .= " updates_only=NO\n";
//    $filestring .= " product_type=OTHER\n";
//    $filestring .= " quoted=YES\n";
	//}
	$result = ep_query($filelayout_sql);
	$row =  mysql_fetch_array($result);

	// Here we need to allow for the mapping of internal field names to external field names
	// default to all headers named like the internal ones
	// the field mapping array only needs to cover those fields that need to have their name changed
	if (count($fileheaders) != 0 ) {
		$filelayout_header = $fileheaders; // if they gave us fileheaders for the dl, then use them; langer - (froogle only??)
	} else {
		$filelayout_header = $filelayout; // if no mapping was spec'd use the internal field names for header names
	}
	//We prepare the table heading with layout values
	foreach( $filelayout_header as $key => $value ){
		$filestring .= $key . $separator;
	}
	// now lop off the trailing tab
	$filestring = substr($filestring, 0, strlen($filestring)-1);

	// set the type
	if ($ep_dltype == 'froogle'){
		$endofrow = "\n";
	} else {
		// default to normal end of row
		$endofrow = $separator . 'EOREOR' . "\n";
	}
	$filestring .= $endofrow;

	$num_of_langs = count($langcode);
	while ($row){

		// if the filelayout says we need a products_name, get it
		// build the long full froogle image path
		$row['v_products_fullpath_image'] = DIR_WS_CATALOG_IMAGES . $row['v_products_image'];
		// Other froogle defaults go here for now
		$row['v_froogle_instock']     = 'Y';
		$row['v_froogle_shipping']    = '';
		$row['v_froogle_upc']       = '';
		$row['v_froogle_color']     = '';
		$row['v_froogle_size']      = '';
		$row['v_froogle_quantitylevel']   = '';
		$row['v_froogle_manufacturer_id'] = '';
		$row['v_froogle_exp_date']    = '';
		$row['v_froogle_product_type']    = 'OTHER';
		$row['v_froogle_delete']    = '';
		$row['v_froogle_currency']    = 'USD';
		$row['v_froogle_offer_id']    = $row['v_products_model'];
		$row['v_froogle_product_id']    = $row['v_products_model'];

		// names and descriptions require that we loop thru all languages that are turned on in the store
		foreach ($langcode as $key => $lang){
			$lid = $lang['id'];

			// for each language, get the description and set the vals
			$sql2 = "SELECT *
				FROM ".TABLE_PRODUCTS_DESCRIPTION."
				WHERE
					products_id = " . $row['v_products_id'] . " AND
					language_id = '" . $lid . "'
				";
			$result2 = ep_query($sql2);
			$row2 =  mysql_fetch_array($result2);

			// I'm only doing this for the first language, since right now froogle is US only.. Fix later! langer - is this still relevant?
			// adding url for froogle, but it should be available no matter what
			
				if ($num_of_langs == 1) {
					$row['v_froogle_products_url_' . $lid] = zen_catalog_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $row['v_products_id']);
				} else {
					$row['v_froogle_products_url_' . $lid] = zen_catalog_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $row['v_products_id'] . '&language=' . $lid);
				}

			$row['v_products_name_' . $lid]   = $row2['products_name'];
			$row['v_products_description_' . $lid]  = $row2['products_description'];
			if ($ep_supported_mods['psd'] == true) {
				$row['v_products_short_desc_' . $lid]   = $row2['products_short_desc'];
			}
			$row['v_products_url_' . $lid]    = $row2['products_url'];

			// froogle advanced format needs the quotes around the name and desc
			$row['v_froogle_products_name_' . $lid] = '"' . strip_tags(str_replace('"','""',$row2['products_name'])) . '"';
			$row['v_froogle_products_description_' . $lid] = '"' . strip_tags(str_replace('"','""',$row2['products_description'])) . '"';

			// support for Linda's Header Controller 2.0 here
			if (isset($filelayout['v_products_head_title_tag_' . $lid])){
				$row['v_products_head_title_tag_' . $lid]   = $row2['products_head_title_tag'];
				$row['v_products_head_desc_tag_' . $lid]  = $row2['products_head_desc_tag'];
				$row['v_products_head_keywords_tag_' . $lid]  = $row2['products_head_keywords_tag'];
			}
			// end support for Header Controller 2.0
		}
		
		// langer - specials
		if (isset($filelayout['v_specials_price'])) {
			
			$specials_query = ep_query("SELECT
						specials_new_products_price,
						specials_date_available,
						expires_date
				FROM
						".TABLE_SPECIALS."
				WHERE
				products_id = " . $row['v_products_id']);
					
			if (mysql_num_rows($specials_query)) {
				// we have a special
				$ep_specials = mysql_fetch_array($specials_query);
				$row['v_specials_price'] = $ep_specials['specials_new_products_price'];
				$row['v_specials_date_avail'] = $ep_specials['specials_date_available'];
				$row['v_specials_expires_date'] = $ep_specials['expires_date'];
			} else {
				$row['v_specials_price'] = '';
				$row['v_specials_date_avail'] = '';
				$row['v_specials_expires_date'] = '';
			}
		}
		// langer - end specials
		
		// for the categories, we need to keep looping until we find the root category

		// start with v_categories_id
		// Get the category description
		// set the appropriate variable name
		// if parent_id is not null, then follow it up.
		// we'll populate an aray first, then decide where it goes in the
		$thecategory_id = $row['v_categories_id'];
		$fullcategory = ''; // this will have the entire category stack for froogle
		for( $categorylevel=1; $categorylevel<$max_categories+1; $categorylevel++){
			if (!empty($thecategory_id)){
				$sql2 = "SELECT categories_name
					FROM ".TABLE_CATEGORIES_DESCRIPTION."
					WHERE 
						categories_id = " . $thecategory_id . " AND
						language_id = " . $epdlanguage_id ;
				$result2 = ep_query($sql2);
				$row2 =  mysql_fetch_array($result2);
				// only set it if we found something
				$temprow['v_categories_name_' . $categorylevel] = $row2['categories_name'];
				// now get the parent ID if there was one
				$sql3 = "SELECT parent_id
					FROM ".TABLE_CATEGORIES."
					WHERE
						categories_id = " . $thecategory_id;
				$result3 = ep_query($sql3);
				$row3 =  mysql_fetch_array($result3);
				$theparent_id = $row3['parent_id'];
				if ($theparent_id != ''){
					// there was a parent ID, lets set thecategoryid to get the next level
					$thecategory_id = $theparent_id;
				} else {
					// we have found the top level category for this item,
					$thecategory_id = false;
				}
				//$fullcategory .= " > " . $row2['categories_name'];
				$fullcategory = $row2['categories_name'] . " > " . $fullcategory;
			} else {
				$temprow['v_categories_name_' . $categorylevel] = '';
			}
		}
		// now trim off the last ">" from the category stack
		$row['v_category_fullpath'] = substr($fullcategory,0,strlen($fullcategory)-3);

		// temprow has the old style low to high level categories.
		$newlevel = 1;
		// let's turn them into high to low level categories
		for( $categorylevel=6; $categorylevel>0; $categorylevel--){
			if ($temprow['v_categories_name_' . $categorylevel] != ''){
				$row['v_categories_name_' . $newlevel++] = $temprow['v_categories_name_' . $categorylevel];
			}
		}
		// if the filelayout says we need a manufacturers name, get it
		if (isset($filelayout['v_manufacturers_name'])){
			if ($row['v_manufacturers_id'] != ''){
				$sql2 = "SELECT manufacturers_name
					FROM ".TABLE_MANUFACTURERS."
					WHERE
					manufacturers_id = " . $row['v_manufacturers_id']
					;
				$result2 = ep_query($sql2);
				$row2 =  mysql_fetch_array($result2);
				$row['v_manufacturers_name'] = $row2['manufacturers_name'];
			}
		}


		// If you have other modules that need to be available, put them here

		// VJ product attribs begin
		if (isset($filelayout['v_attribute_options_id_1'])){
			$languages = zen_get_languages();

			$attribute_options_count = 1;
			foreach ($attribute_options_array as $attribute_options) {
				$row['v_attribute_options_id_' . $attribute_options_count]  = $attribute_options['products_options_id'];

				for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
					$lid = $languages[$i]['id'];

					$attribute_options_languages_query = "select products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$attribute_options['products_options_id'] . "' and language_id = '" . (int)$lid . "'";
					$attribute_options_languages_values = ep_query($attribute_options_languages_query);

					$attribute_options_languages = mysql_fetch_array($attribute_options_languages_values);

					$row['v_attribute_options_name_' . $attribute_options_count . '_' . $lid] = $attribute_options_languages['products_options_name'];
				}

				$attribute_values_query = "select products_options_values_id from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$attribute_options['products_options_id'] . "' order by products_options_values_id";
				$attribute_values_values = ep_query($attribute_values_query);

				$attribute_values_count = 1;
				while ($attribute_values = mysql_fetch_array($attribute_values_values)) {
					$row['v_attribute_values_id_' . $attribute_options_count . '_' . $attribute_values_count]   = $attribute_values['products_options_values_id'];

					$attribute_values_price_query = "select options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$row['v_products_id'] . "' and options_id = '" . (int)$attribute_options['products_options_id'] . "' and options_values_id = '" . (int)$attribute_values['products_options_values_id'] . "'";
					$attribute_values_price_values = ep_query($attribute_values_price_query);

					$attribute_values_price = mysql_fetch_array($attribute_values_price_values);

					$row['v_attribute_values_price_' . $attribute_options_count . '_' . $attribute_values_count]  = $attribute_values_price['price_prefix'] . $attribute_values_price['options_values_price'];

					for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
						$lid = $languages[$i]['id'];

						$attribute_values_languages_query = "select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$attribute_values['products_options_values_id'] . "' and language_id = '" . (int)$lid . "'";
						$attribute_values_languages_values = ep_query($attribute_values_languages_query);

						$attribute_values_languages = mysql_fetch_array($attribute_values_languages_values);

						$row['v_attribute_values_name_' . $attribute_options_count . '_' . $attribute_values_count . '_' . $lid] = $attribute_values_languages['products_options_values_name'];
					}

					$attribute_values_count++;
				}

				$attribute_options_count++;
			}
		}
		// VJ product attribs end

		// this is for the separate price per customer module
		if (isset($filelayout['v_customer_price_1'])){
			$sql2 = "SELECT
					customers_group_price,
					customers_group_id
				FROM
					".TABLE_PRODUCTS_GROUPS."
				WHERE
				products_id = " . $row['v_products_id'] . "
				ORDER BY
				customers_group_id"
				;
			$result2 = ep_query($sql2);
			$ll = 1;
			$row2 =  mysql_fetch_array($result2);
			while( $row2 ){
				$row['v_customer_group_id_' . $ll]  = $row2['customers_group_id'];
				$row['v_customer_price_' . $ll]   = $row2['customers_group_price'];
				$row2 = mysql_fetch_array($result2);
				$ll++;
			}
		}
		if ($ep_dltype == 'froogle'){
			// For froogle, we check the specials prices for any applicable specials, and use that price
			// by grabbing the specials id descending, we always get the most recently added special price
			// I'm checking status because I think you can turn off specials
			$sql2 = "SELECT
					specials_new_products_price
				FROM
					".TABLE_SPECIALS."
				WHERE
				products_id = " . $row['v_products_id'] . " and
				status = 1 and
				expires_date < CURRENT_TIMESTAMP
				ORDER BY
					specials_id DESC"
				;
			$result2 = ep_query($sql2);
			$ll = 1;
			$row2 =  mysql_fetch_array($result2);
			if (!empty($row2)){
				// reset the products price to our special price if there is one for this product
				$row['v_products_price']  = $row2['specials_new_products_price'];
			}
		}

		//elari -
		//We check the value of tax class and title instead of the id
		//Then we add the tax to price if $price_with_tax is set to 1
		$row_tax_multiplier     = ep_get_tax_class_rate($row['v_tax_class_id']);
		$row['v_tax_class_title']   = zen_get_tax_class_title($row['v_tax_class_id']);
		$row['v_products_price']  = round($row['v_products_price'] + ($price_with_tax * $row['v_products_price'] * $row_tax_multiplier / 100),2);


		// Now set the status to a word the user specd in the config vars
		
		// disabled below to make uploads & downloads consistant - Numeric only
		/*if ( $row['v_status'] == '1' ){
			$row['v_status'] = $active;
		} else {
			$row['v_status'] = $inactive;
		} */

		// remove any bad things in the texts that could confuse EasyPopulate
		$therow = '';
		foreach( $filelayout as $key => $value ){

			$thetext = $row[$key];
			// kill the carriage returns and tabs in the descriptions, they're killing me!
			$thetext = str_replace("\r",' ',$thetext);
			$thetext = str_replace("\n",' ',$thetext);
			$thetext = str_replace("\t",' ',$thetext);
			// and put the text into the output separated by tabs
			$therow .= $thetext . $separator;
		}

		// lop off the trailing tab, then append the end of row indicator
		$therow = substr($therow,0,strlen($therow)-1) . $endofrow;

		$filestring .= $therow;
		// grab the next row from the db
		$row =  mysql_fetch_array($result);
	}
	
	//$EXPORT_TIME=time();
	$EXPORT_TIME = strftime('%Y%b%d-%H%I');
	switch ($ep_dltype) {
		case 'full':
		$EXPORT_TIME = "Full-EP" . $EXPORT_TIME;
		break;
		case 'priceqty':
		$EXPORT_TIME = "PriceQty-EP" . $EXPORT_TIME;
		break;
		case 'category':
		$EXPORT_TIME = "Category-EP" . $EXPORT_TIME;
		break;
		case 'froogle':
		$EXPORT_TIME = "Froogle-EP" . $EXPORT_TIME;
		break;
		case 'attrib':
		$EXPORT_TIME = "Attributes-EP" . $EXPORT_TIME;
		break;
	}

	// now either stream it to them or put it in the temp directory
	if ($ep_dlmethod == 'stream'){
		//*******************************
		// STREAM FILE
		//*******************************
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=$EXPORT_TIME.txt");
		// Changed if using SSL, helps prevent program delay/timeout (add to backup.php also)
		//  header("Pragma: no-cache");
		if ($request_type== 'NONSSL'){
			header("Pragma: no-cache");
		} else {
			header("Pragma: ");
		}
		header("Expires: 0");
		echo $filestring;
		die();
	} else {
		//*******************************
		// PUT FILE IN TEMP DIR
		//*******************************
		$tmpfpath = DIR_FS_CATALOG . '' . $tempdir . "$EXPORT_TIME.txt";
		//unlink($tmpfpath);
		$fp = fopen( $tmpfpath, "w+");
		fwrite($fp, $filestring);
		fclose($fp);
		$messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_FILE_EXPORT_SUCCESS, $EXPORT_TIME, $tempdir), 'success');
	}
}

//*******************************
//*******************************
// DOWNLOADING ENDS HERE
//*******************************
//*******************************



//*******************************
//*******************************
// UPLOADING OF FILES STARTS HERE
//*******************************
//*******************************

if ((isset($_FILES['usrfl']) or isset($GLOBALS[$usrfl . '_name'])) && ($_GET['split']==1 or $_GET['split']==2)) {
	
	
	//*******************************
	//*******************************
	// UPLOAD AND SPLIT FILE
	//*******************************
	//*******************************
	// move the file to where we can work with it
	
	$file = ep_get_uploaded_file('usrfl');
	
	if (is_uploaded_file($file['tmp_name'])) {
		ep_copy_uploaded_file($file, DIR_FS_CATALOG . '' . $tempdir);
	}

	$infp = fopen(DIR_FS_CATALOG . '' . $tempdir . $file['name'], "r");

	//toprow has the field headers
	$toprow = fgets($infp,32768);

	$filecount = 1;
	

	$tmpfname = EASYPOPULATE_FILE_SPLITS_PREFIX . $filecount . "-" . $file['name'];
	//$display_output .= 'Creating file ' . $tmpfname . '...';
	$tmpfpath = DIR_FS_CATALOG  . $tempdir . $tmpfname;
	$fp = fopen( $tmpfpath, "w+");
	fwrite($fp, $toprow);

	$linecount = 0;
	$line = fgets($infp,32768);
	while ($line){
		// walking the entire file one row at a time
		// but a line is not necessarily a complete row, we need to split on rows that have "EOREOR" at the end
		$line = str_replace('"EOREOR"', 'EOREOR', $line);
		fwrite($fp, $line);
		if (strpos($line, 'EOREOR')){
			// we found the end of a line of data, store it
			$linecount++; // increment our line counter
			if ($linecount >= $maxrecs){
				//$display_output .= "Added $linecount records and closing file... <Br>";
				$linecount = 0; // reset our line counter
				// close the existing file and open another;
				fclose($fp);
				$filecount++;
				$tmpfname = EASYPOPULATE_FILE_SPLITS_PREFIX . $filecount . "-" . $file['name'];
				//$display_output .= 'Creating file ' . $tmpfname . '...';
				$tmpfpath = DIR_FS_CATALOG  . $tempdir . $tmpfname;
				//Open next file name
				$fp = fopen( $tmpfpath, "w+");
				fwrite($fp, $toprow);
			}
		}
		$line=fgets($infp,32768);
	}
	//$display_output .= "Added $linecount records and closing file...<br><br> ";
	fclose($fp);
	fclose($infp);

	$display_output .= sprintf(EASYPOPULATE_DISPLAY_SPLIT_LOCATION, $tempdir);
}

// $split = 2 means let's process the split files now..
//if (isset($_POST['localfile']) or isset($GLOBALS['HTTP_POST_FILES'][$localfile]) or ((isset($_FILES['usrfl']) or isset($GLOBALS[$usrfl . '_name'])) && $_GET['split']==0)) {
if ((isset($_POST['localfile']) or $_GET['split']==2) or ((isset($_FILES['usrfl']) or isset($GLOBALS[$usrfl . '_name'])) && $_GET['split']==0)) {
	
	$display_output .= EASYPOPULATE_DISPLAY_HEADING;
	
	//*******************************
	//*******************************
	// UPLOAD AND INSERT FILE
	//*******************************
	//*******************************

	if ((isset($_FILES['usrfl']) or isset($GLOBALS[$usrfl . '_name'])) && $_GET['split']!=2) {
		// move the uploaded file to where we can work with it
		$file = ep_get_uploaded_file('usrfl'); // populate our array $file
		
		// langer - this copies the file to our temp dir. This is required so it can be read into file array.
		// add option to change name so it does not over-write any downloads there in same hour?
		// maybe just add option for seconds to all filenames - problem solved.
		// our uploads don't have any time stamp, but users can manage this..
		
		//$new_file_prefix = 'uploaded-'.strftime('%y%m%d-%H%I%S').'-';
		if (is_uploaded_file($file['tmp_name'])) {
			ep_copy_uploaded_file($file, DIR_FS_CATALOG . $tempdir);
		}
		$display_output .= sprintf(EASYPOPULATE_DISPLAY_UPLOADED_FILE_SPEC, $file['tmp_name'], $file['name'], $file['size']);
		
		// get the entire file into an array
		$readed = file(DIR_FS_CATALOG . $tempdir . $file['name']);
	}
	
	
	if (isset($_POST['localfile']) && $_GET['split']!=2){// dont think $_GET['split']!=2) is rwd, but hey...
		// move the file to where we can work with it
		$file = ep_get_uploaded_file('localfile');
		
		//$file['size'] = filesize(DIR_FS_CATALOG . $tempdir . $filename);
		
		// langer - what is this attribute stuff doing here?!?!!? It appears to be redundant.. Test attributed upload from localfile
		/*
		$attribute_options_query = "select distinct products_options_id from " . TABLE_PRODUCTS_OPTIONS . " order by products_options_id";
		$attribute_options_values = ep_query($attribute_options_query);
		$attribute_options_count = 1;
		*/
		
		//while ($attribute_options = mysql_fetch_array($attribute_options_values))
		
		$display_output .= sprintf(EASYPOPULATE_DISPLAY_LOCAL_FILE_SPEC, $file['name']);

		// get the entire file into an array
		$readed = file(DIR_FS_CATALOG . $tempdir . $file['name']);
	}
	
	// split = 2 means we are using ep to create page for uploading each split file in turn 
	if ($_GET['split'] == 2) {
		
		$printsplit = EASYPOPULATE_FILE_SPLITS_HEADING; //'';
		// let's set our variables for each pass...
		if (isset($_FILES['usrfl']) or isset($GLOBALS[$usrfl . '_name'])) {
			// the 1st pass.. could make it not upload 1st split file I guess (looks nicer?)
			//easy test would be to change $thisiteration = 1; to 0 instead.

			$maxcount = $filecount; // last file set this to max
			$splitfname = $file['name'];
			$thisiteration = 0; // let's begin with no file
		} else {
			$maxcount = $_GET['fc'];
			$splitfname = $_GET['fn'];
			$thisiteration = $_GET['fi'];
		}
		$nextiteration = $thisiteration + 1;
		
		$this_file = EASYPOPULATE_FILE_SPLITS_PREFIX . $thisiteration . "-" . $splitfname;
		$next_file = EASYPOPULATE_FILE_SPLITS_PREFIX . $nextiteration . "-" . $splitfname;
		
		for ($i=1, $n=$thisiteration; $i<=$n; $i++) {
			$printsplit .= EASYPOPULATE_FILE_SPLIT_COMPLETED . EASYPOPULATE_FILE_SPLITS_PREFIX . $i . "-" . $splitfname . '<br />';
		}
		if ($thisiteration == $maxcount)  {
			$printsplit .= EASYPOPULATE_FILE_SPLITS_DONE;
		} else {
			$printsplit .= '<a href="easypopulate.php?fc=' . $maxcount . '&fn=' . $splitfname . '&split=2&fi=' . $nextiteration . '">' . EASYPOPULATE_FILE_SPLIT_ANCHOR_TEXT . $next_file . '</a><br />';
		}
		for ($i=$nextiteration+1, $n=$maxcount; $i<=$n; $i++) {
			$printsplit .= EASYPOPULATE_FILE_SPLIT_PENDING . EASYPOPULATE_FILE_SPLITS_PREFIX . $i . "-" . $splitfname . '<br />';
		}
		
		if ($thisiteration == 0) {
			$readed = array();// don't start until user says..
		} else {
		// get the entire file into an array
		$readed = file(DIR_FS_CATALOG . $tempdir . $this_file);
		$display_output .= sprintf(EASYPOPULATE_DISPLAY_LOCAL_FILE_SPEC, $this_file);
		}
	}
	
	
	//*******************************
	//*******************************
	// PROCESS UPLOAD FILE
	//*******************************
	//*******************************
	
	// langer - input: $readed
		
	// these are the fields that will be defaulted to the current values in the database if they are not found in the incoming file
	// langer - why not qry products table and use result array??
	$default_these = array(
		'v_products_image',
		// redundant image mods removed
		'v_categories_id',
		'v_products_price',
		'v_products_quantity',
		'v_products_weight',
		'v_date_added',
		'v_date_avail',
		'v_instock',
		'v_tax_class_title',
		'v_manufacturers_name',
		'v_manufacturers_id',
		'v_products_dim_type',
		'v_products_length',
		'v_products_width',
		'v_products_height'
	);
	
	// now we string the entire thing together in case there were carriage returns in the data
	$newreaded = "";
	foreach ($readed as $read){
		$newreaded .= $read;
	}

	// now newreaded has the entire file together without the carriage returns
	// if for some reason we get quotes around our EOREOR, adjust our row delimiter to suit
	// this assumes the only change from \tEOREOR would be \t"EOREOR" (other text delimiters won't work yet)
	if (strpos($newreaded, '"EOREOR"') == false) {
		$row_separator = $separator . 'EOREOR';
	} else {
		$row_separator = $separator . '"EOREOR"';
	}
	
	$readed = explode($row_separator,$newreaded);
	//$readed = explode('EOREOR',$newreaded);
	
	// Now we'll populate the filelayout based on the header row.
	$theheaders_array = explode( $separator, $readed[0] ); // explode the first row, it will be our filelayout (column headings)
	$lll = 0;
	$filelayout = array();
	foreach($theheaders_array as $header){
		$cleanheader = trim(str_replace('"', '', $header)); // remove any added quotes
		// are all of our headings prefixed by v_? if not, fail upload!!
		if (substr($cleanheader,0, 2) != 'v_') {
			// we probably do not have a tab file, or 1 or more of our headings are missing..
			// error msg & fail
			// need an error var to change "Upload Complete" to "Upload Failed" or some such
			// continue;
		}
		$filelayout[$cleanheader] = $lll++; // $filelayout['1st_header'] = 1, etc..
	}
	// END CREATE HEADER LAYOUT
	// langer - output $filelayout array; $readed
	
	unset($readed[0]); //  we don't want to process the headers with the data
	// unset($readed[(count($readed))]); // the last row is always blank, so let's drop it too (or is it?? maybe not for non-windows..)
	// now we've got the array broken into parts by the expicit end-of-row marker.
	
	// BEGIN PROCESSING DATA
	foreach ($readed as $item1) {
		
		// first we clean up the row of data
		// chop any blanks from each end
		$item1 = trim($item1," ");


		// this does not account for instances where our column separator (tab, comma, etc) may exist in a text-delimited field
		// how can we explode on these, but only if not delimited??
		// eg model => url => "decription - this tab is part of description => and should not explode" => etc =>
		// the assumption is that a blank field would not have a text delimiter, so only delimited fields will satisfy the regex
		// maybe instead use preg_split on $separator where NOT /\t\".*[$separator].*\"\t/ - this leaves the delimiter in the data, only cleaning out problem ones below!
		// blow it into an array, splitting on $separator (tabs only at the mo..)
		
		// lets replace any $separator within our text delimiters, and put them back after perhaps...
		// /\t\".*?[$separator].*?\"\t/
		// $ep_replace = '/'.$separator.$txt_delim.'.*'.$separator.'.*'.$txt_delim.$separator.'/';
		
		//$items = str_replace($ep_replace,'EP~REPLACE~EP',$items);
		$items = explode($separator, $item1);
		//$items = str_replace('EP~REPLACE~EP',$separator,$items); // shit... this needs to be done for each in array $items
		
		// $separator = '/\t\".*'.$separator.'.*\"\t/';
		// might go another step here and account for all text delimiters above ("EOREOR") and stick delimiter in var (escape 1st if required - '\"')
		// so above would be  '/\t'.$var.'.*'.$separator.'.*'.$var.'\t/'; // hmmm.. but what do we split on?!?
		// needs to be "we split on this except where this... /\t/ &! /\".*(\t).*\"\t/
		// $items = preg_split($pattern, $item1);
		
		// make sure all non-set things are set to '';
		// and strip the quotes from the start and end of the stings.
		// escape any special chars for the database.
		// langer - $key is heading name, $value is column number
		foreach($filelayout as $key=> $value) {
			$i = $filelayout[$key];// langer - i is our column number, $key the name of our column heading. Check exist using if (array_key_exists($filelayout['v_model'])) ??
			
			if (zen_not_null($items[$i]) == false) {
				// let's make our null item data an empty string
				$items[$i]='';
			} else {
				
				//** FIELD DATA CLEANING - a pain in the arse because it is really important..
				
				// Check to see if either of the magic_quotes are turned on or off;
				// And apply filtering accordingly.
				if (function_exists('ini_get') && ini_get('magic_quotes_runtime') == 1) {
					//echo 'magic quotes on<br />';
					$items[$i] = trim($items[$i]);
					// The magic_quotes_runtime are on, so lets account for them
					// check if the 2nd & last character is a quote (/"xxxx/");
					// if it is, chop off the quotes and slashes.
					while (substr($items[$i],-1) == '"' && substr($items[$i],1,1) == '"') {
						$items[$i] = substr($items[$i],2,strlen($items[$i])-4);
					}
					// now any remaining doubled double quotes should be converted to one doublequote
					$items[$i] = str_replace('\"\"','"',$items[$i]);
					if ($replace_quotes == true){
						$items[$i] = str_replace('\"',"&#34",$items[$i]);
						$items[$i] = str_replace("\'","&#39",$items[$i]); // is this right? maybe "\\\'","&#39", as we are checking for literal escape and literal apostrophe??
						//$items[$i] = str_replace("\\\'","&#39",$items[$i]); // try if errors reported, though handling of db updates should be ok.
					}
				} else {
					// check if the 1st & last character are quotes;
					// if yes, chop off the 1st and last character of the string.
					$items[$i] = trim($items[$i]);
					//$debug_log = debug_log($items[$i],'item');
					while (substr($items[$i],-1) == '"' && substr($items[$i],0,1) == '"') {
						$items[$i] = substr($items[$i],1,strlen($items[$i])-2);
					}
					// now any remaining doubled double quotes should be converted to one doublequote
					$items[$i] = str_replace('""','"',$items[$i]);
					if ($replace_quotes == true){
						$items[$i] = str_replace('"',"&#34",$items[$i]);
						$items[$i] = str_replace("'","&#39",$items[$i]);
					}
				}
			}
		}
		// langer - we now have all of our fields for this product in $items[1], $items[2] etc where the array key is the column number
		//echo "DESC:".$items[$filelayout['v_products_description_1']].":END<br />";
		
		//** FIELD DATA CLEANING END
		
		
		//echo 'MODEL'.$items[$filelayout['v_products_model']].'END<br />';
		// all headings in $filelayout['columnheading'] = columnnumber, and row values are in $items[$filelayout] = 'value'
		
		// langer - inputs: $items array (file data by column #); $filelayout array (headings by column #)
		
		// now do a query to get the record's current contents
		$sql = "SELECT
			p.products_id as v_products_id,
			p.products_model as v_products_model,
			p.products_image as v_products_image,
			p.products_price as v_products_price,
			p.products_weight as v_products_weight,
			p.products_date_added as v_date_added,
			p.products_date_available as v_date_avail,
			p.products_tax_class_id as v_tax_class_id,
			p.products_quantity as v_products_quantity,
			p.manufacturers_id as v_manufacturers_id,
			subc.categories_id as v_categories_id
			FROM
			".TABLE_PRODUCTS." as p,
			".TABLE_CATEGORIES." as subc,
			".TABLE_PRODUCTS_TO_CATEGORIES." as ptoc
			WHERE
			p.products_id = ptoc.products_id AND
			p.products_model = '" . zen_db_input($items[$filelayout['v_products_model']]) . "' AND
			ptoc.categories_id = subc.categories_id
			";
		$result = ep_query($sql);
		$row =  mysql_fetch_array($result);
		$product_is_new = true;
		
		// langer - inputs: $items array (file data by column #); $filelayout array (headings by column #); $row (current db TABLE_PRODUCTS data by heading name)
		
		while ($row){
			$product_is_new = false;
						
			/*
			* Get current products descriptions and categories for this model from database
			* $row at present consists of current product data for above fields only (in $sql)
			*/
			
			// since we have a row, the item already exists.
			// let's check and delete it if requested     
			if ($items[$filelayout['v_status']] == 9) {
				$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_DELETED, $items[$filelayout['v_products_model']]);
				ep_remove_product($items[$filelayout['v_products_model']]);
				continue 2;
			}
			
			// Let's get all the data we need and fill in all the fields that need to be defaulted to the current values
			// for each language, get the description and set the vals
			foreach ($langcode as $key => $lang){
				
				$sql2 = "SELECT *
					FROM ".TABLE_PRODUCTS_DESCRIPTION."
					WHERE
						products_id = " . $row['v_products_id'] . " AND
						language_id = '" . $lang['id'] . "'
					";
				$result2 = ep_query($sql2);
				$row2 =  mysql_fetch_array($result2);
				// Need to report from ......_name_1 not ..._name_0
				$row['v_products_name_' . $lang['id']]    = $row2['products_name'];// name assigned
				$row['v_products_description_' . $lang['id']]   = $row2['products_description'];// description assigned
				// if short descriptions exist
				if ($ep_supported_mods['psd'] == true) {
					$row['v_products_short_desc_' . $lang['id']]  = $row2['products_short_desc'];
				}
				$row['v_products_url_' . $lang['id']]     = $row2['products_url'];// url assigned
	
				// support for Linda's Header Controller 2.0 here
				// if (array_key_exists($filelayout['v_products_head_title_tag_' . $lang['id']])) // langer - is this better?!?
				if (isset($filelayout['v_products_head_title_tag_' . $lang['id']])) {
					$row['v_products_head_title_tag_' . $lang['id']] = $row2['products_head_title_tag'];
					$row['v_products_head_desc_tag_' . $lang['id']] = $row2['products_head_desc_tag'];
					$row['v_products_head_keywords_tag_' . $lang['id']] = $row2['products_head_keywords_tag'];
				}
				// end support for Header Controller 2.0
			}
			// table descriptions values by each language assigned into array $row
			
			// langer - outputs: $items array (file data by column #); $filelayout array (headings by column #); $row (current db TABLE_PRODUCTS & TABLE_PRODUCTS_DESCRIPTION data by heading name)
			
			
			/**
			* Categories start.
			*/
			
			// start with v_categories_id
			// Get the category description
			// set the appropriate variable name
			// if parent_id is not null, then follow it up.
			$thecategory_id = $row['v_categories_id'];// master category id
	
			for($categorylevel=1; $categorylevel<$max_categories+1; $categorylevel++){
				if (!empty($thecategory_id)){
					$sql2 = "SELECT categories_name
						FROM ".TABLE_CATEGORIES_DESCRIPTION."
						WHERE
							categories_id = " . $thecategory_id . " AND
							language_id = " . $epdlanguage_id ;
					$result2 = ep_query($sql2);
					$row2 = mysql_fetch_array($result2);
					// only set it if we found something
					$temprow['v_categories_name_' . $categorylevel] = $row2['categories_name'];
					
					// now get the parent ID if there was one
					$sql3 = "SELECT parent_id
						FROM ".TABLE_CATEGORIES."
						WHERE
							categories_id = " . $thecategory_id;
					$result3 = ep_query($sql3);
					$row3 =  mysql_fetch_array($result3);
					$theparent_id = $row3['parent_id'];
					if ($theparent_id != ''){
						// there was a parent ID, lets set thecategoryid to get the next level
						$thecategory_id = $theparent_id;
					} else {
						// we have found the top level category for this item,
						$thecategory_id = false;
					}
				} else {
						$temprow['v_categories_name_' . $categorylevel] = '';
				}
			}
			// temprow has the old style low to high level categories.
			$newlevel = 1;
			// let's turn them into high to low level categories
			for( $categorylevel=$max_categories+1; $categorylevel>0; $categorylevel--){
				if ($temprow['v_categories_name_' . $categorylevel] != ''){
					$row['v_categories_name_' . $newlevel++] = $temprow['v_categories_name_' . $categorylevel];
				}
			}
			/**
			* Categories path for existing product retrieved from db in $row array
			*/
			
			/**
			* retrieve current manufacturer name from db for this product if exist
			*/
			if ($row['v_manufacturers_id'] != ''){
				$sql2 = "SELECT manufacturers_name
					FROM ".TABLE_MANUFACTURERS."
					WHERE
					manufacturers_id = " . $row['v_manufacturers_id']
					;
				$result2 = ep_query($sql2);
				$row2 =  mysql_fetch_array($result2);
				$row['v_manufacturers_name'] = $row2['manufacturers_name'];
			}
			
			/**
			* get tax info for this product
			*/
			//We check the value of tax class and title instead of the id
			//Then we add the tax to price if $price_with_tax is set to true
			$row_tax_multiplier = ep_get_tax_class_rate($row['v_tax_class_id']);
			$row['v_tax_class_title'] = zen_get_tax_class_title($row['v_tax_class_id']);
			if ($price_with_tax){
				$row['v_products_price'] = round($row['v_products_price'] + ($row['v_products_price'] * $row_tax_multiplier / 100),2);
			}
			
			
			/**
			* langer - the following defaults all of our current data from our db ($row array) to our update variables (called internal variables here)
			* for each $default_these - this limits it just to TABLE_PRODUCTS fields defined in this array!
			* eg $v_products_price = $row['v_products_price'];
			* perhaps we should build onto this array with each $row assignment routing above, so as to default all data to existing database
			*/
			
			// now create the internal variables that will be used
			// the $$thisvar is on purpose: it creates a variable named what ever was in $thisvar and sets the value
			// sets them to $row value, which is the existing value for these fields in the database
			foreach ($default_these as $thisvar){
				$$thisvar = $row[$thisvar];
			}
			$row =  mysql_fetch_array($result);// langer - reset our array for next stage??
		}
		/**
		* langer - We have now set our PRODUCT_TABLE vars for existing products, and got our default descriptions & categories in $row still
		* new products start here!
		*/
		
		/**
		* langer - let's have some data error checking..
		* inputs: $items; $filelayout; $product_is_new (no reliance on $row)
		*/
		if ($items[$filelayout['v_status']] == 9 && zen_not_null($items[$filelayout['v_products_model']])) {
			// new delete got this far, so cant exist in db. Cant delete what we don't have...
			$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_DELETE_NOT_FOUND, $items[$filelayout['v_products_model']]);
			continue;
		}
		if ($product_is_new == true) {
			if (!zen_not_null(trim($items[$filelayout['v_categories_name_1']])) && zen_not_null($items[$filelayout['v_products_model']])) {
			// let's skip this new product without a master category..
			$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_CATEGORY_NOT_FOUND, $items[$filelayout['v_products_model']], ' new');
			continue;
			} else {
				// minimum test for new product - model(already tested below), name, price, category, taxclass(?), status (defaults to active)
				// to add
			}
		} else { // not new product
			if (!zen_not_null(trim($items[$filelayout['v_categories_name_1']])) && isset($filelayout['v_categories_name_1'])) {
				// let's skip this existing product without a master category but has the column heading
				// or should we just update it to result of $row (it's current category..)??
				$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_CATEGORY_NOT_FOUND, $items[$filelayout['v_products_model']], '');
				foreach ($items as $col => $langer) {
					if ($col == $filelayout['v_products_model']) continue;
					$display_output .= print_el($langer);
				}
				continue;
			}
		}
		/*
		* End data checking
		**/
		
		
		/**
		* langer - assign to our vars any new data from $items (from our file)
		* output is: $v_products_model = "modelofthing", $v_products_description_1 = "descofthing", etc for each file heading
		* any existing (default) data assigned above is overwritten here with the new vals from file
		*/
		
		// this is an important loop.  What it does is go thru all the fields in the incoming file and set the internal vars.
		// Internal vars not set here are either set in the loop above for existing records, or not set at all (null values)
		// the array values are handled separately, although they will set variables in this loop, we won't use them.
		// $key is column heading name, $value is column number for the heading..
		// langer - this would appear to over-write our defaults with null values in $items if they exist
		// in other words, if we have a file heading, then you want all listed models updated in this field
		// add option here - update all null values, or ignore null values???
		foreach($filelayout as $key => $value){
			$$key = $items[$value];
		}
	
		// so how to handle these?  we shouldn't build the array unless it's been giving to us.
		// The assumption is that if you give us names and descriptions, then you give us name and description for all applicable languages
		foreach ($langcode as $lang){
			$l_id = $lang['id'];
			if (isset($filelayout['v_products_name_' . $l_id ])){ // do for each language in our upload file if exist
				// we set dynamically the language vars
				$v_products_name[$l_id] = smart_tags($items[$filelayout['v_products_name_' . $l_id]],$smart_tags,$cr_replace,false);
				$v_products_description[$l_id] = smart_tags($items[$filelayout['v_products_description_' . $l_id ]],$smart_tags,$cr_replace,$strip_smart_tags);
				
				// if short descriptions exist
				if ($ep_supported_mods['psd'] == true) {
					$v_products_short_desc[$l_id] = smart_tags($items[$filelayout['v_products_short_desc_' . $l_id ]],$smart_tags,$cr_replace,$strip_smart_tags);
				}
				$v_products_url[$l_id] = smart_tags($items[$filelayout['v_products_url_' . $l_id ]],$smart_tags,$cr_replace,false);
				
				// support for Linda's Header Controller 2.0 here
				if (isset($filelayout['v_products_head_title_tag_' . $l_id])){
					$v_products_head_title_tag[$l_id] = $items[$filelayout['v_products_head_title_tag_' . $l_id]];
					$v_products_head_desc_tag[$l_id] = $items[$filelayout['v_products_head_desc_tag_' . $l_id]];
					$v_products_head_keywords_tag[$l_id] = $items[$filelayout['v_products_head_keywords_tag_' . $l_id]];
				}
				// end support for Header Controller 2.0
			}
		}
		//elari... we get the tax_clas_id from the tax_title - from zencart??
		//on screen will still be displayed the tax_class_title instead of the id....
		if (isset($v_tax_class_title)){
			$v_tax_class_id = ep_get_tax_title_class_id($v_tax_class_title);
		}
		//we check the tax rate of this tax_class_id
		$row_tax_multiplier = ep_get_tax_class_rate($v_tax_class_id);
	
		//And we recalculate price without the included tax...
		//Since it seems display is made before, the displayed price will still include tax
		//This is same problem for the tax_clas_id that display tax_class_title
		if ($price_with_tax == true){
			$v_products_price = round( $v_products_price / (1 + ( $row_tax_multiplier * $price_with_tax/100) ), 4);
		}
	
		// if they give us one category, they give us all 6 categories
		// langer - this does not appear to support more than 7 categories??
		unset ($v_categories_name); // default to not set.
		
		//echo 'max cat len: '.$category_strlen_max.'<br/>';
		if (isset($filelayout['v_categories_name_1'])) { // does category 1 column exist in our file..
			
			$category_strlen_long = FALSE;// checks cat length does not exceed db, else exclude product from upload
			$newlevel = 1;
			for($categorylevel=6; $categorylevel>0; $categorylevel--) {
				if ($items[$filelayout['v_categories_name_' . $categorylevel]] != '') {
					if (strlen($items[$filelayout['v_categories_name_' . $categorylevel]]) > $category_strlen_max) $category_strlen_long = TRUE;
					$v_categories_name[$newlevel++] = $items[$filelayout['v_categories_name_' . $categorylevel]]; // adding the category name values to $v_categories_name array
				}// null categories are not assigned
			}
			while( $newlevel < $max_categories+1){
				$v_categories_name[$newlevel++] = ''; // default the remaining items to nothing
			}
			if ($category_strlen_long == TRUE) {
				$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_CATEGORY_NAME_LONG, $v_products_model, $category_strlen_max);
				continue;
			}
		}
		
		// langer - if null, make products qty = 1. Why?? make it 0
		if (trim($v_products_quantity) == '') {
			$v_products_quantity = 0;
		}
		
		if ($sql_fail_test == true) {
			// The following original code causes new product to fail - useful for testing
			// I keep it because I think something I changed introduced the error.. )-:
			if ($v_date_avail == '') {
				$v_date_avail = "NULL";
			} else {
				$v_date_avail = '"' . $v_date_avail . '"';
			}
			
		} else {
			// the (new) good code...
			$v_date_avail = zen_not_null(trim($v_date_avail,"\"")) ? '"' . ep_datoriser(trim($v_date_avail,"\"")) . '"' : "NULL";
			//echo $v_date_avail . '<br />';
		}
		$v_date_added = zen_not_null(trim($v_date_added,"\"")) ? '"' . ep_datoriser(trim($v_date_added,"\"")) . '"' : "NULL";
		//echo $v_date_added . '<br />';
		
		// default the stock if they spec'd it or if it's blank
		$v_db_status = '1'; // default to active
		if ($v_status == '0'){
			// they told us to deactivate this item
			$v_db_status = '0';
		}
		if (EASYPOPULATE_CONFIG_ZERO_QTY_INACTIVE == 'true' && $v_products_quantity == 0) {
			// if they said that zero qty products should be deactivated, let's deactivate if the qty is zero
			$v_db_status = '0';
		}
	
		if ($v_manufacturer_id == '') {
			$v_manufacturer_id = "NULL";
		}
	
		if (trim($v_products_image) == '') {
			$v_products_image = PRODUCTS_IMAGE_NO_IMAGE;
		}
	
		if (strlen($v_products_model) > $modelsize ){
			$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_MODEL_NAME_LONG, $v_products_model);
			continue;
		}
	
		// OK, we need to convert the manufacturer's name into id's for the database
		if ( isset($v_manufacturers_name) && $v_manufacturers_name != '' ){
			$sql = "SELECT man.manufacturers_id
				FROM ".TABLE_MANUFACTURERS." as man
				WHERE
					man.manufacturers_name = '" . zen_db_input($v_manufacturers_name) . "'";
			$result = ep_query($sql);
			$row =  mysql_fetch_array($result);
			if ( $row != '' ){
				foreach( $row as $item ){
					$v_manufacturer_id = $item;
				}
			} else {
				// to add, we need to put stuff in categories and categories_description
				$sql = "SELECT MAX( manufacturers_id) max FROM ".TABLE_MANUFACTURERS;
				$result = ep_query($sql);
				$row =  mysql_fetch_array($result);
				$max_mfg_id = $row['max']+1;
				// default the id if there are no manufacturers yet
				if (!is_numeric($max_mfg_id) ){
					$max_mfg_id=1;
				}
	
				$sql = "INSERT INTO " . TABLE_MANUFACTURERS . "(
					manufacturers_id,
					manufacturers_name,
					date_added,
					last_modified
					) VALUES (
					$max_mfg_id,
					'" . zen_db_input($v_manufacturers_name) . "',
					CURRENT_TIMESTAMP,
					CURRENT_TIMESTAMP
					)";
				$result = ep_query($sql);
				$v_manufacturer_id = $max_mfg_id;
			}
		}
		// if the categories names are set then try to update them
		if (isset($v_categories_name_1)){
			// start from the highest possible category and work our way down from the parent
			$v_categories_id = 0;
			$theparent_id = 0;
			for ( $categorylevel=$max_categories+1; $categorylevel>0; $categorylevel-- ){
				$thiscategoryname = $v_categories_name[$categorylevel];
				if ( $thiscategoryname != ''){
					// we found a category name in this field
	
					// now the subcategory
					$sql = "SELECT cat.categories_id
						FROM ".TABLE_CATEGORIES." as cat, 
								 ".TABLE_CATEGORIES_DESCRIPTION." as des
						WHERE
							cat.categories_id = des.categories_id AND
							des.language_id = $epdlanguage_id AND
							cat.parent_id = " . $theparent_id . " AND
							des.categories_name = '" . zen_db_input($thiscategoryname) . "'";
					$result = ep_query($sql);
					$row =  mysql_fetch_array($result);
					if ( $row != '' ){ // langer - null result here where len of $v_categories_name[] exceeds maximum in database
						foreach( $row as $item ){
							$thiscategoryid = $item;
						}
					} else {
						// to add, we need to put stuff in categories and categories_description
						$sql = "SELECT MAX( categories_id) max FROM ".TABLE_CATEGORIES;
						$result = ep_query($sql);
						$row =  mysql_fetch_array($result);
						$max_category_id = $row['max']+1;
						if (!is_numeric($max_category_id) ){
							$max_category_id=1;
						}
						$sql = "INSERT INTO ".TABLE_CATEGORIES."(
							categories_id,
							categories_image,
							parent_id,
							sort_order,
							date_added,
							last_modified
							) VALUES (
							$max_category_id,
							'',
							$theparent_id,
							0,
							CURRENT_TIMESTAMP,
							CURRENT_TIMESTAMP
							)";
						$result = ep_query($sql);
						$sql = "INSERT INTO ".TABLE_CATEGORIES_DESCRIPTION."(
								categories_id,
								language_id,
								categories_name
							) VALUES (
								$max_category_id,
								'$epdlanguage_id',
								'".zen_db_input($thiscategoryname)."'
							)";
						$result = ep_query($sql);
						$thiscategoryid = $max_category_id;
					}
					// the current catid is the next level's parent
					$theparent_id = $thiscategoryid;
					$v_categories_id = $thiscategoryid; // keep setting this, we need the lowest level category ID later
				}
			}
		}
		
		// insert new, or update existing, product
		if ($v_products_model != "") {
			//   products_model exists!
			
			// First we check to see if this is a product in the current db.
			$result = ep_query("SELECT products_id FROM ".TABLE_PRODUCTS." WHERE (products_model = '" . zen_db_input($v_products_model) . "')");
			
			if (mysql_num_rows($result) == 0)  {
				/*
				if ($v_categories_name_1 = ''){
					// check category exists - return without adding new if not
					$display_output .= "<font color='red'> <b>!No Category for New Product - Rejected!</b></font><br>";
					break;
				}
				*/
				//   insert into products
				$v_date_added = ($v_date_added == 'NULL') ? CURRENT_TIMESTAMP : $v_date_added;
	
				$sql = "SHOW TABLE STATUS LIKE '".TABLE_PRODUCTS."'";
				$result = ep_query($sql);
				$row =  mysql_fetch_array($result);
				$max_product_id = $row['Auto_increment'];
				//echo 'next id '.$max_product_id.'<br />';
				if (!is_numeric($max_product_id) ){
					$max_product_id=1;
				}
				$v_products_id = $max_product_id;
	
				$query = "INSERT INTO ".TABLE_PRODUCTS." (
						products_image,
						products_model,
						products_price,
						products_status,
						products_last_modified,
						products_date_added,
						products_date_available,
						products_tax_class_id,
						products_weight,
						products_quantity,
						manufacturers_id)
							VALUES (
								'".zen_db_input($v_products_image)."',";
				// redundant image mods removed
				$query .="'".zen_db_input($v_products_model)."',
									'".zen_db_input($v_products_price)."',
									'".zen_db_input($v_db_status)."',
									CURRENT_TIMESTAMP,
									$v_date_added,
									$v_date_avail,
									'".zen_db_input($v_tax_class_id)."',
									'".zen_db_input($v_products_weight)."',
									'".zen_db_input($v_products_quantity)."',
									'$v_manufacturer_id')
								";
								//echo 'New product SQL:'.$query.'<br />';
					$result = ep_query($query);
					if ($result == true) {
						$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_NEW_PRODUCT, $v_products_model);
					} else {
						$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_NEW_PRODUCT_FAIL, $v_products_model);
						continue; // langer - any new categories however have been created by now..Adding into product table needs to be 1st action?
					}
					foreach ($items as $col => $langer) {
						if ($col == $filelayout['v_products_model']) continue;
						$display_output .= print_el($langer);
					}
					
			} else {
				// existing product, get the id from the query
				// and update the product data
				
				// if date added is null, let's keep the existing date in db..
				$v_date_added = ($v_date_added == 'NULL') ? $row['v_date_added'] : $v_date_added; // if NULL, let's try to use date in db
				$v_date_added = zen_not_null($v_date_added) ? $v_date_added : CURRENT_TIMESTAMP; // if updating, but date added is null, we use today's date
				
				$row =  mysql_fetch_array($result);
				$v_products_id = $row['products_id'];
				$row =  mysql_fetch_array($result); // langer - is this to reset array, or an accidental duplication?!?
				$query = 'UPDATE '.TABLE_PRODUCTS.'
						SET
						products_price="'.zen_db_input($v_products_price).
						'" ,products_image="'.zen_db_input($v_products_image);
				// redundant image mods removed
				$query .= '", products_weight="'.zen_db_input($v_products_weight) .
						'", products_tax_class_id="'.zen_db_input($v_tax_class_id) . 
						'", products_date_available= ' . $v_date_avail .
						', products_date_added= ' . $v_date_added .
						', products_last_modified=CURRENT_TIMESTAMP' .
						', products_quantity="' . zen_db_input($v_products_quantity) .  
						'" ,manufacturers_id=' . $v_manufacturer_id . 
						' , products_status=' . zen_db_input($v_db_status) . '
						WHERE
							(products_id = "'. $v_products_id . '")';
				$result = ep_query($query);
					if ($result == true) {
						$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_UPDATE_PRODUCT, $v_products_model);
						foreach ($items as $col => $langer) {
							if ($col == $filelayout['v_products_model']) continue;
							$display_output .= print_el($langer);
						}
					} else {
						$display_output .= sprintf(EASYPOPULATE_DISPLAY_RESULT_UPDATE_PRODUCT_FAIL, $v_products_model);
					}
			}
	
			
			//*************************
			// Products Descriptions Start
			//*************************
			
			// the following is common in both the updating an existing product and creating a new product
			if (isset($v_products_name)){
				foreach( $v_products_name as $key => $name){
					if ($name!=''){
						$sql = "SELECT * FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE
								products_id = $v_products_id AND
								language_id = " . $key;
						$result = ep_query($sql);
						if (mysql_num_rows($result) == 0) {
							// nope, this is a new product description
							//$result = ep_query($sql);
							$sql =
								"INSERT INTO ".TABLE_PRODUCTS_DESCRIPTION."
									(products_id,
									language_id,
									products_name,
									products_description,";
							if ($ep_supported_mods['psd'] == true) {
								$sql .= "
									products_short_desc,";
							}
							$sql .= "
									products_url)
									VALUES (
										'" . $v_products_id . "',
										" . $key . ",
										'" . zen_db_input($name) . "',
										'" . zen_db_input($v_products_description[$key]) . "',
										";
							if ($ep_supported_mods['psd'] == true) {
								$sql .= "
									'" . zen_db_input($v_products_short_desc[$key]) . "',";
							}
								$sql .= "
										'" . zen_db_input($v_products_url[$key]) . "'
										)";
							
							// langer - the following is redundant - one SQL string is now contructed with various optional mods
							// support for Linda's Header Controller 2.0
							if (isset($v_products_head_title_tag)){
								// override the sql if we're using Linda's contrib
								$sql =
									"INSERT INTO ".TABLE_PRODUCTS_DESCRIPTION."
										(products_id,
										language_id,
										products_name,
										products_description,
										products_url,
										products_head_title_tag,
										products_head_desc_tag,
										products_head_keywords_tag)
										VALUES (
											'" . $v_products_id . "',
											" . $key . ",
											'" . zen_db_input($name) . "',
											'" . zen_db_input($v_products_description[$key]) . "',
											'". $v_products_url[$key] . "',
											'". $v_products_head_title_tag[$key] . "',
											'". $v_products_head_desc_tag[$key] . "',
											'". $v_products_head_keywords_tag[$key] . "')";
							}
							// end support for Linda's Header Controller 2.0
							//echo 'New product desc:'.$sql.'<br />';
							$result = ep_query($sql);
						} else {
							// already in the description, let's just update it
							$sql =
								"UPDATE ".TABLE_PRODUCTS_DESCRIPTION." SET
									products_name='" . zen_db_input($name) . "',
									products_description='" . zen_db_input($v_products_description[$key]) . "',
									";
							if ($ep_supported_mods['psd'] == true) {
								$sql .= "
										products_short_desc='" . zen_db_input($v_products_short_desc[$key]) . "',
										";
							}
							$sql .= "
									products_url='" . zen_db_input($v_products_url[$key]) . "'
								WHERE
									products_id = '$v_products_id' AND
									language_id = '$key'";
									
							// langer - below is redundant.
							// support for Lindas Header Controller 2.0
							if (isset($v_products_head_title_tag)){
								// override the sql if we're using Linda's contrib
								$sql =
									"UPDATE ".TABLE_PRODUCTS_DESCRIPTION." SET
										products_name='" . zen_db_input($name) . "',
										products_description = '" . zen_db_input($v_products_description[$key]) . "',
										products_url = '" . $v_products_url[$key] ."',
										products_head_title_tag = '" . $v_products_head_title_tag[$key] ."',
										products_head_desc_tag = '" . $v_products_head_desc_tag[$key] ."',
										products_head_keywords_tag = '" . $v_products_head_keywords_tag[$key] ."'
									WHERE
										products_id = '$v_products_id' AND
										language_id = '$key'";
							}
							// end support for Linda's Header Controller 2.0
							//echo 'existing product desc:'.$sql.'<br />';
							$result = ep_query($sql);
						}
					}
				}
			}
			
			//*************************
			// Products Descriptions End
			//*************************
			
			// langer - Assign product to category if linked
			
			if (isset($v_categories_id)){
				//find out if this product is listed in the category given
				$result_incategory = ep_query('SELECT
							'.TABLE_PRODUCTS_TO_CATEGORIES.'.products_id,
							'.TABLE_PRODUCTS_TO_CATEGORIES.'.categories_id
							FROM
								'.TABLE_PRODUCTS_TO_CATEGORIES.'
							WHERE
							'.TABLE_PRODUCTS_TO_CATEGORIES.'.products_id='.$v_products_id.' AND
							'.TABLE_PRODUCTS_TO_CATEGORIES.'.categories_id='.$v_categories_id);
	
				if (mysql_num_rows($result_incategory) == 0) {
					// nope, this is a new category for this product
					$res1 = ep_query('INSERT INTO '.TABLE_PRODUCTS_TO_CATEGORIES.' (products_id, categories_id)
								VALUES ("' . $v_products_id . '", "' . $v_categories_id . '")');
				} else {
					// already in this category, nothing to do!
				}
			}
			
			///************************
			// VJ product attribs begin
			//*************************
			
			if (isset($v_attribute_options_id_1)){
				$has_attributes = true;
				$attribute_rows = 1; // master row count
	
				$languages = zen_get_languages();
	
				// product options count
				$attribute_options_count = 1;
				$v_attribute_options_id_var = 'v_attribute_options_id_' . $attribute_options_count;
				
				// langer - isset & not empty $v_attribute_options_id_1 or v_attribute_options_id_2 etc
				while (isset($$v_attribute_options_id_var) && $$v_attribute_options_id_var != '') {
					// langer - above was: && !empty($$v_attribute_options_id_var)) - this broke because 0 is a legitimate options id value
					// which appears to be not required unless user removes it...
					
					// remove product attribute options linked to this product before proceeding further
					// this is useful for removing attributes linked to a product
					$attributes_clean_query = "delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$v_products_id . "' and options_id = '" . (int)$$v_attribute_options_id_var . "'";
					ep_query($attributes_clean_query);
	
					$attribute_options_query = "select products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$$v_attribute_options_id_var . "'";
					$attribute_options_values = ep_query($attribute_options_query);
	
					// option table update begin
					// langer - does once initially for each model, for all options and languages in upload file
					if ($attribute_rows == 1) {
						// insert into options table if no option exists
						if (mysql_num_rows($attribute_options_values) <= 0) {
							for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
								$lid = $languages[$i]['id'];
	
								$v_attribute_options_name_var = 'v_attribute_options_name_' . $attribute_options_count . '_' . $lid;
	
								if (isset($$v_attribute_options_name_var)) {
									$attribute_options_insert_query = "insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int)$$v_attribute_options_id_var . "', '" . (int)$lid . "', '" . zen_db_input($$v_attribute_options_name_var) . "')";
									$attribute_options_insert = ep_query($attribute_options_insert_query);
								}
							}
						} else { // update options table, if options already exists
							for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
								$lid = $languages[$i]['id'];
	
								$v_attribute_options_name_var = 'v_attribute_options_name_' . $attribute_options_count . '_' . $lid;
	
								if (isset($$v_attribute_options_name_var)) {
									$attribute_options_update_lang_query = "select products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . (int)$$v_attribute_options_id_var . "' and language_id ='" . (int)$lid . "'";
									$attribute_options_update_lang_values = ep_query($attribute_options_update_lang_query);
	
									// if option name doesn't exist for particular language, insert value
									if (mysql_num_rows($attribute_options_update_lang_values) <= 0) {
										$attribute_options_lang_insert_query = "insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int)$$v_attribute_options_id_var . "', '" . (int)$lid . "', '" . zen_db_input($$v_attribute_options_name_var) . "')";
										$attribute_options_lang_insert = ep_query($attribute_options_lang_insert_query);
									} else { // if option name exists for particular language, update table
										$attribute_options_update_query = "update " . TABLE_PRODUCTS_OPTIONS . " set products_options_name = '" . zen_db_input($$v_attribute_options_name_var) . "' where products_options_id ='" . (int)$$v_attribute_options_id_var . "' and language_id = '" . (int)$lid . "'";
										$attribute_options_update = ep_query($attribute_options_update_query);
									}
								}
							}
						}
					}
					// option table update end
	
					// product option values count
					$attribute_values_count = 1;
					$v_attribute_values_id_var = 'v_attribute_values_id_' . $attribute_options_count . '_' . $attribute_values_count;
	
					// while (isset($$v_attribute_values_id_var) && !empty($$v_attribute_values_id_var))
					// langer - allowed for 0 value for attributes id also (like options id)... just in case it is possible
					while (isset($$v_attribute_values_id_var) && $$v_attribute_values_id_var != '') {
						$attribute_values_query = "select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$$v_attribute_values_id_var . "'";
						$attribute_values_values = ep_query($attribute_values_query);
	
						// options_values table update begin
						// langer - does once initially for each model, for all attributes and languages in upload file
						if ($attribute_rows == 1) {
							// insert into options_values table if no option exists
							if (mysql_num_rows($attribute_values_values) <= 0) {
								for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
									$lid = $languages[$i]['id'];
	
									$v_attribute_values_name_var = 'v_attribute_values_name_' . $attribute_options_count . '_' . $attribute_values_count . '_' . $lid;
	
									if (isset($$v_attribute_values_name_var)) {
										$attribute_values_insert_query = "insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$$v_attribute_values_id_var . "', '" . (int)$lid . "', '" . zen_db_input($$v_attribute_values_name_var) . "')";
										$attribute_values_insert = ep_query($attribute_values_insert_query);
									}
								}
	
								// insert values to pov2po table
								$attribute_values_pov2po_query = "insert into " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id) values ('" . (int)$$v_attribute_options_id_var . "', '" . (int)$$v_attribute_values_id_var . "')";
								$attribute_values_pov2po = ep_query($attribute_values_pov2po_query);
							} else { // update options table, if options already exists
								for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
									$lid = $languages[$i]['id'];
	
									$v_attribute_values_name_var = 'v_attribute_values_name_' . $attribute_options_count . '_' . $attribute_values_count . '_' . $lid;
	
									if (isset($$v_attribute_values_name_var)) {
										$attribute_values_update_lang_query = "select products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id = '" . (int)$$v_attribute_values_id_var . "' and language_id ='" . (int)$lid . "'";
										$attribute_values_update_lang_values = ep_query($attribute_values_update_lang_query);
	
										// if options_values name doesn't exist for particular language, insert value
										if (mysql_num_rows($attribute_values_update_lang_values) <= 0) {
											$attribute_values_lang_insert_query = "insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$$v_attribute_values_id_var . "', '" . (int)$lid . "', '" . zen_db_input($$v_attribute_values_name_var) . "')";
											$attribute_values_lang_insert = ep_query($attribute_values_lang_insert_query);
										} else { // if options_values name exists for particular language, update table
											$attribute_values_update_query = "update " . TABLE_PRODUCTS_OPTIONS_VALUES . " set products_options_values_name = '" . zen_db_input($$v_attribute_values_name_var) . "' where products_options_values_id ='" . (int)$$v_attribute_values_id_var . "' and language_id = '" . (int)$lid . "'";
											$attribute_values_update = ep_query($attribute_values_update_query);
										}
									}
								}
							}
						}
						// options_values table update end
	
						// options_values price update begin
						$v_attribute_values_price_var = 'v_attribute_values_price_' . $attribute_options_count . '_' . $attribute_values_count;
	
						if (isset($$v_attribute_values_price_var) && ($$v_attribute_values_price_var != '')) {
							$attribute_prices_query = "select options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$v_products_id . "' and options_id ='" . (int)$$v_attribute_options_id_var . "' and options_values_id = '" . (int)$$v_attribute_values_id_var . "'";
							$attribute_prices_values = ep_query($attribute_prices_query);
	
							$attribute_values_price_prefix = ($$v_attribute_values_price_var < 0) ? '-' : '+';
	
							// options_values_prices table update begin
							// insert into options_values_prices table if no price exists
							if (mysql_num_rows($attribute_prices_values) <= 0) {
								$attribute_prices_insert_query = "insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id, options_values_price, price_prefix) values ('" . (int)$v_products_id . "', '" . (int)$$v_attribute_options_id_var . "', '" . (int)$$v_attribute_values_id_var . "', '" . (float)$$v_attribute_values_price_var . "', '" . zen_db_input($attribute_values_price_prefix) . "')";
								$attribute_prices_insert = ep_query($attribute_prices_insert_query);
							} else { // update options table, if options already exists
								$attribute_prices_update_query = "update " . TABLE_PRODUCTS_ATTRIBUTES . " set options_values_price = '" . $$v_attribute_values_price_var . "', price_prefix = '" . $attribute_values_price_prefix . "' where products_id = '" . (int)$v_products_id . "' and options_id = '" . (int)$$v_attribute_options_id_var . "' and options_values_id ='" . (int)$$v_attribute_values_id_var . "'";
								$attribute_prices_update = ep_query($attribute_prices_update_query);
							}
						}
						// options_values price update end
	
						$attribute_values_count++;
						$v_attribute_values_id_var = 'v_attribute_values_id_' . $attribute_options_count . '_' . $attribute_values_count;
					}
	
					$attribute_options_count++;
					$v_attribute_options_id_var = 'v_attribute_options_id_' . $attribute_options_count;
				}
	
				$attribute_rows++;
				
			}
			
			
			//*************************
			// VJ product attribs end
			//*************************
			
			/**
			* Specials
			* if a null value in specials price, do not add or update. If price = 0, let's delete it
			*/
			if (isset($v_specials_price) && zen_not_null($v_specials_price)) {
				if ($v_specials_price >= $v_products_price) {
					$specials_print .= sprintf(EASYPOPULATE_SPECIALS_PRICE_FAIL, $v_products_model, substr(strip_tags($v_products_name[$epdlanguage_id]), 0, 10));
					//available function: zen_set_specials_status($specials_id, $status)
					// could alternatively make status inactive, and still upload..
					continue;
				}
				// column is in upload file, and price is in field (not empty)
				// if null (set further above), set forever, else get raw date
				$has_specials == true;
				$v_specials_date_avail = zen_not_null($v_specials_date_avail) ? ep_datoriser($v_specials_date_avail) : '0001-01-01';
				$v_specials_expires_date = zen_not_null($v_specials_expires_date) ? ep_datoriser($v_specials_expires_date) : '0001-01-01';
				
				//Check if this product already has a special
				$special = ep_query(  "SELECT products_id
																FROM " . TABLE_SPECIALS . "
																WHERE products_id = ". $v_products_id);
																
				if (mysql_num_rows($special) == 0) {
					// not in db..
					if ($v_specials_price == '0') {
						// delete requested, but is not a special
						$specials_print .= sprintf(EASYPOPULATE_SPECIALS_DELETE_FAIL, $v_products_model, substr(strip_tags($v_products_name[$epdlanguage_id]), 0, 10));
						continue;
					}
					
								// insert new into specials
								$sql =  "INSERT INTO " . TABLE_SPECIALS . "
												(products_id,
												specials_new_products_price,
												specials_date_added,
												specials_date_available,
												expires_date,
												status)
												VALUES (
														'" . (int)$v_products_id . "',
														'" . $v_specials_price . "',
														now(),
														'" . $v_specials_date_avail . "',
														'" . $v_specials_expires_date . "',
														'1')";
								$result = ep_query($sql);
								$specials_print .= sprintf(EASYPOPULATE_SPECIALS_NEW, $v_products_model, substr(strip_tags($v_products_name[$epdlanguage_id]), 0, 10), $v_products_price , $v_specials_price);
								
				} else {
					// existing product
					
					if ($v_specials_price == '0') {
						// delete of existing requested
						$gBitDb->Execute("delete from " . TABLE_SPECIALS . "
									 where products_id = '" . (int)$v_products_id . "'");
						$specials_print .= sprintf(EASYPOPULATE_SPECIALS_DELETE, $v_products_model);
						continue;
					}
								// just make an update
								$sql =  "UPDATE " . TABLE_SPECIALS . " SET
												specials_new_products_price = '" . $v_specials_price . "',
												specials_last_modified = now(),
												specials_date_available = '" . $v_specials_date_avail . "',
												expires_date = '" . $v_specials_expires_date . "',
												status = '1'
												WHERE products_id = '" . (int)$v_products_id . "'";
								//echo $sql . "<br>";
								ep_query($sql);
								$specials_print .= sprintf(EASYPOPULATE_SPECIALS_UPDATE, $v_products_model, substr(strip_tags($v_products_name[$epdlanguage_id]), 0, 10), $v_products_price , $v_specials_price);
				}
				// we still have our special here..
			}
			// end specials for this product
		
		} else {
			// this record is missing the product_model
			$display_output .= EASYPOPULATE_DISPLAY_RESULT_NO_MODEL;
			foreach ($items as $col => $langer) {
				if ($col == $filelayout['v_products_model']) continue;
				$display_output .= print_el($langer);
			}
		}
		// end of row insertion code
	}
	
	/**
	* Post-upload tasks start
	*/
		
	// update price sorter
	ep_update_prices();
	
	// specials status = 0 if date_expires is past..
	if ($has_specials == true) {
		// specials were in upload
		zen_expire_specials();
	}
		
	// update attributes sort order when all processed
	if ($has_attributes == true) {
		// attributes were in upload
		ep_update_attributes_sort_order();
	}
	
	/**
	* Post-upload tasks end
	*/
	
	$display_output .= EASYPOPULATE_DISPLAY_RESULT_UPLOAD_COMPLETE;
}

// END FILE UPLOADS

// if we had an SQL error anywhere, let's tell the user..maybe they can sort out why
if ($ep_stack_sql_error == true) $messageStack->add(EASYPOPULATE_MSGSTACK_ERROR_SQL, 'caution');

/**
* this is a rudimentary date integrity check for references to any non-existant product_id entries
* this check ought to be last, so it checks the tasks just performed as a quality check of EP...
* langer - to add: data present in table products, but not in descriptions.. user will need product info, and decide to add description, or delete product
*/
if ($_GET['dross'] == 'delete') {
	// let's delete data debris as requested...
	ep_purge_dross();
	// now check it is really gone...
	$dross = ep_get_dross();
	if (zen_not_null($dross)) {
		$string = "Product debris corresponding to the following product_id(s) cannot be deleted by EasyPopulate:\n";
		foreach ($dross as $products_id => $langer) {
			$string .= $products_id . "\n";
		}
		$string .= "It is recommended that you delete this corrupted data using phpMyAdmin.\n\n";
		write_debug_log($string);
		$messageStack->add(EASYPOPULATE_MSGSTACK_DROSS_DELETE_FAIL, 'caution');
	} else {
		$messageStack->add(EASYPOPULATE_MSGSTACK_DROSS_DELETE_SUCCESS, 'success');
	}
} else { // elseif ($_GET['dross'] == 'check')
	// we can choose a config option: check always, or only on clicking a button
	// default action when not deleting existing debris is to check for it and alert when discovered..
	$dross = ep_get_dross();
	if (zen_not_null($dross)) {
		$messageStack->add(sprintf(EASYPOPULATE_MSGSTACK_DROSS_DETECTED, count($dross), zen_href_link(FILENAME_EASYPOPULATE, 'dross=delete')), 'caution');
	}
}

/**
* Changes planned for below
* 1) 1 input field for local and server updating
* 2) default to update directly from HDD, with option to upload to temp, or update from temp
* 3) List temp files with upload, delete, etc options
* 4) Auto detecting of mods - display list of (only) installed mods, with check-box to include in download
* 5) may consider an auto-splitting feature if it can be done.
*     Will detect speed of server, safe_mode etc and determine what splitting level is required (can be over-ridden of course)
*/

// all html templating is now below here.
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<title><?php echo TITLE; ?></title>
	<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"/>
			<script type="text/javascript" src="includes/general.js"></script>
</head>
<body >
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
	<table border="0" width="100%" cellspacing="2" cellpadding="2">
		<tr>
<!-- body_text //-->
			<td width="100%" valign="top">
<?php
				echo zen_draw_separator('pixel_trans.gif', '1', '10');
?>
				<table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<td class="pageHeading"><?php echo "Easy Populate $curver"; ?></td>
					</tr>
				</table>
<?php
				echo zen_draw_separator('pixel_trans.gif', '1', '10');
?>
				<table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr>
						<td valign="top">
				
							<table width="70%" border="0" cellpadding="8" valign="top">
								<tr>
									<td width="100%">
										<p>
											<form ENCTYPE="multipart/form-data" ACTION="easypopulate.php?split=0" METHOD="POST">
												<div align = "left">
													<b>Upload EP File</b><br />
													<input TYPE="hidden" name="MAX_FILE_SIZE" value="100000000">
													<input name="usrfl" type="file" size="50">
													<input type="submit" name="buttoninsert" value="Insert into db">
													<br />
												</div>
											</form>
					
											<form ENCTYPE="multipart/form-data" ACTION="easypopulate.php?split=2" METHOD="POST">
												<div align = "left">
													<b>Split EP File</b><br />
													<input TYPE="hidden" name="MAX_FILE_SIZE" value="1000000000">
													<input name="usrfl" type="file" size="50">
													<input type="submit" name="buttonsplit" value="Split file">
													<br />
												</div>
											</form>
					
											<form ENCTYPE="multipart/form-data" ACTION="easypopulate.php" METHOD="POST">
												<div align = "left">
													<b>Import from Temp Dir (<? echo $tempdir; ?>)</b><br />
													<input TYPE="text" name="localfile" size="50">
													<input type="submit" name="buttoninsert" value="Insert into db">
													<br />
												</div>
											</form>
										</p>
										<b>Download EP and Froogle Files</b>
										<br /><br />
										<!-- Download file links -  Add your custom fields here -->
										<a href="easypopulate.php?download=stream&dltype=full">Download <b>Complete</b> tab-delimited .txt file to edit</a>
<?php if ($products_with_attributes == true) { ?>
										<span class="fieldRequired"> (Attributes Included)</span>
<?php } else { ?>
										<span class="fieldRequired"> (Attributes Not Included)</span>
<?php } ?>
										<br />
										<a href="easypopulate.php?download=stream&dltype=priceqty">Download <b>Model/Price/Qty</b> tab-delimited .txt file to edit</a><br />
										<a href="easypopulate.php?download=stream&dltype=category">Download <b>Model/Category</b> tab-delimited .txt file to edit</a><br />
										<a href="easypopulate.php?download=stream&dltype=froogle">Download <b>Froogle</b> tab-delimited .txt file</a><br />
										<a href="easypopulate.php?download=stream&dltype=attrib">Download <b>Model/Attributes</b> tab-delimited .txt file</a>
										<br /><br />
										<b>Create EP and Froogle Files in Temp Dir (<? echo $tempdir; ?>)</b>
										<br /><br />
										<a href="easypopulate.php?download=tempfile&dltype=full">Create <b>Complete</b> tab-delimited .txt file in temp dir</a>
<?php if ($products_with_attributes == true) { ?>
										<span class="fieldRequired"> (Attributes Included)</span>
<?php } else { ?>
										<span class="fieldRequired"> (Attributes Not Included)</span>
<?php } ?>
										<br />
										<a href="easypopulate.php?download=tempfile&dltype=priceqty">Create <b>Model/Price/Qty</b> tab-delimited .txt file in temp dir</a><br />
										<a href="easypopulate.php?download=tempfile&dltype=category">Create <b>Model/Category</b> tab-delimited .txt file in temp dir</a><br />
										<a href="easypopulate.php?download=tempfile&dltype=froogle">Create <b>Froogle</b> tab-delimited .txt file in temp dir</a><br />
										<a href="easypopulate.php?download=tempfile&dltype=attrib">Create <b>Model/Attributes</b> tab-delimited .txt file in temp dir</a><br />
									</td>
								</tr>
							</table>
<?php
							echo '<br />' . $printsplit; // our files splitting matrix
							echo $display_output; // upload results
							if (strlen($specials_print) > strlen(EASYPOPULATE_SPECIALS_HEADING)) {
								echo '<br />' . $specials_print . EASYPOPULATE_SPECIALS_FOOTER; // specials summary
							}
?>
						</td>
					</tr>
				</table>
	
			</td>
<!-- body_text_eof //-->
		</tr>
	</table>
<!-- body_eof //-->
	<br />
<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

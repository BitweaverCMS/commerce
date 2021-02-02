<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers
// |
// | http://www.zen-cart.com/index.php
// |
// | Portions Copyright (c) 2003 osCommerce
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,
// | that is bundled with this package in the file LICENSE, and is
// | available through the world-wide-web at the following url:
// | http://www.zen-cart.com/license/2_0.txt.
// | If you did not receive a copy of the zen-cart license and are unable
// | to obtain it through the world-wide-web, please send a note to
// | license@zen-cart.com so we can mail you a copy immediately.
// +----------------------------------------------------------------------+
//

require_once( '../../kernel/setup_inc.php' );

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );

// Force hiding of columns in admin side for maximum real estate and productivity. - spiderr
$gBitSystem->mConfig["bitcommerce_hide_right_col"] = 'y';
$gBitSystem->mConfig["bitcommerce_hide_left_col"] = 'y';

// Admin is special and we WANT to see any and all errors here
ini_set('display_errors', '1');

// Set the level of error reporting
if( defined( 'IS_LIVE' ) && IS_LIVE ) {
	error_reporting(E_ALL & ~E_NOTICE);
} else {
	error_reporting(E_ALL);
}

global $gBitSystem, $gBitUser, $gBitThemes;
$gBitSystem->verifyPermission( 'p_bitcommerce_admin' );
if( !empty( $_POST ) ) {
	// This is admin - verify ticket at all times
	$gBitUser->verifyTicket();
}

define( 'BITCOMMERCE_ADMIN', TRUE );

// We need to buffer output
ob_start();

// Start the clock for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());


// Set the local configuration parameters - mainly for developers
if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// Check for application configuration parameters
if (!file_exists('includes/configure.php')) {
	if (file_exists('../zc_install/index.php')) {
		//header('location: ../zc_install/index.php');
		echo 'ERROR: Admin configure.php not found. Suggest running install? <a href="../zc_install/index.php">Click here for installation</a>';
	} else {
		die('ERROR: admin/includes/configure.php file not found. Suggest running zc_install/index.php?');
	}
}
// Include application configuration parameters
require_once('includes/configure.php');

// ignore version-check if INI file setting has been set
if (file_exists(DIR_FS_ADMIN . 'includes/local/skip_version_check.ini')) {
	$lines=@file(DIR_FS_ADMIN . 'includes/local/skip_version_check.ini');
	foreach($lines as $line) {
		if (substr($line,0,14)=='admin_configure_php_check=') $check_cfg=substr(trim(strtolower(str_replace('admin_configure_php_check=','',$line))),0,3);
	}
}

// include the list of extra configure files
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
	while ($zv_file = $za_dir->read()) {
		if (strstr($zv_file, '.php')) {
			require_once(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
		}
	}
}

// set the type of request (secure or not)
$request_type = ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';

// Used in the "Backup Manager" to compress backups
define('LOCAL_EXE_GZIP', '/usr/bin/gzip');
define('LOCAL_EXE_GUNZIP', '/usr/bin/gunzip');
define('LOCAL_EXE_ZIP', '/usr/local/bin/zip');
define('LOCAL_EXE_UNZIP', '/usr/local/bin/unzip');

// include the list of project filenames
require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'database_tables.php');

// include the list of compatibility issues
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'compatibility.php');

// customization for the design layout
define('BOX_WIDTH', 125); // how wide the boxes should be in pixels (default: 125)

// include the list of extra database tables and filenames
//	include(DIR_WS_MODULES . 'extra_datafiles.php');
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_datafiles')) {
	while ($zv_file = $za_dir->read()) {
		if (strstr($zv_file, '.php')) {
			require_once(DIR_WS_INCLUDES . 'extra_datafiles/' . $zv_file);
		}
	}
}

// Define the project version	(must come after db class is loaded)
require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'version.php');

// Determine the DATABASE patch level
$project_db_info= $gBitDb->getRow('select * from ' . TABLE_PROJECT_VERSION . " WHERE `project_version_key` = 'Zen-Cart Database' ");
define('PROJECT_DB_VERSION_MAJOR',$project_db_info['project_version_major']);
define('PROJECT_DB_VERSION_MINOR',$project_db_info['project_version_minor']);
define('PROJECT_DB_VERSION_PATCH1',$project_db_info['project_version_patch1']);
define('PROJECT_DB_VERSION_PATCH2',$project_db_info['project_version_patch2']);
define('PROJECT_DB_VERSION_PATCH1_SOURCE',$project_db_info['project_version_patch1_source']);
define('PROJECT_DB_VERSION_PATCH2_SOURCE',$project_db_info['project_version_patch2_source']);

// GZIP for Admin
// if gzip_compression is enabled, start to buffer the output
if ( (GZIP_LEVEL == '1') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4') ) {
	if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
		if (PHP_VERSION >= '4.0.4') {
			ob_start('ob_gzhandler');
		} else {
			include(DIR_WS_FUNCTIONS . 'gzip_compression.php');
			ob_start();
			ob_implicit_flush();
		}
	} else {
		@ini_set('zlib.output_compression_level', GZIP_LEVEL);
	}
}

// define our general functions used application-wide
require_once(DIR_WS_FUNCTIONS . 'general.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceVoucher.php' );
require_once(DIR_WS_FUNCTIONS . 'html_output.php');
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_email.php');

// include the list of extra functions
if ($za_dir = @dir(DIR_WS_FUNCTIONS . 'extra_functions')) {
	while ($zv_file = $za_dir->read()) {
		if (strstr($zv_file, '.php')) {
			require_once(DIR_WS_FUNCTIONS . 'extra_functions/' . $zv_file);
		}
	}
}

// initialize the logger class
require_once(DIR_WS_CLASSES . 'logger.php');

$session_started = true;

// include the language translations
require_once(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '.php');
$current_page = basename($_SERVER['SCRIPT_NAME']);
if (file_exists(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/' . $current_page)) {
	include_once(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/' . $current_page);
}
require_once( BITCOMMERCE_PKG_PATH.'admin/includes/languages/en/orders.php' );


if ($za_dir = @dir(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/extra_definitions')) {
	while ($zv_file = $za_dir->read()) {
		if (strstr($zv_file, '.php')) {
			require_once(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/extra_definitions/' . $zv_file);
		}
	}
}
// load the product class
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'products.php');
$zc_products = new products;

// Include validation functions (right now only email address)
require_once(DIR_WS_FUNCTIONS . 'validations.php');

// setup our boxes
require_once(DIR_WS_CLASSES . 'table_block.php');
require_once(DIR_WS_CLASSES . 'box.php');

// initialize the message stack for output messages
require_once(DIR_WS_CLASSES . 'message_stack.php');
$messageStack = new messageStack;
$gBitSmarty->assign_by_ref( 'messageStack', $messageStack );

//	split-page-results
//	require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'split_page_results.php');

require_once(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
require_once(DIR_WS_CLASSES . 'object_info.php');

// email classes
require_once(DIR_WS_CLASSES . 'mime.php');
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'email.php');

// file uploading class
require_once(DIR_WS_CLASSES . 'upload.php');

// set a default time limit
zen_set_time_limit( $gCommerceSystem->getConfig( 'GLOBAL_SET_TIME_LIMIT', 600 ) );

// auto activate and expire banners
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'banner.php');
zen_activate_banners();
zen_expire_banners();

// auto expire special products
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'specials.php');
zen_start_specials();
zen_expire_specials();

// auto expire featured products
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'featured.php');
zen_start_featured();
zen_expire_featured();

// auto expire salemaker sales
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'salemaker.php');
zen_start_salemaker();
zen_expire_salemaker();

// calculate category path
if (isset($_GET['cPath'])) {
	$cPath = $_GET['cPath'];
} else {
	$cPath = '';
}

if (zen_not_null($cPath)) {
	$cPath_array = zen_parse_category_path($cPath);
	$cPath = implode('_', $cPath_array);
	$current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
} else {
	$current_category_id = 0;
}

// default open navigation box
if( empty( $_SESSION['selected_box'] ) ) {
	$_SESSION['selected_box'] = 'configuration';
}

if (isset($_GET['selected_box'])) {
	$_SESSION['selected_box'] = $_GET['selected_box'];
}


// check if a default currency is set
if (!defined('DEFAULT_CURRENCY')) {
	$messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
}

// check if a default language is set
if (!defined('DEFAULT_LANGUAGE')) {
	$messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
}

if (function_exists('ini_get') && ((bool)ini_get('file_uploads') == false) ) {
	$messageStack->add(WARNING_FILE_UPLOADS_DISABLED, 'warning');
}

// set demo message
if (zen_get_configuration_key_value('ADMIN_DEMO')=='1') {
	if (zen_admin_demo()) {
		$messageStack->add(ADMIN_DEMO_ACTIVE, 'warning');
	} else {
		$messageStack->add(ADMIN_DEMO_ACTIVE_EXCLUSION, 'warning');
	}
}

// default admin settings
$admin_security = false;

$demo_check = $gBitDb->Execute("select * from " . TABLE_ADMIN . " where (`admin_name`='demo' and `admin_pass`='23ce1aad0e04a3d2334c7aef2f8ade83:58') or (`admin_name`='Admin' and `admin_pass`='e30d3c90284b0f42993b99f2c99261ae:c9')");
if (!$demo_check->EOF) {
	$admin_security = true;

	if ($admin_security = true) {
		$messageStack->add(ERROR_ADMIN_SECURITY_WARNING, 'caution');
	}
}

// include the password crypto functions
require_once(DIR_WS_FUNCTIONS . 'password_funcs.php');

// audience functions are for newsletter and mass-email audience-selection queries
require_once(DIR_WS_FUNCTIONS.'audience.php');

// log page visit into admin activity history
if (basename($_SERVER['SCRIPT_NAME']) != FILENAME_LOGIN . '.php' && basename($_SERVER['SCRIPT_NAME']) != FILENAME_DEFAULT . '.php' && isset($_SESSION['admin_id'])) {
	$sql_data_array = array( 'access_date' => $gBitDb->NOW(),
							 'admin_id' => $gBitUser->mUserId,
							 'page_accessed' =>	basename($_SERVER['SCRIPT_NAME']),
							 'page_parameters' => zen_get_all_get_params(),
							 'ip_address' => $_SERVER['REMOTE_ADDR']
							 );
	$gBitDb->associateInsert(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
}
if (!isset($_SESSION['html_editor_preference_status'])) {
	$_SESSION['html_editor_preference_status'] = HTML_EDITOR_PREFERENCE;
}

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php' );
if( !empty( $_REQUEST['oID'] ) && is_numeric( $_REQUEST['oID'] ) && $_REQUEST['oID'] < 0x1FFFFFFF ) {
	$oID = zen_db_prepare_input($_REQUEST['oID']);
	if( $order_exists = $gBitDb->GetOne("select orders_id from " . TABLE_ORDERS . " where `orders_id` = ?", array( $oID ) ) ) {
		$order = new order($oID);
		$gBitSmarty->assign( 'gBitOrder', $order, TRUE );
	} else {
		$messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
	}
}

$gBitThemes->loadCss( BITCOMMERCE_PKG_PATH.'css/admin.css' );

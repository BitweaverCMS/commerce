<?php
// globalize the major singletons
global $gBitProduct, $gCommerceSystem, $gBitDb, $gBitSmarty, $gBitThemes, $gBitUser;
$directory_array = array();

// Set the local configuration parameters - mainly for developers
if (file_exists(BITCOMMERCE_PKG_INCLUDE_PATH.'local/configure.php')) {
	require_once(BITCOMMERCE_PKG_INCLUDE_PATH.'local/configure.php');
}
// include server parameters
if (file_exists(BITCOMMERCE_PKG_INCLUDE_PATH.'configure.php')) {
	require_once(BITCOMMERCE_PKG_INCLUDE_PATH.'configure.php');
}

// include the list of extra configure files
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
	while ($zv_file = $za_dir->read()) {
		if (strstr($zv_file, '.php')) {
			require_once(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
		}
	}
}

// Define the project version	(must come after DB class is loaded)
require_once(DIR_FS_INCLUDES . 'version.php');

// set the type of request (secure or not)
$request_type = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';

// include the list of project filenames
require_once(DIR_FS_INCLUDES . 'filenames.php');

// include the list of project database tables
require_once(DIR_FS_INCLUDES . 'database_tables.php');

// include the list of compatibility issues
require_once(DIR_FS_INCLUDES . 'functions/compatibility.php');

// include the list of compatibility issues
require_once(BITCOMMERCE_PKG_PATH . 'includes/functions/functions_categories.php');

// include the list of extra database tables and filenames
//	require_once(DIR_WS_MODULES . 'extra_datafiles.php');
if ($za_dir = @dir(DIR_FS_INCLUDES . 'extra_datafiles')) {
	while ($zv_file = $za_dir->read()) {
		if (strstr($zv_file, '.php')) {
			require_once(DIR_FS_INCLUDES . 'extra_datafiles/' . $zv_file);
		}
	}
}

// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
if ( defined( 'SEARCH_ENGINE_FRIENDLY_URLS' ) && SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
	if (strlen($_SERVER['REQUEST_URI']) > 1) {
		$GET_array = array();
		$vars = explode('/', substr($_SERVER['REQUEST_URI'], 1));
		for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
			if (strpos($vars[$i], '[]')) {
				$GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
			} else {
				$_REQUEST[$vars[$i]] = $vars[$i+1];
			}
			$i++;
		}

		if (sizeof($GET_array) > 0) {
			while (list($key, $value) = each($GET_array)) {
				$_REQUEST[$key] = $value;
			}
		}
	}
}

// Load db classes
CommerceSystem::loadSingleton();

// set the language
if( empty( $_SESSION['languages_id'] ) || isset($_GET['language'])) {
	require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'classes/language.php' );
	$lng = new language();

	if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
		$lng->set_language($_GET['language']);
	} else {
		$lng->get_browser_language();
		$lng->set_language(DEFAULT_LANGUAGE);
	}

//		if( $lng->load( $gBitCustomer->getLanguage() ) ) {
//			$_SESSION['languages_id'] = $lng->mInfo['languages_id'];
//	} else {
		$_SESSION['languages_id'] = 1;
//	}
}

// include the cache class
require_once(DIR_FS_CLASSES . 'cache.php');
$zc_cache = new cache;

// sniffer class
require_once(DIR_FS_CLASSES . 'sniffer.php');
$sniffer = new sniffer;

if( !empty( $_SESSION['customer_id'] ) && !$gBitUser->isRegistered() ) {
	// we have lost our bitweaver login
	unset( $_SESSION['customer_id'] );
	$customerId = NULL;
} elseif( $gBitUser->isRegistered() ) {
	CommerceCustomer::syncBitUser( $gBitUser->mInfo );
	$_SESSION['customer_id'] = $gBitUser->mUserId;
	$customerId = $gBitUser->mUserId;
} else {
	$customerId = NULL;
}

global $gBitCustomer, $gCommerceCart;
$gBitCustomer = new CommerceCustomer( $customerId );
$gBitCustomer->load();
$gBitSmarty->assign( 'gBitCustomer', $gBitCustomer );

// lookup information
require(BITCOMMERCE_PKG_INCLUDE_PATH.'functions/functions_lookups.php');

if( !isset( $_SESSION['cc_id'] ) ) {
	$_SESSION['cc_id'] = NULL;
}

// include currencies class and create an instance
require_once(DIR_FS_CLASSES . 'currencies.php');
global $currencies;
$currencies = new currencies();
$gBitSmarty->assign( 'gCommerceCurrencies', $currencies );

require_once(DIR_FS_CLASSES . 'category_tree.php');

// taxes
require_once(DIR_FS_FUNCTIONS . 'functions_taxes.php');

// include cache functions if enabled
if ( defined( 'USE_CACHE' ) && USE_CACHE == 'true') {
	require_once(DIR_WS_FUNCTIONS . 'cache.php');
}

// include navigation history class
require_once(DIR_FS_CLASSES . 'navigation_history.php');

// include the breadcrumb class and start the breadcrumb trail
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'classes/breadcrumb.php' );
$breadcrumb = new breadcrumb;
$gBitSmarty->assign( 'gCommerceBreadcrumbs', $breadcrumb );

// add category names or the manufacturer name to the breadcrumb trail
if (isset($cPath_array)) {
	for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
		$categories_query = "SELECT `categories_name`
												 FROM " . TABLE_CATEGORIES_DESCRIPTION . "
												 WHERE `categories_id` = '" . (int)$cPath_array[$i] . "'
												 and `language_id` = '" . (int)$_SESSION['languages_id'] . "'";


		$categories = $gBitDb->Execute($categories_query);

		if ($categories->RecordCount() > 0) {
			$breadcrumb->add($categories->fields['categories_name'], zen_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
		} else {
			break;
		}
	}
}

// split to add manufacturers_name to the display
if (isset($_REQUEST['manufacturers_id'])) {
	$manufacturers_query = "SELECT `manufacturers_name`
													FROM " . TABLE_MANUFACTURERS . "
													WHERE `manufacturers_id` = '" . (int)$_REQUEST['manufacturers_id'] . "'";

	$manufacturers = $gBitDb->Execute($manufacturers_query);

	if ($manufacturers->RecordCount() > 0) {
		$breadcrumb->add($manufacturers->fields['manufacturers_name'], zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $_REQUEST['manufacturers_id']));
	}
}

// create the global product. products_id can be an array, such as in removing from cart
if ( isset( $_REQUEST['products_id'] ) ) {
	// clean products id to by signed 4 byte integer regardless of what kind of crap comes in
	$_GET['products_id'] = $_REQUEST['products_id'] = (int)$_REQUEST['products_id'] & 0x1FFFFFFF;
	if( !empty( $_POST['products_id'] ) ) {
		$_POST['products_id'] = $_GET['products_id']; 
	}

	if( $gBitProduct = bc_get_commerce_product( array( 'products_id' => $_REQUEST['products_id'] ) ) ) {
		if( $gBitProduct->isValid() ) {
			if( empty( $_REQUEST['cPath'] ) && !empty( $gBitProduct->mInfo['master_categories_id'] ) ) {
				$_REQUEST['cPath'] = $gBitProduct->mInfo['master_categories_id'];
			}
		} else {
			global $gBitSystem;
			$gBitSystem->setHttpStatus( HttpStatusCodes::HTTP_NOT_FOUND );
			unset( $gBitProduct );
		}
	}
}

if( empty( $gBitProduct ) && class_exists( 'CommerceProduct' ) ) {
	$gBitProduct = new CommerceProduct();
}

if( empty( $_REQUEST['cPath'] ) ) {
	$_REQUEST['cPath'] = '';
}
$gComCategory = new CommerceCategory( $_REQUEST['cPath'] );

if( !empty( $_REQUEST['cPath'] ) && is_numeric( $_REQUEST['cPath'] ) ) {
	$breadcrumb->add( zen_get_category_name( $_REQUEST['cPath'], $_SESSION['languages_id']), zen_href_link( FILENAME_DEFAULT, 'cPath=' . $_REQUEST['cPath'] ) );
}
if($gBitProduct->isValid() ) {
	$breadcrumb->add( $gBitProduct->getTitle(), $gBitProduct->getDisplayUrl() );
}
if( !empty( $gBitProduct ) ) {
	$gBitSmarty->assign( 'gBitProduct', $gBitProduct );
}

$gBitSmarty->assign( 'runNormal', zen_run_normal() );

$gBitThemes->loadCss( BITCOMMERCE_PKG_PATH.'css/bitcommerce.css' );


<?php
	$directory_array = array();

// Set the local configuration parameters - mainly for developers
  if (file_exists(BITCOMMERCE_PKG_PATH.'includes/local/configure.php')) {
    require_once(BITCOMMERCE_PKG_PATH.'includes/local/configure.php');
  }
// include server parameters
  if (file_exists(BITCOMMERCE_PKG_PATH.'includes/configure.php')) {
    require_once(BITCOMMERCE_PKG_PATH.'includes/configure.php');
  }

// include the list of extra configure files
  if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
    while ($zv_file = $za_dir->read()) {
      if (strstr($zv_file, '.php')) {
        require_once(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
      }
    }
  }

// Define the project version  (must come after DB class is loaded)
  require_once(DIR_FS_INCLUDES . 'version.php');

// set the type of request (secure or not)
  $request_type = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// include the list of project filenames
  require_once(DIR_FS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require_once(DIR_FS_INCLUDES . 'database_tables.php');

// include the list of compatibility issues
  require_once(DIR_FS_INCLUDES . 'functions/compatibility.php');

// include the list of compatibility issues
   require_once(BITCOMMERCE_PKG_PATH . 'includes/functions/functions_categories.php');

// include the list of extra database tables and filenames
//  require_once(DIR_WS_MODULES . 'extra_datafiles.php');
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
      $PHP_SELF = $_SERVER['SCRIPT_NAME'];
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
	global $gCommerceSystem, $gBitDb, $gBitSmarty;
	$gCommerceSystem = new CommerceSystem();
	$gBitSmarty->assign_by_ref( 'gCommerceSystem', $gCommerceSystem );

  // set the language
  if( empty( $_SESSION['languages_id'] ) || isset($_GET['language'])) {
    require_once( BITCOMMERCE_PKG_PATH.'includes/classes/language.php' );
    $lng = new language();

    if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
      $lng->set_language(DEFAULT_LANGUAGE);
    }

//    if( $lng->load( $gBitCustomer->getLanguage() ) ) {
//      $_SESSION['languages_id'] = $lng->mInfo['languages_id'];
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

	global $gBitUser;
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

	// lookup information
	require(BITCOMMERCE_PKG_PATH.'includes/functions/functions_lookups.php');

	if( !isset( $_SESSION['cc_id'] ) ) {
		$_SESSION['cc_id'] = NULL;
	}

	// include currencies class and create an instance
	require_once(DIR_FS_CLASSES . 'currencies.php');
	global $currencies;
	$currencies = new currencies();
	$gBitSmarty->assign_by_ref( 'gCommerceCurrencies', $currencies );

	require_once(DIR_FS_CLASSES . 'category_tree.php');

	// taxes
	require_once(DIR_FS_FUNCTIONS . 'functions_taxes.php');






// set the top level domains
  $http_domain = zen_get_top_level_domain(HTTP_SERVER);
  $https_domain = zen_get_top_level_domain(HTTPS_SERVER);
  $current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);
  if (SESSION_USE_FQDN == 'False') $current_domain = '.' . $current_domain;

// include cache functions if enabled
  if ( defined( 'USE_CACHE' ) && USE_CACHE == 'true') require_once(DIR_WS_FUNCTIONS . 'cache.php');


// include navigation history class
  require_once(DIR_FS_CLASSES . 'navigation_history.php');

// define how the session functions will be used
  require_once(DIR_FS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  zen_session_name('zenid');
  zen_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
    session_set_cookie_params(0, '/', (zen_not_null($current_domain) ? $current_domain : ''));

// set the session ID if it exists
   if (isset($_REQUEST[zen_session_name()])) {
     zen_session_id($_REQUEST[zen_session_name()]);
   } elseif ( ($request_type == 'SSL') && isset($_REQUEST[zen_session_name()]) ) {
     zen_session_id($_REQUEST[zen_session_name()]);
   }

// start the session
  $session_started = false;
  if (SESSION_FORCE_COOKIE_USE == 'True') {
    zen_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, '/', (zen_not_null($current_domain) ? $current_domain : ''));

    if (isset($_COOKIE['cookie_test'])) {
      zen_session_start();
      $session_started = true;
    }
  } else {
    zen_session_start();
    $session_started = true;
  }

// include the breadcrumb class and start the breadcrumb trail
  require_once( BITCOMMERCE_PKG_PATH.'includes/classes/breadcrumb.php' );
  $breadcrumb = new breadcrumb;
  $gBitSmarty->assign_by_ref( 'breadcrumb', $breadcrumb );

  $breadcrumb->add( tra( 'Shopping' ), BITCOMMERCE_PKG_URL );

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
if ( !empty( $_REQUEST['products_id'] ) && is_numeric( $_REQUEST['products_id'] ) ) {
	$gBitProduct = bc_get_commerce_product( array( 'products_id' => $_REQUEST['products_id'] ) );

	if( $gBitProduct->isValid() ) {
		if( empty( $_REQUEST['cPath'] ) && !empty( $gBitProduct->mInfo['master_categories_id'] ) ) {
			$_REQUEST['cPath'] = $gBitProduct->mInfo['master_categories_id'];
		}
		$breadcrumb->add( $gBitProduct->getTitle(), $gBitProduct->getDisplayUrl() );
	}
} elseif( class_exists( 'CommerceProduct' ) ) {
	$gBitProduct = new CommerceProduct();
}

if( empty( $_REQUEST['cPath'] ) ) {
	$_REQUEST['cPath'] = '';
}
$gComCategory = new CommerceCategory( $_REQUEST['cPath'] );

if( !empty( $_REQUEST['cPath'] ) && is_numeric( $_REQUEST['cPath'] ) ) {
	$breadcrumb->add( zen_get_category_name( $_REQUEST['cPath'], $_SESSION['languages_id']), zen_href_link( FILENAME_DEFAULT, 'cPath=' . $_REQUEST['cPath'] ) );
}

if( !empty( $gBitProduct ) ) {
	$gBitSmarty->assign_by_ref( 'gBitProduct', $gBitProduct );
}

$gBitSmarty->assign( 'runNormal', zen_run_normal() );

?>

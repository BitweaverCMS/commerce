<?php
	$directory_array = array();

// Set the local configuration parameters - mainly for developers
  if (file_exists(BITCOMMERCE_PKG_PATH.'includes/local/configure.php')) {
    include(BITCOMMERCE_PKG_PATH.'includes/local/configure.php');
  }
// include server parameters
  if (file_exists(BITCOMMERCE_PKG_PATH.'includes/configure.php')) {
    include(BITCOMMERCE_PKG_PATH.'includes/configure.php');
  }

// include the list of extra configure files
  if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
    while ($zv_file = $za_dir->read()) {
      if (strstr($zv_file, '.php')) {
        require(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
      }
    }
  }


// Define the project version  (must come after DB class is loaded)
  require(DIR_FS_INCLUDES . 'version.php');

// set the type of request (secure or not)
  $request_type = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// include the list of project filenames
  require(DIR_FS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require_once(DIR_FS_INCLUDES . 'database_tables.php');

// include the list of compatibility issues
  require(DIR_FS_INCLUDES . 'functions/compatibility.php');

// include the list of extra database tables and filenames
//  include(DIR_WS_MODULES . 'extra_datafiles.php');
  if ($za_dir = @dir(DIR_FS_INCLUDES . 'extra_datafiles')) {
    while ($zv_file = $za_dir->read()) {
      if (strstr($zv_file, '.php')) {
        require(DIR_FS_INCLUDES . 'extra_datafiles/' . $zv_file);
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
  global $gBitDb;
  $db = $gBitDb;




  $configuration = $db->Query('SELECT `configuration_key` AS `cfgkey`, `configuration_value` AS `cfgvalue`
                                 FROM ' . TABLE_CONFIGURATION );

  while (!$configuration->EOF) {
//    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
//    echo $configuration->fields['cfgkey'] . '#';
    $configuration->MoveNext();
  }
  $configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                          from ' . TABLE_PRODUCT_TYPE_LAYOUT);

  while (!$configuration->EOF) {
    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
    $configuration->movenext();
  }

  // set the language
  if( empty( $_SESSION['languages_id'] ) || isset($_GET['language'])) {
    include( BITCOMMERCE_PKG_PATH.'includes/classes/language.php' );
    $lng = new language();

    if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
      $lng->set_language(DEFAULT_LANGUAGE);
    }

//    if( $lng->load( $gBitLanguage->getLanguage() ) ) {
//      $_SESSION['languages_id'] = $lng->mInfo['languages_id'];
//	} else {
      $_SESSION['languages_id'] = 1;
//	}
  }

// include the cache class
  require(DIR_FS_CLASSES . 'cache.php');
  $zc_cache = new cache;

// sniffer class
  require(DIR_FS_CLASSES . 'sniffer.php');
  $sniffer = new sniffer;

// {{{ TIKI_MOD
	global $gBitUser;
	if( !empty( $_SESSION['customer_id'] ) && !$gBitUser->isRegistered() ) {
		// we have lost our bitweaver
		unset( $_SESSION['customer_id'] );
		$customerId = NULL;
	} elseif( $gBitUser->isRegistered() ) {
	  CommerceCustomer::syncBitUser( $gBitUser->mInfo );
	  $_SESSION['customer_id'] = $gBitUser->mUserId;
	  $customerId = $_SESSION['customer_id'];
	} else {
	  $customerId = NULL;
	}

	global $gBitCustomer;
	$gBitCustomer = new CommerceCustomer( $customerId );
	$gBitCustomer->load();

	if( $gBitUser->isRegistered() && empty( $_SESSION['customer_id'] ) ) {
		// Set theme related directories
		$sql = "SELECT count(*) as total FROM " . TABLE_CUSTOMERS . "
				WHERE customers_id = '" . zen_db_input( $gBitUser->mUserId ) . "'";
		$check_user = $db->Execute($sql);
		if( empty( $check_user['fields']['total'] ) ) {
			$_REQUEST['action'] = 'process';
			$email_format = zen_db_prepare_input( $gBitUser->mInfo['email'] );
			if( $space = strpos( $gBitUser->mInfo['real_name'], ' ' ) ) {
				$firstname = zen_db_prepare_input( substr( $gBitUser->mInfo['real_name'], 0, $space ) );
				$lastname = zen_db_prepare_input( $gBitUser->mInfo['lastname'], $space );
			}
		}
	}

// }}} TIKI_MOD

// include shopping cart class
  require_once(DIR_FS_CLASSES . 'shopping_cart.php');


// create the shopping cart & fix the cart if necesary
  if( empty( $_SESSION['cart'] ) ) {
    $_SESSION['cart'] = new shoppingCart;
  }

// include currencies class and create an instance
  require(DIR_FS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  require(DIR_FS_CLASSES . 'category_tree.php');


?>

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
// $Id$
//

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
//  define('DISPLAY_PAGE_PARSE_TIME', 'true');
// set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail('ian@zen-cart.com','IPN DEBUG MESSAGE', '0.0. in app top ' . $PHP_SELF);

  @ini_set("arg_separator.output","&");

// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/configure.php')) {
    include('includes/local/configure.php');
  }
// include server parameters
  if (file_exists('includes/configure.php')) {
    include('includes/configure.php');
  }


  require('includes/classes/db/' .DB_TYPE . '/query_factory.php');
  $gBitDb = new queryFactory();
  if ( (!$gBitDb->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false)) && (file_exists('zc_install/index.php')) ) {
    header('location: zc_install/index.php');
    exit;
  };

// Define the project version  (must come after DB class is loaded)
  require(DIR_FS_INCLUDES . 'version.php');

// set the type of request (secure or not)
  $request_type = ($_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// include the list of project filenames
  require(DIR_FS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_FS_INCLUDES . 'database_tables.php');

// include the list of compatibility issues
  require(DIR_FS_FUNCTIONS . 'compatibility.php');

// include the list of extra database tables and filenames
//  include(DIR_WS_MODULES . 'extra_datafiles.php');
  if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_datafiles')) {
    while ($zv_file = $za_dir->read()) {
      if (strstr($zv_file, '.php')) {
        require(DIR_FS_INCLUDES . 'extra_datafiles/' . $zv_file);
      }
    }
  }

// include the cache class
  require(DIR_FS_CLASSES . 'cache.php');
  $zc_cache = new cache;

  $configuration = $gBitDb->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                                 from ' . TABLE_CONFIGURATION, '', true, 150);

  while (!$configuration->EOF) {
//    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
    define($configuration->fields['cfgkey'], $configuration->fields['cfgvalue']);
//    echo $configuration->fields['cfgkey'] . '#';
    $configuration->MoveNext();
  }
// Load the database dependant query defines
  if (file_exists(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php')) {
    include(DIR_WS_CLASSES . 'db/' . DB_TYPE . '/define_queries.php');
  }
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '0.1. Got past Configuration Load ' . $PHP_SELF);

// define general functions used application-wide
  require(DIR_FS_FUNCTIONS . 'functions_general.php');
  require(DIR_FS_FUNCTIONS . 'html_output.php');
  require(DIR_FS_FUNCTIONS . 'functions_email.php');

// load extra functions
  include(DIR_WS_MODULES . 'extra_functions.php');


// set the top level domains
  $http_domain = zen_get_top_level_domain(HTTP_SERVER);
  $https_domain = zen_get_top_level_domain(HTTPS_SERVER);


// include shopping cart class
  require(DIR_FS_CLASSES . 'shopping_cart.php');


// include navigation history class
  require(DIR_FS_CLASSES . 'navigation_history.php');

// define how the session functions will be used
  require(DIR_FS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  zen_session_name('zenid');
  zen_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session ID if it exists
   if (isset($_POST[zen_session_name()])) {
     zen_session_id($_POST[zen_session_name()]);
   } elseif ( ($request_type == 'SSL') && isset($_GET[zen_session_name()]) ) {
     zen_session_id($_GET[zen_session_name()]);
   }

if (!$_SESSION['customer_id']) {
  $sql = "select * from " . TABLE_PAYPAL_SESSION . " where session_id = '" . $session_stuff[1] . "'";
  $stored_session = $gBitDb->Execute($sql);
  $_SESSION = unserialize(base64_decode($stored_session->fields['saved_session']));
}
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '0.2. Got past Session Start ' . $PHP_SELF);

// create the shopping cart & fix the cart if necesary
  if (!$gBitCustomer->mCart) {
    $gBitCustomer->mCart = new shoppingCart;
  }


// include currencies class and create an instance
  require(DIR_FS_CLASSES . 'currencies.php');
  $currencies = new currencies();

// include the mail classes
  require(DIR_FS_CLASSES . 'mime.php');
  require(DIR_FS_CLASSES . 'email.php');

// set the language
  if (!$gBitCustomer->getLanguage() || isset($_GET['language'])) {

    require(DIR_FS_CLASSES . 'language.php');

    $lng = new language();

    if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
      $lng->set_language(DEFAULT_LANGUAGE);
    }

    $gBitCustomer->getLanguage() = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];

  }

// Set theme related directories
  $sql = "select template_dir
          from " . TABLE_TEMPLATE_SELECT .
         " where template_language = '0'";

  $template_query = $gBitDb->Execute($sql);

  $template_dir = $template_query->fields['template_dir'];

  $sql = "select template_dir
          from " . TABLE_TEMPLATE_SELECT .
         " where template_language = '" . $_SESSION['languages_id'] . "'";

  $template_query = $gBitDb->Execute($sql);

  if ($template_query->RecordCount() > 0) {
      $template_dir = $template_query->fields['template_dir'];
  }

  define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES . $template_dir . '/');

  define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE . 'images/');
  define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

  require(DIR_FS_CLASSES . 'template_func.php');
  $template = new template_func(DIR_WS_TEMPLATE);

// include the language translations
// include template specific language files
  if (file_exists(DIR_WS_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '.php')) {
    $template_dir_select = $template_dir . '/';
//die('Yes ' . DIR_WS_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '.php');
  } else {
//die('NO ' . DIR_WS_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '.php');
    $template_dir_select = '';
  }


  include(DIR_WS_LANGUAGES . $template_dir_select . $gBitCustomer->getLanguage() . '.php');
if (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'Yes') mail(STORE_OWNER_EMAIL_ADDRESS,'IPN DEBUG MESSAGE', '0.3. Got past language loads ' . $PHP_SELF);

// include the extra language translations
  include(DIR_WS_MODULES . 'extra_definitions.php');

// currency
  if (!$_SESSION['currency'] || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency']) ) ) {
    if (isset($_GET['currency'])) {
      if (!$_SESSION['currency'] = zen_currency_exists($_GET['currency'])) $_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    } else {
      $_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    }
  }


// navigation history
  if (!isset($_SESSION['navigation'])) {
    $_SESSION['navigation'] = new navigationHistory;
  }
  $_SESSION['navigation']->add_current_page();
// infobox
  require(DIR_FS_CLASSES . 'boxes.php');

// initialize the message stack for output messages
  require(DIR_FS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;

// include the who's online functions
  require(DIR_FS_FUNCTIONS . 'whos_online.php');
  zen_update_whos_online();

// include the password crypto functions
  require(DIR_FS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
  require(DIR_FS_FUNCTIONS . 'validations.php');

// split-page-results
  require(DIR_FS_CLASSES . 'split_page_results.php');

// auto activate and expire banners
  require(DIR_FS_FUNCTIONS . 'banner.php');
  zen_activate_banners();
  zen_expire_banners();

// auto expire special products
  require(DIR_FS_FUNCTIONS . 'specials.php');
  zen_start_specials();
  zen_expire_specials();

// auto expire featured products
  require(DIR_FS_FUNCTIONS . 'featured.php');
  zen_start_featured();
  zen_expire_featured();

// auto expire salemaker sales
  require(DIR_FS_FUNCTIONS . 'salemaker.php');
  zen_start_salemaker();
  zen_expire_salemaker();


// include the breadcrumb class and start the breadcrumb trail
  require(DIR_FS_CLASSES . 'breadcrumb.php');
  $breadcrumb = new breadcrumb;

  $breadcrumb->add(HEADER_TITLE_CATALOG, zen_href_link(FILENAME_DEFAULT));
?>

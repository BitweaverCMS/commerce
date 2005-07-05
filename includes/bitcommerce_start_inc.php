<?php

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
//  define('DISPLAY_PAGE_PARSE_TIME', 'true');
// set the level of error reporting
error_reporting(E_ALL & ~E_NOTICE);

  @ini_set("arg_separator.output","&");

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


// determine install status
  if (( (!file_exists( BITCOMMERCE_PKG_PATH.'includes/configure.php') && !file_exists( BITCOMMERCE_PKG_PATH.'includes/local/configure.php' )) ) || (DB_TYPE == '') || (!file_exists( BITCOMMERCE_PKG_PATH.'includes/classes/db/' .DB_TYPE . '/query_factory.php'))) {
print "DIE DIE DIE ";
    header('location: zc_install/index.php');
    exit;
  }
  global $gBitDb;
  $db = $gBitDb;

// Define the project version  (must come after DB class is loaded)
  require(DIR_FS_INCLUDES . 'version.php');

// set the type of request (secure or not)
  $request_type = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// include the list of project filenames
  require(DIR_FS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_FS_INCLUDES . 'database_tables.php');

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

// include the cache class
  require(DIR_FS_CLASSES . 'cache.php');
  $zc_cache = new cache;

  $configuration = $db->Execute('select configuration_key as cfgkey, configuration_value as cfgvalue
                                 from ' . TABLE_CONFIGURATION, '', true, 150);

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

// sniffer class
  require(DIR_FS_CLASSES . 'sniffer.php');
  $sniffer = new sniffer;

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

// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
  if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
    if (strlen($_SERVER['REQUEST_URI']) > 1) {
      $GET_array = array();
      $PHP_SELF = $_SERVER['SCRIPT_NAME'];
      $vars = explode('/', substr($_SERVER['REQUEST_URI'], 1));
      for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
        if (strpos($vars[$i], '[]')) {
          $GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
        } else {
          $_GET[$vars[$i]] = $vars[$i+1];
        }
        $i++;
      }

      if (sizeof($GET_array) > 0) {
        while (list($key, $value) = each($GET_array)) {
          $_GET[$key] = $value;
        }
      }
    }
  }

// define general functions used application-wide
  require(DIR_FS_FUNCTIONS . 'functions_general.php');
  require(DIR_FS_FUNCTIONS . 'html_output.php');
  require(DIR_FS_FUNCTIONS . 'functions_email.php');

// load extra functions
  include(DIR_FS_MODULES . 'extra_functions.php');



?>

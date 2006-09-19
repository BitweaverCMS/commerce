<?php
//
// +----------------------------------------------------------------------+
// |Zen Cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The Zen Cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the Zen Cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//
require_once( BITCOMMERCE_PKG_PATH.'includes/common_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceSystem.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceProduct.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCategory.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCustomer.php' );

// Define the webserver and path parameters
  // Main webserver: eg, http://localhost - should not be empty for productive servers
  define('HTTP_SERVER', 'http://'.$_SERVER['HTTP_HOST']);
  // Secure webserver: eg, https://localhost - should not be empty for productive servers
  define('HTTPS_SERVER', 'https://'.$_SERVER['HTTP_HOST']);
  // secure webserver for checkout procedure?
  if( !defined( 'ENABLE_SSL' ) ) {
	define('ENABLE_SSL', 'true');
  }

// NOTE: be sure to leave the trailing '/' at the end of these lines if you make changes!
// * DIR_WS_* = Webserver directories (virtual/URL)
  // these paths are relative to top of your webspace ... (ie: under the public_html or httpdocs folder)
  define('DIR_WS_CATALOG', BITCOMMERCE_PKG_URL);
  define('DIR_WS_HTTPS_CATALOG', BITCOMMERCE_PKG_URL);

  define('DIR_WS_IMAGES', BITCOMMERCE_PKG_URL.'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
  define('DIR_WS_DOWNLOAD_PUBLIC', DIR_WS_CATALOG . 'pub/');
  define('DIR_WS_BLOCKS', DIR_WS_INCLUDES . 'blocks/');
  define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');

// * DIR_FS_* = Filesystem directories (local/physical)
  //the following path is a COMPLETE path to your Zen Cart files. eg: /var/www/vhost/accountname/public_html/store/
  define('DIR_FS_CATALOG', BITCOMMERCE_PKG_PATH);

  define('DIR_FS_CATALOG_IMAGES', STORAGE_PKG_PATH.BITCOMMERCE_PKG_NAME.'/images/');
  define('DIR_WS_CATALOG_IMAGES', STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/images/');

  define('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
  define('DIR_FS_CLASSES', DIR_FS_INCLUDES . 'classes/');
  define('DIR_FS_FUNCTIONS', DIR_FS_INCLUDES . 'functions/');
  define('DIR_FS_MODULES', DIR_FS_INCLUDES . 'modules/');
//  define('DIR_WS_LANGUAGES', DIR_FS_INCLUDES . 'languages/');
  define('DIR_FS_PAGES', BITCOMMERCE_PKG_PATH . 'pages/');
  define('DIR_FS_BLOCKS', DIR_FS_INCLUDES . 'blocks/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_WS_UPLOADS', DIR_WS_IMAGES . 'uploads/');
  define('DIR_FS_UPLOADS', DIR_FS_CATALOG . DIR_WS_UPLOADS);
  define('DIR_FS_EMAIL_TEMPLATES', DIR_FS_CATALOG . 'email/');

// define our database connection
  global $gBitDbType, $gBitDbName, $gBitDbHost, $gBitDbUser, $gBitDbPassword;
  define('DB_TYPE', $gBitDbType);
  define('DB_PREFIX', BITCOMMERCE_DB_PREFIX);
  define('DB_SERVER', $gBitDbHost); // eg, localhost - should not be empty
  define('DB_SERVER_USERNAME', $gBitDbUser);
  define('DB_SERVER_PASSWORD', $gBitDbPassword);
  define('DB_DATABASE', $gBitDbName);
  define('USE_PCONNECT', 'false'); // use persistent connections?
  define('STORE_SESSIONS', 'db'); // leave empty '' for default handler or set to 'db'

  // The next 2 "defines" are for SQL cache support.
  // For SQL_CACHE_METHOD, you can select from:  none, database, or file
  // If you choose "file", then you need to set the DIR_FS_SQL_CACHE to a directory where your apache
  // or webserver user has write privileges (chmod 666 or 777). We recommend using the "cache" folder inside the Zen Cart folder
  // ie: /path/to/your/webspace/public_html/zen/cache   -- leave no trailing slash
  define('SQL_CACHE_METHOD', 'none');
  define('DIR_FS_SQL_CACHE', TEMP_PKG_PATH.'zencache');

  define('DIR_WS_ICONS', BITCOMMERCE_PKG_URL.'icons/');

?>

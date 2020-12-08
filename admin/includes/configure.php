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



require_once( BITCOMMERCE_PKG_PATH.'includes/configure.php' );

define('HTTP_CATALOG_SERVER', 'http://'.$_SERVER['HTTP_HOST'] );
define('HTTPS_CATALOG_SERVER', 'https://'.$_SERVER['HTTP_HOST'] );

// secure webserver for catalog module and/or admin areas?
define('ENABLE_SSL_CATALOG', 'false');
define('ENABLE_SSL_ADMIN', 'false');

define('DIR_WS_ADMIN', BITCOMMERCE_PKG_URL.'admin/');
define('DIR_WS_HTTPS_ADMIN', BITCOMMERCE_PKG_URL.'admin/');

define('DIR_WS_CATALOG_TEMPLATE', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'includes/templates/');

define('DIR_WS_CATALOG_LANGUAGES', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'includes/languages/');

define('DIR_FS_ADMIN', '/a1/viovio/live/commerce/admin/');

define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG . 'includes/languages/');
define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');
define('DIR_FS_CATALOG_TEMPLATES', DIR_FS_CATALOG . 'includes/templates/');
define('DIR_FS_CATALOG_BLOCKS', DIR_FS_CATALOG . 'includes/blocks/');
define('DIR_FS_CATALOG_BOXES', DIR_FS_CATALOG . 'includes/boxes/');
define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');
define('DIR_FS_FILE_MANAGER_ROOT', BITCOMMERCE_PKG_PATH); // path to starting directory of the file manager

define('DIR_FS_ADMIN_INCLUDES', 'includes/');

mkdir_p( DIR_FS_CATALOG_IMAGES );

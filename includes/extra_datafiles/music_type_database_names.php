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
// $Id: music_type_database_names.php,v 1.2 2005/07/05 21:57:22 spiderr Exp $
//

define('TABLE_RECORD_ARTISTS', '`'.BIT_DB_PREFIX.DB_PREFIX . 'record_artists`');
define('TABLE_RECORD_ARTISTS_INFO', '`'.BIT_DB_PREFIX.DB_PREFIX . 'record_artists_info`');
define('TABLE_RECORD_COMPANY', '`'.BIT_DB_PREFIX.DB_PREFIX . 'record_company`');
define('TABLE_RECORD_COMPANY_INFO', '`'.BIT_DB_PREFIX.DB_PREFIX . 'record_company_info`');
define('TABLE_PRODUCT_MUSIC_EXTRA', '`'.BIT_DB_PREFIX.DB_PREFIX . 'product_music_extra`');
define('TABLE_MUSIC_GENRE', '`'.BIT_DB_PREFIX.DB_PREFIX . 'music_genre`');
define('TABLE_MUSIC_GENRE_INFO', '`'.BIT_DB_PREFIX.DB_PREFIX . 'music_genre_info`');
define('TABLE_MEDIA_MANAGER', '`'.BIT_DB_PREFIX.DB_PREFIX . 'media_manager`');
define('TABLE_MEDIA_TYPES', '`'.BIT_DB_PREFIX.DB_PREFIX . 'media_types`');
define('TABLE_MEDIA_CLIPS', '`'.BIT_DB_PREFIX.DB_PREFIX . 'media_clips`');
define('TABLE_MEDIA_TO_PRODUCTS', '`'.BIT_DB_PREFIX.DB_PREFIX . 'media_to_products`');
?>

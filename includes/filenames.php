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

$routePages = array_slice( scandir( BITCOMMERCE_PKG_PATH . 'pages/' ), 2 );
foreach( $routePages as $pageName ) {
	define( 'FILENAME_'.strtoupper( $pageName ), $pageName );
}

// define the filenames used in the project
define('FILENAME_ATTRIBUTES_CONTROLLER', 'attributes_controller');
define('FILENAME_ADMIN', 'admin');
define('FILENAME_ALT_NAV', 'alt_nav');
define('FILENAME_BACKUP', 'backup');
define('FILENAME_BANNER_MANAGER', 'banner_manager');
define('FILENAME_BANNER_STATISTICS', 'banner_statistics');
define('FILENAME_CACHE', 'cache');
define('FILENAME_CATALOG_ACCOUNT_HISTORY_INFO', 'account_history_info');
define('FILENAME_CATEGORIES', 'categories');
define('FILENAME_CONFIGURATION', 'configuration');
define('FILENAME_COUNTRIES', 'countries');
define('FILENAME_CURRENCIES', 'currencies');
define('FILENAME_CUSTOMERS', 'customers');
define('FILENAME_COUPON_ADMIN', 'coupon_admin');
define('FILENAME_CREATE_ACCOUNT', 'create_account');
define('FILENAME_DEFAULT', 'index');

// define page editor files
define('FILENAME_DEFINE_PAGES_EDITOR', 'define_pages_editor');
define('FILENAME_DEFINE_MAIN_PAGE', 'define_main_page');
define('FILENAME_DEFINE_CONTACT_US', 'define_contact_us');
define('FILENAME_DEFINE_PRIVACY', 'define_privacy');
define('FILENAME_DEFINE_SHIPPINGINFO', 'define_shippinginfo');
define('FILENAME_DEFINE_CONDITIONS', 'define_conditions');
define('FILENAME_DEFINE_CHECKOUT_SUCCESS', 'define_checkout_success');
define('FILENAME_DEFINE_PAGE_2', 'define_page_2');
define('FILENAME_DEFINE_PAGE_3', 'define_page_3');
define('FILENAME_DEFINE_PAGE_4', 'define_page_4');

define('FILENAME_DEFINE_LANGUAGE', 'define_language');
define('FILENAME_DEVELOPERS_TOOL_KIT', 'developers_tool_kit');
define('FILENAME_DOWNLOADS_MANAGER','downloads_manager');
define('FILENAME_EMAIL_WELCOME','email_welcome');
define('FILENAME_GEO_ZONES', 'geo_zones');
define('FILENAME_GROUP_PRICING', 'group_pricing');
define('FILENAME_GV_QUEUE', 'gv_queue');
define('FILENAME_GV_MAIL', 'gv_mail');
define('FILENAME_GV_SENT', 'gv_sent');
define('FILENAME_FILE_MANAGER', 'file_manager');
define('FILENAME_LANGUAGES', 'languages');
define('FILENAME_LAYOUT_CONTROLLER','layout_controller');
define('FILENAME_MAIL', 'mail');
define('FILENAME_MANUFACTURERS', 'manufacturers');
define('FILENAME_MODULES', 'modules');
define('FILENAME_NEWSLETTERS', 'newsletters');
define('FILENAME_ORDERS', 'orders');
define('FILENAME_ORDERS_INVOICE', 'invoice');
define('FILENAME_ORDERS_PACKINGSLIP', 'packingslip');
define('FILENAME_ORDERS_STATUS', 'orders_status');
define('FILENAME_OPTIONS_NAME_MANAGER', 'options_name_manager');
define('FILENAME_OPTIONS_VALUES_MANAGER', 'options_values_manager');
define('FILENAME_PAYPAL', 'paypal.php');
define('FILENAME_PRODUCTS_PRICE_MANAGER', 'products_price_manager');
define('FILENAME_PRODUCTS_EXPECTED', 'products_expected');
define('FILENAME_PRODUCTS_OPTIONS_NAME','option_name');
define('FILENAME_PRODUCTS_OPTIONS_VALUES','option_values');
define('FILENAME_PAGE_2', 'page_2');
define('FILENAME_PAGE_3', 'page_3');
define('FILENAME_PAGE_4', 'page_4');
define('FILENAME_POPUP_SHIPPING_ESTIMATOR', 'popup_shipping_estimator');
define('FILENAME_PRODUCT', 'product');
define('FILENAME_PRODUCT_TYPES', 'product_types');
define('FILENAME_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
define('FILENAME_SALEMAKER', 'salemaker');
define('FILENAME_SALEMAKER_INFO', 'salemaker_info');
define('FILENAME_SALEMAKER_POPUP', 'salemaker_popup');
define('FILENAME_SERVER_INFO', 'server_info');
define('FILENAME_SHIPPING_MODULES', 'shipping_modules');
define('FILENAME_STATS_CUSTOMERS', 'stats_customers');
define('FILENAME_STATS_CUSTOMERS_REFERRALS', 'stats_customers_referrals');
define('FILENAME_STATS_PRODUCTS_PURCHASED', 'stats_products_purchased');
define('FILENAME_STATS_PRODUCTS_VIEWED', 'stats_products_viewed');
define('FILENAME_STORE_MANAGER', 'store_manager');
define('FILENAME_STATS_PRODUCTS_LOWSTOCK', 'stats_products_lowstock');
define('FILENAME_SHIPPING', 'shippinginfo');
define('FILENAME_SUPPLIERS', 'suppliers');  
define('FILENAME_TEMPLATE_SELECT', 'template_select');
define('FILENAME_TAX_CLASSES', 'tax_classes');
define('FILENAME_TAX_RATES', 'tax_rates');
define('FILENAME_ZONES', 'zones');
define('FILENAME_WHOS_ONLINE', 'whos_online');

define('FILENAME_EMAIL_EXTRAS','email_extras.php');
define('FILENAME_ALSO_PURCHASED_PRODUCTS', 'also_purchased_products.php');
define('FILENAME_NEW_PRODUCTS', 'new_products.php');
define('FILENAME_PRODUCTS_NEW_LISTING', 'products_new_listing.php');
define('FILENAME_UPCOMING_PRODUCTS', 'upcoming_products.php');
define('FILENAME_PREV_NEXT', 'products_previous_next.php');
define('FILENAME_PREV_NEXT_DISPLAY', 'products_previous_next_display.php');

define('FILENAME_BB_INDEX', 'index.php'); // phpBB main index filename

// used for header.php settings can be overridden per template
define('FILENAME_HEADER','header.php');

define('FILENAME_BUTTON_NAMES','button_names.php');
define('FILENAME_ICON_NAMES','icon_names.php');
define('FILENAME_OTHER_IMAGES_NAMES','other_images_names.php');

define('FILENAME_COMMISSIONED','commissioned');
define('FILENAME_COMMISSIONED_PRODUCTS','commissioned_products');
define('FILENAME_COMMISSIONED_PRODUCTS_MODULE','commissioned_products.php');
define('FILENAME_COMMISSIONED_PRODUCTS_LISTING','commissioned_products_listing.php');

define('FILENAME_FEATURED','featured');
define('FILENAME_FEATURED_PRODUCTS_MODULE','featured_products.php');
define('FILENAME_FEATURED_PRODUCTS_LISTING','featured_products_listing.php');

define('FILENAME_PRODUCTS_ALL_LISTING', 'products_all_listing.php');

define('FILENAME_WISHLIST_SEND', 'wishlist_email.php');
define('FILENAME_WISHLIST', 'wishlist.php');
define('FILENAME_WISHLIST_HELP', 'wishlist_help.php');

define('FILENAME_SPECIALS_INDEX', 'specials_index.php');

define('FILENAME_CREDIT_CARDS', 'credit_cards.php');

define('FILENAME_PRODUCTS_DISCOUNT_PRICES','products_discount_prices.php');


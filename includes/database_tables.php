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
// $Id: database_tables.php,v 1.13 2006/12/13 19:32:37 lsces Exp $
//

// define the database table names used in the project
  if (!defined('BITCOMMERCE_DB_PREFIX')) define('BITCOMMERCE_DB_PREFIX', '');
  define('TABLE_ADDRESS_BOOK', '`'.BITCOMMERCE_DB_PREFIX . 'com_address_book`');
  define('TABLE_ADMIN', '`'.BITCOMMERCE_DB_PREFIX . 'com_admin`');
  define('TABLE_ADMIN_ACTIVITY_LOG', '`'.BITCOMMERCE_DB_PREFIX . 'com_admin_activity_log`');
  define('TABLE_ADDRESS_FORMAT', '`'.BITCOMMERCE_DB_PREFIX . 'com_address_format`');
  define('TABLE_AUTHORIZENET', '`'.BITCOMMERCE_DB_PREFIX . 'com_authorizenet`');
  define('TABLE_BANNERS', '`'.BITCOMMERCE_DB_PREFIX . 'com_banners`');
  define('TABLE_BANNERS_HISTORY', '`'.BITCOMMERCE_DB_PREFIX . 'com_banners_history`');
  define('TABLE_CATEGORIES', '`'.BITCOMMERCE_DB_PREFIX . 'com_categories`');
  define('TABLE_CATEGORIES_DESCRIPTION', '`'.BITCOMMERCE_DB_PREFIX . 'com_categories_description`');
  define('TABLE_CONFIGURATION', '`'.BITCOMMERCE_DB_PREFIX . 'com_configuration`');
  define('TABLE_CONFIGURATION_GROUP', '`'.BITCOMMERCE_DB_PREFIX . 'com_configuration_group`');
  define('TABLE_COUNTER', '`'.BITCOMMERCE_DB_PREFIX . 'com_counter`');
  define('TABLE_COUNTER_HISTORY', '`'.BITCOMMERCE_DB_PREFIX . 'com_counter_history`');
  define('TABLE_COUNTRIES', '`'.BITCOMMERCE_DB_PREFIX . 'com_countries`');
  define('TABLE_COUPON_GV_QUEUE', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupon_gv_queue`');
  define('TABLE_COUPON_GV_CUSTOMER', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupon_gv_customer`');
  define('TABLE_COUPON_EMAIL_TRACK', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupon_email_track`');
  define('TABLE_COUPON_REDEEM_TRACK', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupon_redeem_track`');
  define('TABLE_COUPON_RESTRICT', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupon_restrict`');
  define('TABLE_COUPONS', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupons`');
  define('TABLE_COUPONS_DESCRIPTION', '`'.BITCOMMERCE_DB_PREFIX . 'com_coupons_description`');
  define('TABLE_CURRENCIES', '`'.BITCOMMERCE_DB_PREFIX . 'com_currencies`');
  define('TABLE_CUSTOMERS', '`'.BITCOMMERCE_DB_PREFIX . 'com_customers`');
  define('TABLE_CUSTOMERS_BASKET', '`'.BITCOMMERCE_DB_PREFIX . 'com_customers_basket`');
  define('TABLE_CUSTOMERS_BASKET_ATTRIBUTES', '`'.BITCOMMERCE_DB_PREFIX . 'com_customers_basket_att`');
  define('TABLE_CUSTOMERS_INFO', '`'.BITCOMMERCE_DB_PREFIX . 'com_customers_info`');
  define('TABLE_DB_CACHE', '`'.BITCOMMERCE_DB_PREFIX . 'com_db_cache`');
  define('TABLE_EMAIL_ARCHIVE', '`'.BITCOMMERCE_DB_PREFIX . 'com_email_archive`');
  define('TABLE_FEATURED', '`'.BITCOMMERCE_DB_PREFIX . 'com_featured`');
  define('TABLE_FILES_UPLOADED', '`'.BITCOMMERCE_DB_PREFIX . 'com_files_uploaded`');
  define('TABLE_GROUP_PRICING', '`'.BITCOMMERCE_DB_PREFIX . 'com_group_pricing`');
  define('TABLE_GET_TERMS_TO_FILTER', '`'.BITCOMMERCE_DB_PREFIX . 'com_get_terms_to_filter`');
  define('TABLE_LANGUAGES', '`'.BITCOMMERCE_DB_PREFIX . 'com_languages`');
  define('TABLE_LAYOUT_BOXES', '`'.BITCOMMERCE_DB_PREFIX . 'com_layout_boxes`');
  define('TABLE_MANUFACTURERS', '`'.BITCOMMERCE_DB_PREFIX . 'com_manufacturers`');
  define('TABLE_MANUFACTURERS_INFO', '`'.BITCOMMERCE_DB_PREFIX . 'com_manufacturers_info`');
  define('TABLE_META_TAGS_PRODUCTS_DESCRIPTION', '`'.BITCOMMERCE_DB_PREFIX . 'com_meta_tags_products_desc`');
  define('TABLE_NEWSLETTERS', '`'.BITCOMMERCE_DB_PREFIX . 'com_newsletters`');
  define('TABLE_ORDERS', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders`');
  define('TABLE_ORDERS_PRODUCTS', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_products`');
  define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_products_att`');
  define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_products_dld`');
  define('TABLE_ORDERS_STATUS', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_status`');
  define('TABLE_ORDERS_STATUS_HISTORY', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_status_history`');
  define('TABLE_ORDERS_TYPE', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_type`');
  define('TABLE_ORDERS_TOTAL', '`'.BITCOMMERCE_DB_PREFIX . 'com_orders_total`');
  define('TABLE_PAYPAL', '`'.BITCOMMERCE_DB_PREFIX . 'com_paypal`');
  define('TABLE_PAYPAL_SESSION', '`'.BITCOMMERCE_DB_PREFIX . 'com_paypal_session`');
  define('TABLE_PAYPAL_PAYMENT_STATUS', '`'.BITCOMMERCE_DB_PREFIX . 'com_paypal_payment_status`');
  define('TABLE_PAYPAL_PAYMENT_STATUS_HISTORY', '`'.BITCOMMERCE_DB_PREFIX . 'com_paypal_pment_stat_his`');
  define('TABLE_PRODUCTS', '`'.BITCOMMERCE_DB_PREFIX . 'com_products`');
  define('TABLE_PRODUCT_TYPES', '`'.BITCOMMERCE_DB_PREFIX . 'com_product_types`');
  define('TABLE_PRODUCT_TYPE_LAYOUT', '`'.BITCOMMERCE_DB_PREFIX . 'com_product_type_layout`');
  define('TABLE_PRODUCT_TYPES_TO_CATEGORY', '`'.BITCOMMERCE_DB_PREFIX . 'com_product_types_to_category`');
  define('TABLE_PRODUCTS_ATTRIBUTES', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_attributes`');
  define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_attributes_dld`');
  define('TABLE_PRODUCTS_DESCRIPTION', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_description`');
  define('TABLE_PRODUCTS_DISCOUNT_QUANTITY', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_discount_quantity`');
  define('TABLE_PRODUCTS_NOTIFICATIONS', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_notifications`');
  define('TABLE_PRODUCTS_OPTIONS', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_options`');
  define('TABLE_PRODUCTS_OPTIONS_VALUES', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_options_values`');
  define('TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS', '`'.BITCOMMERCE_DB_PREFIX . 'com_prd_opt_val_to_prd_opt`');
  define('TABLE_PRODUCTS_OPTIONS_TYPES', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_options_types`');
  define('TABLE_PRODUCTS_TO_CATEGORIES', '`'.BITCOMMERCE_DB_PREFIX . 'com_products_to_categories`');
  define('TABLE_PROJECT_VERSION', '`'.BITCOMMERCE_DB_PREFIX . 'com_project_version`');
  define('TABLE_PROJECT_VERSION_HISTORY', '`'.BITCOMMERCE_DB_PREFIX . 'com_project_version_history`');
  define('TABLE_PUBS_CREDIT_CARD_LOG',  '`'.BITCOMMERCE_DB_PREFIX . 'com_pubs_credit_card_log`');
  define('TABLE_QUERY_BUILDER', '`'.BITCOMMERCE_DB_PREFIX . 'com_query_builder`');
  define('TABLE_REVIEWS', '`'.BITCOMMERCE_DB_PREFIX . 'com_reviews`');
  define('TABLE_REVIEWS_DESCRIPTION', '`'.BITCOMMERCE_DB_PREFIX . 'com_reviews_description`');
  define('TABLE_SALEMAKER_SALES', '`'.BITCOMMERCE_DB_PREFIX . 'com_salemaker_sales`');
  define('TABLE_SESSIONS', '`'.BITCOMMERCE_DB_PREFIX . 'com_sessions`');
  define('TABLE_SPECIALS', '`'.BITCOMMERCE_DB_PREFIX . 'com_specials`');
  define('TABLE_SUPPLIERS', '`'.BITCOMMERCE_DB_PREFIX . 'com_suppliers`');
  define('TABLE_SUPPLIERS_INFO', '`'.BITCOMMERCE_DB_PREFIX . 'com_suppliers_info`'); 
  define('TABLE_TEMPLATE_SELECT', '`'.BITCOMMERCE_DB_PREFIX . 'com_template_select`');
  define('TABLE_TAX_CLASS', '`'.BITCOMMERCE_DB_PREFIX . 'com_tax_class`');
  define('TABLE_TAX_RATES', '`'.BITCOMMERCE_DB_PREFIX . 'com_tax_rates`');
  define('TABLE_GEO_ZONES', '`'.BITCOMMERCE_DB_PREFIX . 'com_geo_zones`');
  define('TABLE_ZONES_TO_GEO_ZONES', '`'.BITCOMMERCE_DB_PREFIX . 'com_zones_to_geo_zones`');
  define('TABLE_UPGRADE_EXCEPTIONS', '`'.BITCOMMERCE_DB_PREFIX . 'com_upgrade_exceptions`');
  define('TABLE_WISHLIST', '`'.BITCOMMERCE_DB_PREFIX . 'com_customers_wishlist`');
  define('TABLE_WHOS_ONLINE', '`'.BITCOMMERCE_DB_PREFIX . 'com_whos_online`');
  define('TABLE_ZONES', '`'.BITCOMMERCE_DB_PREFIX . 'com_zones`');

global $gBitDb;
DEFINE('SQL_CC_ENABLED', "select `configuration_key` from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE '%CC_ENABLED%' and `configuration_value`= '1'");
DEFINE('SQL_SHOW_PRODUCT_INFO_CATEGORY', "select `configuration_key`, `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE '%SHOW_PRODUCT_INFO_CATEGORY%' order by `configuration_value`");
DEFINE('SQL_SHOW_PRODUCT_INFO_MAIN',"select `configuration_key`, `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE 'SHOW_PRODUCT_INFO_MAIN%' order by `configuration_value`");
DEFINE('SQL_SHOW_PRODUCT_INFO_MISSING',"select `configuration_key`, `configuration_value` from " . TABLE_CONFIGURATION  . " where `configuration_key` LIKE '%SHOW_PRODUCT_INFO_MISSING%' order by `configuration_value`");
DEFINE('SQL_SHOW_PRODUCT_INFO_LISTING_BELOW',"select `configuration_key`, `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE '%SHOW_PRODUCT_INFO_LISTING_BELOW%' order by `configuration_value`");
DEFINE('SQL_BANNER_CHECK_QUERY', 'select count(*) as "count" from ' . TABLE_BANNERS_HISTORY . "                where `banners_id` = '%s' and ".$gBitDb->SQLDate( 'Ymd', 'banners_history_date' )." = ".$gBitDb->SQLDate( 'Ymd' ));
DEFINE('SQL_BANNER_CHECK_UPDATE', "update " . TABLE_BANNERS_HISTORY . " set `banners_shown` = `banners_shown` +1 where `banners_id` = '%s' and ".$gBitDb->SQLDate( 'Ymd', 'banners_history_date' )." = ".$gBitDb->SQLDate( 'Ymd' ));
DEFINE('SQL_BANNER_UPDATE_CLICK_COUNT', "update " . TABLE_BANNERS_HISTORY . " set `banners_clicked` = `banners_clicked` + 1 where `banners_id` = '%s' and ".$gBitDb->SQLDate( 'Ymd', 'banners_history_date' )." = ".$gBitDb->SQLDate( 'Ymd' ));
DEFINE('SQL_ALSO_PURCHASED', "select p.`products_id`, p.`products_image`
                     from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, "
                            . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p
                     where opa.`products_id` = ?  and opa.`orders_id` = opb.`orders_id` and opb.`products_id` != ?  and opb.`products_id` = p.`products_id` and opb.`orders_id` = o.`orders_id` and p.`products_status` = '1'
                     order by o.`date_purchased` desc"
                     );


?>

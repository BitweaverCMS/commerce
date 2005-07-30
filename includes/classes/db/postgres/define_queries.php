<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |   
// | http://www.zen-cart.com/index.php                                    |   
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: define_queries.php,v 1.2 2005/07/30 03:01:53 spiderr Exp $
//
DEFINE('SQL_CC_ENABLED', "select configuration_key from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%CC_ENABLED%' and configuration_value= '1'");
DEFINE('SQL_SHOW_PRODUCT_INFO_CATEGORY', "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%SHOW_PRODUCT_INFO_CATEGORY%' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_SHOW_PRODUCT_INFO_MAIN',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%SHOW_PRODUCT_INFO_MAIN%' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_SHOW_PRODUCT_INFO_MISSING',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION  . " where configuration_key LIKE '%SHOW_PRODUCT_INFO_MISSING%' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_SHOW_PRODUCT_INFO_LISTING_BELOW',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%SHOW_PRODUCT_INFO_LISTING_BELOW%' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_BANNER_CHECK_QUERY', "select count(*) as count from " . TABLE_BANNERS_HISTORY . "                where banners_id = '" . (int)$banner_id . "' and to_char(banners_history_date, 'YYYYMMDD') = to_char(now(), 'YYYYMMDD')");
DEFINE('SQL_BANNER_CHECK_UPDATE', "update " . TABLE_BANNERS_HISTORY . " set banners_shown = banners_shown +1 where banners_id = '" . (int)$banner_id . "' and to_char(banners_history_date, 'YYYYMMDD') = to_char(now(), 'YYYYMMDD')");
DEFINE('SQL_BANNER_UPDATE_CLICK_COUNT', "update " . TABLE_BANNERS_HISTORY . " set banners_clicked = banners_clicked + 1 where banners_id = '" . (int)$banner_id . "' and to_char(banners_history_date, 'YYYYMMDD) = to_char(now(), 'YYYYMMDD')");
DEFINE('SQL_ALSO_PURCHASED', "select p.products_id, p.products_image 
                     from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, " 
                            . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p 
                     where opa.products_id = '" . (int)$_GET['products_id'] . "' 
                     and opa.orders_id = opb.orders_id 
                     and opb.products_id != '" . (int)$_GET['products_id'] . "' 
                     and opb.products_id = p.products_id 
                     and opb.orders_id = o.orders_id 
                     and p.products_status = '1' 
                     group by p.products_id, p.products_image, o.date_purchased 
                     order by o.date_purchased desc 
                     limit " . MAX_DISPLAY_ALSO_PURCHASED);
?>

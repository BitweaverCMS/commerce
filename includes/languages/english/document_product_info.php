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
// $Id: document_product_info.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

define('TEXT_PRODUCT_NOT_FOUND', 'Sorry, the product was not found.');
define('TEXT_CURRENT_REVIEWS', 'Current Reviews:');
define('TEXT_MORE_INFORMATION', 'For more information, please visit this product\'s <a href="%s" target="_blank">webpage</a>.');
define('TEXT_DATE_ADDED', 'This product was added to our catalog on %s.');
define('TEXT_DATE_AVAILABLE', '<font color="#ff0000">This product will be in stock on %s.</font>');
define('TEXT_ALSO_PURCHASED_PRODUCTS', 'Customers who bought this product also purchased...');
define('TEXT_PRODUCT_OPTIONS', '<strong>Please Choose:</strong>');
define('TEXT_PRODUCT_MANUFACTURER', 'Manufactured by: ');
define('TEXT_PRODUCT_WEIGHT', 'Shipping Weight: ');
define('TEXT_PRODUCT_WEIGHT_UNIT', ' lbs');
define('TEXT_PRODUCT_QUANTITY', ' Units in Stock');
define('TEXT_PRODUCT_MODEL', 'Model: ');



// previous next product
define('PREV_NEXT_PRODUCT', 'Product ');
define('PREV_NEXT_FROM', ' from ');
define('IMAGE_BUTTON_PREVIOUS','Previous Item');
define('IMAGE_BUTTON_NEXT','Next Item');
define('IMAGE_BUTTON_RETURN_TO_PRODUCT_LIST','Back to Product List');

// missing products
//define('TABLE_HEADING_NEW_PRODUCTS', 'New Products For %s');
//define('TABLE_HEADING_UPCOMING_PRODUCTS', 'Upcoming Products');
//define('TABLE_HEADING_DATE_EXPECTED', 'Date Expected');

define('TEXT_ATTRIBUTES_PRICE_WAS',' [was: ');
define('TEXT_ATTRIBUTE_IS_FREE',' now is: Free]');
define('TEXT_ONETIME_CHARGE_SYMBOL', ' *');
define('TEXT_ONETIME_CHARGE_DESCRIPTION', ' One time charges may apply');
define('TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK','Quantity Discounts Available');
define('ATTRIBUTES_QTY_PRICE_SYMBOL', zen_image(DIR_WS_TEMPLATE_ICONS . 'icon_status_green.gif', TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK, 10, 10) . '&nbsp;');
?>
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
// $Id$
//

define('TEXT_PRODUCT_NOT_FOUND', tra( 'Sorry, the product was not found.' ) );
define('TEXT_CURRENT_REVIEWS', tra( 'Current Reviews:' ) );
define('TEXT_RECORD_COMPANY_URL', tra( 'For more information, please visit the Record Companies <a href="%s" target="_blank">webpage</a>.' ) );
define('TEXT_ARTIST_URL', tra( 'For more information, please visit the Artist\'s <a href="%s" target="_blank">webpage</a>.' ) );
define('TEXT_DATE_ADDED', tra( 'This product was added to our catalog on %s.' ) );
define('TEXT_DATE_AVAILABLE', tra( '<font color="#ff0000">This product will be in stock on %s.</font>' ) );
define('TEXT_ALSO_PURCHASED_PRODUCTS', tra( 'Customers who bought this product also purchased...' ) );
define('TEXT_PRODUCT_OPTIONS', tra( '<strong>Please Choose:</strong>' ) );
define('TEXT_PRODUCT_RECORD_COMPANY', tra( 'Record Company: ' ) );
define('TEXT_PRODUCT_ARTIST', tra( 'Artist: ' ) );
define('TEXT_PRODUCT_MUSIC_GENRE', tra( 'Music Genre: ' ) );
define('TEXT_PRODUCT_WEIGHT', tra( 'Shipping Weight: ' ) );
define('TEXT_PRODUCT_WEIGHT_UNIT', tra( ' lbs' ) );
define('TEXT_PRODUCT_QUANTITY', tra( ' Units in Stock' ) );
define('TEXT_PRODUCT_MODEL', tra( 'Model: ' ) );
define('TEXT_PRODUCT_COLLECTIONS', tra( 'Media Collection: ' ) );



// previous next product
define('PREV_NEXT_PRODUCT', tra( 'Product ' ) );
define('PREV_NEXT_FROM', tra( ' from ' ) );
define('IMAGE_BUTTON_PREVIOUS', tra( 'Previous Item' ) );
define('IMAGE_BUTTON_NEXT', tra( 'Next Item' ) );
define('IMAGE_BUTTON_RETURN_TO_PRODUCT_LIST', tra( 'Back to Product List' ) );

// missing products
//define('TABLE_HEADING_NEW_PRODUCTS', tra( 'New Products For %s' ) );
//define('TABLE_HEADING_UPCOMING_PRODUCTS', tra( 'Upcoming Products' ) );
//define('TABLE_HEADING_DATE_EXPECTED', tra( 'Date Expected' ) );

define('TEXT_ATTRIBUTES_PRICE_WAS', tra( ' [was: ' ) );
define('TEXT_ATTRIBUTE_IS_FREE', tra( ' now is: Free]' ) );
define('TEXT_ONETIME_CHARGE_SYMBOL', tra( ' *' ) );
define('TEXT_ONETIME_CHARGE_DESCRIPTION', tra( ' One time charges may apply' ) );
define('TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK', tra( 'Quantity Discounts Available' ) );
define('ATTRIBUTES_QTY_PRICE_SYMBOL', zen_image(DIR_WS_TEMPLATE_ICONS . 'icon_status_green.gif', TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK, 10, 10) . '&nbsp;');
?>

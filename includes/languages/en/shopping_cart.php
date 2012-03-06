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

define('NAVBAR_TITLE', tra( 'Cart Contents' ) );
define('HEADING_TITLE', tra( 'The Shopping Cart Contains:' ) );
define('TABLE_HEADING_REMOVE', tra( 'Remove' ) );
define('TABLE_HEADING_QUANTITY', tra( 'Qty.' ) );
define('TABLE_HEADING_MODEL', tra( 'Model' ) );
define('TABLE_HEADING_PRODUCTS', tra( 'Product(s)' ) );
define('TABLE_HEADING_TOTAL', tra( 'Total' ) );
define('TEXT_CART_EMPTY', tra( 'Your Shopping Cart is empty.' ) );
define('SUB_TITLE_SUB_TOTAL', tra( 'Sub-Total:' ) );
define('SUB_TITLE_TOTAL', tra( 'Total:' ) );

define('OUT_OF_STOCK_CANT_CHECKOUT', tra( 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock or there are not enough in stock to fill your order.Please change the quantity of products marked with (' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '). Thank you' ) );
define('OUT_OF_STOCK_CAN_CHECKOUT', tra( 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock.Items not in stock will be placed on backorder.' ) );

define('TEXT_TOTAL_ITEMS', tra( 'Total Items: ' ) );
define('TEXT_TOTAL_WEIGHT', tra( '&nbsp;&nbsp;Weight: ' ) );
define('TEXT_TOTAL_AMOUNT', tra( '&nbsp;&nbsp;Amount: ' ) );

?>
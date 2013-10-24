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

define('HEADING_COUPON_HELP', tra( 'Discount Coupon Help' ) );
define('TEXT_CLOSE_WINDOW', tra( 'Close Window [x]' ) );
define('TEXT_COUPON_HELP_HEADER', tra( 'Congratulations, you have redeemed a Discount Coupon.' ) );
define('TEXT_COUPON_HELP_NAME', tra( 'Coupon Name : %s' ) );
define('TEXT_COUPON_HELP_FIXED', tra( 'The coupon is worth %s discount against your order' ) );
define('TEXT_COUPON_HELP_MINORDER', tra( 'You need to spend %s to use this coupon' ) );
define('TEXT_COUPON_HELP_FREESHIP', tra( 'This coupon gives you free shipping on your order' ) );
define('TEXT_COUPON_HELP_DESC', tra( 'Coupon Description : %s' ) );
define('TEXT_COUPON_HELP_DATE', tra( 'The coupon is valid between %s and %s' ) );
define('TEXT_COUPON_HELP_RESTRICT', tra( 'Product/Category Restrictions' ) );
define('TEXT_COUPON_HELP_CATEGORIES', tra( 'Category' ) );
define('TEXT_COUPON_HELP_PRODUCTS', tra( 'Product' ) );
define('TEXT_ALLOW', tra( 'Allow' ) );
define('TEXT_DENY', tra( 'Deny' ) );

define('TEXT_ALLOWED', tra( ' (Allowed)' ) );
define('TEXT_DENIED', tra( ' (Denied)' ) );

// gift certificates cannot be purchased with Discount Coupons
define('TEXT_COUPON_GV_RESTRICTION', tra( 'Discount Coupons may not be applied towards the purchase of ' . TEXT_GV_NAMES . '.' ) );
?>
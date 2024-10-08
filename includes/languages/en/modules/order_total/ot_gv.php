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

//  define('MODULE_ORDER_TOTAL_GV_TITLE', tra( 'Discount Coupons' ) );
  define('MODULE_ORDER_TOTAL_GV_HEADER', tra( 'Gift Certificates' ) . '/' . tra( 'Discount Coupons' ) );
  define('MODULE_ORDER_TOTAL_GV_DESCRIPTION', tra( 'Discount Coupons' ) );
if( !defined( 'SHIPPING_NOT_INCLUDED' ) ) {
  define('SHIPPING_NOT_INCLUDED', tra( ' [Shipping not included]' ) );
}
if( !defined( 'TAX_NOT_INCLUDED' ) ) {
  define('TAX_NOT_INCLUDED', tra( ' [Tax not included]' ) );
}
  define('MODULE_ORDER_TOTAL_GV_USER_PROMPT', tra( 'Apply balance ->&nbsp;' ) );
  define('MODULE_ORDER_TOTAL_GV_TEXT_ENTER_CODE', tra( 'Redemption Code' ) );
  define('TEXT_INVALID_REDEEM_AMOUNT', tra( 'Incorrect amount of balance to use' ) );
  define('MODULE_ORDER_TOTAL_GV_USER_BALANCE', tra( 'Available balance: ' ) );
?>

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

define('NAVBAR_TITLE_1', tra( 'Checkout' ) );
define('NAVBAR_TITLE_2', tra( 'Shipping Method' ) );


define('TABLE_HEADING_SHIPPING_ADDRESS', tra( 'Shipping Address' ) );
define('TEXT_CHOOSE_SHIPPING_DESTINATION', tra( 'Your order will be shipped to the following address or you may change the shipping address by clicking the <em>Change Address</em> button.' ) );
define('TITLE_SHIPPING_ADDRESS', tra( 'Shipping Address:' ) );

define('TABLE_HEADING_SHIPPING_METHOD', tra( 'Shipping Method' ) );
define('TEXT_CHOOSE_SHIPPING_METHOD', tra( 'Please select the preferred shipping method to use on this order.' ) );
define('TITLE_PLEASE_SELECT', tra( 'Please Select' ) );
define('TEXT_ENTER_SHIPPING_INFORMATION', tra( 'This is currently the only shipping method available to use on this order.' ) );

define('TABLE_HEADING_COMMENTS', tra( 'Special Instructions or Comments About Your Order' ) );

define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', tra( 'Continue to Step 2' ) );
define('TEXT_CONTINUE_CHECKOUT_PROCEDURE', tra( '- choose your payment method.' ) );

// when free shipping for orders over $XX.00 is active
  define('FREE_SHIPPING_TITLE', tra( 'Free Shipping' ) );
  define('FREE_SHIPPING_DESCRIPTION', tra( 'Free shipping for orders over %s' ) );
?>

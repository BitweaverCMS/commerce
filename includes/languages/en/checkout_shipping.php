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
// $Id: checkout_shipping.php,v 1.3 2005/10/06 21:01:48 spiderr Exp $
//

define('NAVBAR_TITLE_1', 'Checkout');
define('NAVBAR_TITLE_2', 'Shipping Method');


define('TABLE_HEADING_SHIPPING_ADDRESS', 'Shipping Address');
define('TEXT_CHOOSE_SHIPPING_DESTINATION', 'Your order will be shipped to the following address or you may change the shipping address by clicking the <em>Change Address</em> button.');
define('TITLE_SHIPPING_ADDRESS', 'Shipping Address:');

define('TABLE_HEADING_SHIPPING_METHOD', 'Shipping Method');
define('TEXT_CHOOSE_SHIPPING_METHOD', 'Please select the preferred shipping method to use on this order.');
define('TITLE_PLEASE_SELECT', 'Please Select');
define('TEXT_ENTER_SHIPPING_INFORMATION', 'This is currently the only shipping method available to use on this order.');

define('TABLE_HEADING_COMMENTS', 'Special Instructions or Comments About Your Order');

define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', 'Continue to Step 2');
define('TEXT_CONTINUE_CHECKOUT_PROCEDURE', '- choose your payment method.');

// when free shipping for orders over $XX.00 is active
  define('FREE_SHIPPING_TITLE', 'Free Shipping');
  define('FREE_SHIPPING_DESCRIPTION', 'Free shipping for orders over %s');
?>

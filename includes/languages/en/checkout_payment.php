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
define('NAVBAR_TITLE_2', tra( 'Payment Method' ) );

define('HEADING_TITLE', tra( 'Step 2 of 3 - Payment Information' ) );

define('TABLE_HEADING_BILLING_ADDRESS', tra( 'Billing Address' ) );
define('TEXT_SELECTED_BILLING_DESTINATION', tra( 'Your billing address is shown to the left. The billing address should match the address on your credit card statement. You can change the billing address by clicking the <em>Change Address</em> button.' ) );
define('TITLE_BILLING_ADDRESS', tra( 'Billing Address:' ) );

define('TABLE_HEADING_PAYMENT_METHOD', tra( 'Payment Method' ) );
define('TEXT_SELECT_PAYMENT_METHOD', tra( 'Please select a payment method for this order.' ) );
define('TITLE_PLEASE_SELECT', tra( 'Please Select' ) );
define('TEXT_ENTER_PAYMENT_INFORMATION', tra( 'This is currently the only payment method available to use on this order.' ) );
define('TABLE_HEADING_COMMENTS', tra( 'Special Instructions or Order Comments' ) );

define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', tra( '<strong>Continue to Step 3</strong>' ) );
define('TEXT_CONTINUE_CHECKOUT_PROCEDURE', tra( '- to confirm your order.' ) );

define('TABLE_HEADING_CONDITIONS', tra( 'Terms and Conditions' ) );
define('TEXT_CONDITIONS_DESCRIPTION', 'Please acknowledge the terms and conditions bound to this order by ticking the following box. The terms and conditions can be read <a href="' . zen_href_link(FILENAME_CONDITIONS, '', 'SSL') . '"><u>here</u></a>.' );
define('TEXT_CONDITIONS_CONFIRM', tra( 'I have read and agreed to the terms and conditions bound to this order.' ) );

define('TEXT_CHECKOUT_AMOUNT_DUE', tra( 'Total Amount Due: ' ) );
?>

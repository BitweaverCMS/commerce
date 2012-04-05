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

define('EMAIL_TEXT_SUBJECT', tra( 'Order Confirmation' ) );
define('EMAIL_TEXT_HEADER', tra( 'Order Confirmation' ) );
define('EMAIL_TEXT_FROM', tra( ' from ' ) );  //added to the EMAIL_TEXT_HEADER, above on text-only emails
define('EMAIL_THANKS_FOR_SHOPPING', tra( 'Thanks for shopping with us today!' ) );
define('EMAIL_DETAILS_FOLLOW', tra( 'The following are the details of your order.' ) );
define('EMAIL_TEXT_ORDER_NUMBER', tra( 'Order Number:' ) );
define('EMAIL_TEXT_INVOICE_URL', tra( 'Detailed Invoice:' ) );
define('EMAIL_TEXT_INVOICE_URL_CLICK', tra( 'Click here for a Detailed Invoice' ) );
define('EMAIL_TEXT_DATE_ORDERED', tra( 'Date Ordered:' ) );
define('EMAIL_TEXT_PRODUCTS', tra( 'Products' ) );
define('EMAIL_TEXT_SUBTOTAL', tra( 'Sub-Total:' ) );
define('EMAIL_TEXT_TAX', tra( 'Tax:        ' ) );
define('EMAIL_TEXT_SHIPPING', tra( 'Shipping: ' ) );
define('EMAIL_TEXT_TOTAL', tra( 'Total:    ' ) );
define('EMAIL_TEXT_DELIVERY_ADDRESS', tra( 'Delivery Address' ) );
define('EMAIL_TEXT_BILLING_ADDRESS', tra( 'Billing Address' ) );
define('EMAIL_TEXT_PAYMENT_METHOD', tra( 'Payment Method' ) );

define('EMAIL_SEPARATOR', tra( '------------------------------------------------------' ) );
define('TEXT_EMAIL_VIA', tra( 'via' ) );

// suggest not using # vs No as some spamm protection block emails with these subjects
define('EMAIL_ORDER_NUMBER_SUBJECT', tra( ' No: ' ) );
define('HEADING_SHIPPING_METHOD', tra( 'Shipping Method' ) );

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

  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ADMIN_TITLE', tra( 'Authorize.net' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CATALOG_TITLE', tra( 'Credit Card' ) );  // Payment option title as displayed to the customer
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION', tra( 'Credit Card Test Info:<br /><br />CC#: 4111111111111111<br />Expiry: Any' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TYPE', tra( 'Type:' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER', tra( 'Credit Card Owner:' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER', tra( 'Credit Card Number:' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES', tra( 'Credit Card Expiry Date:' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER', tra( '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER', tra( '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE', tra( 'There has been an error processing your credit card. Please try again.' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE', tra( 'Your credit card was declined. Please try another card or contact your bank for more info.' ) );
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR', tra( 'Credit Card Error!' ) );
?>
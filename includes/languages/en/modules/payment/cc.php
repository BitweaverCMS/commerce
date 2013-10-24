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

  define('MODULE_PAYMENT_CC_TEXT_TITLE', tra( 'Credit Card' ) );
  define('MODULE_PAYMENT_CC_TEXT_DESCRIPTION', tra( 'Credit Card Test Info:<br /><br />CC#: 4111111111111111<br />Expiration: Any' ) );
  define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_TYPE', tra( 'Credit Card Type:' ) );
  define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER', tra( 'Card Owner\'s Name:' ) );
  define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER', tra( 'Card Number:' ) );
  define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV', tra( 'CVV Number (<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . 'More Info' . '</a>)' ) );
  define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES', tra( 'Expiration Date:' ) );
  define('MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER', tra( '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n' ) );
  define('MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER', tra( '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n' ) );
  define('MODULE_PAYMENT_CC_TEXT_ERROR', tra( 'Credit Card Error:' ) );
  define('MODULE_PAYMENT_CC_TEXT_JS_CC_CVV', tra( '* The CVV number must be at least ' . CC_CVV_MIN_LENGTH . ' characters.\n' ) );
?>
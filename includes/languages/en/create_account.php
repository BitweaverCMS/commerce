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

define('NAVBAR_TITLE', tra( 'Create an Account' ) );

define('HEADING_TITLE', tra( 'My Account Information' ) );

define('TEXT_ORIGIN_LOGIN', tra( '<strong class="note">NOTE:</strong> If you already have an account with us, please login at the <a href="%s">login page</a>.' ) );

// greeting salutation
define('EMAIL_SUBJECT', 'Welcome to ' . STORE_NAME);
define('EMAIL_GREET_MR', 'Dear Mr. %s,' . "\n\n");
define('EMAIL_GREET_MS', 'Dear Ms. %s,' . "\n\n");
define('EMAIL_GREET_NONE', 'Dear %s' . "\n\n");

// First line of the greeting
define('EMAIL_WELCOME', tra( 'We wish to welcome you to <strong>' . STORE_NAME . '</strong>.' ) );
define('EMAIL_SEPARATOR', tra( '--------------------' ) );
define('EMAIL_COUPON_INCENTIVE_HEADER', 'Congratulations! To make your next visit to our online shop a more rewarding experience, listed below are details for a Discount Coupon created just for you!' . "\n\n");
// your Discount Coupon Description will be inserted before this next define
define('EMAIL_COUPON_REDEEM', 'To use the Discount Coupon, enter the ' . TEXT_GV_REDEEM . ' code during checkout:  <strong>%s</strong>' . "\n\n");

define('EMAIL_GV_INCENTIVE_HEADER', 'Just for stopping by today, we have sent you a ' . TEXT_GV_NAME . ' for %s!' . "\n");
define('EMAIL_GV_REDEEM', tra( 'The ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' is: %s ' . "\n\n" . 'You can enter the ' . TEXT_GV_REDEEM . ' during Checkout, after making your selections in the store. ' ) );
define('EMAIL_GV_LINK', ' Or, you may redeem it now by following this link: ' . "\n");
// GV link will automatically be included before this line

define('EMAIL_GV_LINK_OTHER','Once you have added the ' . TEXT_GV_NAME . ' to your account, you may use the ' . TEXT_GV_NAME . ' for yourself, or send it to a friend!' . "\n\n");

define('EMAIL_TEXT', 'With your account, you can now take part in the <strong>various services</strong> we have to offer you. Some of these services include:' . "\n\n" . '<li><strong>Permanent Cart</strong> - Any products added to your online cart remain there until you remove them, or check them out.' . "\n\n" . '<li><strong>Address Book</strong> - We can now deliver your products to another address other than yours! This is perfect to send birthday gifts direct to the birthday-person themselves.' . "\n\n" . '<li><strong>Order History</strong> - View your history of purchases that you have made with us.' . "\n\n" . '<li><strong>Products Reviews</strong> - Share your opinions on products with our other customers.' . "\n\n");
define('EMAIL_CONTACT', 'For help with any of our online services, please email the store-owner: <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'. STORE_OWNER_EMAIL_ADDRESS ." </a>\n\n");
define('EMAIL_GV_CLOSURE','Sincerely,' . "\n\n" . STORE_OWNER . "\nStore Owner\n\n". '<a href="' . zen_get_page_uri() . '">' . zen_get_page_uri() . "</a>\n\n");

// email disclaimer - this disclaimer is seperate from all other email disclaimers
define('EMAIL_DISCLAIMER_NEW_CUSTOMER', tra( 'This email address was given to us by you or by one of our customers. If you did not signup for an account, or feel that you have received this email in error, please send an email to %s ' ) );

define('TABLE_HEADING_PRIVACY_CONDITIONS', tra( 'Privacy Statement' ) );
define('TEXT_PRIVACY_CONDITIONS_DESCRIPTION', 'Please acknowledge you agree with our privacy statement by ticking the following box. The privacy statement can be read <a href="' . zen_href_link(FILENAME_PRIVACY, '', 'SSL') . '"><u>here</u></a>.' );
define('TEXT_PRIVACY_CONDITIONS_CONFIRM', tra( 'I have read and agreed to your privacy statement.' ) );
?>

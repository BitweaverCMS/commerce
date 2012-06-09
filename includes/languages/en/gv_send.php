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

define('HEADING_TITLE', 'Send ' . TEXT_GV_NAME);
define('NAVBAR_TITLE', 'Send ' . TEXT_GV_NAME);
define('EMAIL_SUBJECT', 'Message from ' . STORE_NAME);
define('HEADING_TEXT','Please enter below the details of the ' . TEXT_GV_NAME . ' you wish to send. For more information, please see our <a href="' . zen_href_link(FILENAME_GV_FAQ, '', 'NONSSL').'">' . GV_FAQ . '.</a>' );
define('ENTRY_NAME', tra( 'Recipients Name:' ) );
define('ENTRY_EMAIL', tra( 'Recipients E-Mail Address:' ) );
define('ENTRY_MESSAGE', tra( 'Message to Recipients:' ) );
define('ENTRY_AMOUNT', tra( 'Amount of ' . TEXT_GV_NAME . ':' ) );
define('ERROR_ENTRY_AMOUNT_CHECK', tra( '&nbsp;&nbsp;<span class="errorText">Invalid Amount</span>' ) );
define('ERROR_ENTRY_EMAIL_ADDRESS_CHECK', tra( '&nbsp;&nbsp;<span class="errorText">Invalid Email Address</span>' ) );
define('MAIN_MESSAGE', tra( 'Hi %sYou have been sent a ' . TEXT_GV_NAME . ' worth %s by %s' ) );
define('TEXT_SUCCESS', tra( 'Congratulations, your ' . TEXT_GV_NAME . ' has successfully been sent' ) );

define('TEXT_AVAILABLE_BALANCE', tra( 'Current Available Balance: ' ) );

define('EMAIL_GV_TEXT_SUBJECT', tra( 'A gift from %s' ) );
define('EMAIL_SEPARATOR', tra( '----------------------------------------------------------------------------------------' ) );
define('EMAIL_GV_TEXT_HEADER', tra( 'Congratulations, You have received a ' . TEXT_GV_NAME . ' worth %s' ) );
define('EMAIL_GV_FROM', tra( 'This ' . TEXT_GV_NAME . ' has been sent to you by %s' ) );
define('EMAIL_GV_MESSAGE', tra( 'with a message saying: ' ) );
define('EMAIL_GV_SEND_TO', tra( 'Hi %s' ) );
define('EMAIL_GV_REDEEM', tra( 'To redeem this ' . TEXT_GV_NAME . ', please click on the link below. Please also write down the ' . TEXT_GV_REDEEM . ': %s  just in case you have problems.' ) );
define('EMAIL_GV_LINK', tra( 'To redeem please click here' ) );
define('EMAIL_GV_VISIT', tra( ' or visit ' ) );
define('EMAIL_GV_ENTER', tra( ' and enter the ' . TEXT_GV_REDEEM . ' ' ) );
define('EMAIL_GV_FIXED_FOOTER', 'If you have problems redeeming the ' . TEXT_GV_NAME . ' using the automated link above, ' . "\n" .
                                'you can also enter the ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' during the checkout process at our store.');
define('EMAIL_GV_SHOP_FOOTER', tra( '' ) );
?>

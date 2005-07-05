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
// $Id: gv_send.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

define('HEADING_TITLE', 'Send ' . TEXT_GV_NAME);
define('NAVBAR_TITLE', 'Send ' . TEXT_GV_NAME);
define('EMAIL_SUBJECT', 'Message from ' . STORE_NAME);
define('HEADING_TEXT','<br />Please enter below the details of the ' . TEXT_GV_NAME . ' you wish to send. For more information, please see our <a href="' . zen_href_link(FILENAME_GV_FAQ, '', 'NONSSL').'">' . GV_FAQ . '.</a><br />');
define('ENTRY_NAME', 'Recipients Name:');
define('ENTRY_EMAIL', 'Recipients E-Mail Address:');
define('ENTRY_MESSAGE', 'Message to Recipients:');
define('ENTRY_AMOUNT', 'Amount of ' . TEXT_GV_NAME . ':');
define('ERROR_ENTRY_AMOUNT_CHECK', '&nbsp;&nbsp;<span class="errorText">Invalid Amount</span>');
define('ERROR_ENTRY_EMAIL_ADDRESS_CHECK', '&nbsp;&nbsp;<span class="errorText">Invalid Email Address</span>');
define('MAIN_MESSAGE', 'You have decided to post a ' . TEXT_GV_NAME . ' worth %s to %s who\'s email address is %s<br /><br />The text accompanying the email will read<br /><br />Dear %s<br /><br />' .
                        'You have been sent a ' . TEXT_GV_NAME . ' worth %s by %s');

define('PERSONAL_MESSAGE', '%s says');
define('TEXT_SUCCESS', 'Congratulations, your ' . TEXT_GV_NAME . ' has successfully been sent');

define('TEXT_AVAILABLE_BALANCE','Current Available Balance: ');

define('EMAIL_GV_TEXT_SUBJECT', 'A gift from %s');
define('EMAIL_SEPARATOR', '----------------------------------------------------------------------------------------');
define('EMAIL_GV_TEXT_HEADER', 'Congratulations, You have received a ' . TEXT_GV_NAME . ' worth %s');
define('EMAIL_GV_FROM', 'This ' . TEXT_GV_NAME . ' has been sent to you by %s');
define('EMAIL_GV_MESSAGE', 'with a message saying: ');
define('EMAIL_GV_SEND_TO', 'Hi, %s');
define('EMAIL_GV_REDEEM', 'To redeem this ' . TEXT_GV_NAME . ', please click on the link below. Please also write down the ' . TEXT_GV_REDEEM . ': %s  just in case you have problems.');
define('EMAIL_GV_LINK', 'To redeem please click here');
define('EMAIL_GV_VISIT', ' or visit ');
define('EMAIL_GV_ENTER', ' and enter the ' . TEXT_GV_REDEEM . ' ');
define('EMAIL_GV_FIXED_FOOTER', 'If you have problems redeeming the ' . TEXT_GV_NAME . ' using the automated link above, ' . "\n" .
                                'you can also enter the ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' during the checkout process at our store.');
define('EMAIL_GV_SHOP_FOOTER', '');
?>
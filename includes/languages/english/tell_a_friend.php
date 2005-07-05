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
// $Id: tell_a_friend.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

define('NAVBAR_TITLE', 'Tell A Friend');

define('HEADING_TITLE', 'Tell A Friend About \'%s\'');

define('FORM_TITLE_CUSTOMER_DETAILS', 'Your Details');
define('FORM_TITLE_FRIEND_DETAILS', 'Your Friend\'s Details');
define('FORM_TITLE_FRIEND_MESSAGE', 'Your Message');

define('FORM_FIELD_CUSTOMER_NAME', 'Your Name:');
define('FORM_FIELD_CUSTOMER_EMAIL', 'Your E-Mail Address:');
define('FORM_FIELD_FRIEND_NAME', 'Your Friend\'s Name:');
define('FORM_FIELD_FRIEND_EMAIL', 'Your Friend\'s E-Mail Address:');

define('EMAIL_SEPARATOR', '----------------------------------------------------------------------------------------');

define('TEXT_EMAIL_SUCCESSFUL_SENT', 'Your email about <strong>%s</strong> has been successfully sent to <strong>%s</strong>.');

define('EMAIL_TEXT_HEADER','Important Notice!');

define('EMAIL_TEXT_SUBJECT', 'Your friend %s has recommended this great product from %s');
define('EMAIL_TEXT_GREET', 'Hi %s!' . "\n\n");
define('EMAIL_TEXT_INTRO', 'Your friend, %s, thought that you would be interested in %s from %s.');

define('EMAIL_TELL_A_FRIEND_MESSAGE','%s sent a note saying:');

define('EMAIL_TEXT_LINK', 'To view the product, click on the link below or copy and paste the link into your web browser:' . "\n\n" . '%s');
define('EMAIL_TEXT_SIGNATURE', 'Regards,' . "\n\n" . '%s');

define('ERROR_TO_NAME', 'Error: Your friend\'s name must not be empty.');
define('ERROR_TO_ADDRESS', 'Error: Your friend\'s e-mail address does not appear to be valid. Please try again.');
define('ERROR_FROM_NAME', 'Error: Your name must not be empty.');
define('ERROR_FROM_ADDRESS', 'Error: Your e-mail address does not appear to be valid. Please try again.');
?>

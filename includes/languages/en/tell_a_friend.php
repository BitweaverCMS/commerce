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

define('NAVBAR_TITLE', tra( 'Tell A Friend' ) );

define('HEADING_TITLE', tra( 'Tell A Friend About \'%s\'' ) );

define('FORM_TITLE_CUSTOMER_DETAILS', tra( 'Your Details' ) );
define('FORM_TITLE_FRIEND_DETAILS', tra( 'Your Friend\'s Details' ) );
define('FORM_TITLE_FRIEND_MESSAGE', tra( 'Your Message' ) );

define('FORM_FIELD_CUSTOMER_NAME', tra( 'Your Name:' ) );
define('FORM_FIELD_CUSTOMER_EMAIL', tra( 'Your E-Mail Address:' ) );
define('FORM_FIELD_FRIEND_NAME', tra( 'Your Friend\'s Name:' ) );
define('FORM_FIELD_FRIEND_EMAIL', tra( 'Your Friend\'s E-Mail Address:' ) );

define('EMAIL_SEPARATOR', tra( '----------------------------------------------------------------------------------------' ) );

define('TEXT_EMAIL_SUCCESSFUL_SENT', tra( 'Your email about <strong>%s</strong> has been successfully sent to <strong>%s</strong>.' ) );

define('EMAIL_TEXT_HEADER', tra( 'Important Notice!' ) );

define('EMAIL_TEXT_SUBJECT', tra( 'Your friend %s has recommended this great product from %s' ) );
define('EMAIL_TEXT_GREET', 'Hi %s!' . "\n\n");
define('EMAIL_TEXT_INTRO', tra( 'Your friend, %s, thought that you would be interested in %s from %s.' ) );

define('EMAIL_TELL_A_FRIEND_MESSAGE', tra( '%s sent a note saying:' ) );

define('EMAIL_TEXT_LINK', tra( 'To view the product, click on the link below or copy and paste the link into your web browser:' . "\n\n" . '%s' ) );
define('EMAIL_TEXT_SIGNATURE', tra( 'Regards,' . "\n\n" . '%s' ) );

define('ERROR_TO_NAME', tra( 'Error: Your friend\'s name must not be empty.' ) );
define('ERROR_TO_ADDRESS', tra( 'Error: Your friend\'s e-mail address does not appear to be valid. Please try again.' ) );
define('ERROR_FROM_NAME', tra( 'Error: Your name must not be empty.' ) );
define('ERROR_FROM_ADDRESS', tra( 'Error: Your e-mail address does not appear to be valid. Please try again.' ) );
?>

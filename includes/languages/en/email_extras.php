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

// office use only
  define('OFFICE_FROM', tra( '<strong>From:</strong>' ) );
  define('OFFICE_EMAIL', tra( '<strong>e-mail:</strong>' ) );

  define('OFFICE_SENT_TO', tra( '<strong>Sent To:</strong>' ) );
  define('OFFICE_EMAIL_TO', tra( '<strong>To e-mail:</strong>' ) );

  define('OFFICE_USE', tra( '<strong>Office Use Only:</strong>' ) );
  define('OFFICE_LOGIN_NAME', tra( '<strong>Login Name:</strong>' ) );
  define('OFFICE_LOGIN_EMAIL', tra( '<strong>Login e-mail:</strong>' ) );
  define('OFFICE_LOGIN_PHONE', tra( '<strong>Telephone:</strong>' ) );
  define('OFFICE_IP_ADDRESS', tra( '<strong>IP Address:</strong>' ) );
  define('OFFICE_HOST_ADDRESS', tra( '<strong>Host Address:</strong>' ) );
  define('OFFICE_DATE_TIME', tra( '<strong>Date and Time:</strong>' ) );
  define('OFFICE_IP_TO_HOST_ADDRESS', tra( 'OFF' ) );

// email disclaimer
  define('EMAIL_DISCLAIMER', tra( 'This email address was given to us by you or by one of our customers. If you feel that you have received this email in error, please send an email to %s ' ) );
  define('EMAIL_SPAM_DISCLAIMER', tra( 'This e-mail is sent in accordance with the US CAN-SPAM Law in effect 01/01/2004. Removal requests can be sent to this address and will be honored and respected.' ) );
  define('EMAIL_FOOTER_COPYRIGHT', tra( '' ) );

// email advisory for all emails customer generate - tell-a-friend and GV send
  define('EMAIL_ADVISORY', '-----' . "\n" . '<strong>IMPORTANT:</strong> For your protection and to prevent malicious use, all emails sent via this web site are logged and the contents recorded and available to the store owner. If you feel that you have received this email in error, please send an email to ' . STORE_OWNER_EMAIL_ADDRESS . "\n\n");

// email advisory included warning for all emails customer generate - tell-a-friend and GV send
  define('EMAIL_ADVISORY_INCLUDED_WARNING', tra( '<strong>This message is included with all emails sent from this site:</strong>' ) );


// Admin additional email subjects
  define('SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_SUBJECT', tra( '[CREATE ACCOUNT]' ) );
  define('SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO_SUBJECT', tra( '[TELL A FRIEND]' ) );
  define('SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_SUBJECT', tra( '[GV CUSTOMER SENT]' ) );
  define('SEND_EXTRA_NEW_ORDERS_EMAILS_TO_SUBJECT', tra( '[NEW ORDER]' ) );
  define('SEND_EXTRA_CC_EMAILS_TO_SUBJECT', tra( '[EXTRA CC ORDER INFO] #' ) );

// Low Stock Emails
  define('EMAIL_TEXT_SUBJECT_LOWSTOCK', tra( 'Warning: Low Stock' ) );
  define('SEND_EXTRA_LOW_STOCK_EMAIL_TITLE', tra( 'Low Stock Report: ' ) );
?>

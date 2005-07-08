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
// $Id: checkout_success.php,v 1.1 2005/07/08 06:12:29 spiderr Exp $
//

define('NAVBAR_TITLE_1', 'Checkout');
define('NAVBAR_TITLE_2', 'Success - Thank You');

define('HEADING_TITLE', 'Thank You! We Appreciate your Business!');

define('TEXT_SUCCESS', 'A few words about the approximate shipping time or your processing policy would be put here. You can change this text in: <strong>includes/ languages/ YOUR_LANGUAGE/ checkout_success.php</strong>');
define('TEXT_NOTIFY_PRODUCTS', 'Please notify me of updates to the products I have selected below:');
define('TEXT_SEE_ORDERS', 'You can view your order history by going to the <a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">My Account</a> page and by clicking on view all orders.');
define('TEXT_CONTACT_STORE_OWNER', 'Please direct any questions you have to <a href="' . zen_href_link(FILENAME_CONTACT_US) . '">customer service</a>.');
define('TEXT_THANKS_FOR_SHOPPING', 'Thanks for shopping with us online!');

define('TABLE_HEADING_COMMENTS', '');

define('TABLE_HEADING_DOWNLOAD_DATE', 'Link expires:');
define('TABLE_HEADING_DOWNLOAD_COUNT', 'Downloads remaining:');
define('HEADING_DOWNLOAD', 'Download your products here:');
define('FOOTER_DOWNLOAD', 'You can also download your products at a later time at \'%s\'');

define('TABLE_HEADING_DOWNLOAD_FILENAME','Product Download:');
define('TEXT_YOUR_ORDER_NUMBER', '<strong>Your Order Number is:</strong> ');
?>
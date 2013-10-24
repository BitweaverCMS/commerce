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

define('NAVBAR_TITLE_1', tra( 'Checkout' ) );
define('NAVBAR_TITLE_2', tra( 'Success - Thank You' ) );

define('TEXT_SUCCESS', tra( 'A few words about the approximate shipping time or your processing policy would be put here. You can change this text in: <strong>includes/ languages/ YOUR_LANGUAGE/ checkout_success.php</strong>' ) );
define('TEXT_SEE_ORDERS', 'You can view your order history by going to the <a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">My Account</a> page and by clicking on view all orders.' );
define('TEXT_CONTACT_STORE_OWNER', tra( 'Please direct any questions you have to <a href="' . zen_href_link(FILENAME_CONTACT_US) . '">customer service</a>.' ) );

define('TABLE_HEADING_COMMENTS', tra( '' ) );

define('TABLE_HEADING_DOWNLOAD_DATE', tra( 'Link expires:' ) );
define('TABLE_HEADING_DOWNLOAD_COUNT', tra( 'Downloads remaining:' ) );
define('HEADING_DOWNLOAD', tra( 'Download your products here:' ) );
define('FOOTER_DOWNLOAD', tra( 'You can also download your products at a later time at \'%s\'' ) );

define('TABLE_HEADING_DOWNLOAD_FILENAME', tra( 'Product Download:' ) );
?>

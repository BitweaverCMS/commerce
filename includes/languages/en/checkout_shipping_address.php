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
//define('NAVBAR_TITLE', tra( 'Change Shipping Address' ) );
//define('NAVBAR_TITLE_1', tra( 'Checkout' ) );
//define('NAVBAR_TITLE_2', tra( 'Change Shipping Address' ) );

global $gCommerceSystem;
$gCommerceSystem->setHeadingTitle( 'Change the Shipping Address' );

define('TABLE_HEADING_ADDRESS_BOOK_ENTRIES', tra( '...Or Choose From Your Address Book Entries' ) );

define('TEXT_CREATE_NEW_SHIPPING_ADDRESS', tra( 'Please use the following form to create a new shipping address for use with this order.' ) );
define('TEXT_SELECT_OTHER_SHIPPING_DESTINATION', tra( 'Please select the preferred shipping address if this order is to be delivered elsewhere.' ) );

?>

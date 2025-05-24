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

global $newOrdersId;
require(BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');
if( BitBase::verifyIdParameter( $_SESSION, 'customer_id' ) && ($newOrdersId = $gBitDb->getOne( "select `orders_id` from " . TABLE_ORDERS . " where `customers_id` = ? order by `date_purchased` desc", array( $_SESSION['customer_id'] ) )) ) {
	$newOrder = new order( $newOrdersId );
	$gBitSmarty->assign( 'newOrdersId', $newOrdersId );
	$gBitSmarty->assign( 'newOrder', $newOrder );
} else {
	bit_redirect( zen_get_page_url( 'shopping_cart' ) );
}

if (isset($_GET['action']) && ($_GET['action'] == 'update')) {
	$notify_string = 'action=notify&';

	if( !empty( $_POST['notify'] ) && is_array( $_POST['notify'] ) ) {
		for ($i=0, $n=sizeof( $_POST['notify'] ); $i<$n; $i++) {
			$notify_string .= 'notify[]=' . $_POST['notify'][$i] . '&';
		}
		if (strlen($notify_string) > 0) $notify_string = substr($notify_string, 0, -1);
	}
	zen_redirect( zen_get_page_url( 'account' ) );
}

require_once(DIR_FS_MODULES . 'require_languages.php');
$breadcrumb->add(NAVBAR_TITLE_1);
$breadcrumb->add(NAVBAR_TITLE_2);

if( $gBitCustomer->getGlobalNotifications() != '1' ) {
	$products_array = array();
	$products_query = "SELECT DISTINCT `products_id`, `products_name` from " . TABLE_ORDERS_PRODUCTS . "
					   WHERE `orders_id` = ?
					   ORDER BY `products_name`";
	$products = $gBitDb->getAssoc($products_query, array($newOrdersId) );
	$gBitSmarty->assign_by_ref( 'notifyProducts', $products );
}

$gv_query = "SELECT `amount` from " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id`=?";
$gBitSmarty->assign( 'gvAmount', $gBitDb->getOne( $gv_query, array( $gBitUser->mUserId ) ) );

// include template specific file name defines
$define_checkout_success = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/html_includes/', FILENAME_DEFINE_CHECKOUT_SUCCESS, 'false');

$gCommerceSystem->setHeadingTitle( tra( 'Order Success!' )." #$newOrdersId" );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_success.tpl' );


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
// $Id: checkout_success.php,v 1.5 2007/05/06 19:36:55 spiderr Exp $
//

global $newOrdersId;
$newOrdersId = $gBitDb->getOne( "select `orders_id` from " . TABLE_ORDERS . " where `customers_id` = ? order by `date_purchased` desc", array( $_SESSION['customer_id'] ) );
$gBitSmarty->assign( 'newOrdersId', $newOrdersId );

if( $gBitCustomer->getGlobalNotifications() != '1' ) {
	$products_array = array();
	$products_query = "SELECT DISTINCT `products_id`, `products_name` from " . TABLE_ORDERS_PRODUCTS . "
					   WHERE `orders_id` = ?
					   ORDER BY `products_name`";
	$products = $gBitDb->getAssoc($products_query, array($newOrdersId) );
	$gBitSmarty->assign_by_ref( 'notifyProducts', $products );
}

$gv_query = "SELECT `amount` from " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id`=?";
$gBitSmarty->assign( 'gvAmount', $gBitDb->getOne( $gv_query, array( $gCustomer->mCustomersId ) ) );

// include template specific file name defines
$define_checkout_success = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/html_includes/', FILENAME_DEFINE_CHECKOUT_SUCCESS, 'false');

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/checkout_success.tpl' );

?>

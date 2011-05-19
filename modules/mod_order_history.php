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
global $gBitDb, $gBitProduct;
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );

if( $gBitUser->isRegistered() ) {
	// retreive the last x products purchased
	$orders_history_query = "select distinct op.`products_id`, o.date_purchased
					from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS . " p
					where o.`customers_id` = ?
					and o.`orders_id` = op.`orders_id`
					and op.`products_id` = p.`products_id`
					and p.`products_status` = '1'
					order by o.date_purchased desc";

	$orders_history = $gBitDb->query( $orders_history_query, $gBitUser->mUserId,MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX );

	if ($orders_history->RecordCount() > 0) {
		$product_ids = '';
		while (!$orders_history->EOF) {
			$product_ids .= (int)$orders_history->fields['products_id'] . ',';
			$orders_history->MoveNext();
		}
		$product_ids = substr($product_ids, 0, -1);
		$rows=0;
		$customer_orders_string = '<table border="0" width="100%" cellspacing="0" cellpadding="1">';
		$products_history_query = "select `products_id`, `products_name`
							from " . TABLE_PRODUCTS_DESCRIPTION . "
							where `products_id` in (" . $product_ids . ")
							and `language_id` = '" . (int)$_SESSION['languages_id'] . "'
							order by `products_name`";

		$products_history = $gBitDb->Execute($products_history_query);

		while (!$products_history->EOF) {
			$rows++;
			$customer_orders[$rows] = $products_history->fields;
			$customer_orders[$rows]['display_url'] = CommerceProduct::getDisplayUrl( $products_history->fields['products_id'] );
			$products_history->MoveNext();
		}

		$gBitSmarty->assign( 'sideboxCustomerOrders', $customer_orders );

	}
	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle',  'Order History' ) ;
	}
}
?>

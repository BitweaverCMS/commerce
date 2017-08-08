<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                                 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id$
//
	
if (isset($_GET['product_type'])) {
	$product_type = zen_db_prepare_input($_GET['product_type']);
} else {
	$product_type='1';
}

$type_admin_handler = $zc_products->get_admin_handler($product_type);

function zen_reset_page() {
	global $gBitDb, $current_category_id;
	$look_up = $gBitDb->Execute("select p.`products_id`, pd.`products_name`, p.`products_model`, p.`products_quantity`, p.`products_image`, p.`products_price`, p.`products_date_added`, p.products_last_modified, p.`products_date_available`, p.`products_status, p.`products_quantity`_order_min, p.`products_quantity`_order_units, p.`products_priced_by_attribute`, p.`product_is_free`, p.product_is_call, p.`products_quantity`_mixed, p.product_is_always_free_ship, p.`products_quantity_order_max` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.`products_id` = pd.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p.`products_id` = p2c.`products_id` and p2c.`categories_id` = '" . $current_category_id . "' order by pd.`products_name`");
	while (!$look_up->EOF) {
		$look_count ++;
		if ($look_up->fields['products_id']== $_GET['pID']) {
			exit;
		} else {
			$look_up->MoveNext();
		}
	}
	return round( ($look_count+.05)/MAX_DISPLAY_RESULTS_CATEGORIES);
}
// make array for product types

$productTypesHash = $gBitDb->getAssoc( "SELECT `type_id`, `type_name` FROM " . TABLE_PRODUCT_TYPES . " ORDER BY `type_name`" );


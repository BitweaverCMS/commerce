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
global $gCommerceSystem;
if (isset($_GET['products_id']) && $gCommerceSystem->getConfig( 'SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS' ) ) {
	global $gCommerceSystem;
	$query = "SELECT p.`products_id`
			  FROM " . TABLE_ORDERS_PRODUCTS . " opa
				INNER JOIN " . TABLE_ORDERS_PRODUCTS . " opb ON (opa.`orders_id` = opb.`orders_id`)
				INNER JOIN " . TABLE_ORDERS . " o ON (opb.`orders_id` = o.`orders_id`)
				INNER JOIN " . TABLE_PRODUCTS . " p ON (opb.`products_id` = p.`products_id`)
				INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON (p.`products_type` = pt.`type_id`)
			  WHERE opa.`products_id` = ?	AND opb.`products_id` != ? AND p.`products_status` = '1'
			  GROUP BY p.`products_id`, p.`products_image`, pt.`type_class`
			  ORDER BY SUM( opb.`products_quantity` ) DESC";

	if( $relatedProductIds = $gCommerceSystem->mDb->getCol( $query, array( (int)$_GET['products_id'], (int)$_GET['products_id']), MAX_DISPLAY_ALSO_PURCHASED ) ) {

		if( count( $relatedProductIds ) >= $gCommerceSystem->getConfig( 'MIN_DISPLAY_ALSO_PURCHASED' ) ) {
			$list_box_contents = '';
			$info_box_contents = array();
			foreach( $relatedProductIds as $relatedProductId ) {
				if( $listProduct = bc_get_commerce_product( array( 'products_id' => $relatedProductId ) ) ) {
					$productTitle = htmlspecialchars( $listProduct->getTitle() );
					$relatedList[$relatedProductId] = '<a href="'.$listProduct->getDisplayUrl().'"><img src="'.$listProduct->getThumbnailUrl('small').'" class="img-responsive" alt="'.$productTitle.'"/></a><br />'.$listProduct->getDisplayLink();
				}
			}
			require( DIR_FS_MODULES . 'tpl_modules_also_purchased_products.php');
		}
	}
}
?>

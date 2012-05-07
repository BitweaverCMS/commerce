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
	global $gBitProduct, $gBitSmarty;

	$title = TABLE_HEADING_FEATURED_PRODUCTS;

	$listHash = array();
	$listHash['max_records'] = MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS;
	$listHash['offset'] = MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
	$listHash['sort_mode'] = 'random';
	$listHash['featured'] = TRUE;
	$listHash['thumbnail_size'] = 'avatar';

	$row = 0;
	$col = 0;
	$listBoxContents = '';

	// show only when 1 or more
	if( $featuredProducts = $gBitProduct->getList( $listHash ) ) {
		$num_products_count = count( $featuredProducts );
		if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS) {
			$col_width = 100/$num_products_count;
		} else {
			$col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS;
		}
		foreach( array_keys( $featuredProducts ) as $productsId ) {
			$products_price = CommerceProduct::getDisplayPrice( $productsId );
			$listBoxContents[$row][$col] = array('align' => 'center',
													'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
													'text' => '<a href="' . CommerceProduct::getDisplayUrlFromId( $productsId ) . '">' . zen_image( $featuredProducts[$productsId]['products_image_url'], $featuredProducts[$productsId]['products_name'] ) . '</a><br /><a href="' . CommerceProduct::getDisplayUrlFromId( $productsId ) . '">' . $featuredProducts[$productsId]['products_name'] . '</a><br />' . $products_price);

			$col ++;
			if ($col > (SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS - 1)) {
				$col = 0;
				$row ++;
			}
		}

		if (isset($new_products_category_id)) {
			$category_title = zen_get_categories_name((int)$new_products_category_id);
			$title =  $title . ($category_title != '' ? ' - ' . $category_title : '');
		} else {
			$title = TABLE_HEADING_FEATURED_PRODUCTS;
		}
		$gBitSmarty->assign( 'listBoxContents', $listBoxContents );
		$gBitSmarty->assign( 'productListTitle', $title );
		$gBitSmarty->display( 'bitpackage:bitcommerce/list_box_content_inc.tpl' );
	}
?>

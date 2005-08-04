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
// $Id: featured_products.php,v 1.5 2005/08/04 07:01:01 spiderr Exp $
//


	$title = TABLE_HEADING_FEATURED_PRODUCTS;

	$listHash['max_records'] = MAX_DISPLAY_SEARCH_RESULTS_FEATURED;
	$listHash['sort_mode'] = 'random';
	$listHash['featured'] = TRUE;

	$row = 0;
	$col = 0;
	$list_box_contents = '';

	// show only when 1 or more
	if( $featuredProducts = $gBitProduct->getList( $listHash ) ) {
		$num_products_count = count( $featuredProducts );
		if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS) {
			$col_width = 100/$num_products_count;
		} else {
			$col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS;
		}
		foreach( array_keys( $featuredProducts ) as $productsId ) {
			$products_price = zen_get_products_display_price( $productsId );
			$list_box_contents[$row][$col] = array('align' => 'center',
													'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
													'text' => '<a href="' . zen_href_link( $featuredProducts[$productsId]['info_page'], 'products_id=' . $productsId ) . '">' . zen_image( $featuredProducts[$productsId]['products_image_url'], $featuredProducts[$productsId]['products_name'], IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH, IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT) . '</a><br /><a href="' . zen_href_link( zen_get_info_page( $productsId ), 'products_id=' . $productsId ) . '">' . $featuredProducts[$productsId]['products_name'] . '</a><br />' . $products_price);

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
		require( DIR_FS_MODULES . '/tpl_modules_featured_products.php');
	}
?>

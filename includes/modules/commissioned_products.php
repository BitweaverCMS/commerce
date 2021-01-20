<?php
	global $gCommerceSystem, $gBitProduct, $gBitSmarty;

	$title = $gCommerceSystem->getConfig( 'TABLE_HEADING_COMMISSIONED_PRODUCTS', tra('Member Products') );

	$listHash = array();
	$columnCount = $gCommerceSystem->getConfig( 'SHOW_PRODUCT_INFO_COLUMNS_COMMISSIONED_PRODUCTS', 3 );
	$listHash['max_records'] = $gCommerceSystem->getConfig( 'MAX_DISPLAY_PRODUCTS_COMMISSIONED_PRODUCTS', $columnCount * 3 );
	$listHash['offset'] = $listHash['max_records'] * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
	$listHash['sort_mode'] = 'random';
	$listHash['commissioned'] = TRUE;
	$listHash['thumbnail_size'] = 'avatar';

	$row = 0;
	$col = 0;
	$listBoxContents = array();

	// show only when 1 or more
	if( $commissionedProducts = $gBitProduct->getList( $listHash ) ) {
		$num_products_count = count( $commissionedProducts );
		if( $num_products_count < $columnCount  ) {
			$col_width = 100/$num_products_count;
		} else {
			$col_width = 100/$columnCount;
		}
		foreach( array_keys( $commissionedProducts ) as $productsId ) {
			$products_price = CommerceProduct::getDisplayPriceFromHash( $productsId );
			$listBoxContents[$row][$col] = array('align' => 'center',
													'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
													'text' => '<a href="' . CommerceProduct::getDisplayUrlFromId( $productsId ) . '">' . zen_image( $commissionedProducts[$productsId]['products_image_url'], $commissionedProducts[$productsId]['products_name'] ) . '</a><br /><a href="' . CommerceProduct::getDisplayUrlFromId( $productsId ) . '">' . $commissionedProducts[$productsId]['products_name'] . '</a><br />' . $products_price);

			$col ++;
			if ($col > ($columnCount - 1)) {
				$col = 0;
				$row ++;
			}
		}

		if (isset($new_products_category_id)) {
			$category_title = zen_get_categories_name((int)$new_products_category_id);
			$title =  $title . ($category_title != '' ? ' - ' . $category_title : '');
		}
		$gBitSmarty->assign( 'listBoxContents', $listBoxContents );
		$gBitSmarty->assign( 'productListTitle', $title );
		$gBitSmarty->display( 'bitpackage:bitcommerce/list_box_content_inc.tpl' );
	}

?>

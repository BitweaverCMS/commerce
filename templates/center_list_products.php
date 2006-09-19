<?php
	global $gBitProduct;

	$listHash = array();
	$title						= !empty( $module_title ) ? $module_title : tra( 'Products' );
	$columnCount 				= !empty( $module_params['columns'] ) ? $module_params['columns'] : 3;
	$listHash['max_records']	= !empty( $module_rows ) ? $module_rows : $gBitSystem->getConfig( 'max_records', $columnCount * 3 );
	$listHash['offset'] 		= $listHash['max_records'] * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
	$listHash['sort_mode']		= !empty( $module_params['sort_mode'] ) ? $module_params['sort_mode'] : 'random';
	if( !empty( $gQueryUser ) && $gQueryUser->mUserId ) {
		$listHash['user_id'] = $gQueryUser->mUserId;
	}
	if( !empty( $module_params['category_id'] ) ) {
		$listHash['category_id'] = $module_params['category_id'];
	}
	if( !empty( $module_params['commissioned'] ) ) {
		$listHash['commissioned'] = TRUE;
	}
	$row = 0;
	$col = 0;
	$listBoxContents = '';

	// show only when 1 or more
	if( $commissionedProducts = $gBitProduct->getList( $listHash ) ) {
		$num_products_count = count( $commissionedProducts );
		if( $num_products_count < $columnCount  ) {
			$col_width = 100/$num_products_count;
		} else {
			$col_width = 100/$columnCount;
		}
		foreach( array_keys( $commissionedProducts ) as $productsId ) {
			$products_price = CommerceProduct::getDisplayPrice( $productsId );
			$listBoxContents[$row][$col] = array('align' => 'center',
													'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
													'text' => '<a href="' . CommerceProduct::getDisplayUrl( $productsId ) . '">' . zen_image( CommerceProduct::getImageUrl( $commissionedProducts[$productsId]['products_id'], 'avatar' ), $commissionedProducts[$productsId]['products_name'] ) . '</a><br /><a href="' . CommerceProduct::getDisplayUrl( $productsId ) . '">' . $commissionedProducts[$productsId]['products_name'] . '</a><br />' . $products_price);

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
	}

?>

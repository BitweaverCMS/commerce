<?php
	global $gBitProduct;

	$listHash = array();
	$title						= !empty( $moduleParams['title'] ) ? $moduleParams['title'] : (!empty( $moduleParams['module_params']['title'] ) ? $moduleParams['module_params']['title'] : tra( 'Products' ));
	$columnCount 				= !empty( $moduleParams['module_params']['columns'] ) ? $moduleParams['module_params']['columns'] : 3;
	$listHash['max_records']	= !empty( $moduleParams['module_rows'] ) ? $moduleParams['module_rows'] : $gBitSystem->getConfig( 'max_records', $columnCount * 3 );
	$listHash['offset'] 		= $listHash['max_records'] * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
	$listHash['sort_mode']		= !empty( $moduleParams['sort_mode'] ) ? $moduleParams['sort_mode'] : 'random';
	if( !empty( $gQueryUser ) && $gQueryUser->mUserId ) {
		$listHash['user_id'] = $gQueryUser->mUserId;
	}
	if( !empty( $moduleParams['module_params']['category_id'] ) ) {
		$listHash['category_id'] = $moduleParams['category_id'];
	}
	if( !empty( $moduleParams['module_params']['featured'] ) ) {
		$listHash['featured'] = TRUE;
	}
	if( !empty( $moduleParams['module_params']['commissioned'] ) ) {
		$listHash['commissioned'] = TRUE;
	}
	$row = 0;
	$col = 0;
	$listBoxContents = '';
	if( is_object( $gBitProduct ) && $listedProducts = $gBitProduct->getList( $listHash ) ) {
		// show only when 1 or more
		$num_products_count = count( $listedProducts );
		if( $num_products_count < $columnCount  ) {
			$col_width = 100/$num_products_count;
		} else {
			$col_width = 100/$columnCount;
		}
		foreach( array_keys( $listedProducts ) as $productsId ) {
			$products_price = CommerceProduct::getDisplayPrice( $productsId );
			$listBoxContents[$row][$col] = array('align' => 'center',
													'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
													'text' => '<a href="' . CommerceProduct::getDisplayUrl( $productsId ) . '">' . zen_image( CommerceProduct::getImageUrl( $listedProducts[$productsId]['products_id'], 'avatar' ), $listedProducts[$productsId]['products_name'] ) . '</a><br /><a href="' . CommerceProduct::getDisplayUrl( $productsId ) . '">' . $listedProducts[$productsId]['products_name'] . '</a><br />' . $products_price);

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
		$gBitSmarty->assign( 'productListTitle', $title );
	}
	$gBitSmarty->assign( 'listBoxContents', $listBoxContents );

?>

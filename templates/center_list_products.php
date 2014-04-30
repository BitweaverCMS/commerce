<?php
global $gBitProduct, $gQueryUser, $currencies;

require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
$listHash = array();
$title						= !empty( $moduleParams['title'] ) ? $moduleParams['title'] : (!empty( $moduleParams['module_params']['title'] ) ? $moduleParams['module_params']['title'] : tra( 'Products' ));
$columnCount 				= !empty( $moduleParams['module_params']['columns'] ) ? $moduleParams['module_params']['columns'] : 3;
$listHash['max_records']	= !empty( $moduleParams['module_rows'] ) ? $moduleParams['module_rows'] : $gBitSystem->getConfig( 'max_records', $columnCount * 3 );
$listHash['offset'] 		= $listHash['max_records'] * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
$listHash['sort_mode']		= !empty( $moduleParams['sort_mode'] ) ? $moduleParams['sort_mode'] : 'random';
$listHash['thumbnail_size'] = 'medium';

if( !empty( $gQueryUser ) && $gQueryUser->mUserId ) {
	$listHash['user_id'] = $gQueryUser->mUserId;
}
if( !empty( $moduleParams['module_params']['category_id'] ) ) {
	$listHash['category_id'] = $moduleParams['module_params']['category_id'];
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
	$_template->tpl_vars['listColWidth'] = new Smarty_variable( $col_width );
	foreach( array_keys( $listedProducts ) as $productsId ) {
		$products_price = CommerceProduct::getDisplayPriceFromHash( $productsId );
		$listBoxContents[$row][$col] = $listedProducts[$productsId];
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
	$_template->tpl_vars['productListTitle'] = new Smarty_variable( $title );
}
$_template->tpl_vars['listedProducts'] = new Smarty_variable( $listedProducts );
$_template->tpl_vars['listBoxContents'] = new Smarty_variable( $listBoxContents );


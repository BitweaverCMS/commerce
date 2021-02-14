<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2005-2010 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
// | Portions Copyright (c) 2003 The zen-cart developers				|
// | Portions Copyright (c) 2003 osCommerce								|	
// +--------------------------------------------------------------------+
//

$gContent = &$gBitProduct;

if ( $gBitProduct->isAvailable() ) {

	if( $productOptions = $gBitProduct->getProductOptions() ) {
		$gBitSmarty->assign_by_ref( 'productOptions', $productOptions );
	}

	$gCommerceSystem->setHeadingTitle( 'HEADING_TITLE', $gBitProduct->getTitle().' - '.tra( $gBitProduct->getField( 'products_model' ) ) );

	$mid = $gBitProduct->getPreference( 'products_custom_tpl', 'bitpackage:bitcommerce/page_product_info.tpl' );

	// Comments engine!
	if( $gCommerceSystem->getConfig( 'SHOW_PRODUCT_INFO_REVIEWS' ) ) {
		$comments_return_url = $gBitProduct->getDisplayUrl();
		$commentsParentId = $gBitProduct->mContentId;
		include_once ( LIBERTY_PKG_INCLUDE_PATH.'comments_inc.php' );
	}
} else {
	$gCommerceSystem->setHeadingTitle( tra( 'Product not found' ) );
	$mid = 'bitpackage:bitcommerce/product_not_available.tpl';
}

if( !empty( $mid ) ) {
	print $gBitSmarty->fetch( $mid );
} else {
	require( DIR_FS_PAGES . $current_page_base . '/' . $tpl_page_body);
}

require zen_get_module_path( FILENAME_ALSO_PURCHASED_PRODUCTS );


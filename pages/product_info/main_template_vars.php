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

$res = $gBitDb->query( $sql, array( (int)$_GET['products_id'], (int)$_SESSION['languages_id'] ) );

if ( $gBitProduct->isAvailable() ) {
	require(DIR_FS_PAGES . $current_page_base . '/main_template_vars_attributes.php');
	define( 'HEADING_TITLE', $gBitProduct->getTitle().' - '.tra( $gBitProduct->getField( 'products_model' ) ) );
	$mid = 'bitpackage:bitcommerce/product_info_display.tpl';

	if (is_dir(DIR_WS_TEMPLATE . $current_page_base . '/extra_main_template_vars')) {
		if ($za_dir = @dir(DIR_WS_TEMPLATE . $current_page_base. '/extra_main_template_vars')) {
			while ($zv_file = $za_dir->read()) {
				if (strstr($zv_file, '*.php') ) {
					require(DIR_FS_TEMPLATE . $current_page_base . '/extra_main_template_vars/' . $zv_file);
				}
			}
		}
	}

	// Comments engine!
	if( $gCommerceSystem->getConfig( 'SHOW_PRODUCT_INFO_REVIEWS' ) ) {
		$comments_return_url = $gBitProduct->getDisplayUrl();
		$commentsParentId = $gBitProduct->mContentId;
		include_once ( LIBERTY_PKG_PATH.'comments_inc.php' );
	}
} else {
	$mid = 'bitpackage:bitcommerce/product_not_available.tpl';
}

if( !empty( $mid ) ) {
	print $gBitSmarty->fetch( $mid );
} else {
	require( DIR_FS_PAGES . $current_page_base . '/' . $tpl_page_body);
}

require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_ALSO_PURCHASED_PRODUCTS));


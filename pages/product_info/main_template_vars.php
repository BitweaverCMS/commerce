<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id: main_template_vars.php,v 1.20 2007/12/03 05:54:53 spiderr Exp $
//

	$sql = "SELECT COUNT(*) as `total`
			FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON ( pd.`products_id` = p.`products_id` )
			WHERE p.`products_status` = '1' AND p.`products_id` = ? AND pd.`language_id` = ?";

	$res = $gBitDb->query( $sql, array( (int)$_GET['products_id'], (int)$_SESSION['languages_id'] ) );

	if ( $res->fields['total'] < 1 ) {
		$mid = 'bitpackage:bitcommerce/product_not_available.tpl';
	} else {
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
	}

	if( !empty( $mid ) ) {
		print $gBitSmarty->fetch( $mid );
	} else {
		require( DIR_FS_PAGES . $current_page_base . '/' . $tpl_page_body);
	}

	require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_ALSO_PURCHASED_PRODUCTS));
?>

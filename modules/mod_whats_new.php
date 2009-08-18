<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce										  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers							  |
// |																	  |
// | http://www.zen-cart.com/index.php									  |
// |																	  |
// | Portions Copyright (c) 2003 osCommerce								  |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,		  |
// | that is bundled with this package in the file LICENSE, and is		  |
// | available through the world-wide-web at the following url:			  |
// | http://www.zen-cart.com/license/2_0.txt.							  |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to		  |
// | license@zen-cart.com so we can mail you a copy immediately.		  |
// +----------------------------------------------------------------------+
// $Id: mod_whats_new.php,v 1.11 2009/08/18 20:44:00 spiderr Exp $
//
	global $gBitDb, $gBitProduct, $currencies;

	require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );

	$listHash['freshness'] = SHOW_NEW_PRODUCTS_LIMIT;
	$listHash['max_records'] = 1;
	$listHash['sort_mode'] = 'products_date_added_desc';

	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', tra( 'New Products' ) );
	}

	if( $productList = $gBitProduct->getList( $listHash ) ) {
		$newProduct = current( $productList );
		$whats_new_price = CommerceProduct::getDisplayPrice( $newProduct['products_id'] );
		$gBitSmarty->assign_by_ref( 'newProduct', $newProduct );
	}
?>

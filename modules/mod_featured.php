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
global $gBitDb, $gCommerceSystem, $gBitProduct, $currencies;

if( empty( $gCommerceSystem ) ) {
	require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );
}

$listHash['max_records'] = 1; // ? MAX_RANDOM_SELECT_FEATURED_PRODUCTS;
$listHash['sort_mode'] = 'random';
$listHash['featured'] = TRUE;
if( $sideboxFeature = $gBitProduct->getList( $listHash ) ) {
	$sideboxFeature = current( $sideboxFeature );
	$whats_new_price = CommerceProduct::getDisplayPriceFromHash($sideboxFeature['products_id']);

	$_template->tpl_vars['sideboxFeature'] = new Smarty_variable( $sideboxFeature );
}
?>

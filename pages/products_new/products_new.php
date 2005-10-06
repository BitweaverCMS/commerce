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
// $Id: products_new.php,v 1.1 2005/10/06 19:38:31 spiderr Exp $
//
  $products_new_array = array();

  $show_submit = zen_run_normal();

  	$listHash['sort_mode'] = !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'products_date_added_desc';
	$listHash['page'] = !empty( $_REQUEST['page'] ) && is_numeric( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
	// check how many rows
	$listHash['thumbnail_size'] = 'small';
	$listHash['max_records'] = MAX_DISPLAY_PRODUCTS_NEW;
	$listHash['offset'] = $listHash['max_records'] * ($listHash['page'] - 1);
	if( SHOW_NEW_PRODUCTS_LIMIT > 0 ) {
		$pListHash['freshness'] = SHOW_NEW_PRODUCTS_LIMIT;
	}
	$productsList = $gBitProduct->getList( $listHash );

	$gBitSmarty->assign( 'listTitle', tra( 'New Products' ) );
	$gBitSmarty->assign( 'listInfo', $listHash );
	$gBitSmarty->assign( 'listProducts', $productsList );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/list_products.tpl' );

?>
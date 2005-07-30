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
// $Id: mod_whats_new.php,v 1.1 2005/07/30 15:08:16 spiderr Exp $
//
	global $db, $gBitProduct, $currencies;

  switch (true) {
    case (SHOW_NEW_PRODUCTS_LIMIT == '0'):
      $display_limit = '';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '1'):
      $display_limit = " and date_format(p.products_date_added, '%Y%m') >= date_format(now(), '%Y%m')";
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '30'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 30';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '60'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 60';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '90'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 90';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '120'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 120';
      break;
  }

	if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
		list( $selectSql, $fromSql, $whereSql ) = CommerceProduct::getGatekeeperSql();
	}
	$listHash['max_records'] = 1;
	$listHash['sort_mode'] = 'random';

	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', tra( 'New Products' ) );
	}

  if( $productList = $gBitProduct->getList( $listHash ) ) {
  	$newProduct = current( $productList );
    $whats_new_price = zen_get_products_display_price($newProduct['products_id']);

	$newProduct['display_price'] = $currencies->display_price($newProduct['products_price'], zen_get_tax_rate($newProduct['products_tax_class_id']));
    if( $newProduct['specials_new_products_price'] = zen_get_products_special_price($newProduct['products_id']) ) {
		$newProduct['display_special_price'] = $currencies->display_price($newProduct['specials_new_products_price'], zen_get_tax_rate($newProduct['products_tax_class_id']));
	}
	$gBitSmarty->assign_by_ref( 'newProduct', $newProduct );
  }
?>

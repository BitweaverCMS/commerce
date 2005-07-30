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
// $Id: mod_reviews.php,v 1.1 2005/07/30 15:08:15 spiderr Exp $
//
	global $db, $gBitProduct;

	if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
		list( $selectSql, $fromSql, $whereSql ) = CommerceProduct::getGatekeeperSql();
	}

	$listHash['reviews'] = TRUE;
	if( $gBitProduct->isValid() ) {
		$listHash['products_id'] = $gBitProduct->mProductsId;
	}
	$listHash['max_records'] = MAX_RANDOM_SELECT_REVIEWS;
	if( $sideboxReview = $gBitProduct->getList( $listHash ) ) {
		$gBitSmarty->assign( 'sideboxReview', current( $sideboxReview ) );
	} elseif ( isset($_GET['products_id']) and zen_products_id_valid($_GET['products_id'])) {
		$gBitSmarty->assign( 'writeReview', TRUE );
	}
	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', '<img src="'.BITCOMMERCE_PKG_URL.'icons/star.png" alt="*" />'.tra( 'Reviews' ) );
	}
?>
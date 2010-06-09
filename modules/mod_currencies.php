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
	global $gBitDb, $gBitProduct, $currencies;

	// test if box should display
	$show_currencies= false;

	if( substr( basename( $_SERVER['PHP_SELF'] ), 0, 8 ) != 'checkout' ) {
		$show_currencies= true;
	}

	if ($show_currencies == true) {
		if( isset( $currencies ) && is_object( $currencies ) ) {
			reset( $currencies->currencies );
			$currenciesHash = array();
			while( list( $key, $value ) = each( $currencies->currencies ) ) {
				$currenciesHash[$key] = $value['title'];
			}

			$gBitSmarty->assign( 'modCurrencies', $currenciesHash );
			$gBitSmarty->assign( 'modSelectedCurrency', !empty( $_SESSION['currency'] ) ? $_SESSION['currency'] : DEFAULT_CURRENCY );
			if( empty( $moduleTitle ) ) {
				$gBitSmarty->assign( 'moduleTitle', tra( 'Currencies' ) );
			}
		}
	}
?>

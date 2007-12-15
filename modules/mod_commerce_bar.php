<?php
//
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id: mod_commerce_bar.php,v 1.1 2007/12/15 22:39:45 spiderr Exp $
//
	global $gBitDb, $gBitProduct, $currencies, $gBitUser, $gBitCustomer;

	require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
	if( !empty( $_SESSION['cart'] ) && is_object( $_SESSION['cart'] ) && $_SESSION['cart']->count_contents() > 0 ) {
		$gBitSmarty->assign_by_ref( 'sessionCart', $_SESSION['cart'] );
	}

?>

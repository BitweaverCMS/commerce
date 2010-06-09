<?php
// +--------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
/**
 * @version	$Header$
 *
 * System class for handling the liberty package
 *
 * @package	bitcommerce
 * @author	 spider <spider@steelsun.com>
 */
	require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php' );

	global $gBitSmarty;

	$gBitSmarty->assign( 'gvBalance', CommerceVoucher::getGiftAmount() );
	$gBitSmarty->assign( 'couponAmount', CommerceVoucher::getCouponAmount() );

    if ( !empty( $messageStack ) && $messageStack->size('account') >0) {
		$gBitSmarty->assign( 'accountMessage',  $messageStack->output('account') );
	}

	$gBitSmarty->display( 'bitpackage:bitcommerce/page_account.tpl' );
?>

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
// $Id: gv_redeem.php,v 1.2 2005/11/30 07:17:24 spiderr Exp $
//

	require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php' );

	if( !$gBitUser->isRegistered() ) {
		$_SESSION['navigation']->set_snapshot();
		zen_redirect(FILENAME_LOGIN);
	}
	// check for a voucher number in the url
	if( !empty( $_REQUEST['gv_no'] ) && ($couponAmount = CommerceVoucher::redeemCoupon( $_REQUEST['gv_no'] )) ) {
		$_SESSION['gv_id'] = '';
		$feedback['success']['valid']  = sprintf(TEXT_VALID_GV, $currencies->format( $couponAmount ) );
	} else {
		$feedback['error']['invalid']  = TEXT_INVALID_GV;
	}

	$breadcrumb->add(NAVBAR_TITLE);

	$gBitSmarty->assign( 'feedback', $feedback );
	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_redeem.tpl' );
?>

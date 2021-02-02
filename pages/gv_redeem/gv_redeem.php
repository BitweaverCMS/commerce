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

	require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceVoucher.php' );

	if( !$gBitUser->isRegistered() ) {
		$_SESSION['navigation']->set_snapshot();
		zen_redirect(FILENAME_LOGIN);
	}
	// check for a voucher number in the url
	if( isset( $_REQUEST['gv_no'] ) ) {
		if( !empty( $_REQUEST['gv_no'] ) && ($couponAmount = CommerceVoucher::redeemCoupon( $gBitUser->mUserId, $_REQUEST['gv_no'] )) ) {
			$_SESSION['gv_id'] = '';
			$feedback['success']['valid']  = sprintf(TEXT_VALID_GV, $currencies->format( $couponAmount ) ).' '.tra( 'If you would like, you can use your balance to <a href="'.BITCOMMERCE_PKG_URL.'index.php?main_page=gv_send">send a gift certificate to someone else</a>.' );
		} else {
			$feedback['error']['invalid']  = TEXT_INVALID_GV;
		}
	}

	$breadcrumb->add(NAVBAR_TITLE);

	$gBitSmarty->assign( 'feedback', $feedback );
	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_redeem.tpl' );
?>

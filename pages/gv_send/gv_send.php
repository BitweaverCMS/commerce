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
	require_once( BITCOMMERCE_PKG_PATH.'includes/classes/http_client.php' );
	require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php' );

// if the customer is not logged on, redirect them to the login page
	if( !$gBitUser->isRegistered() ) {
		$_SESSION['navigation']->set_snapshot();
		zen_redirect(FILENAME_LOGIN);
	}



	$feedback = array();

	// do a fresh calculation after sending an email

	$requestAction = !empty( $_REQUEST['action'] ) ? strtolower( $_REQUEST['action'] ) : NULL;

	$requestAmount = NULL;
	if( !empty( $_POST['amount'] ) ) {
		$requestAmount = preg_replace( '/[^0-9.]/', '', $_POST['amount'] );
		$requestAmountValue = $currencies->value( $requestAmount, true, DEFAULT_CURRENCY );
	}

	$gvBalance = CommerceVoucher::getGiftAmount( FALSE );
	if( $requestAction == 'send' ) {
		$_SESSION['complete'] = '';
		if (!zen_validate_email(trim($_POST['email']))) {
			$feedback['error']['error_email'] = ERROR_ENTRY_EMAIL_ADDRESS_CHECK;
		}

		if( !is_numeric( $requestAmount ) || $requestAmountValue > $gvBalance ) {
			$feedback['error']['error_amount'] = ERROR_ENTRY_AMOUNT_CHECK;
		}
	} elseif ($requestAction == 'process') {
		if( $couponCode = CommerceVoucher::customerSendCoupon( $gBitUser, $_POST, $requestAmountValue ) ) {
			$requestAction = 'complete';
			// do a fresh calculation after sending an email
			$gvBalance = CommerceVoucher::getGiftAmount( FALSE );
		} else {
			$feedback['error']['error_amount'] = ERROR_ENTRY_AMOUNT_CHECK;
			$requestAction = 'send';
		}
	}

	$gBitSmarty->assign( 'gvBalance', $currencies->format( $gvBalance, true ) );

	if ($requestAction == 'complete') {
		zen_redirect(zen_href_link(FILENAME_GV_SEND, 'action=doneprocess'));
	}
	$breadcrumb->add(NAVBAR_TITLE);

	if ($requestAction == 'doneprocess') {
		$feedback['success'] = tra( TEXT_SUCCESS );
	} elseif( $requestAction == 'send' && empty( $formfeedback['error'] ) ) {
		// validate entries
		$gvAmount = $currencies->format( $requestAmount, false );
		$gBitSmarty->assign( 'gvAmount', $gvAmount );

		$mainMessage = sprintf(MAIN_MESSAGE, $_POST['to_name'], $gvAmount, $gBitUser->getDisplayName() );
		$gBitSmarty->assign( 'mainMessage', $mainMessage );
	}

	$gBitSmarty->assign( 'feedback', $feedback );

	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/gv_send.tpl' );
?>

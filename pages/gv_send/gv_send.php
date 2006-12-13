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
// $Id: gv_send.php,v 1.4 2006/12/13 18:20:04 spiderr Exp $
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
		if( $couponCode = CommerceVoucher::customerSendCoupon( $gBitUser, $_POST['email'], $requestAmountValue ) ) {
			$requestAction = 'complete';
/*
			$gv_query="update " . TABLE_COUPON_GV_CUSTOMER . "
						set `amount` = '" .  $new_amount . "'
						where `customer_id` = '" . $_SESSION['customer_id'] . "'";

			$db->Execute($gv_query);

			$gv_query="select `customers_firstname`, `customers_lastname`
						from " . TABLE_CUSTOMERS . "
						where `customers_id` = ?";
			$gv_customer=$db->query($gv_query, array( $_SESSION['customer_id'] ) );

			$gv_query="insert into " . TABLE_COUPONS . " (`coupon_type`, `coupon_code`, `date_created`, `coupon_amount`) values ('G', ?, NOW(), ?)";
			$gv = $db->query($gv_query, array( $couponCode, $requestAmountValue ) );
			$insert_id = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );

			$gv_query="insert into " . TABLE_COUPON_EMAIL_TRACK . "
								(`coupon_id`, `customer_id_sent`, `sent_firstname`, `sent_lastname`, `emailed_to`, `date_sent`)
						values ('" . $insert_id . "' ,'" . $_SESSION['customer_id'] . "', '" .
								$gv_customer->fields['customers_firstname'] . "', '" .
								$gv_customer->fields['customers_lastname'] . "', '" .
								$_POST['email'] . "', now())";
			$db->Execute($gv_query);
*/
			$gv_email = STORE_NAME . "\n" .
					EMAIL_SEPARATOR . "\n" .
					sprintf(EMAIL_GV_TEXT_HEADER, $currencies->format( $requestAmount, false ) ) . "\n" .
					EMAIL_SEPARATOR . "\n\n" .
					sprintf( EMAIL_GV_FROM, $gBitUser->getDisplayName() ) . "\n";

			$html_msg['EMAIL_GV_TEXT_HEADER'] =  sprintf(EMAIL_GV_TEXT_HEADER, '');
			$html_msg['EMAIL_GV_AMOUNT'] =  $currencies->format( $requestAmount, false );
			$html_msg['EMAIL_GV_FROM'] =  sprintf(EMAIL_GV_FROM, $gBitUser->getDisplayName() ) ;

			if (isset($_POST['message'])) {
				$gv_email .= EMAIL_GV_MESSAGE . "\n\n";
				$html_msg['EMAIL_GV_MESSAGE'] = EMAIL_GV_MESSAGE . '<br />';

				if (isset($_POST['to_name'])) {
					$gv_email .= sprintf(EMAIL_GV_SEND_TO, $_POST['to_name']) . "\n\n";
					$html_msg['EMAIL_GV_SEND_TO'] = '<tt>'.sprintf(EMAIL_GV_SEND_TO, $_POST['to_name']). '</tt><br />';
				}
				$gv_email .= stripslashes($_POST['message']) . "\n\n";
				$gv_email .= EMAIL_SEPARATOR . "\n\n";
				$html_msg['EMAIL_MESSAGE_HTML'] = stripslashes($_POST['message']);
			}

			$html_msg['GV_REDEEM_HOW'] = sprintf(EMAIL_GV_REDEEM, '<strong>' . $couponCode . '</strong>');
			$html_msg['GV_REDEEM_URL'] = '<a href="'.zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $couponCode, 'NONSSL').'">'.EMAIL_GV_LINK.'</a>';
			$html_msg['GV_REDEEM_CODE'] = $couponCode;

			$gv_email .= sprintf(EMAIL_GV_REDEEM, $couponCode) . "\n\n";
			$gv_email .= EMAIL_GV_LINK . ' ' . zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $couponCode, 'NONSSL');;
			$gv_email .= "\n\n";
			$gv_email .= EMAIL_GV_FIXED_FOOTER . "\n\n";
			$gv_email .= EMAIL_GV_SHOP_FOOTER;

			$gv_email_subject = sprintf(EMAIL_GV_TEXT_SUBJECT, $gBitUser->getDisplayName()).' '.tra( 'to' ).' '.STORE_NAME;

		// include disclaimer
			$gv_email .= "\n\n" . EMAIL_ADVISORY . "\n\n";

			$html_msg['EMAIL_GV_FIXED_FOOTER'] = str_replace(array("\r\n", "\n", "\r", "-----"), '', EMAIL_GV_FIXED_FOOTER);
			$html_msg['EMAIL_GV_SHOP_FOOTER'] =	EMAIL_GV_SHOP_FOOTER;

		// send the email
			zen_mail('', $_POST['email'], $gv_email_subject, nl2br($gv_email), STORE_NAME, EMAIL_FROM, $html_msg,'gv_send');

		// send additional emails
			if (SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' and SEND_EXTRA_GV_CUSTOMER_EMAILS_TO !='') {
				if ($_SESSION['customer_id']) {
					$account_query = "select `customers_firstname`, `customers_lastname`, `customers_email_address`
										from " . TABLE_CUSTOMERS . "
										where `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

					$account = $db->Execute($account_query);
				}
				$extra_info=email_collect_extra_info($_POST['to_name'],$_POST['email'], $account->fields['customers_firstname'] . ' ' . $account->fields['customers_lastname'] , $account->fields['customers_email_address'] );
				$html_msg['EXTRA_INFO'] = $extra_info['HTML'];
				zen_mail('', SEND_EXTRA_GV_CUSTOMER_EMAILS_TO, SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_SUBJECT . ' ' . $gv_email_subject,
					$gv_email . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg,'gv_send_extra');
			}

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

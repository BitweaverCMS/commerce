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
//  $Id: gv_mail.php,v 1.15 2007/10/31 12:44:00 spiderr Exp $
//

  require_once('includes/application_top.php');
  require_once( BITCOMMERCE_PKG_PATH.'includes/classes/order.php');

  $_REQUEST['amount'] = (!empty( $_REQUEST['amount'] ) ? preg_replace( '/[^\d.]/', '', $_REQUEST['amount'] ) : NULL);

	if ( !empty( $_GET['action'] ) ) {
		if ($_GET['action'] == 'set_editor') {
			if ($_GET['reset_editor'] == '0') {
			$_SESSION['html_editor_preference_status'] = 'NONE';
			} else {
			$_SESSION['html_editor_preference_status'] = 'HTMLAREA';
			}
			$action='';
			zen_redirect(zen_href_link_admin(FILENAME_GV_MAIL));
		}
		if ( ($_GET['action'] == 'send_email_to_user') && (!empty( $_REQUEST['customers_email_address'] ) || !empty( $_REQUEST['email_to'] )) && (empty( $_REQUEST['back'] ) ) ) {
			$from = zen_db_prepare_input($_REQUEST['from']);
			$subject = zen_db_prepare_input($_REQUEST['subject']);
/*
This code is just too damned dangerous to be useful - possibly send every customer a gift certificate! - spiderr

			if( !empty( $_REQUEST['customers_email_address'] ) ) {
				$audience_select = get_audience_sql_query($_REQUEST['customers_email_address'], 'email');
				$mail = $gBitDb->Execute($audience_select['query_string']);
				$mailSentTo = $audience_select['query_name'];

				$recip_count=0;
				
				while (!$mail->EOF) {
					$id1 = CommerceVoucher::generateCouponCode( $mail->fields['customers_email_address'] );
					$insert_query = $gBitDb->Execute("insert into " . TABLE_COUPONS . "
												(coupon_code, coupon_type, coupon_amount, date_created)
												values ('" . $id1 . "', 'G', '" . $_REQUEST['amount'] . "', now())");

					$insert_id = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );

					$gBitDb->Execute("insert into " . TABLE_COUPON_EMAIL_TRACK . "
								(coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
								values ('" . $insert_id ."', '0', 'Admin',
										'" . $mail->fields['customers_email_address'] . "', now() )");

					$message = $_REQUEST['message'];
					$html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_REQUEST['message_html']);
					$message .= "\n\n" . TEXT_GV_WORTH  . $currencies->format($_REQUEST['amount']) . "\n\n";
					$message .= TEXT_TO_REDEEM;
					$message .= TEXT_WHICH_IS . ' ' . $id1 . ' ' . TEXT_IN_CASE . "\n\n";

					$html_msg['GV_WORTH']  = TEXT_GV_WORTH;
					$html_msg['GV_AMOUNT']  = $currencies->format($_REQUEST['amount']);
					$html_msg['GV_REDEEM'] = TEXT_TO_REDEEM . TEXT_WHICH_IS . ' <strong>' . $id1 . '</strong> ' . TEXT_IN_CASE;

					if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
						$message .= HTTP_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1 . "\n\n";
						$html_msg['GV_CODE_URL'] = '<a href="'.HTTP_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1.'">' .TEXT_CLICK_TO_REDEEM . '</a>'. "&nbsp;";
					} else {
						$message .= HTTP_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 . "\n\n";
						$html_msg['GV_CODE_URL'] =  '<a href="'. HTTP_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 .'">' .TEXT_CLICK_TO_REDEEM . '</a>' . "&nbsp;";
					}

					$message .= TEXT_OR_VISIT . HTTP_SERVER  . DIR_WS_CATALOG . TEXT_ENTER_CODE . "\n\n";
					$html_msg['GV_CODE_URL'] .= TEXT_OR_VISIT .  '<a href="'.HTTP_SERVER  . DIR_WS_CATALOG.'">' . STORE_NAME . '</a>' . TEXT_ENTER_CODE;
					$html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
					$html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];

					// disclaimer
					$message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

					zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], $subject , $message, $from, $from, $html_msg, 'gv_mail');
					$recip_count++;
					if (SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO != '') {
						zen_mail('', SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO, SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, $from, $from, $html_msg, 'gv_mail_extra');
					}

					// Now create the coupon main and email entry
					$mail->MoveNext();
				}
			}
*/
			if ( !empty( $_REQUEST['send_gv'] ) ) {
				$mailSentTo = $_REQUEST['email_to'];
				$id1 = CommerceVoucher::generateCouponCode( $_REQUEST['email_to'] );
				$message = zen_db_prepare_input($_REQUEST['message']);
				$message .= "\n\n" . TEXT_GV_WORTH  . $currencies->format($_REQUEST['amount']) . "\n\n";
				$message .= TEXT_TO_REDEEM;
				$message .= TEXT_WHICH_IS . ' ' . $id1 . ' ' . TEXT_IN_CASE . "\n\n";

				$html_msg['GV_WORTH']  = TEXT_GV_WORTH  . $currencies->format($_REQUEST['amount']) .'<br />';
				$html_msg['GV_REDEEM'] = TEXT_TO_REDEEM . TEXT_WHICH_IS . ' <strong>' . $id1 . '</strong> ' . TEXT_IN_CASE . "\n\n";

				if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
					$message .= HTTP_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1 . "\n\n";
					$html_msg['GV_CODE_URL']  = '<a href="'.HTTP_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1.'">' .TEXT_CLICK_TO_REDEEM . '</a>'. "&nbsp;";
				} else {
					$message .= HTTP_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 . "\n\n";
					$html_msg['GV_CODE_URL']  =  '<a href="'. HTTP_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 .'">' .TEXT_CLICK_TO_REDEEM . '</a>' . "&nbsp;";
				}
				$message .= TEXT_OR_VISIT . HTTP_SERVER  . DIR_WS_CATALOG  . TEXT_ENTER_CODE . "\n\n";
				$html_msg['GV_CODE_URL']  .= TEXT_OR_VISIT .  '<a href="'.HTTP_SERVER  . DIR_WS_CATALOG.'">' . STORE_NAME . '</a>' . TEXT_ENTER_CODE;

				$html_msg['EMAIL_MESSAGE_HTML'] = !empty( $_REQUEST['message_html'] ) ? zen_db_prepare_input($_REQUEST['message_html']) : '';
				$html_msg['EMAIL_FIRST_NAME'] = ''; // unknown, since only an email address was supplied
				$html_msg['EMAIL_LAST_NAME']  = ''; // unknown, since only an email address was supplied

				// disclaimer
				$message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
			//Send the emails
				zen_mail('Friend', $_REQUEST['email_to'], $subject , $message, $from, $from, $html_msg, 'gv_mail');
				$recip_count++;
				if (SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO != '') {
					zen_mail('', SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO, SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, $from, $from, $html_msg, 'gv_mail_extra');
				}

				// Now create the coupon main entry
				$insert_query = $gBitDb->Execute("insert into " . TABLE_COUPONS . "
											(coupon_code, coupon_type, coupon_amount, date_created)
											values ('" . $id1 . "', 'G', '" . $_REQUEST['amount'] . "', now())");

				$insert_id = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );

				$insert_query = $gBitDb->Execute("insert into " . TABLE_COUPON_EMAIL_TRACK . "
											(coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
											values ('" . $insert_id ."', '0', 'Admin',
													'" . $_REQUEST['email_to'] . "', now() )");

				if( !empty( $_REQUEST['oID'] ) ) {
					$order = new order( $_REQUEST['oID'] );
					$status['comments'] = 'A $'.$_REQUEST['amount'].' Gift Certificate ( '.$id1.' ) was emailed to '.$_REQUEST['email_to'].' in relation to order '.$_REQUEST['oID'].'';
					$order->updateStatus( $status );
					zen_redirect(zen_href_link_admin(FILENAME_ORDERS, 'oID=' . $_REQUEST['oID'] ));
				} else {
					zen_redirect(zen_href_link_admin(FILENAME_GV_MAIL, 'mailSentTo=' . urlencode($mailSentTo) . '&recip_count='. $recip_count ));
				}
			}
		}

		if ( ($_GET['action'] == 'preview') && empty( $_REQUEST['customers_email_address'] ) && empty( $_REQUEST['email_to'] ) ) {
			$messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
		}

		if ( ($_GET['action'] == 'preview') && empty( $_REQUEST['subject'] ) ) {
			$messageStack->add(ERROR_NO_SUBJECT, 'error');
		}
		if ( ($_GET['action'] == 'preview') && ($_REQUEST['amount'] <= 0) ) {
			$messageStack->add(ERROR_NO_AMOUNT_SELECTED, 'error');
		}
	}

	if( !empty( $_GET['mailSentTo'] ) ) {
		$messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mailSentTo']. '(' . $_GET['recip_count'] . ')'), 'success');
	}
	if ( !empty( $_GET['action'] ) && $_GET['action'] == 'preview' && (!empty( $_REQUEST['customers_email_address'] ) || !empty( $_REQUEST['email_to']) ) ) {
        if( !empty( $_REQUEST['email_to'] ) ) {
          $mailSentTo = $_REQUEST['email_to'];
        } elseif( !empty( $_REQUEST['customers_email_address'] ) ) {
			$audience_select = get_audience_sql_query($_REQUEST['customers_email_address']);
    		$mailSentTo = $audience_select['query_name'];
		}
		$gBitSmarty->assign( 'mailSentTo', $mailSentTo );
	} 
	$gBitSystem->display( "bitpackage:bitcommerce/admin_gv_mail.tpl" );
?>

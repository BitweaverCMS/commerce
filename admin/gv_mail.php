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
//  $Id: gv_mail.php,v 1.16 2007/11/05 03:48:25 spiderr Exp $
//

  require_once('includes/application_top.php');
  require_once( BITCOMMERCE_PKG_PATH.'includes/classes/order.php');
  require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php');

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
			if ( !empty( $_REQUEST['send_gv'] ) ) {
				$voucher = new CommerceVoucher();
				$voucher->adminSendCoupon( $_REQUEST );
				if( !empty( $_REQUEST['oID'] ) ) {
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

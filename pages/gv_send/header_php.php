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
// $Id: header_php.php,v 1.3 2005/08/24 15:06:36 lsces Exp $
//
  require('includes/classes/http_client.php');

// if the customer is not logged on, redirect them to the login page
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_MODULES . 'require_languages.php');

  if (($_POST['back_x']) || ($_POST['back_y'])) {
  }

  $gv_query = "select amount
               from " . TABLE_COUPON_GV_CUSTOMER . "
               where `customer_id` = '" . $_SESSION['customer_id'] . "'";

  $gv_result = $db->Execute($gv_query);

// Sanity Check
// Some stuff for debugging
// First lets get the local and base for how much the customer has in his GV account
// The customer_gv account is alwaus stored in the store base currency
//   $local_customer_gv = $currencies->value($gv_result->fields['amount']);
//   $base_customer_gv = $gv_result->fields['amount'];
// Now lets get the amount that the customer wants to send.
//   $local_customer_send = $_POST['amount'];
//   $base_customer_send = $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY);


  if ($_GET['action'] == 'send') {
    $_SESSION['complete'] = '';
    $error = false;
    if (!zen_validate_email(trim($_POST['email']))) {
      $error = true;
      $error_email = ERROR_ENTRY_EMAIL_ADDRESS_CHECK;
    }

    $customer_amount = $gv_result->fields['amount'];

    $_POST['amount'] = str_replace('$', '', $_POST['amount']);

    $gv_amount = trim($_POST['amount']);
    if (ereg('[^0-9/.]', $gv_amount)) {
      $error = true;
      $error_amount = ERROR_ENTRY_AMOUNT_CHECK;
    }
    if ( $currencies->value($gv_amount, true,DEFAULT_CURRENCY) > $customer_amount || $gv_amount == 0) {
//echo $currencies->value($customer_amount, true,DEFAULT_CURRENCY);
      $error = true;
      $error_amount = ERROR_ENTRY_AMOUNT_CHECK;
    }
  }

  if ($_GET['action'] == 'process') {
    $id1 = zen_create_coupon_code($mail['customers_email_address']);

    $_POST['amount'] = str_replace('$', '', $_POST['amount']);

    $new_amount = $gv_result->fields['amount'] - $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY);
//die($currencies->value($_POST['amount'], true, $_SESSION['currency']));
    $new_db_amount = $gv_result->fields['amount'] - $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY);
    if ($new_amount < 0) {
      $error= true;
      $error_amount = ERROR_ENTRY_AMOUNT_CHECK;
      $_GET['action'] = 'send';
    } else {
      $_GET['action'] = 'complete';
      $gv_query="update " . TABLE_COUPON_GV_CUSTOMER . "
                 set amount = '" .  $new_amount . "'
                 where `customer_id` = '" . $_SESSION['customer_id'] . "'";

      $db->Execute($gv_query);

      $gv_query="select customers_firstname, customers_lastname
                 from " . TABLE_CUSTOMERS . "
                 where `customers_id` = '" . $_SESSION['customer_id'] . "'";

      $gv_customer=$db->Execute($gv_query);
      $gv_query="insert into " . TABLE_COUPONS . "
                 (coupon_type, coupon_code, date_created, coupon_amount)
                 values ('G', '" . $id1 . "', NOW(), '" . $currencies->value($_POST['amount'], true, DEFAULT_CURRENCY) . "')";

      $gv = $db->Execute($gv_query);

      $insert_id = zen_db_insert_id( TABLE_COUPONS, 'coupon_id' );

      $gv_query="insert into " . TABLE_COUPON_EMAIL_TRACK . "
                        (coupon_id, customer_id_sent, sent_firstname, sent_lastname, emailed_to, date_sent)
                 values ('" . $insert_id . "' ,'" . $_SESSION['customer_id'] . "', '" .
                         $gv_customer->fields['customers_firstname'] . "', '" .
                         $gv_customer->fields['customers_lastname'] . "', '" .
                         $_POST['email'] . "', now())";

      $db->Execute($gv_query);

      $gv_email = STORE_NAME . "\n" .
              EMAIL_SEPARATOR . "\n" .
              sprintf(EMAIL_GV_TEXT_HEADER, $currencies->format($_POST['amount'], false)) . "\n" .
              EMAIL_SEPARATOR . "\n\n" .
              sprintf(EMAIL_GV_FROM, $_POST['send_name']) . "\n";

		$html_msg['EMAIL_GV_TEXT_HEADER'] =  sprintf(EMAIL_GV_TEXT_HEADER, '');
		$html_msg['EMAIL_GV_AMOUNT'] =  $currencies->format($_POST['amount'], false);
		$html_msg['EMAIL_GV_FROM'] =  sprintf(EMAIL_GV_FROM, $_POST['send_name']) ;

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

	  $html_msg['GV_REDEEM_HOW'] = sprintf(EMAIL_GV_REDEEM, '<strong>' . $id1 . '</strong>');
	  $html_msg['GV_REDEEM_URL'] = '<a href="'.zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $id1, 'NONSSL').'">'.EMAIL_GV_LINK.'</a>';
	  $html_msg['GV_REDEEM_CODE'] = $id1;

      $gv_email .= sprintf(EMAIL_GV_REDEEM, $id1) . "\n\n";
      $gv_email .= EMAIL_GV_LINK . ' ' . zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $id1, 'NONSSL');;
      $gv_email .= "\n\n";
      $gv_email .= EMAIL_GV_FIXED_FOOTER . "\n\n";
      $gv_email .= EMAIL_GV_SHOP_FOOTER;

      $gv_email_subject = sprintf(EMAIL_GV_TEXT_SUBJECT, $_POST['send_name']);

// include disclaimer
      $gv_email .= "\n\n" . EMAIL_ADVISORY . "\n\n";

   		$html_msg['EMAIL_GV_FIXED_FOOTER'] = str_replace(array("\r\n", "\n", "\r", "-----"), '', EMAIL_GV_FIXED_FOOTER);
		$html_msg['EMAIL_GV_SHOP_FOOTER'] =	EMAIL_GV_SHOP_FOOTER;

// send the email
      zen_mail('', $_POST['email'], $gv_email_subject, nl2br($gv_email), STORE_NAME, EMAIL_FROM, $html_msg,'gv_send');

// send additional emails
      if (SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' and SEND_EXTRA_GV_CUSTOMER_EMAILS_TO !='') {
        if ($_SESSION['customer_id']) {
          $account_query = "select customers_firstname, customers_lastname, customers_email_address
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
      $gv_query = "select amount
                   from " . TABLE_COUPON_GV_CUSTOMER . "
                   where `customer_id` = '" . $_SESSION['customer_id'] . "'";

      $gv_result = $db->Execute($gv_query);
    }
  }

  $gv_current_balance = $gv_result->fields['amount'];

  if ($_GET['action'] == 'complete') zen_redirect(zen_href_link(FILENAME_GV_SEND, 'action=doneprocess'));
  $breadcrumb->add(NAVBAR_TITLE);


?>

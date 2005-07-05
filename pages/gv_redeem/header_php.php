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
// $Id: header_php.php,v 1.1 2005/07/05 05:59:11 bitweaver Exp $
//
// if the customer is not logged on, redirect them to the login page
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
// check for a voucher number in the url
  if (isset($_GET['gv_no'])) {
    $error = true;
    $gv_query = "select c.coupon_id, c.coupon_amount
                 from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et
                 where coupon_code = '" . $_GET['gv_no'] . "'
                 and c.coupon_id = et.coupon_id";

    $coupon = $db->Execute($gv_query);

    if ($coupon->RecordCount() >0) {
      $redeem_query = "select coupon_id
                       from ". TABLE_COUPON_REDEEM_TRACK . "
                       where coupon_id = '" . $coupon->fields['coupon_id'] . "'";

      $redeem = $db->Execute($redeem_query);

      if ($redeem->RecordCount() == 0 ) {
// check for required session variables
        $_SESSION['gv_id'] = $coupon->fields['coupon_id'];
        $error = false;
      } else {
        $error = true;
      }
    }
  } else {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
  }
  if ((!$error) && ($_SESSION['customer_id'])) {
// Update redeem status
    $gv_query = "insert into  " . TABLE_COUPON_REDEEM_TRACK . "
                              (coupon_id, customer_id, redeem_date, redeem_ip)
                               values ('" . $coupon->fields['coupon_id'] . "', '" . $_SESSION['customer_id'] . "',
                               now(),'" . $REMOTE_ADDR . "')";

    $db->Execute($gv_query);

    $gv_update = "update " . TABLE_COUPONS . "
                  set coupon_active = 'N'
                  where coupon_id = '" . $coupon['coupon_id'] . "'";

    $db->Execute($gv_update);

    zen_gv_account_update($_SESSION['customer_id'], $_SESSION['gv_id']);
    $_SESSION['gv_id'] = '';
  }

  require(DIR_WS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);
?>
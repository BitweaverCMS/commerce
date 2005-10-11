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
// $Id: header_php.php,v 1.5 2005/10/11 03:50:11 spiderr Exp $
//
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(FILENAME_LOGIN);
  }
  $gv_query = "select `amount`
               from " . TABLE_COUPON_GV_CUSTOMER . "
               where `customer_id` = '" . $_SESSION['customer_id'] . "'";
  $gv_result = $db->Execute($gv_query);

  if ($gv_result->fields['amount'] > 0 ) {
    $customer_has_gv_balance = true;
	  $customer_gv_balance = $currencies->format($gv_result->fields['amount']);
  }

  require(DIR_FS_MODULES . 'require_languages.php');
// only show when there is a GV balance
  if ($_SESSION['customer_id']) {
    $gv_query = "select `amount`
                 from " . TABLE_COUPON_GV_CUSTOMER . "
                 where `customer_id` = '" . $_SESSION['customer_id'] . "'";
    $gv_result = $db->Execute($gv_query);

    if ($gv_result->fields['amount'] > 0 ) {
      $gift_voucher_amount = $currencies->format($gv_result->fields['amount']);
    }
  }
  $breadcrumb->add(NAVBAR_TITLE);
?>
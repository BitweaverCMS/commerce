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
// $Id: header_php.php,v 1.2 2005/08/24 15:06:41 lsces Exp $
//
  require(DIR_WS_MODULES . 'require_languages.php');
  
  if ($_SESSION['customer_id']) {
    $gv_query = "select `amount`
                 from " . TABLE_COUPON_GV_CUSTOMER . "
                 where `customer_id` = '" . $_SESSION['customer_id'] . "'";
    $gv_result = $db->Execute($gv_query);
    if ($gv_result->fields['amount'] > 0 ) $has_gv_balance = true;
  }
  if ($_SESSION['gv_id']) {
    $gv_query = "select `coupon_amount`
                 from " . TABLE_COUPONS . "
                 where `coupon_id` = '" . $_SESSION['gv_id'] . "'";

    $coupon = $db->Execute($gv_query);
  }
  $breadcrumb->add(NAVBAR_TITLE);
?>

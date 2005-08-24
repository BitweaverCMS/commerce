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
// $Id: blk_account_history.php,v 1.4 2005/08/24 02:50:50 lsces Exp $
//
  $orders_total = zen_count_customer_orders();

  if ($orders_total > 0) {
    $history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name,
                                 o.billing_name, ot.text as order_total, s.orders_status_name
                          from   " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " .
                                     TABLE_ORDERS_STATUS . " s
                          where      o.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                          and        o.orders_id = ot.orders_id
                          and        ot.class = 'ot_total'
                          and        o.orders_status = s.orders_status_id
                          and        s.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                          order by   orders_id DESC";

    $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
    $history = $db->Execute($history_split->sql_query);

    while (!$history->EOF) {
      $products_query = "select count(*) as count
                         from   " . TABLE_ORDERS_PRODUCTS . "
                         where      orders_id = '" . (int)$history->fields['orders_id'] . "'";

      $products = $db->Execute($products_query);
      if (zen_not_null($history->fields['delivery_name'])) {
        $order_type = TEXT_ORDER_SHIPPED_TO;
        $order_name = $history->fields['delivery_name'];
      } else {
        $order_type = TEXT_ORDER_BILLED_TO;
        $order_name = $history->fields['billing_name'];
      }
      require(DIR_WS_BLOCKS . 'tpl_block_account_history.php');
      $history->MoveNext();
    }
  } else {
    require( DIR_FS_BLOCKS . 'tpl_block_no_account_history.php');
  }
?>
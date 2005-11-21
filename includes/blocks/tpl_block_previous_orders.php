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
// $Id: tpl_block_previous_orders.php,v 1.2 2005/11/21 14:42:51 spiderr Exp $
//
?>
  <tr class="moduleRow" onmouseOver="rowOverEffect(this)" onmouseOut="rowOutEffect(this)" onclick="document.location.href='<?php echo zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders->fields['orders_id'], 'SSL'); ?>'">
    <td class="main" valign="top">
      <?php echo zen_date_short($orders->fields['date_purchased']); ?>
    </td>
    <td class="main" valign="top">
      <a href="<?php echo zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders->fields['orders_id'], 'SSL')?>"><?php echo TEXT_NUMBER_SYMBOL . $orders->fields['orders_id']; ?></a>
    </td>
    <td class="main" valign="top">
      <?php echo zen_output_string_protected($order_name) . ', ' . $order_country; ?>
    </td>
    <td class="main" valign="top">
      <?php echo $orders->fields['orders_status_name']; ?>
    </td>
    <td class="main" align="right" valign="top">
      <?php echo $orders->fields['order_total']; ?>
    </td>
  </tr>
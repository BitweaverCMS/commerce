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
// $Id: tpl_block_account_history.php,v 1.1 2005/08/04 07:24:04 spiderr Exp $
//
?>
      <table border="0" width="100%" cellspacing="2" cellpadding="2" class="plainBox">
        <tr>
          <td class="main"><?php echo '<strong>' . TEXT_ORDER_NUMBER . '</strong> ' . $history->fields['orders_id']; ?></td>
          <td align="right" colspan="2"><?php echo '<strong>' . TEXT_ORDER_STATUS . '</strong> ' . $history->fields['orders_status_name']; ?></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
        </tr>
        <tr>
          <td width="55%" valign="top"><?php echo '<strong>' . TEXT_ORDER_DATE . '</strong> ' . zen_date_long($history->fields['date_purchased']) . '<br /><strong>' . $order_type . '</strong> ' . zen_output_string_protected($order_name); ?></td>
          <td width="30%" valign="top"><?php echo '<strong>' . TEXT_ORDER_PRODUCTS . '</strong> ' . $products->fields['count'] . '<br /><strong>' . TEXT_ORDER_COST . '</strong> ' . strip_tags($history->fields['order_total']); ?></td>
          <td width="15%"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $history->fields['orders_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_VIEW_SMALL, BUTTON_VIEW_SMALL_ALT) . '</a>'; ?></td>
        </tr>
      </table><br />
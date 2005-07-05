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
// $Id: tpl_order_history.php,v 1.1 2005/07/05 05:59:27 bitweaver Exp $
//
  $id = orderhistory;
  $content = "";
  $content = '<table border="0" width="100%" cellspacing="0" cellpadding="1">';

  for ($i=1; $i<=sizeof($customer_orders); $i++) {

        $content .= '  <tr>' .
                    '    <td class="infoboxcontents"><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $customer_orders[$i]['id']) . '">' . $customer_orders[$i]['name'] . '</a></td>' .
                    '    <td class="infoboxcontents" align="right" valign="top"><a href="' . zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $customer_orders[$i]['id']) . '">' . zen_image(DIR_WS_TEMPLATE_ICONS . 'cart.gif', ICON_CART) . '</a></td>' .
                    '  </tr>';
  }
  $content .= '</table>';
?>
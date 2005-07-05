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
// $Id: tpl_account_history_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<h1><?php echo HEADING_TITLE; ?></h1>

			<?php
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
                          and        s.language_id = '" . (int)$_SESSION['languages_id'] . "' 
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
?>
          <table border="0" width="100%" cellspacing="2" cellpadding="2" class="plainBox">
            <tr>
              <td><?php echo '<b>' . TEXT_ORDER_NUMBER . '</b> ' . $history->fields['orders_id']; ?></td>
              <td align="right" colspan="2"><?php echo '<b>' . TEXT_ORDER_STATUS . '</b> ' . $history->fields['orders_status_name']; ?></td>
            </tr>
	         <tr>
         		<td colspan="3"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
	         </tr>
                <tr>
                  <td width="55%" valign="top"><?php echo '<b>' . TEXT_ORDER_DATE . '</b> ' . zen_date_long($history->fields['date_purchased']) . '<br /><b>' . $order_type . '</b> ' . zen_output_string_protected($order_name); ?></td>
                  <td width="30%" valign="top"><?php echo '<b>' . TEXT_ORDER_PRODUCTS . '</b> ' . $products->fields['count'] . '<br /><b>' . TEXT_ORDER_COST . '</b> ' . strip_tags($history->fields['order_total']); ?></td>
                  <td width="15%"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $history->fields['orders_id'], 'SSL') . '">' . zen_image_button('small_view.gif', SMALL_IMAGE_BUTTON_VIEW) . '</a>'; ?></td>
                </tr>
              </table>
<?php
      $history->MoveNext();
    }
  } else {
?>

	     <p><?php echo TEXT_NO_PURCHASES; ?></p>		

<?php
  }
?>
<?php
  if ($orders_total > 0) {
?>
<br class="clear" />
<div class="row" id="pageresultsbottom">
<span class="left"><?php echo $history_split->display_count(TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></span>
<span class="right"><?php echo TEXT_RESULT_PAGE . ' ' . $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></span>
  </div>
<br class="clear" />       
		 
<?php
  }
?>
	
<?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?>
	


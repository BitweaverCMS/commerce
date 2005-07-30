




















  {bitmodule title=$moduleTitle name="orderhistory"}
  $content = "";
  $content = '<table border="0" width="100%" cellspacing="0" cellpadding="1">

  for ($i=1; $i<=sizeof($customer_orders); $i++) {

  <tr>' .
                    '    <td class="infoboxcontents"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_info&products_id={$customer_orders[ix].id}">' . $customer_orders[$i]['name'] . '</a></td>' .
                    '    <td class="infoboxcontents" align="right" valign="top"><a href="' . zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $customer_orders[ix].id) . '">' . zen_image(DIR_WS_TEMPLATE_ICONS . 'cart.gif', ICON_CART) . '</a></td>' .
                    '  </tr>
{/if}
/table>
{/bitmodule}
{if $sideboxCustomerOrders}

{bitmodule title=$moduleTitle name="orderhistory"}

<table border="0" width="100%" cellspacing="0" cellpadding="1">
{section name=ix loop=$sideboxCustomerOrders}
<tr>
	<td class="infoboxcontents">
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_info&products_id={$sideboxCustomerOrders[ix].id}">{$sideboxCustomerOrders[ix].name}</a>
	</td>
	<td class="infoboxcontents" align="right" valign="top"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=index.php&amp;action=cust_order&pid={$customer_orders[ix].id}">{biticon ipackage="bitcommerce" iname="cart"}</a>
	</td>
</tr>
{/section}
</table>
{/bitmodule}

{/if}
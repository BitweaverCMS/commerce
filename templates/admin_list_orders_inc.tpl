<table class="data">
<tr><th colspan="4">{tr}New Orders{/tr}</th></tr>
<tr><td>{tr}Filter Orders{/tr}:</td>
	<td colspan="4" align="right">
{form}
<select name="orders_status_comparison">
	<option value="">{tr}Exactly{/tr}</option>
	<option value=">=" {if $smarty.session.orders_status_comparison == '>='}selected="selected"{/if}>{tr}At Least{/tr}</option>
	<option value="<=" {if $smarty.session.orders_status_comparison == '<='}selected="selected"{/if}>{tr}At Most{/tr}</option>
</select>

{html_options name="orders_status_id" options=$commerceStatuses selected=$smarty.session.orders_status_id|default:'all'}

<input type="submit" value="Go" name="list_filter"/>
{/form}
	</td>
</tr>
{foreach from=$listOrders key=orderId item=order}
<tr>
	<td><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$orderId}&amp;origin=index&amp;action=edit" class="contentlink">{$orderId} - {$gBitUser->getDisplayName(0,$order)}</a></td>
	<td>{$order.order_total|round:2}</td>
	<td align="right">{$order.date_purchased|bit_short_date}</td>
	<td>{$order.orders_status_name}</td>
</tr>
{/foreach}

</table>


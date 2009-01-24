<table class="data">
<tr><th colspan="4">{tr}Order List{/tr}</th></tr>
<tr><td colspan="4">
{form}
{tr}Search{/tr}:
<input type="text" style="width:auto" size="15" name="search" value="{$smarty.request.search}"/>
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
	<td><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$orderId}" class="contentlink">{$orderId} - {$gBitUser->getDisplayName(0,$order)}</a></td>
	<td>{$order.order_total|round:2}</td>
	<td align="right">{$order.purchase_time}</td>
	<td>{$order.orders_status_name}</td>
</tr>
{if $order.comments && $order.comments!='Credit Card processed'}
<tr>
	<td colspan="4">{$order.comments}</td>
</tr>
{/if}
{/foreach}

</table>


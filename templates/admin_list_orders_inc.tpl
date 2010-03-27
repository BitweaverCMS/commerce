<table class="data">
<tr><th colspan="4">{tr}Order List{/tr}</th></tr>
{if $searchScope}
<tr><td colspan="4">
{form}
{html_options name="search_scope" options=$searchScopes selected=$smarty.session.search_scope|default:'all'}
: <input type="text" style="width:auto" size="15" name="search" value="{$smarty.session.search|default:$smarty.request.search}"/>
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
{/if}
{foreach from=$listOrders key=orderId item=order}
<tr>
	<td style="width:10em;text-align:left">{$order.purchase_time}</td>
	<td><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$orderId}" class="contentlink">{$orderId} - {$gBitUser->getDisplayName(0,$order)}</a></td>
	<td style="text-align:right">{$order.orders_status_name}</td>
	<td style="text-align:right">{$order.order_total|round:2}</td>
</tr>
{if $order.comments && $order.comments!='Credit Card processed'}
<tr>
	<td colspan="4">{$order.comments}</td>
</tr>
{/if}
{if $order.products}
<tr>
	<td colspan="4">
		<ol style="padding:0 0 15px 15px">
		{foreach from=$order.products item=product key=ordersProductsId}
			<li style="clear:both"><img src="{$gBitProduct->getImageUrl($product.products_id)}" style="float:left;width:48px;"/><a href="{$gBitProduct->getDisplayUrl($product.products_id)}">{$product.products_name}</a></li>
		{/foreach}
		</ol>
	</td>
</tr>
{/if}
{/foreach}

</table>


<h2>{tr}Previous Orders{/tr}</h2>

<table class="table table-hover">
{foreach from=$ordersHistory item=ordersRow key=ordersId}
<tr>
	<td>
		<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}index.php?main_page=account_history_info&order_id={$ordersRow.orders_id}">{tr}#{/tr}{$ordersRow.orders_id}</a>
		<div class="small">{$ordersRow.date_purchased|zen_date_short}</div>
	</td>
	<td>
		{$ordersRow.billing_name}
		{if $ordersRow.delivery_name|trim && $ordersRow.billing_name != $ordersRow.delivery_name}
			<em>{tr}sent to{/tr} {$ordersRow.delivery_name}</em>
		{/if}
	<dt>
	<dd class="item">
		{if $ordersRow.delivery_country}
			{$ordersRow.delivery_street_address}, {$ordersRow.delivery_city}, {$ordersRow.delivery_state} {$ordersRow.delivery_country}
		{else}
			<em>{tr}Online Order{/tr}</em>
		{/if}
		</dd>
	</td>
	<td>
		<div>{$ordersRow.order_total}</div>
		<div class="small">{$ordersRow.orders_status_name}</div>
	</td>
</tr>
{foreachelse}
<tr>
	<td>
		<em>{tr}No Orders{/tr}</em>
	</td>
</tr>
{/foreach}
</table>

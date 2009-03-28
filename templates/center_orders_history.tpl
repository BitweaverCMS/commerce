<h2>{tr}Previous Orders{/tr}</h2>

<dl class="data">
{foreach from=$ordersHistory item=ordersRow key=ordersId}
	<dt class="item clear">
		<div class="floaticon">
			<div>{$ordersRow.orders_status_name}, {$ordersRow.date_purchased|zen_date_short}</div>
			<div>{$ordersRow.order_total}</div>
		</div>
		<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}index.php?main_page=account_history_info&order_id={$ordersRow.orders_id}">{tr}#{/tr}{$ordersRow.orders_id}</a>
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
{foreachelse}
	<dt><em>{tr}No Orders{/tr}</em></dt>
{/foreach}
</dl>

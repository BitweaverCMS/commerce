<div class="page-header">
	<div class="floaticon">
		<a href="{$smarty.server.REQUEST_URI}">{booticon iname='icon-refresh'}</a>
	</div>
	<h1>{tr}Sales and Income{/tr}</h1>
</div>

<div class="body">

	<table class="table data">
	<tr>
		<th colspan="2">{tr}Order{/tr}</th>
		<th class="alignright">{tr}Wholesale Income{/tr}</th>
		<th class="alignright">{tr}Coupon{/tr}</th>
		<th class="alignright">{tr}Gift Certificate{/tr}</th>
		<th class="alignright">{tr}Net Income{/tr}</th>
	</tr>
	{if $salesAndIncome.totals.sum}
	<tr>
		<th colspan="2" class="total">{tr}Total{/tr}</th>
		<th class="total alignright">{$currencies->format($salesAndIncome.totals.sum.wholesale_gross)}</th>
		<th class="total alignright">{$currencies->format($salesAndIncome.totals.sum.coupon_discount)}</th>
		<th class="total alignright">{$currencies->format($salesAndIncome.totals.sum.gift_certificate)}</th>
		<th class="total alignright">{$currencies->format($salesAndIncome.totals.sum.wholesale_net)}</th>
	</tr>
	{/if}
	{foreach from=$salesAndIncome.orders item=order key=ordersId}
		{assign var="period" value=$order.period}
		{if $lastPeriod != $period}
		<tr>
			<th colspan="2" class="subtotal">{$period}</th>
			<th class="subtotal alignright">{$currencies->format($salesAndIncome.totals.$period.wholesale_gross)}</th>
			<th class="subtotal alignright">{$currencies->format($salesAndIncome.totals.$period.coupon_discount)}</th>
			<th class="subtotal alignright">{$currencies->format($salesAndIncome.totals.$period.gift_certificate)}</th>
			<th class="subtotal alignright">{$currencies->format($salesAndIncome.totals.$period.wholesale_net)}</th>
		</tr>
		{/if}
		<tr>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$ordersId}">{$ordersId}</a></td>
			<td class="item">{$order.date_purchased|bit_short_datetime}</a></td>
			<td class="item alignright">{$currencies->format($order.wholesale_gross)}</td>
			<td class="item alignright">{$currencies->format($order.coupon_discount)}</td>
			<td class="item alignright">{$currencies->format($order.gift_certificate)}</td>
			<td class="item alignright">{$currencies->format($order.wholesale_net)}</td>
		</tr>
		{assign var="lastPeriod" value=$order.period}
	{foreachelse}
		<tr>
			<td class="item">{tr}No orders found.{/tr}</td>
		</tr>
	{/foreach}
	</table>
</div>

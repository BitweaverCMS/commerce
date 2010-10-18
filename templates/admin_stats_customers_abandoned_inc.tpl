<table class="data stats">
	<caption>{tr}{$title}{/tr}</caption>
	<thead>
	<tr>
		<th>{tr}Customer{/tr}</th>
		<th>{smartlink ititle="Revenue" isort="revenue" icontrol=$listInfo iorder="desc"}</th>
		<th>{smartlink ititle="#" isort="orders" icontrol=$listInfo iorder="desc"}</th>
		<th>{smartlink ititle="First" isort="first_order" icontrol=$listInfo iorder="desc"}</th>
		<th>{smartlink ititle="Last" isort="most_recent_order" icontrol=$listInfo iorder="desc"}</th>
	</tr>
	<tr>
		<td><em>{tr}Total Customers{/tr} {$custHash.totals.customers}</em></th>
		<td class="item currency">${$custHash.totals.revenue|round:2}</td>
		<td class="item">{$custHash.totals.orders}</td>
	</tr>
	</thead>
	<tbody>
	{foreach from=$custHash.customers item=cust key=custId}
	<tr>
		<td>{displayname hash=$cust}</td>
		<td class="item currency">${$cust.revenue|round:2}</td>
		<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?user_id={$custId}">{$cust.orders}</a></td>
		<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$cust.first_orders_id}">{$cust.first_purchase|zen_date_short}</a></td>
		<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$cust.last_orders_id}">{$cust.last_purchase|zen_date_short}</a></td>
	</tr>
	{/foreach}
	</tbody>
</table>

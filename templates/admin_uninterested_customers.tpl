<h1 class="header">
	{tr}Uninterested Customers{/tr}
</h1>

<div class="body">

	<p>{tr}The following customers have placed orders but have not indicated an interest.{/tr}</p>

	<table class="data">
	<tr>
		<th>{tr}Customer{/tr}</th>
		<th>{tr}# of Orders{/tr}</th>
		<th>{tr}Revenue{/tr}</th>
		<th colspan="2">{tr}Last Order{/tr}</th>
	</tr>
	{foreach from=$uninterestedCustomers item=customer key=userId}
		<tr>
			<td class="item">{displayname user_id=$userId nolink=1}</td>
			<td class="item">{$customer.num_orders}</td>
			<td class="item">{$customer.total_revenue}</td>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$customer.most_recent_order}">{$customer.most_recent_order}</a></td>
			<td class="item">{$customer.most_recent_date}</td>
		</tr>
	{/foreach}
	</table>

</div>

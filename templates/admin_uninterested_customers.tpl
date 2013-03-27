<div class="page-header">
	<h1>
		{tr}Uninterested Customers{/tr}
	</h1>
</div>

<div class="body">

	<p>{tr}The following customers have placed orders but have not indicated an interest.{/tr}</p>

	<table class="table data">
	<tr>
		<th></th>
		<th>{tr}Customer{/tr}</th>
		<th>{tr}# of Orders{/tr}</th>
		<th>{tr}Revenue{/tr}</th>
		<th colspan="2">{tr}Last Order{/tr}</th>
	</tr>
	{foreach from=$uninterestedCustomers name="uninterested" item=customer key=userId}
		<tr>
			<td class="item numeric">{$smarty.foreach.uninterested.iteration}</td>
			<td class="item">{displayname user_id=$userId nolink=1}</td>
			<td class="item numeric">{$customer.num_orders}</td>
			<td class="item nuremic">{$customer.total_revenue}</td>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$customer.most_recent_order}">{$customer.most_recent_order}</a></td>
			<td class="item">{$customer.most_recent_date}</td>
		</tr>
	{/foreach}
	</table>

</div>

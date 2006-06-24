<div class="listing bitcommerce">
	<div class="header">
		<h1>{tr}Sales and Commissions{/tr}</h1>
	</div>

{if $commissionList}
	<table class="data">
	<tr>
		<th>{tr}Date Purchased{/tr}</th>
		<th>{tr}Product Sold{/tr}</th>
		<th>{tr}Commission{/tr}</th>
	</tr>
	{foreach from=$commissionList key=orderId item=orderProduct}
	<tr>
		<td class="item">{$orderProduct.date_purchased}</td>
		<td class="item">{$orderProduct.products_name}</td>
		<td class="item">${$orderProduct.products_commission}</td>
	</tr>
	{/foreach}
	</table>
{else}
	<div>
No sales or commissions.
	</div>
{/if}

</div>

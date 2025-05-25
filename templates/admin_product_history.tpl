{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Product History{/tr}</h1>
	</div>
	<div class="body">

<table class="table data">
	{foreach from=$productHistory item=hist}	
	<tr valign="top">
		<td class="item" style="text-align:right">
			<a href="orders.php?oID={$hist.orders_id}&amp;action=edit">{$hist.orders_id}</a><br/>
			{$hist.purchase_time}
		</td>
		<td class="item" style="text-align:right">{$hist.products_quantity} x</td>
		<td class="item">
			<span class="pull-right">(<a href="{$gBitProduct->getDisplayUrlFromHash($hist)}">{$hist.products_id}</a>)</span> {$hist.products_name} <br/>
			v{$hist.products_version}
		</td>
		<td>
			${$hist.order_total|round:2}
		</td>
		<td>
			{$hist.delivery_name}<br/>
			{$hist.delivery_city}, {$hist.delivery_state}<br/>
			{$hist.delivery_country}
		</td>
	</tr>
	{/foreach}
</table>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

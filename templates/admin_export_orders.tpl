<div class="page-header">
	<h1>
		{tr}Export Orders{/tr}
	</h1>
</div>

<div class="body customerexport">
{literal}
<style type="text/css">
.row .formlabel {text-align:right; width:14em;float:left; }
.row .forminput {margin-left: 15em;}
</style>
{/literal}

{form}
<table class="table">
	<thead>
	<tr>
		<th><label class="checkbox"> <input type="checkbox" id="select-all" checked onclick="$('.order-checkbox').each(function () { this.checked = $('#select-all').is(':checked'); });"> {tr}Select All{/tr}</label></th>
		<th>
			{html_options class="form-control" name="orders_status_id" options=$commerceStatuses selected=$smarty.request.orders_status_id|default:'all'}
		</th>
		<th></th>
	{foreach from=$headerHash key=columnName item=orderKey}{if $orderKey}{else}<th class="text-center">{tr}{$columnName}{/tr}</th>{/if}
	{/foreach}
	</tr>
	</thead>
	{foreach from=$orders item=orderHash key=ordersId}
	<tr>
		<td><label class="checkbox"> <input type="checkbox" class="order-checkbox" name="export[]" value="{$ordersId}" checked> <a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$ordersId}">{$ordersId}</a></label></td>
		<td>
			{$orderHash.delivery_name}<br>
			{if $orderHash.delivery_company}{$orderHash.delivery_company}<br>{/if}
			{$orderHash.delivery_street_address}<br>
			{if $orderHash.delivery_suburb}{$orderHash.delivery_suburb}<br>{/if}
			{$orderHash.delivery_city}, {$orderHash.delivery_state} {$orderHash.delivery_postcode}
		</td>
		<td>{$orderHash.delivery_country}</td>
		{foreach from=$headerHash key=columnName item=orderKey}{if $orderKey}{else}<td><input type="text" class="form-control" name="order[{$ordersId}][{$columnName}]"></td>{/if}
		{/foreach}
	</tr>
	{/foreach}
	<tr>
		<td></td>
		<td colspan="5"><input type="submit" name="action" value="{tr}Export{/tr}" class="btn btn-default"></td>
	</tr>
</table>
{/form}

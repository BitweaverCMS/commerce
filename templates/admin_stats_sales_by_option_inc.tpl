
<table class="data stats">
	<caption>{tr}Product Options Sales{/tr}</caption>
	<thead>
	<tr>
		<th class="item">{tr}Product Option{/tr}</th>
		<th class="item" style="text-align:right">{tr}Total Units{/tr}</th>
	</tr>
	</thead>
	<tbody>
{foreach from=$statsByOption item=s key=typeId}
	<tr>
		<td class="item">{$s.products_options}: {$s.products_options_values_name}</td>
		<td class="item" style="text-align:right"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?products_options_values_id={$s.products_options_values_id}{if $smarty.request.timeframe}&timeframe={$smarty.request.timeframe}&period={$smarty.request.period}{/if}">{$s.total_units}</td>
	</tr>
{/foreach}
	</tbody>
</table>

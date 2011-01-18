
<table class="data stats">
	<caption>{tr}Product Options Sales{/tr}</caption>
	<thead>
	<tr>
		<th class="item">{tr}Product Option{/tr}</th>
		<th class="item" colspan="2">{tr}Total Units{/tr}</th>
	</tr>
	</thead>
	<tbody>
{foreach from=$statsByOption item=s key=typeId}
	{assign var=productsOptionsId value=$s.products_options_id}
	<tr>
		<td class="item">{$s.products_options}: {$s.products_options_values_name}</td>
		<td class="item" style="text-align:right"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?products_options_values_id={$s.products_options_values_id}{if $smarty.request.timeframe}&timeframe={$smarty.request.timeframe}&period={$smarty.request.period}{/if}">{$s.total_units}</a>
		<td class="item">({math equation="round(units/total,2) * 100" total=$statsByOptionTotalUnits.$productsOptionsId units=$s.total_units}%)</td>
	</tr>
{/foreach}
	</tbody>
</table>

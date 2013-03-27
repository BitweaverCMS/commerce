
<table class="table data stats">
	<caption>{tr}Product Sales By Type{/tr}</caption>
	<thead>
	<tr>
		<th class="item">{tr}Product Type{/tr}</th>
		<th class="item" style="text-align:right">{tr}Total Units{/tr}</th>
		<th class="item" style="text-align:right">{tr}Total Sales{/tr}</th>
		<th class="item" style="text-align:right">{tr}Avg Size{/tr}</th>
	</tr>
	</thead>
	<tbody>
{foreach from=$statsByType item=s key=typeId}
	<tr>
		<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?products_type={$typeId}&amp;period={$smarty.request.period}&amp;timeframe={$smarty.request.timeframe}">{$s.type_name} {if $s.type_class}({$s.type_class}){/if}</a></td>
		<td class="item" style="text-align:right">{$s.total_units}</td>
		<td class="item" style="text-align:right">{$s.total_revenue|round:2}</td>
		<td class="item" style="text-align:right">{math equation="round( (r/u), 2 )" r=$s.total_revenue u=$s.total_units}</td>
	</tr>
{/foreach}
	</tbody>
</table>

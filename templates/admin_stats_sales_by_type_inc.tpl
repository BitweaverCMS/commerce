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
		<th class="item">{tr}{$s.totals.type_name}{/tr} {if $s.totals.type_class}({$s.totals.type_class}){/if}</th>
		<th class="item" style="text-align:right">{$s.totals.total_units}</th>
		<th class="item" style="text-align:right">{$s.totals.total_revenue|round:2}</th>
		<th class="item" style="text-align:right">{math equation="round( (r/u), 2 )" r=$s.totals.total_revenue u=$s.totals.total_units}</th>
	</tr>
	{foreach from=$s.models item=sub key=modelName}
		<tr>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?products_type={$typeId}&amp;products_model={$modelName|urlencode}&amp;period={$smarty.request.period}&amp;timeframe={$smarty.request.timeframe}">{$modelName|escape}</a></td>
			<td class="item" style="text-align:right">{$sub.total_units}</td>
			<td class="item" style="text-align:right">{$sub.total_revenue|round:2}</td>
			<td class="item" style="text-align:right">{math equation="round( (r/u), 2 )" r=$sub.total_revenue u=$sub.total_units}</td>
		</tr>
	{/foreach}
{/foreach}
	</tbody>
</table>

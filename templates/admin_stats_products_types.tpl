<h1 class="header">
	<div class="floaticon">
		<a href="{$smarty.server.REQUEST_URI}">{biticon iname='view-refresh'}</a>
	</div>
	{tr}Product Sales Summary{/tr}
</h1>

<table class="data stats span-12">
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
{foreach from=$typesStats item=s key=typeId}
	<tr>
		<td class="item">{$s.type_name} {if $s.type_class}({$s.type_class}){/if}</td>
		<td class="item" style="text-align:right">{$s.total_units}</td>
		<td class="item" style="text-align:right">{$s.total_revenue|round:2}</td>
		<td class="item" style="text-align:right">{math equation="round( (r/u), 2 )" r=$s.total_revenue u=$s.total_units}</td>
	</tr>
{/foreach}
	</tbody>
</table>

<table class="data stats">
	<caption>{tr}Product Options Sales{/tr}</caption>
	<thead>
	<tr>
		<th class="item">{tr}Product Option{/tr}</th>
		<th class="item" style="text-align:right">{tr}Total Units{/tr}</th>
	</tr>
	</thead>
	<tbody>
{foreach from=$optionsStats item=s key=typeId}
	<tr>
		<td class="item">{$s.products_options}: {$s.products_options_values_name}</td>
		<td class="item" style="text-align:right">{$s.total_units}</td>
	</tr>
{/foreach}
	</tbody>
</table>

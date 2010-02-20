
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
		<td class="item" style="text-align:right">{$s.total_units}</td>
	</tr>
{/foreach}
	</tbody>
</table>

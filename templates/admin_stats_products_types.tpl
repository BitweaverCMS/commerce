<div class="header">
	<div class="floaticon">
		<a href="{$smarty.server.REQUEST_URI}">{biticon iname='view-refresh'}</a>
	</div>
	<h1>{tr}Product Sales Summary{/tr}</h1>
<div style="display:inline-block;padding:6px;">
<input id="calendarfrom" name="calendarfrom" value="{$smarty.request.calendarfrom|default:$smarty.session.date_from}" onchange="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)" style="width:7em;" /> {biticon ipackage="icons" iname="office-calendar" iexplain="Choose From Data" id="calendarfrompic"}
    &nbsp; {tr}to{/tr} &nbsp;
<input id="calendarto" name="calendarto" value="{$smarty.request.calendarto|default:$smarty.session.date_to}" onchange="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)" style="width:7em;" /> {biticon ipackage="icons" iname="office-calendar" iexplain="Choose From Data" id="calendartopic"}
<input type="submit" name="change_dates" value="Go" onclick="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)"/>
<div id="datehelp"></div>
	</div>
</div>

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

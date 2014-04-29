<div class="page-header">
	<div class="floaticon">
		<a href="{$smarty.server.REQUEST_URI}">{booticon iname='icon-refresh'}</a>
	</div>
	<h1>{tr}Product Sales Summary{/tr}</h1>
</div>
<div>
	<div style="display:inline-block;padding:6px;">
		<input id="calendarfrom" name="calendarfrom" value="{$smarty.request.calendarfrom|default:$smarty.session.date_from}" onchange="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)" style="width:7em;" /> {booticon iname="icon-calendar" iexplain="Choose From Data" id="calendarfrompic"}
			&nbsp; {tr}to{/tr} &nbsp;
		<input id="calendarto" name="calendarto" value="{$smarty.request.calendarto|default:$smarty.session.date_to}" onchange="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)" style="width:7em;" /> {booticon ipackage="icons" iname="icon-calendar" iexplain="Choose From Data" id="calendartopic"}
		<input class="btn" type="submit" name="change_dates" value="Go" onclick="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)"/>
		<div id="datehelp"></div>
	</div>
</div>

{include file="bitpackage:bitcommerce/admin_stats_sales_by_type_inc.tpl"}

{include file="bitpackage:bitcommerce/admin_stats_sales_by_option_inc.tpl"}


<div class="page-header">
	<div class="pull-right">
		{form method="get" class="form-inline"}
			<input size="12" type="text" name="date_from" value="{$smarty.request.date_from|default:$smarty.session.date_from}" class="form_datetime input-sm form-control" data-date-format="yyyy-mm-dd">
				&nbsp; {tr}to{/tr} &nbsp;
			<input size="12" type="text" name="date_to" value="{$smarty.request.date_to|default:$smarty.session.date_to}" class="form_datetime input-sm form-control" data-date-format="yyyy-mm-dd">
			<input class="btn btn-default btn-sm" type="submit" name="change_dates" value="Go" onclick="changeDates(document.getElementById('calendarfrom').value,document.getElementById('calendarto').value)"/>
			<div id="datehelp"></div>
		{/form}
	</div>
	<h1>{tr}Product Sales Summary{/tr}</h1>
</div>

<script type="text/javascript">{literal}
    $(".form_datetime").datetimepicker({format: 'yyyy-mm-dd', todayBtn: true, minView:2, autoclose: true,});
{/literal}</script>

{include file="bitpackage:bitcommerce/admin_stats_sales_by_type_inc.tpl"}

{include file="bitpackage:bitcommerce/admin_stats_sales_by_option_inc.tpl"}


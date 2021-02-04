{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_ADMIN_PATH`includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Customer Statistics{/tr}</h1>

		<table class="width20p">
		<tr>
			<td>{tr}Total Customers{/tr}</td>
			<td>{$retainedCustomers.totals.customers+$abandonedCustomers.totals.customers}</td>
		</tr>
		<tr>
			<td>{tr}Avg Rentention{/tr}</td>
			<td>{$averageRetention|round:2}%</td>
		</tr>
		</table>
		<em> Eventually build table for <a href="http://hbsp.harvard.edu/multimedia/flashtools/cltv/index.html">Liftetime Customer Value</a></em>
	</div>
	<div class="body">

	<div class="width45p floatleft">
	{include file="bitpackage:bitcommerce/admin_stats_customers_abandoned_inc.tpl" title="Retained Customers" custHash=$retainedCustomers}
	</div>
	<div class="width2p floatleft">&nbsp;
	</div>
	<div class="width45p floatleft">
	{include file="bitpackage:bitcommerce/admin_stats_customers_abandoned_inc.tpl" title="Abandonded Customers" custHash=$abandonedCustomers}
	</div>

	</div>
</div>

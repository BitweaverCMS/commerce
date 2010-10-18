{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Customer Statistics{/tr}</h1>
	</div>
	<div class="body">

	<div class="width20p floatleft">
	<table>
	<tr>
		<td>{tr}Avg Rentention{/tr}</td>
		<td>{$averageRetention|round:2}%</td>
	</tr>
	</table>
	</div>

	<div class="width40p floatleft">
	{include file="bitpackage:bitcommerce/admin_stats_customers_abandoned_inc.tpl" title="Retained Customers" custHash=$retainedCustomers}
	</div>
	<div class="width40p floatleft">
	{include file="bitpackage:bitcommerce/admin_stats_customers_abandoned_inc.tpl" title="Abandonded Customers" custHash=$abandonedCustomers}
	</div>

	</div>
</div>

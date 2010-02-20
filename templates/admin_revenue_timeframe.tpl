{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Revenue Breakdown{/tr} {$smarty.request.timeframe}</h1>
	</div>
	<div class="body">


<div class="span-12">
{include file="bitpackage:bitcommerce/admin_stats_sales_by_type_inc.tpl"}
{include file="bitpackage:bitcommerce/admin_stats_sales_by_option_inc.tpl"}
</div>
<div class="span-12 last">
<table class="data stats">
	<caption>{tr}Customer Stats{/tr}</caption>
	<thead>
	</thead>
	<tbody>
	<tr>
		<td>New Registrations</td><td class="item">{$statsCustomers.new_registrations}</td>
	</tr>
	<tr>
		<td class="item">New Customers That Created Products</td><td class="item">{$statsCustomers.new_customers_that_created_products}</td>
	</tr>
	<tr>
		<td class="item">New Customers That Purchased New Products</td><td class="item">{$statsCustomers.new_customers_that_purchased_new_products}</td>
	</tr>
	<tr>
		<td class="item">New Products Created By New Customers</td><td class="item">{$statsCustomers.new_products_created_by_new_customers}</td>
	</tr>
	<tr>
		<td class="item">New Products Purchased By New Customers</td><td class="item">{$statsCustomers.new_products_purchased_by_new_customers}</td>
	</tr>
	<tr>
		<td class="item">Total Unique Products Purchased</td><td class="item">{$statsCustomers.unique_products_ordered}</td>
	</tr>
	</tbody>
</table>

</div>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

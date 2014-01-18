{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Revenue Breakdown{/tr} {$smarty.request.timeframe}</h1>
	</div>
	<div class="body">


<div class="row">
	<div class="span6">
	{include file="bitpackage:bitcommerce/admin_stats_sales_by_type_inc.tpl"}
	{include file="bitpackage:bitcommerce/admin_stats_sales_by_option_inc.tpl"}
	</div>
	<div class="span6">
		<table class="table data stats">
			<caption>{tr}Customer Created Products Stats{/tr}</caption>
			<thead>
			<tr>
				<th>{tr}New Customers{/tr}</th>
				<th>Conversion Rate</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>New Registrations</td><td class="">{$statsCustomers.new_registrations}</td>
			</tr>
			<tr>
				<td class="">{tr}New Customers That Created Products{/tr}</td>
				<td class="">{$statsCustomers.new_customers_that_created_products} / {math equation="round(x/y*100)" y=$statsCustomers.new_registrations x=$statsCustomers.new_customers_that_created_products}%</td>
			</tr>
			<tr>
				<td class="">{tr}New Customers That Purchased New Products{/tr}</td>
				<td class="">{$statsCustomers.new_customers_that_purchased_new_products} / {math equation="round(x/y*100)" x=$statsCustomers.new_customers_that_purchased_new_products y=$statsCustomers.new_customers_that_created_products}%</td>
			</tr>
			<tr>
				<td class="">{tr}New Products Created By New Customers{/tr}</td>
				<td class="">{$statsCustomers.new_products_created_by_new_customers}</td>
			</tr>
			<tr>
				<td class="">{tr}New Products Purchased By New Customers{/tr}</td>
				<td class="">{$statsCustomers.new_products_purchased_by_new_customers} / {math equation="round(x/y*100)" x=$statsCustomers.new_products_purchased_by_new_customers y=$statsCustomers.new_products_created_by_new_customers}%</td>
			</tr>
			<tr><th colspan="3">{tr}Existing Customers{/tr}</th></tr>
			<tr>
				<td class="">{tr}Existing Customers That Created Products{/tr}</td>
				<td class="">{$statsCustomers.all_customers_that_created_products-$statsCustomers.new_customers_that_created_products}</td>
			</tr>
			<tr>
				<td class="">{tr}Existing Customers That Purchased New Products{/tr}</td>
				<td class="">{$statsCustomers.all_customers_that_purchased_new_products-$statsCustomers.new_customers_that_purchased_new_products} / {math equation="round((w-x)/(y-z)*100)" w=$statsCustomers.all_customers_that_purchased_new_products x=$statsCustomers.new_customers_that_purchased_new_products y=$statsCustomers.all_customers_that_created_products z=$statsCustomers.new_customers_that_created_products}%</td>
			</tr>
			<tr>
				<td class="">{tr}New Products Created By Exsting Customers{/tr}</td>
				<td class="">{$statsCustomers.new_products_created_by_all_customers-$statsCustomers.new_products_created_by_new_customers}</td>
			</tr>
			<tr>
				<td class="">{tr}New Products Purchased By Exsting Customers{/tr}</td>
				<td class="">{$statsCustomers.new_products_purchased_by_all_customers-$statsCustomers.new_products_purchased_by_new_customers} / {math equation="round((w-x)/(y-z)*100)" w=$statsCustomers.new_products_purchased_by_all_customers x=$statsCustomers.new_products_purchased_by_new_customers y=$statsCustomers.new_products_created_by_all_customers z=$statsCustomers.new_products_created_by_new_customers}%</td>
			</tr>
			<tr><th colspan="2">{tr}Totals{/tr}</th></tr>
			<tr>
				<td class="">Total Unique Products Purchased</td>
				<td class="">{$statsCustomers.unique_products_ordered}</td>
			</tr>
			<tr>
				<td class="">Total Orders</td><td class="">{$statsCustomers.total_orders}</td>
			</tr>
			<tr>
				<td class="" colspan="2"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/revenue.php?period={$smarty.request.period}&">{tr}All Time Periods{/tr}</a></td>
			</tr>
			</tbody>
		</table>
	</div>

<div class="span6 last">
	<table class="table data stats">
		<caption>{tr}Revenue By Interest{/tr}</caption>
		<thead>
		<tr>
			<th>{tr}Interest{/tr}</th>
			<th>{tr}Orders{/tr}</th>
			<th>{tr}Amount{/tr}</th>
			<th>{tr}Avg. Size{/tr}</th>
		</tr>
		</thead>
		<tbody>
		{foreach from=$valuableInterests item=interest key=interestsId}
		<tr>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/revenue.php?period={$smarty.request.period}&timeframe={$smarty.request.timeframe}&interests_id={$interest.interests_id}">{$interest.interests_name}</a></td>
			<td class="item">{$interest.total_orders}</td>
			<td class="item currency">${$interest.total_revenue|round:2}</td>
			<td class="item currency">${math equation="round(x/y,2)" x=$interest.total_revenue y=$interest.total_orders}</td>
		</tr>
		{/foreach}
		</tbody>
	</table>
</div>


<div class="span6 last">
<table class="table data stats">
	<caption>{tr}Most Valuable Customers{/tr}</caption>
	<thead>
	<tr>
		<th>{tr}Customer{/tr} [<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/revenue.php?period={$smarty.request.period}&timeframe={$smarty.request.timeframe}&referer=all">Referers</a>]</th>
		<th>{tr}Orders{/tr}</th>
		<th>{tr}Amount{/tr}</th>
		<th>{tr}Avg. Size{/tr}</th>
	</tr>
	</thead>
	<tbody>
	{foreach from=$valuableCustomers item=cust key=custId}
	<tr>
		<td class="item">{displayname user_id=$custId} <span class="floatright small">&rsaquo; {$custId|list_customers_interests}</span>
		</td>
		<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?user_id={$custId}">{$cust.total_orders}</a></td>
		<td class="item currency">${$cust.total_revenue|round:2}</td>
		<td class="item currency">${math equation="round(x/y,2)" x=$cust.total_revenue y=$cust.total_orders}</td>
	</tr>
	<tr>
		<td colspan="4">
			<span class="date">Reg. {$cust.registration_date|bit_short_date}</span> 
			{if $cust.referer_url}<a href="{$cust.referer_url}" target="_new" title="{$cust.referer_url|escape}">{$cust.referer_url|stats_referer_display_short|escape}</a>{/if}

		</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="4"></td>
	</tr>
	</tbody>
</table>
</div>

</div>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

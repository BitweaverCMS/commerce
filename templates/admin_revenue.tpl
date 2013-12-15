{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<header class="page-header">
		<h1>{tr}Revenue{/tr}</h1>
	</header>
	<div class="body">

	<table class="table data span-24 last">
	<tr>
		<th colspan="5" style="border:none"></th>
		<th colspan="2" style="border:none">New Customer Products</th>
		<th colspan="3" style="border:none">Existing Customer Products</th>
	</tr>
	<tr>
		<th class="item" style="width:6em">{tr}Period{/tr}</th>
		<th class="item" style="width:20%;text-align:left">{tr}Revenue{/tr}</th>
		<th class="item" style="width:10%;text-align:left">{tr}Orders{/tr}</th>
		<th class="item" style="text-align:right">{tr}Avg. Size{/tr}</th>
		<th class="item">{tr}Registrations{/tr}</th>
		<th class="item">{tr}Created{/tr}</th>
		<th class="item">{tr}Purchased{/tr} (%)</th>
		<th class="item">{tr}Created{/tr}</th>
		<th class="item">{tr}Purchased{/tr} (%)</th>
		<th {if !empty($smarty.request.tacked)}style="border:2px solid black" class="highlight"{/if}><a href="{$smarty.server.REQUEST_URI}&amp;tacked={if empty($smarty.request.tacked)}1{else}0{/if}">{booticon iname="icon-paper-clip" iexplain="Show Only Highlight Rows"}</a></th>
	</tr>
	{foreach from=$stats key=statKey item=statHash}
		{if $statKey != 'stats'}
			{cycle assign="oddeven" values="odd,even"}
			{if empty($smarty.request.tacked) || isset($smarty.request.compare.$statKey)}
	<tr {if isset($smarty.request.compare.$statKey)}class="highlight"{/if}>
		<td class="item {$oddeven}" style="white-space:nowrap;"><a href="{$smarty.server.php_self}?period={$smarty.request.period}&timeframe={$statKey|escape:url}">{$statKey}</td>
		<td class="item {$oddeven}"><span style="background-color:#bfb;display:inline-block;width:{math equation="round(100*(gross/max))" gross=$statHash.gross_revenue max=$stats.stats.gross_revenue_max}%">${$statHash.gross_revenue}</span></td>
		<td class="item {$oddeven}"><span style="background:#def;display:inline-block;width:{math equation="round(100*(count/max))" count=$statHash.order_count max=$stats.stats.order_count_max}%">{$statHash.order_count}</span></td>
		<td class="item {$oddeven}" style="text-align:right">${$statHash.avg_order_size}</td>

		<td class="item {$oddeven}" style="">{if $statsCustomers.$statKey.new_registrations}<span style="background:#fed;display:inline-block;width:{math equation="round(100*(count/max))" count=$statsCustomers.$statKey.new_registrations max=$statsCustomers.max_stats.new_registrations}%">{$statsCustomers.$statKey.new_registrations}</span>{/if}</td>

		<td class="item {$oddeven}" style="">{if $statsCustomers.$statKey.new_customers_that_created_products}<span style="background:#fed;display:inline-block;width:{math equation="round(100*(count/max))" count=$statsCustomers.$statKey.new_customers_that_created_products max=$statsCustomers.max_stats.new_customers_that_created_products}%">{$statsCustomers.$statKey.new_customers_that_created_products}</span>{/if}</td>
		<td class="item {$oddeven}" style="">{if $statsCustomers.$statKey.new_customers_that_purchased_new_products}<span style="background:#fed;display:inline-block;width:{math equation="round(100*(count/max))" count=$statsCustomers.$statKey.new_customers_that_purchased_new_products max=$statsCustomers.max_stats.new_customers_that_purchased_new_products}%">{$statsCustomers.$statKey.new_customers_that_purchased_new_products}</span><span class="floatright">({math equation="round(100*(pur/cre))" pur=$statsCustomers.$statKey.new_customers_that_purchased_new_products|default:0 cre=$statsCustomers.$statKey.new_customers_that_created_products}%){/if}</span></td>

		<td class="item {$oddeven}" style=""><span style="background:#fde;display:inline-block;width:{math equation="round(100*(count/max))" count=$statsCustomers.$statKey.all_customers_that_created_products-$statsCustomers.$statKey.new_customers_that_created_products max=$statsCustomers.max_stats.all_customers_that_purchased_new_products}%">{$statsCustomers.$statKey.all_customers_that_created_products-$statsCustomers.$statKey.new_customers_that_created_products}</span></td>
		<td class="item {$oddeven}" style="">{if $statsCustomers.$statKey.all_customers_that_created_products-$statsCustomers.$statKey.new_customers_that_created_products}<span style="background:#fde;display:inline-block;width:{math equation="round(100*(count/max))" count=$statsCustomers.$statKey.all_customers_that_purchased_new_products-$statsCustomers.$statKey.new_customers_that_purchased_new_products max=$statsCustomers.max_stats.all_customers_that_purchased_new_products}%">{$statsCustomers.$statKey.all_customers_that_purchased_new_products-$statsCustomers.$statKey.new_customers_that_purchased_new_products}</span><span class="floatright">({math equation="round(100*(pur/cre))" pur=$statsCustomers.$statKey.all_customers_that_purchased_new_products-$statsCustomers.$statKey.new_customers_that_purchased_new_products cre=$statsCustomers.$statKey.all_customers_that_created_products-$statsCustomers.$statKey.new_customers_that_created_products}%)</span>{/if}</td>
		<td class="item {$oddeven}" style="">{if !isset($smarty.request.compare.$statKey)}<a href="{$smarty.server.REQUEST_URI}&amp;compare[]={$statKey|urlencode}">{booticon iname="icon-paper-clip" iexplain="Highlight"}</a>{/if}</td>
	</tr>
				{assign var=break value=true}
			{else}
				{if $break}
	<tr>
		<td colspan="10">&nbsp;</td>
	</tr>
					{assign var=break value=false}
				{/if}
			{/if}
		{/if}
	{foreachelse}
	<tr>
		<td class="item">{tr}No Commissions.{/tr}</td>
	</tr>
	{/foreach}
	</table>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

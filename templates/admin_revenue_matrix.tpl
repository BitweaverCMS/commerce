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

	<table class="table table-hover">
	<tr>
		<th></th>
	{foreach from=$matrixHeaders item=header}
		<th class="text-center">{$header}</th>
	{/foreach}
	</tr>
	{for $year=$beginYear to $endYear step -1}
	<tr>
		<th>{$year}</th>
	{foreach from=$matrixHeaders item=header}
		{assign var=statKey value="$year-$header"}
		<td>
			{if $stats.$statKey}
			{assign var=statHash value=$stats.$statKey}
			<div class="strong"><span style="background-color:#bfb;display:inline-block;width:{math equation="round(100*(gross/max))" gross=$statHash.gross_revenue max=$stats.stats.gross_revenue_max}%">${$statHash.gross_revenue}</span></div>
			<div class="date"><span style="background:#def;display:inline-block;width:{math equation="round(100*(count/max))" count=$statHash.order_count max=$stats.stats.order_count_max}%">&sum;{$statHash.order_count}</span></div>
			<div class="date">x&#772; {$gCommerceCurrencies->format($statHash.avg_order_size|round)}</div>
			{/if}
		</td>
	{foreachelse}
		<td class="item">{tr}No Data.{/tr}</td>
	{/foreach}
	</tr>
	{/for}
	</table>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

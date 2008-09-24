{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Revenue{/tr}</h1>
	</div>
	<div class="body">

	<table class="data">
	<tr>
		<th class="item">{tr}Period{/tr}</th>
		<th class="item" style="width:50%;text-align:left">{tr}Revenue{/tr}</th>
		<th class="item" style="width:30%;text-align:left">{tr}Orders{/tr}</th>
		<th class="item" style="text-align:right">{tr}Avg. Size{/tr}</th>
	</tr>
	{foreach from=$stats key=statKey item=statHash}
		{if $statKey != 'stats'}
	{cycle assign="oddeven" values="odd,even"}
	<tr>
		<td class="item {$oddeven}">{$statKey}</td>
		<td class="item {$oddeven}"><span style="background-color:#def;display:inline-block;width:{math equation="round(100*(gross/max))" gross=$statHash.gross_revenue max=$stats.stats.gross_revenue_max}%">${$statHash.gross_revenue}</span></td>
		<td class="item {$oddeven}"><span style="background:#def;display:inline-block;width:{math equation="round(100*(count/max))" count=$statHash.order_count max=$stats.stats.order_count_max}%">{$statHash.order_count}</span></td>
		<td class="item {$oddeven}" style="text-align:right">${$statHash.avg_order_size}</td>
	</tr>
	<tr style="display:none" id="enterpayment{$commission.user_id}">
		<td colspan="5" class="item {$oddeven}" >
			{include file="bitpackage:bitcommerce/admin_commission_payment_inc.tpl" commission=$commission}
		</td>
	</tr>
		{/if}
	{foreachelse}
	<tr>
		<td class="item">{tr}No Commissions.{/tr}</td>
	</tr>
	{/foreach}
	</table>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

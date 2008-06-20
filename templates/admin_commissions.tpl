{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Commissions{/tr}</h1>
	</div>
	<div class="body">

	<table class="data">
	<tr>
		<th class="item" colspan="2">{tr}Payee{/tr}</th>
		<th class="item">{tr}Commission Due{/tr}</th>
		<th class="item" colspan="2">{tr}Payment Method{/tr}</th>
	</tr>
	{foreach from=$commissionsDue key=userId item=commission}
	{cycle assign="oddeven" values="odd,even"}
	<tr>
		<td class="item {$oddeven}">{displayname hash=$commission}</td>
		<td class="item {$oddeven}">{$commission.email}</td>
		<td class="item {$oddeven}" style="text-align:right">{$commission.commission_sum|string_format:"$%.2f"}</td>
		<td class="item {$oddeven}"><a href="#" onclick="toggle('enterpayment{$userId}');return false;">Enter Payment</a></td>
		<td class="item">{$commission.payment_method}</td>
	</tr>
	<tr style="display:none" id="enterpayment{$commission.user_id}">
		<td colspan="5" class="item {$oddeven}" >
			{include file="bitpackage:bitcommerce/admin_commission_payment_inc.tpl" commission=$commission}
		</td>
	</tr>

	{foreachelse}
	<tr>
		<td class="item">{tr}No Commissions.{/tr}</td>
	</tr>
	{/foreach}
	</table>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

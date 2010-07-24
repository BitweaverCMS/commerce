{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

{formfeedback hash=$feedback}

<div class="admin bitcommerce coupons">
	<div class="header">
		<h1>{tr}Discount Coupons{/tr}: {tr}Report for {/tr} {$gCoupon->getField('coupon_name')} ({$gCoupon->getField('coupon_code')})</h1>
	</div>
		{$gCoupon->getField('coupon_description')}
	<div class="body">

		<table class="data">
		<tr >
			<th>&nbsp;</th>
			<th>{smartlink ititle="Order" isort="order_id" icontrol=$listInfo }</th>
			<th>{smartlink ititle="Customer Name" isort="real_name" icontrol=$listInfo}</th>
			<th>{tr}IP Address{/tr}</th>
			<th>{smartlink ititle="Redemption" isort="orders_value" icontrol=$listInfo}</th>
			<th>{smartlink ititle="Date Redeemed" isort="redeem_date" icontrol=$listInfo}</th>
		</tr>
		{foreach from=$redeemList item=redeem name=redeemList}
		<tr>
			<td class="item">{$smarty.foreach.redeemList.iteration+$listInfo.offset}</td>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$redeem.order_id}">{$redeem.order_id}</a></td>
			<td class="item">{displayname hash=$redeem}</td>
			<td class="item">{$redeem.redeem_ip}</td>
			<td class="item currency">{$gCommerceCurrencies->format($redeem.orders_value)}</td>
			<td class="item">{$redeem.redeem_date|strtotime|bit_short_datetime}</td>
		</tr>
		{/foreach}
		</table>

		{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}
	</div>
</div>

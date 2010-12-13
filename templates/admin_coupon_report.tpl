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
			<th>{smartlink ititle="Customer Name" isort="real_name" icontrol=$listInfo} / {smartlink ititle="Referrer" isort="referer_url" icontrol=$listInfo}</th>
			<th>{smartlink ititle="IP" isort="redeem_ip" icontrol=$listInfo}</th>
			<th>{smartlink ititle="Order History" isort="previous_orders" icontrol=$listInfo}</th>
			<th>{smartlink ititle="Redeemed" isort="orders_value" icontrol=$listInfo}</th>
			<th>{smartlink ititle="Gross" isort="order_total" icontrol=$listInfo}</th>
			<th>{smartlink ititle="Date Redeemed" isort="redeem_date" icontrol=$listInfo}</th>
		</tr>
		{foreach from=$redeemList item=redeem name=redeemList}
		<tr>
			<td class="item">{$smarty.foreach.redeemList.iteration+$listInfo.offset}</td>
			<td class="item"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$redeem.order_id}">{$redeem.order_id}</a></td>
			<td class="item"><strong>{displayname hash=$redeem}</strong>{if $redeem.referer_url}<br/><a href="{$redeem.referer_url}">{$redeem.referer_url|stats_referer_display_short|escape}</a>{/if}</td>
			<td class="item">{$redeem.redeem_ip}</td>
			<td class="item"><a href="list_orders.php?user_id={$redeem.user_id}&amp;orders_status_id=all&amp;list_filter=all">{$redeem.previous_orders|ordinalize}</a>{if $redeem.customers_age} {tr}in{/tr} {$redeem.customers_age}{/if}</td>
			<td class="item currency">{$gCommerceCurrencies->format($redeem.orders_value)} {tr}off{/tr}</td>
			<td class="item currency">{$gCommerceCurrencies->format($redeem.order_total+$redeem.orders_value)} </td>
			<td class="item">{$redeem.redeem_date|strtotime|bit_short_datetime}</td>
		</tr>
		{/foreach}
		</table>

		{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}
	</div>
</div>

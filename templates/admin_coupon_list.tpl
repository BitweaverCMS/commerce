{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

{formfeedback hash=$feedback}
{strip}
<div class="admin bitcommerce coupons">
	<div class="header">
		<h1>{tr}Discount Coupons{/tr}</h1>
		<form name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php" method="get">
			<select name="status" onchange="this.form.submit();">
				<option value="" selected="selected">All Coupons</option>
				<option value="Y" {if $smarty.request.status=='Y'}selected="selected"{/if}>{tr}Active Coupons{/tr}</option>
				<option value="N" {if $smarty.request.status=='N'}selected="selected"{/if}>{tr}Inactive Coupons{/tr}</option>
			</select>							
		</form>
		<div class="floaticon"><a href="{$smarty.server.PHP_SELF}?action=new" class="button">{tr}Create Coupon{/tr}</a></div>
	</div>
	<div class="body">

		<table class="data">
		<tr >
			<th>&nbsp;</th>
			<th>{smartlink ititle="Coupon Code" isort="coupon_code" icontrol=$listInfo }</th>
			<th>{tr}Amount{/tr}</th>
			<th class="currency">{smartlink ititle="#" isort="redeemed_count" icontrol=$listInfo iorder="desc"}</th>
			<th class="currency">{smartlink ititle="Cost" isort="redeemed_sum" icontrol=$listInfo iorder="desc"}</th>
			<th class="currency">{smartlink ititle="Income" isort="redeemed_revenue" icontrol=$listInfo iorder="desc"}</th>
			<th>{smartlink ititle="Start" isort="coupon_start_date" icontrol=$listInfo iorder="desc"} / {tr}First{/tr}</th>
			<th>{smartlink ititle="End" isort="coupon_expire_date" icontrol=$listInfo iorder="desc"} / {tr}List{/tr}</th>
			<th></th>
		</tr>

		{foreach from=$couponList item=coupon key=couponId name=couponList}
		<tr class="{if $coupon.coupon_active!='Y'}inactive{elseif $coupon.coupon_start_date|strtotime > time()}pending{elseif $coupon.coupon_expire_date|strtotime > time()}active{else}expired{/if}">
			<td class="item">{$smarty.foreach.couponList.iteration+$listInfo.offset}</td>
			<td class="item">
				<div class="floaticon">
				{if $coupon.redeemed_count == 0}
					<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php?action=delete&amp;cid={$couponId}">{biticon iname="edit-delete"}</a>
				{/if}
					<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php?action=edit&amp;cid={$couponId}">{biticon iname="accessories-text-editor"}</a>
				</div>
				<strong>{if $coupon.redeemed_count > 0}<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php?action=report&amp;cid={$couponId}">{/if}{$coupon.coupon_code}{if $coupon.redeemed_count > 0}</a>{/if}</strong> <em>{$coupon.coupon_name|escape}</em>
				<br/>{$coupon.coupon_description}
			</td>
			<td class="item currency">{if $coupon.coupon_type=='P'}{$coupon.coupon_amount}%{elseif $coupon.coupon_type=='S'}<em>{tr}FREE SHIP{/tr}</em>{else}{$gCommerceCurrencies->format($coupon.coupon_amount)}{/if} {$coupon.restrict_to_shipping}</td>
			<td class="item currency">{$coupon.redeemed_count}</td>
			<td class="item currency">{$gCommerceCurrencies->format($coupon.redeemed_sum)}</td>
			<td class="item currency">{$gCommerceCurrencies->format($coupon.redeemed_revenue)}</td>
			<td class="item">{$coupon.coupon_start_date|strtotime|bit_short_datetime}<div class="date">{$coupon.redeemed_first_date|strtotime|bit_short_datetime}</div></td>
			<td class="item">{$coupon.coupon_expire_date|strtotime|bit_short_datetime}<div class="date">{$coupon.redeemed_first_date|strtotime|bit_short_datetime}</div></td>
		</tr>
		{/foreach}
		</table>

		{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}
	</div>
</div>
{/strip}

{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

{strip}
<div class="admin bitcommerce coupons">
	<header>
		<h1>{tr}Discount Coupons{/tr}</h1>
		<a href="{$smarty.server.SCRIPT_NAME}?action=new" class="pull-right btn btn-primary btn-xs">{tr}Create Coupon{/tr}</a>
		<form class="form form-inline" name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php" method="get">
			<select class="form-control" name="status" onchange="this.form.submit();">
				<option value="" selected="selected">All Activity</option>
				<option value="Y" {if $smarty.request.status=='Y'}selected="selected"{/if}>{tr}Active Coupons{/tr}</option>
				<option value="N" {if $smarty.request.status=='N'}selected="selected"{/if}>{tr}Inactive Coupons{/tr}</option>
			</select>							
			<select class="form-control" name="uses" onchange="this.form.submit();">
				<option value="+" {if $smarty.request.uses=='+'}selected="selected"{/if}>{tr}Unlimited Use{/tr}</option>
				<option value="2" {if $smarty.request.uses!='1' && $smarty.request.uses!='+'}selected="selected"{/if}>{tr}Multiple Use{/tr}</option>
				<option value="1" {if $smarty.request.uses=='1'}selected="selected"{/if}>{tr}Single Use{/tr}</option>
				<option value="" {if empty($smarty.request.uses)}selected="selected"{/if}>{tr}All Uses{/tr}</option>
			</select>							
		</form>
	</header>
	<div class="body">

		<table class="table data">
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
		<tr class="coupon coupon-{if $coupon.coupon_active!='Y'}inactive{elseif $coupon.coupon_start_date|strtotime > time()}pending{elseif $coupon.coupon_expire_date|strtotime > time()}active{else}expired{/if}">
			<td class="item">{$smarty.foreach.couponList.iteration+$listInfo.offset}</td>
			<td class="item text-left">
				<div class="floaticon text-left">
					<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php?action=edit&amp;cid={$couponId}">{booticon iname="fa-pen-to-square"}</a>
					<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_restrict.php?cid={$couponId}">{if $coupon.restrictions_count}<span class="badge">{$coupon.restrictions_count} {booticon iname="fa-lock"}</span>{else}{booticon iname="fa-lock"}{/if}</a>
				{if $coupon.redeemed_count == 0}
					<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php?action=delete&amp;cid={$couponId}">{booticon iname="fa-trash"}</a>
				{/if}
				</div>
				<strong>{$coupon.coupon_code}</strong> {if $coupon.coupon_code != $coupon.coupon_name}<em>{$coupon.coupon_name|escape}</em>{/if}
				<br/>{$coupon.coupon_description}: {if $coupon.uses_per_coupon}{$coupon.uses_per_coupon}{else}{tr}unlimited{/tr}{/if} {tr}use{/tr}{if $coupon.uses_per_user}, {$coupon.uses_per_user} per user{/if}
			</td>
			<td class="item currency">{if $coupon.coupon_type=='P'}{$coupon.coupon_amount}%{else}{$gCommerceCurrencies->format($coupon.coupon_amount)}{/if} {if $coupon.free_ship}{tr}FREE SHIP{/tr}{/if} {$coupon.restrict_to_shipping}</td>
			<td class="item currency">{if $coupon.redeemed_count > 0}<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/coupon_admin.php?action=report&amp;cid={$couponId}">{/if}{$coupon.redeemed_count}{if $coupon.redeemed_count > 0}</a>{/if}</td>
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

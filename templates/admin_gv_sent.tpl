{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

{formfeedback hash=$feedback}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Gift Vouchers Sent{/tr}</h1>
	</div>
	<div class="body">
		<table class="table data">
			<tr class="dataTableHeadingRow">
				<th>{tr}Code{/tr}</th>
				<th>{tr}Emailed To{/tr}</th>
				<th>{tr}Amount{/tr}</th>
				<th>{tr}Senders Name and Reason{/tr}</th>
				<th>{tr}Date Sent{/tr}</th>
				<th>{tr}Redeemed{/tr}</th>
			</tr>
			{foreach from=$couponList item=coupon key=couponId}
			<tr>
				<td class="dataTableContent"><code>{$coupon.coupon_code}</code></td>
				<td class="dataTableContent">{$coupon.emailed_to}</td>
				<td class="dataTableContent">{$gCommerceCurrencies->format($coupon.coupon_amount)}</td>
				<td class="dataTableContent">{$coupon.sent_firstname} {$coupon.sent_lastname} {if $coupon.admin_note}: <em>{$coupon.admin_note|escape}</em>{/if}</td>
				<td class="dataTableContent">{$coupon.date_sent|zen_date_short}</td>
				<td>{if $coupon.redeem_date}{$coupon.redeem_date|zen_date_short} by {displayname user_id=$coupon.customer_id} {if $coupon.order_id} on <a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$coupon.order_id}">Order {$coupon.order_id}</a>{/if}{else}<div class="floaticon"><a onclick="return confirm('{tr}Are you sure you want to delete this gift voucher?{/tr}\n\n{$gCommerceCurrencies->format($coupon.coupon_amount)|escape} to {$coupon.emailed_to|escape}');" href="{$smarty.server.PHP_SLEF}?gid={$couponId}&amp;action=delete">{booticon iname="icon-trash" iexplain="Delete Gift Certificate"}</a>{/if}</td>
			</tr>
			{/foreach}
		</table>

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

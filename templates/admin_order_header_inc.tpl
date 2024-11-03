{literal}
<script>/* <![CDATA[ */
function editAddress( pAddress ) {
	jQuery.ajax({
		data: 'address_type='+pAddress+'&oID='+{/literal}{$smarty.request.oID}{literal},
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#'+pAddress+'address').html(r);
		}
	})
}
function editPayment( pPayment ) {
	jQuery.ajax({
		data: 'edit_payment='+pPayment+'&oID='+{/literal}{$smarty.request.oID}{literal},
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#record-payment-block').html(r);
		}
	})
}
/* ]]> */</script>
{/literal}

<div class="row pb-1">
	<div class="col-sm-4 col-xs-12">
		<h4>{tr}Customer{/tr}</h4>
		{displayname hash=$order->customer} (ID: {$order->customer.user_id})
		<a href="list_orders.php?user_id={$order->customer.user_id}">{booticon iname="fa-clock" iexplain="Customer Sales History"}</a>
		{smartlink ipackage=liberty ifile="list_content.php" user_id=$order->customer.user_id ititle="User Content" booticon="fa-list"}
		{smartlink ipackage=users ifile="admin/index.php" assume_user=$order->customer.user_id ititle="Assume User Identity" booticon="fa-user-doctor"} 
		{smartlink ipackage=users ifile="preferences.php" view_user=$order->customer.user_id ititle="Edit User" booticon="fa-pen-to-square"} 
		{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$order->customer.user_id ititle="User Activity" booticon="fa-bolt"}
		{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$order->customer.user_id ititle="Assume User" booticon="fa-key"}
		<br/>
{if $order->customer.telephone}
	{$order->customer.telephone}<br/>
{/if}
		<a href="mailto:{$order->customer.email_address}">{$order->customer.email_address}</a><br/>
		{if $gBitSystem->isPackageActive('stats')}{if $order->customer.referer_url}<a href="{$order->customer.referer_url}"><small>{$order->customer.referer_url|stats_referer_display_short}</a></small><br/>{/if}{/if}
		{if $customerStats.orders_count == 1}<em>First Order</em>
		{else}
		<strong>Tier {$customerStats.tier|round}</strong>: <a href="list_orders.php?user_id={$order->customer.user_id}&amp;orders_status_id=all&amp;list_filter=all">{$customerStats.orders_count} {tr}orders{/tr} {tr}total{/tr} ${$customerStats.customers_total|round:2}</a> {tr}over{/tr} {$customerStats.customers_age} 
			{if $customerStats.gifts_redeemed || $customerStats.gifts_balance}<br/>
				Gift: ${$customerStats.gifts_redeemed} redeemed {if $customerStats.gifts_balance|round:2}, ${$customerStats.gifts_balance|round:2} {tr}remaining{/tr}{/if}{if $customerStats.commissions}, ${$customerStats.commissions|round:2} {tr}Commissions{/tr}{/if}
			{/if}
		{/if}
		{if $customerStats.negative_orders}
			<div class="alert alert-danger"><ul>
			{foreach from=$customerStats.negative_orders key=status item=negCount}
				<li><a href="list_orders.php?user_id={$order->customer.user_id}">{$negCount} {$status}</a></li>
			{/foreach}
			</ul></div>
		{/if}
	</div>
	<div class="col-sm-4 col-xs-12">
		<h4><a class="icon" onclick="editAddress('delivery');return false;"><i class="fa fal fa-edit"></i></a> {tr}Shipping Address{/tr}</h4>
		<div id="deliveryaddress">
			{$order->getFormattedAddress('delivery')}
		</div>
	</div>
	{if $order->hasDifferentBillingAddress()}
	<div class="col-sm-4 col-xs-12">
		<h4><a class="icon" onclick="editAddress('billing');return false;"><i class="fa fal fa-edit"></i></a> {tr}Billing Address{/tr}</h4>
		<div id="billingaddress">
			{$order->getFormattedAddress('billing')}
		</div>
	</div>
	{/if}
</div>

<div class="row">
	<div class="col-xs-12">
		{if $order->info.amount_due}
		<div class="alert alert-danger">{tr}Amount Due:{/tr} {$currencies->format($order->info.amount_due,true,$order->info.currency,$order->info.currency_value)}<div class="pull-right"><div class="btn btn-default btn-xs" onclick="editPayment('new');return false;">{tr}Record Payment{/tr}</div></div></div>
		{/if}
		<form method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$smarty.request.oID}">
			<div id="record-payment-block" style=""></div>
		</form>
<table class="table table-condensed">
<tr>
	<th>{tr}Date{/tr}</th>
	<th>{tr}IP{/tr}</th>
	<th>{tr}Payment{/tr}</th>
	<th>{tr}Number{/tr}</th>
	<th>{tr}Amount{/tr}</th>
</tr>
{foreach from=$order->mPayments item=paymentHash}
<tr>
	<td><span class="date">{$paymentHash.payment_date|date_format:'Y-m-d H:i'}</span></td>
	<td>{$paymentHash.ip_address}{if $paymentHash.user_id!=$paymentHash.customers_id} {displayname hash=$paymentHash}{/if}</td>
	<td>{if $paymentHash.payment_owner}{$paymentHash.payment_owner} {$paymentHash.address_company}{/if}</td>
	<td>{if $paymentHash.payment_type && $paymentHash.payment_type!=$paymentHash.payment_module}{$paymentHash.payment_module|replace:'_':' '|ucwords} {$paymentHash.payment_type}: {/if}{if $paymentHash.payment_parent_ref_id}{$paymentHash.payment_parent_ref_id}{/if}{if $paymentHash.payment_number}<tt>{$paymentHash.payment_number}</tt>{/if}{if $paymentHash.payment_expires} <div class="inline-block">{tr}Expires{/tr}: <tt>{$paymentHash.payment_expires}</tt></div>{/if}
		{if empty( $paymentHash.payment_ref_id)}
			{if $paymentHash.payment_type=='charge'}
				<div class="inline-block alert alert-danger">{booticon iname="fa-triangle-exclamation"}{tr}This charge payment has no Transaction ID. Verify funds were collected, or if this is a duplicate order.{/tr}</div>
			{/if}
		{else}
		Ref <tt>{$paymentHash.payment_ref_id}</tt>
		{/if}
{if $paymentHash.payment_message && $paymentHash.payment_message != 'Approved' && $paymentHash.payment_message != 'Processed'}
	<div>{$paymentHash.payment_message}</div>
{/if}
	</td>
	<td class="text-right">{$currencies->format(1.0,true,$paymentHash.currency,$paymentHash.payment_amount)}</td>
</tr>
{/foreach}
</table>
	</div>
</div>

{$notificationBlock}

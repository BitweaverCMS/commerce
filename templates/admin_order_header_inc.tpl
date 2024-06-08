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
/* ]]> */</script>
{/literal}

<div class="row">
	<div class="col-xs-6">
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
	<div class="col-xs-6">
		<h4>{tr}Payment Info{/tr}</h4>
		{if $order->info.amount_due}
		<div class="alert alert-danger">{tr}Amount Due:{/tr} {$currencies->format($order->info.amount_due,true,$order->info.currency,$order->info.currency_value)}<span class="pull-right"><a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}invoices.php?oID={$smarty.request.oID}" class="btn btn-default btn-xs">{tr}Record Payment{/tr}</a></span></div>
		{/if}
<dd>
{foreach from=$order->mPayments item=paymentHash}
	<dt>{$paymentHash.payment_module}: {$paymentHash.payment_owner} / {$paymentHash.payment_number}</dt>
		{if $paymentHash.payment_type || $paymentHash.payment_owner || $paymentHash.payment_number}
		<div class="clear">
			<div class="pull-left">{tr}Owner{/tr}:</div>
			<div class="pull-right">{$paymentHash.payment_owner}</div>
		</div>
		<div class="clear">
			<div class="pull-left">{$paymentHash.payment_type}:</div>
			<div class="pull-right">{$paymentHash.payment_number}</div>
		</div>
		{if $paymentHash.payment_expires}
		<div class="clear">
			<div class="pull-left">{tr}Expires{/tr}: </div>
			<div class="pull-right">{$paymentHash.payment_expires}</div>
		</div>
		{/if}
		{if empty( $paymentHash.payment_ref_id)}
			<div class="alert alert-danger">{booticon iname="fa-triangle-exclamation"} {$order->info.payment_module_code}: {tr}This payment has no Transaction ID. Verify funds were actually collected, or if this is a duplicate order.{/tr}</div>
		{else}
		<div class="clear">
			<div class="pull-left">{$paymentHash.payment_module|replace:'_':' '|ucwords} {tr}Ref ID{/tr}:</div>
			<div class="pull-right">{$paymentHash.payment_ref_id}</div>
		</div>
		{/if}
		<div class="clear">
			<div class="pull-left">{tr}IP{/tr}:</div>
			<div class="pull-right"> {$paymentHash.ip_address}</div>
		</div>
		{/if}
	<dd>
{$paymentHash|vd}
{/foreach}
</dd>

		{if $order->info.currency != $smarty.const.DEFAULT_CURRENCY}
		<div class="clear">
			<div class="pull-left">{tr}Currency{/tr}:</div>
			<div class="pull-right">{$currencies->format(1.0,true,$order->info.currency,$order->info.currency_value)} / {$currencies->format(1.0,true,$smarty.const.DEFAULT_CURRENCY)}</div>
		</div>
		{/if}
	</div>
</div>

<div class="row">
	<div class="col-xs-6">
		<h4><a class="icon" onclick="editAddress('delivery');return false;"><i class="fa fal fa-edit"></i></a> {tr}Shipping Address{/tr}</h4>
		<div id="deliveryaddress">
			{$order->getFormattedAddress('delivery')}
		</div>
	</div>
	<div class="col-xs-6">
		<h4><a class="icon" onclick="editAddress('billing');return false;"><i class="fa fal fa-edit"></i></a> {tr}Billing Address{/tr}</h4>
		<div id="billingaddress">
			{$order->getFormattedAddress('billing')}
		</div>
	</div>
</div>

{$notificationBlock}

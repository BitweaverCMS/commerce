{literal}
<script type="text/javascript">/* <![CDATA[ */
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
		<a href="list_orders.php?user_id={$order->customer.user_id}">{booticon iname="icon-time" iexplain="Customer Sales History"}</a>
		{smartlink ipackage=liberty ifile="list_content.php" user_id=$order->customer.user_id ititle="User Content" booticon="icon-list" iforce="icon"}
		{smartlink ipackage=users ifile="admin/index.php" assume_user=$order->customer.user_id ititle="Assume User Identity" booticon="icon-user-md" iforce=icon} 
		{smartlink ipackage=users ifile="preferences.php" view_user=$order->customer.user_id ititle="Edit User" booticon="icon-pencil" iforce=icon} 
		{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$order->customer.user_id ititle="User Activity" booticon="icon-bolt" iforce="icon"}
		{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$order->customer.user_id ititle="Assume User" booticon="icon-key" iforce="icon"}
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
		{if $order->info.cc_type || $order->info.cc_owner || $order->info.cc_number}
		<div class="clear">
			<div class="floatleft">{$order->info.cc_type}: </div>
			<div class="floatright">{$order->info.cc_owner}</div>
		</div>
		<div class="clear">
			<div class="floatleft">{tr}Number{/tr}: </div>
			<div class="floatright">{$order->info.cc_number}</div>
		</div>
		{if $order->info.cc_expires}
		<div class="clear">
			<div class="floatleft">{tr}Expires{/tr}: </div>
			<div class="floatright">{$order->info.cc_expires}</div>
		</div>
		{/if}
		{/if}
		<div class="clear">
		{if empty( $order->info.cc_ref_id )}
			<div class="alert alert-danger">{booticon iname="icon-warning-sign"} {tr}This payment has no Transaction ID. Verify funds were actually collected, or if this is a duplicate order.{/tr}</div>
		{else}
			<div class="floatleft">{tr}Transaction ID{/tr}: </div>
			<div class="floatright">{$order->info.cc_ref_id}</div>
		{/if}
		</div>
		<div class="clear">
			<div class="floatleft">{tr}IP{/tr}:</div>
			<div class="floatright"> {$order->info.ip_address}</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xs-6">
		<h4><a class="icon" onclick="editAddress('delivery');return false;"><i class="icon-edit"></i></a> {tr}Shipping Address{/tr}</h4>
		<div id="deliveryaddress">
			{$order->getFormattedAddress('delivery')}
		</div>
	</div>
	<div class="col-xs-6">
		<h4><a class="icon" onclick="editAddress('billing');return false;"><i class="icon-edit"></i></a> {tr}Billing Address{/tr}</h4>
		<div id="billingaddress">
			{$order->getFormattedAddress('billing')}
		</div>
	</div>
</div>

{$notificationBlock}

{literal}<style>
@media print {
	header { max-width: 612pt; }
}
</style>{/literal}
<header>
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<div class="panel panel-default height">
				<div class="panel-heading">{$gBitSystem->getConfig('site_title')}</div>
				<div class="panel-body">
					{$smarty.const.STORE_NAME_ADDRESS|nl2br}
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-8 text-center">
			<h1 class="page-heading">{tr}Invoice{/tr}: {tr}Order{/tr} #{$order->mOrdersId}</h1>
			<div class="date">{tr}Purchased{/tr}: {$order->getField('date_purchased')|bit_long_datetime}</div>
		</div>
	</div>

	<div class="row clear">
		<div class="col-sm-4 col-xs-12">
		{if $showPricing}
			<div class="row">
				<div class="col-sm-12 col-xs-6">
					<div class="panel panel-default height">
						<div class="panel-heading">{tr}Payment{/tr}</div>
						<div class="panel-body">
							{if $order->info.payment_type}
							<div style="clear:both">
								<div class="pull-left">{$order->info.payment_type}: </div>
								<div class="pull-right">{$order->info.payment_owner}</div>
							</div>
							{/if}
							{if $order->info.payment_number}
							<div style="clear:both">
								<div class="pull-left">{tr}Number{/tr}: </div>
								<div class="pull-right">{$order->info.payment_number}</div>
							</div>
							{/if}
							{if $order->info.payment_number}
							<div style="clear:both">
								<div class="pull-left">{tr}Expiration{/tr}: </div>
								<div class="pull-right">{$order->info.payment_expires}</div>
							</div>
							{/if}
							{if $order->info.payment_ref_id}
							<div style="clear:both">
								<div class="pull-left">{tr}Transaction ID{/tr}: </div>
								<div class="pull-right">{$order->info.payment_ref_id}</div>
							</div>
							{/if}
							<div style="clear:both">
								<div class="pull-left">{tr}IP{/tr}:</div>
								<div class="pull-right"> {$order->info.ip_address}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		</div>

		<div class="col-sm-4 col-xs-6">
		{if $showPricing}
			<div class="panel panel-default height">
				<div class="panel-heading">{tr}Billing Address{/tr}</div>
				<div class="panel-body">
					{$order->getFormattedAddress('billing')}
				</div>
			</div>
		{/if}
		</div>

		{if $order->delivery}
		<div class="col-sm-4 col-xs-6">
			<div class="panel panel-default height">
				<div class="panel-heading">{tr}Delivery Address{/tr}</div>
				<div class="panel-body">
					<p>{$order->getFormattedAddress('delivery')}</p>
				</div>
			</div>
		</div>
		{/if}
	</div>
</header>

{include file="bitpackage:bitcommerce/order_invoice_contents_inc.tpl"}

{if $showPricing && $order->mHistory}
<h3>{tr}Status History & Comments{/tr}</h3>
<ul class="list-unstyled">
{foreach from=$order->mHistory|@array_reverse item=history}
	{if $history.customer_notified || $gBitUser->hasPermission( 'p_bitcommerce_admin' )}
	<li class="alert {if $history.customer_notified}alert-warning{else}alert-info{/if}">
		<div class="strong"><strong>{$history.date_added|bit_short_datetime}</strong> <em>{$history.orders_status_name}</em></div>
		{if $history.comments|escape:"html"}
		<p>{$history.comments}</p>
		{/if}
   </li>
   {/if}
{/foreach}
</ul>

<a class="btn btn-default" href="mailto:{$smarty.const.STORE_OWNER_EMAIL_ADDRESS}">Contact Us</a>
{/if}


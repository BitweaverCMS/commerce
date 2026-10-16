{literal}<style>
@media print {

.col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
  float: left!important;
}
.col-sm-12 {
  width: 100%;
}
.col-sm-11 {
  width: 91.66666666666666%;
}
.col-sm-10 {
  width: 83.33333333333334%;
}
.col-sm-9 {
  width: 75%;
}
.col-sm-8 {
  width: 66.66666666666666%;
}
.col-sm-7 {
  width: 58.333333333333336%;
}
.col-sm-6 {
  width: 50%;
}
.col-sm-5 {
  width: 41.66666666666667%;
}
.col-sm-4 {
  width: 33.33333333333333%;
}
.col-sm-3 {
  width: 25%;
}
.col-sm-2 {
  width: 16.666666666666664%;
}
.col-sm-1 {
  width: 8.333333333333332%;
}

}
</style>{/literal}
<header>
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<div class="panel panel-default height">
				<div class="panel-heading">{tr}Return Address{/tr}</div>
				<div class="panel-body">
					{$smarty.const.STORE_NAME_ADDRESS|nl2br}
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-center">
			<h1 class="page-heading">{tr}Order{/tr} #{$order->mOrdersId}</h1>
			<div class="date">{tr}Purchased{/tr}: {$order->getField('date_purchased')|bit_long_datetime}</div>
			{if $order->info.estimated_ship_date}<div class="date">{tr}Estimated Ship{/tr}: {$order->info.estimated_ship_date|bit_long_date}</div>{/if}
			{if $order->info.estimated_arrival_date}<div class="date">{tr}Estimated Delivery{/tr}: {$order->info.estimated_arrival_date|bit_long_date}</div>{/if}
		</div>

		{if $order->delivery}
		<div class="col-xs-12 col-sm-4">
			<div class="panel panel-default height">
				<div class="panel-heading">{tr}Delivery Address{/tr}</div>
				<div class="panel-body">
					<p>{$order->getFormattedAddress('delivery')}</p>
				</div>
			</div>
		</div>
		{/if}
	</div>

	{if $showPricing}
	<div class="row">
		<div class="col-xs-12 col-sm-4"></div>
		<div class="col-xs-12 col-sm-4">
			<div class="panel panel-default height">
				<div class="panel-heading">{tr}Payment{/tr}</div>
				<div class="panel-body">
					{if $order->info.payment_method}
					<div style="clear:both">
						<div class="pull-left">{tr}Method{/tr}: </div>
						<div class="pull-right">{$order->info.payment_method|escape}</div>
					</div>
					{/if}
					{if $order->mPayments}
						{foreach from=$order->mPayments item=paymentHash}
						<div style="clear:both">
							{if $paymentHash.payment_date}
							<div style="clear:both">
								<div class="pull-left">{tr}Date{/tr}: </div>
								<div class="pull-right">{$paymentHash.payment_date|bit_short_datetime}</div>
							</div>
							{/if}
							{if $paymentHash.payment_type || $paymentHash.payment_mode}
							<div style="clear:both">
								<div class="pull-left">{tr}Type{/tr}: </div>
								<div class="pull-right">{$paymentHash.payment_type|escape}{if $paymentHash.payment_mode}{if $paymentHash.payment_type} {/if}{$paymentHash.payment_mode|escape}{/if}</div>
							</div>
							{/if}
							{if $paymentHash.payment_owner}
							<div style="clear:both">
								<div class="pull-left">{tr}Name{/tr}: </div>
								<div class="pull-right">{$paymentHash.payment_owner|escape}</div>
							</div>
							{/if}
							{if $paymentHash.payment_number}
							<div style="clear:both">
								<div class="pull-left">{tr}Number{/tr}: </div>
								<div class="pull-right">{$paymentHash.payment_number|escape}</div>
							</div>
							{/if}
							{if $paymentHash.payment_expires}
							<div style="clear:both">
								<div class="pull-left">{tr}Expiration{/tr}: </div>
								<div class="pull-right">{$paymentHash.payment_expires|escape}</div>
							</div>
							{/if}
							{if $paymentHash.payment_ref_id}
							<div style="clear:both">
								<div class="pull-left">{tr}Transaction ID{/tr}: </div>
								<div class="pull-right">{$paymentHash.payment_ref_id|escape}</div>
							</div>
							{/if}
							<div style="clear:both">
								<div class="pull-left">{tr}Amount{/tr}: </div>
								<div class="pull-right">{$gCommerceCurrencies->format($paymentHash.payment_amount, false, $paymentHash.payment_currency)}</div>
							</div>
							{if $paymentHash.payment_message && $paymentHash.payment_message != 'Approved' && $paymentHash.payment_message != 'Processed'}
							<div style="clear:both">{$paymentHash.payment_message|escape}</div>
							{/if}
						</div>
						{/foreach}
					{else}
						{if $order->info.payment_type}
						<div style="clear:both">
							<div class="pull-left">{$order->info.payment_type|escape}: </div>
							<div class="pull-right">{$order->info.payment_owner|escape}</div>
						</div>
						{/if}
						{if $order->info.payment_number}
						<div style="clear:both">
							<div class="pull-left">{tr}Number{/tr}: </div>
							<div class="pull-right">{$order->info.payment_number|escape}</div>
						</div>
						{/if}
						{if $order->info.payment_expires}
						<div style="clear:both">
							<div class="pull-left">{tr}Expiration{/tr}: </div>
							<div class="pull-right">{$order->info.payment_expires|escape}</div>
						</div>
						{/if}
					{/if}
					<div style="clear:both">
						<div class="pull-left">{tr}IP{/tr}:</div>
						<div class="pull-right"> {$order->info.ip_address|escape}</div>
					</div>
				</div>
			</div>
		</div>
		<div class=" col-xs-6 col-sm-4">
			<div class="panel panel-default height">
				<div class="panel-heading">{tr}Billing Address{/tr}</div>
				<div class="panel-body">
					{$order->getFormattedAddress('billing')}
				</div>
			</div>
		</div>
	</div>
	{/if}
</header>

{include file="bitpackage:bitcommerce/order_invoice_contents_inc.tpl" orderAgainForm=1}

{if $showPricing && $order->mHistory}
<h3>{tr}Status History & Comments{/tr}</h3>
<ul class="list-unstyled">
{foreach from=$order->mHistory|@array_reverse item=history}
	{if $history.customer_notified || $gBitUser->hasPermission( 'p_bitcommerce_admin' )}
	<li class="alert {if $history.customer_notified}alert-warning{else}alert-info{/if}">
		<div class="strong"><strong>{$history.date_added|bit_short_datetime}</strong> <em>{$history.orders_status_name}</em></div>
		{if $history.comments}
		<div class="order-history-comment">{if $history.comments_html}{$history.comments_html}{else}{$history.comments|escape}{/if}</div>
		{/if}
   </li>
   {/if}
{/foreach}
</ul>

<a class="btn btn-default" href="mailto:{$smarty.const.STORE_OWNER_EMAIL_ADDRESS}">Contact Us</a>
{/if}


{strip}{assign var=checkoutStep value=3}
{include file="bitpackage:bitcommerce/page_checkout_header_inc.tpl" title="Order Confirmation" step=3}
{form action=$formActionUrl onsubmit="paymentSubmit(this)"}
<div class="row shopping-cart">
	<div class="col-md-8 col-sm-12">
	{fieldset legend="Shopping Cart Contents"}
		{if sizeof($order->info.tax_groups) > 1}
			{assign var=colspan value=3}
		{else}
			{assign var=colspan value=2}
		{/if}
		{foreach from=$order->contents item=orderItem key=opid}
		<div class="cart-item pv-2">
			<div class="row">
				{if $orderItem.image_url}
				<div class="col-md-2 col-sm-2">
					<a href="{$orderItem.display_url}"><img class="img-responsive center-block" src="{$orderItem.image_url}" alt="{$orderItem.name|escape}"/></a>
				</div>
				{/if}
				<div class="{if $orderItem.image_url}col-sm-6{else}col-sm-8{/if} col-xs-12">
					<a href="{$orderItem.display_url}"><strong style="font-size:120%;">{$orderItem.name}</strong></a> 
				</div>
				<div class="col-md-2 col-sm-2 col-xs-3 text-right">
					<strong>{tr}Qty.{/tr} {$orderItem.products_quantity}</strong>
				</div>
				<div class="col-sm-2 col-xs-3">
				{if sizeof($order->info.tax_groups) > 1}
					{if !empty( $orderItem.tax )}
						{$orderItem.tax|zen_display_tax_value}%
					{/if}
				{/if}
					<strong class="pull-right">{$gCommerceCurrencies->display_price($orderItem.final_price, $orderItem.tax, $orderItem.products_quantity)}</strong>
					{if $orderItem.onetime_charges != 0}
					<div>{$gCommerceCurrencies->display_price($orderItem.onetime_charges, $orderItem.tax, 1)}</div>
					{/if}
				</div>
			{if !empty( $orderItem.attributes )}
				<div class="col-md-10 col-sm-9 col-xs-12">
					<ul>
					{foreach $orderItem.attributes item=orderItemAttribute}
						<li><em>{$orderItemAttribute.products_options_name|escape}:</em> {$orderItemAttribute.value}</li>
					{/foreach}
					</ul>
				</div>
			{/if}
			</div>
		</div>
		{/foreach}
		{if $order->content_type!='virtual' && $order->info.shipping_method}
			<p class="text-right mt-2">{tr}Shipping Method{/tr}: {$order->info.shipping_method} <a class="btn btn-default btn-xs" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping">{booticon iname="fa-truck"}&nbsp;{tr}Change{/tr}</a></p>
		{/if}
		{foreach from=$order->otOutput() item=otOutput}
		<div class="row {$otOutput.code}">
			<div class="col-xs-9 col-sm-10 text-right">{$otOutput.title}</div>
			<div class="col-xs-3 col-sm-2 text-right"><strong>{$otOutput.text}</strong></div>
		</div>
		{/foreach}
	{/fieldset}
	{fieldset legend="Order Comments" class="width100p"}
		<textarea name="comments" rows="3" class="form-control special-instructions">{$order->info.comments|escape}</textarea>
	{/fieldset}
	</div>
	
	<div class="col-md-4">
		<div class="row">
			<div class="col-md-12 col-sm-4 col-xs-12">
				{fieldset legend="Shipping Address"}
					<div class="pull-right"><a class="btn btn-default btn-xs" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping&amp;change_address=1">{booticon iname="fa-truck"} {tr}Change{/tr}</a></div>
					{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$order->getDelivery()}
				{/fieldset}
			</div>
			<div class="col-md-12 col-sm-4 col-xs-12">
				{include file="bitpackage:bitcommerce/page_checkout_deadline_inc.tpl" deadline=$smarty.session.deadline_date}
			</div>
			<div class="col-md-12 col-sm-4 col-xs-12">
				{fieldset legend="Billing Address"}
					<div class="pull-right"><a class="btn btn-default btn-xs" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_payment&amp;change_address=1">{booticon iname="fa-house"} {tr}Change{/tr}</a></div>
					{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$order->getBilling()}
				{/fieldset}
			</div>
			<div class="col-md-12  col-sm-4 col-xs-12">
				{fieldset legend="Payment Method"}
					<div class="pull-right"><a class="btn btn-default btn-xs" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_payment">{booticon iname="fa-credit-card"} {tr}Change{/tr}</a></div>
					{if $paymentConfirmation}
						<h4 class="no-margin">{$paymentConfirmation.title|escape}</h4>
						{foreach from=$paymentConfirmation.fields item=payFields}
						<div class="clear">
							{if $payFields.field}<div class="">{$payFields.field}</div>{/if}
						</div>
						{/foreach}
					{elseif $smarty.session.payment}
						{$smarty.session.payment->title}
					{/if}
				{/fieldset}
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-8 col-xs-12 mv-3 text-center">
		{$paymentModules->process_button()}
		<div class="form-group submit">
			<button id="payment-submit-btn" onclick='paymentSubmit(this.form); this.form.submit();' type="submit" class="btn btn-primary btn-lg" />{tr}Submit Order{/tr}</button>
		</div>
	</div>
</div>


<script>
function paymentSubmit( pForm ) {ldelim}
	$('#payment-submit-btn').html("<i class=\"fa fal fa-spinner fa-spin\"></i> {tr}Processsing Payment...{/tr}");
	$('#payment-submit-btn').prop("disabled",true);
	return true;
{rdelim}
</script>
{/form}
{/strip}

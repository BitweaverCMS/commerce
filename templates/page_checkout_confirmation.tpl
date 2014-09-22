
<header class="page-header">
	<h1>{tr}Step 3 of 3{/tr} - {tr}Order Confirmation{/tr}</h1>
</header>

{form action=$formActionUrl}
<div class="row">
	<div class="col-md-8">

<div class="row">
	<div class="col-md-6">
		{legend legend="Shipping Address"}
			{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$order->delivery}
			<a class="btn btn-default btn-sm" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping&amp;change_address=1"><i class="icon-home"></i> {tr}Change Address{/tr}</a>
		{/legend}
	</div>
	<div class="col-md-6">
		{legend legend="Billing Address"}
			{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$order->billing}
			<a class="btn btn-default btn-sm" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_payment&amp;change_address=1"><i class="icon-home"></i> {tr}Change Address{/tr}</a>
		{/legend}
	</div>
</div>

<div class="row">
	<div class="col-md-12">

	{legend legend="Shopping Cart Contents"}
		<table class="table">
			{if sizeof($order->info.tax_groups) > 1}
				{assign var=colspan value=3}
			{else}
				{assign var=colspan value=2}
			{/if}
			{foreach from=$order->contents item=orderItem key=opid}
			<tr>
				<td class="width25p" rowspan="2"><a href="{$orderItem.display_url}"><img class="img-responsive" src="{$orderItem.image_url}" alt="{$orderItem.name|escape}"/></a></td>
				<td>
					<strong>{$orderItem.products_quantity}</strong>&nbsp;x <a href="{$orderItem.display_url}">{$orderItem.name}</a> 
				</td>
				{if sizeof($order->info.tax_groups) > 1}
					<td class="text-right">
					{if !empty( $orderItem.tax )}
						{$orderItem.tax|zen_display_tax_value}%
					{/if}
					</td>
				{/if}
				<td class="text-right">
					{$gCommerceCurrencies->display_price($orderItem.final_price, $orderItem.tax, $orderItem.products_quantity)}
					{if $orderItem.onetime_charges != 0}
					<br />{$gCommerceCurrencies->display_price($orderItem.onetime_charges, $orderItem.tax, 1)}
					{/if}
				</td>
			</tr>
			{if !empty( $orderItem.attributes )}
				<tr>
					<td colspan="{$colspan}" style="border:none;">
						<ul>
						{foreach $orderItem.attributes item=orderItemAttribute}
							<li><em>{$orderItemAttribute.option|escape} : {$orderItemAttribute.value}</em></li>
						{/foreach}
						</ul>
					</td>
				</tr>
			{/if}
			{/foreach}
			{if $order->content_type!='virtual' && $order->info.shipping_method}
			<tr>
				<td class="text-right" colspan="{$colspan}">{tr}Shipping Method{/tr}: {$order->info.shipping_method}</td>
				<td><a class="btn btn-default btn-sm" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping"><i class="icon-truck"></i>&nbsp;{tr}Change{/tr}</a></td>
			</tr>
			{/if}
			{if $orderTotalsModules}
				{foreach from=$orderTotalsModules->modules item=otFile}
					{assign var=classInfo value=$otFile|pathinfo}
					{assign var=className value=$classInfo.filename}
					{foreach from=$GLOBALS.$className->output item=otOutput}
						<tr>
							<td  colspan="{$colspan}" class="text-right">{$otOutput.title|escape}</td>
							<td class="text-right">{$otOutput.text|escape}</td>
						</tr>
					{/foreach}
				{/foreach}
			{/if}
		</table>
	{/legend}
	</div>
</div>
</div>
<div class="col-md-4">
	{legend legend="Payment Method"}
		{if $paymentConfirmation}
			<h4>{$paymentConfirmation.title|escape}</h4>
			{foreach from=$paymentConfirmation.fields item=payFields}
			<div class="clear">
				{if $payFields.title}<label class="control-label">{$payFields.title}</label>{/if}
				{if $payFields.field}<div class="controls">{$payFields.field}</div>{/if}
			</div>
			{/foreach}
		{elseif $smarty.session.payment}
			{$smarty.session.payment->title}
		{/if}
		<div class="clear">
			<a class="btn btn-default btn-sm" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_payment"><i class="icon-credit-card"></i> {tr}Change{/tr}</a>
		</div>
	{/legend}

	{legend legend="Order Comments" class="width100p"}
		<textarea name="comments" rows="10" class="width95p">{$order->info.comments|escape}</textarea>
	{/legend}
</div>

</div>


<h3>{tr}Final Step{/tr}</h3>
<p>{tr}- continue to confirm your order. Thank you!{/tr}</p>

{if $paymentModules->modules}
	{$paymentModules->process_button()}
{/if}

<div class="form-group submit">
	<input type="submit" class="btn btn-primary" value="{tr}Confirm Order{/tr}" />
</div>

{/form}

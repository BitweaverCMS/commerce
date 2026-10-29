<div class="drop-ship drop-ship-confirmation">
{if $gBitUser->hasPermission('p_bitcommerce_dropship')}
	{include file="bitpackage:bitcommerce/page_dropship_header_inc.tpl" step=2}
	<header class="page-header">
		<h1>{tr}Final Review{/tr} <small>{tr}drop ship{/tr}</small></h1>
		<p class="lead">{tr}One order per address, priced together as one batch. Check them, then create them.{/tr} {tr}To change an address, its shipping, or its quantities, go back.{/tr}</p>
	</header>

	{if $confirmSkipped}
		<div class="alert alert-warning">
			{foreach from=$confirmSkipped item=skipped}<div>{$skipped}</div>{/foreach}
		</div>
	{/if}

	{form name='dropship_create' id='dropship-create' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post'}
	<input type="hidden" name="dropship_process" value="1">
	<input type="hidden" name="dropship_remove" value="">
	{foreach from=$confirmPayment key=paymentKey item=paymentValue}
		<input type="hidden" name="{$paymentKey|escape}" value="{$paymentValue|escape}">
	{/foreach}
	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<table class="table confirm-orders">
					<thead>
						<tr>
							<th>{tr}Ship to{/tr}</th>
							<th>{tr}Order{/tr}</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$confirmOrders item=co}
						<tr class="confirm-order{if $co.problems} danger{/if}">
							<td class="confirm-ship-to">
								{if !$co.problems}
									<input type="hidden" name="dropship_order[]" value="{$co.idx}">
									<input type="hidden" name="shipping_quote[{$co.idx}]" value="{$co.quote|escape}">
									{if $co.save}<input type="hidden" name="save_address[]" value="{$co.idx}">{/if}
								{/if}
								<div class="ship-to-name">{$co.delivery.firstname|escape} {$co.delivery.lastname|escape}{if $co.delivery.company}, {$co.delivery.company|escape}{/if}</div>
								<div class="ship-to-address">{$co.delivery.street_address|escape}{if $co.delivery.suburb}, {$co.delivery.suburb|escape}{/if}<br>{$co.delivery.city|escape}{if $co.delivery.state}, {$co.delivery.state|escape}{/if} {$co.delivery.postcode|escape}{if $co.delivery.countries_name}, {$co.delivery.countries_name|escape}{/if}</div>
								{if $co.telephone}<div class="ship-to-address">{booticon iname="fa-phone"} {$co.telephone|escape}</div>{/if}
								{if $co.comments}<div class="ship-to-address"><em>{$co.comments|escape}</em></div>{/if}
								{if $co.save}<div class="small text-muted drop-ship-save">{booticon iname="fa-check"} {tr}Will be saved to your address book{/tr}</div>{/if}
								{if $co.problems}<div class="ship-to-why">{foreach from=$co.problems item=problem name=probs}{$problem}{if !$smarty.foreach.probs.last}. {/if}{/foreach}</div>{/if}
							</td>
							<td class="confirm-items">
								{include file="bitpackage:bitcommerce/dropship_cart_inc.tpl" cart=$co.cart}
								{if !$co.problems}
								<div class="confirm-summary">
									<div class="total-line"><span>{tr}Subtotal{/tr}</span><span class="num">{$co.subtotal_display}</span></div>
									<div class="total-line"><span>{tr}Shipping{/tr} <span class="text-muted">{$co.shipping_method|escape}</span></span><span class="num">{$co.shipping_display}</span></div>
									{foreach from=$co.ot item=ot}{if $ot.code != 'ot_subtotal' && $ot.code != 'ot_shipping' && $ot.code != 'ot_total'}<div class="total-line"><span>{$ot.title}</span><span class="num">{$ot.text}</span></div>{/if}{/foreach}
									<div class="total-line grand"><span>{tr}Total{/tr}</span><span class="num">{$co.total_display}</span></div>
								</div>
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
					<tfoot>
						<tr><td>{$confirmReadyCount} {tr}orders{/tr}</td><td class="num">{$confirmTotalDisplay}</td></tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="col-md-4">
			<div class="well drop-ship-rail">
				<h3>{tr}Priced as One Batch{/tr}</h3>
				{if $confirmBatchLines}
					{foreach from=$confirmBatchLines item=line}
						<div class="confirm-batch">
							<div class="ship-to-name">{$line.quantity} &times; {$line.title|escape}</div>
							{if $line.options}<div class="ship-to-address">{$line.options|escape}</div>{/if}
							<div class="ship-to-address">{$line.price_display} {tr}each{/tr}{if $line.alone_display} <span class="was">{$line.alone_display}</span> {$line.percent}% {tr}quantity discount{/tr}{else} <span class="text-muted">{tr}no quantity discount yet{/tr}</span>{/if}</div>
						</div>
					{/foreach}
					<p class="help-block">{tr}Every order in this batch gets the discount for the combined quantity, whatever each address receives.{/tr}</p>
				{else}
					<p class="text-muted">{tr}No products.{/tr}</p>
				{/if}
				<hr>
				<div class="total-line"><span>{tr}Items{/tr}</span><span class="num">{$confirmItemsDisplay}</span></div>
				<div class="total-line"><span>{tr}Shipping{/tr}</span><span class="num">{$confirmShippingDisplay}</span></div>
				<div class="total-line grand"><span>{tr}Total{/tr}{if $confirmPoNumber} {tr}due on{/tr} {$confirmPoNumber|escape}{/if}</span><span class="num">{$confirmTotalDisplay}</span></div>
				{if $confirmPaymentConfirmation}
					<dl class="confirm-payment">
						<dt>{tr}Payment{/tr}</dt>
						<dd>{$confirmPaymentConfirmation.title|escape}</dd>
						{foreach from=$confirmPaymentConfirmation.fields item=payField}
							{if $payField.field}<dt>{$payField.title|escape}</dt><dd>{$payField.field|escape}</dd>{/if}
						{/foreach}
					</dl>
				{/if}
				<input type="submit" value="{tr}Create Orders{/tr}" class="btn btn-primary btn-lg btn-block" id="dropship-create-button" {if !$confirmReadyCount}disabled{/if}>
				<div class="checkbox">
					<label><input name="notify" type="checkbox" value="1" {if $confirmNotify}checked{/if}> {tr}Email me a receipt for each order. Recipients are never emailed.{/tr}</label>
				</div>
				<a class="btn btn-link btn-block" href="{$smarty.const.BITCOMMERCE_PKG_URL}dropship">{tr}Change Addresses or Shipping{/tr}</a>
				<p class="help-block">{tr}Nothing is charged now. The orders wait unpaid in{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}invoices">{tr}Due Orders{/tr}</a>, {tr}where a wrong one can be deleted before you pay.{/tr}</p>
			</div>
		</div>
	</div>
	{/form}

	<div class="drop-ship-wait" style="display:none" role="status" aria-live="polite">
		<div class="drop-ship-wait-box">
			<div class="drop-ship-spinner" aria-hidden="true"></div>
			<div class="drop-ship-wait-title">{tr}Please wait{/tr}</div>
			<div class="drop-ship-wait-text">{tr}Creating your orders. Keep this page open.{/tr}</div>
		</div>
	</div>
{literal}
<script>
$(document).ready(function() {
	$('#dropship-create').on('submit', function() { $('.drop-ship-wait').css('display', 'flex'); });
	$(window).on('pageshow', function() { $('.drop-ship-wait').hide(); });
});
</script>
{/literal}
{/if}
</div>

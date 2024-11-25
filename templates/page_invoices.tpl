<header class="page-header">
	<h1>{tr}Due Orders{/tr}</h1>
</header>

{$messageStack->output('address')}
{formfeedback hash=$feedback}
{form name='invoices' method='post' enctype='multipart/form-data' autocomplete="on"}
<input type="hidden" name="main_page" value="invoices" />
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
{foreach from=$dueOrders key=userId item=$customerHash name=dueorders}
	<div class="panel panel-default user-{$userId}">
		<div class="panel-heading" role="tab" id="due-user-{$userId}">
			<h4 class="panel-title">
				{*<input type="checkbox" class="batch-checkbox" name="" value="" onclick="toggleBatchCheckbox(this,'.user-{$userId} .batch-checkbox')">*} <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-user-{$userId}" aria-expanded="true" aria-controls="collapse-user-{$userId}">{displayname user_id=$userId nolink=1}</a>
			</h4>
		</div>
		<div id="collapse-user-{$userId}" class="panel-collapse collapse {if $smarty.foreach.dueorders.first}in{/if}" role="tabpanel" aria-labelledby="due-user-{$userId}">
			<div class="panel-body">

	{foreach from=$customerHash key=paymentRefId item=$customerOrders}
	<div class="payment-{$paymentRefId|regex_replace:"/[^[:alnum:]]/u":""}">
		{assign var=orderCount value=0}
		{assign var=orderSum value=0}
		{foreach from=$customerOrders.orders item=orderHash name=customerOrders}
			{assign var=orderCount value=$orderCount+1}
			{assign var=orderSum value=$orderSum+$orderHash.amount_due}
			{forminput label="checkbox"}
			<div class="row">
				<div class="col-xs-2 col-sm-1"><input type="checkbox" name="" value="" class="batch-checkbox" disabled autocomplete="off"> 
					{if $gBitUser->hasPermission( 'p_bitcommerce_admin' )}<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$orderHash.orders_id}">{else} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}account_history_info?order_id={$orderHash.orders_id}">{/if}{$orderHash.orders_id}</a>
				</div>
				<div class="col-xs-2 col-sm-1">{$gCommerceCurrencies->format($orderHash.amount_due,true,$orderHash.currency,$orderHash.currency_value)}</div>
				<div class="col-xs-8 col-sm-5"><span class="date">{$orderHash.date_purchased|strtotime|date_format:"%Y-%m-%d %H:%m"}</span> {$orderHash.delivery_name}, {$orderHash.delivery_city}, {$orderHash.delivery_state}</div>
				<div class="col-xs-8 col-sm-3">{$orderHash.payment_method}: {$orderHash.payment_number}</div>
			</div>
			{/forminput}
		{/foreach}
		{forminput label="checkbox"}
			<div class="row" style="border-top:1px solid #ccc;padding-bottom:2em;">
				<div class="col-xs-2 col-sm-1"><input class="batch-checkbox" type="checkbox" name="invoice[{$userId}][]" value="{$paymentRefId}" onclick="selectInvoice('{$paymentRefId|escape:'quotes'}','{$orderSum}');toggleBatchCheckbox(this,'.user-{$userId} .payment-{$paymentRefId|regex_replace:"/[^[:alnum:]]/u":""} .batch-checkbox');" autocomplete="off"> <tt>@{$orderCount}</tt> </div>
				<div class="col-xs-10">{$gCommerceCurrencies->format($orderSum,true,$orderHash.currency,$orderHash.currency_value)} <strong class="inline-block pl-2">{$paymentRefId}</strong></div>
			</div>
		{/forminput}
	</div>
	{/foreach}

			</div>
		</div>
	</div>
{foreachelse}
<div class="alert alert-success">You have no outstanding invoices.</div>
{/foreach}
</div>

{if $dueOrders}
{assign var=selection value=$paymentSelections|current}
{include file="bitpackage:bitcommerce/order_payment_edit.tpl"}
{/if}
{/form}



<script>{literal}
function toggleBatchCheckbox( pCheckbox, pSelector ) {
	var checkState = pCheckbox.checked;
	$('.batch-checkbox').prop('checked', false);
	$('.batch-button').prop('disabled', false);
	pCheckbox.checked = checkState;
	$(pSelector).prop('checked', checkState);
}
function editPayment( pPayment ) {
	jQuery.ajax({
		data: 'action=record_payment',
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/invoices.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#record-payment-block').html(r);
		}
	})
}
function selectInvoice( pPaymentRefId, pPaymentAmount ) {
	$('#form-edit-payment').show();
	$("input[name='payment_parent_ref_id']").val( pPaymentRefId );
	$("input[name='payment_amount']").val( pPaymentAmount );
}
{/literal}</script>

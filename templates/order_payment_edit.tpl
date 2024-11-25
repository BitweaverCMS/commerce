<input type="hidden" name="action" value="save_payment">
<div class="row">
	<div class="col-sm-6 col-xs-12">
{fieldset legend="Payment Contact" id="form-edit-payment"}
		<div class="form-group">
			{formlabel label="Payment Owner" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="128" name="payment_owner" autocomplete="shipping payment_owner"value="{$payment.payment_owner|default:$smarty.request.payment_owner|default:$address.name|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Company" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="128" name="address_company" autocomplete="shipping address_company"value="{$address.company|default:$smarty.request.address_company|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.street_address}
			{formlabel label="Street Address" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="250" name="address_street_address" autocomplete="shipping address_street_address"value="{$address.street_address|default:$smarty.request.address_street_address|escape:"htmlall"}" required>
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Address Line 2" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="address_suburb" autocomplete="shipping address_suburb"value="{$address.suburb|default:$smarty.request.address_suburb|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.city}
			{formlabel label="City" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="address_city" autocomplete="shipping address_city" value="{$address.city|default:$smarty.request.address_city|escape:"htmlall"}" required>
			{/forminput}
		</div>
<div class="row">
	<div class="col-sm-8">
		<div class="form-group">
			{formlabel label="State/Province" for=""}
			{formfeedback error=$errors.state}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				{if $statePullDown}
				{$statePullDown}
				{else}
				<input class="form-control" type="text" name="address_state" autocomplete="shipping state" value="{$address.state|default:$smarty.request.address_state|escape:"htmlall"}" required>
				{/if}	
			{/forminput}
		</div>
	</div>
	<div class="col-sm-4">
		<div class="form-group">
			{formfeedback error=$errors.address_postcode}
			{formlabel label="Postal Code" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="10" name="address_postcode" autocomplete="shipping address_postcode" value="{$address.postcode|default:$smarty.request.address_postcode|escape:"htmlall"}" required>
			{/forminput}
		</div>
	</div>
</div>
		<div class="form-group">
			{formfeedback error=$errors.country_id}
			{formlabel label="Country" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				{$countryPullDown}
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.payment_message}
			{formlabel label="Payment Message" for=""}
			{forminput}
				<textarea name="comments" rows="4" class="form-control special-instructions">{$payment.payment_message|default:$smarty.request.payment_message|escape}</textarea>
{if $gBitUser->hasPermission('p_commerce_admin')}
				{formhelp note="Private note for store staff."}
{/if}
			{/forminput}
		</div>
{/fieldset}
	</div>
	<div class="col-sm-6 col-xs-12">

<div class="row">
	<div class="col-xs-6">
		<div class="form-group">
			{formfeedback error=$errors.payment_amount}
			{formlabel label="Payment Amount" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput id="charge-amount"}
				{assign var=leftSymbol value=$gCommerceCurrencies->getLeftSymbol( $order->getField('currency') )}
				{assign var=rightSymbol value=$gCommerceCurrencies->getRightSymbol( $order->getField('currency') )}
				<input type="hidden" name="payment_currency" value="{$order->getField('currency', $smarty.const.DEFAULT_CURRENCY )}"/>
				<input type="hidden" name="charge_currency" value="{$order->getField('currency', $smarty.const.DEFAULT_CURRENCY )}"/>
				<input type="hidden" name="exchange_rate" value="{$order->getField('currency_value', 1.0)}"/>
				<div class="input-group">
					{if $leftSymbol}<span class="input-group-addon">{$leftSymbol}</span>{/if}
					<input class="form-control input-sm text-right" type="text" name="payment_amount" value="{$payment.payment_amount|default:$smarty.request.payment_amount|default:$order->getField('amount_due')|escape:"htmlall"}" {if !$gBitUser->hasPermission('p_commerce_admin')}readonly{/if} />
					{if $rightSymbol}<span class="input-group-addon">{$rightSymbol}</span>{/if}
					<span class="input-group-addon">{$order->getField('currency', $smarty.const.DEFAULT_CURRENCY )}</span>
				</div>
			{/forminput}
		</div>
	</div>
	<div class="col-xs-6">
		<div class="form-group">
			{formfeedback error=$errors.payment_date}
			{formlabel label="Date" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<div class="input-group date" id="payment-date">
					<input type="text" class="form-control" name="payment_date" value="{$payment.payment_date|default:time|date_format:'Y-m-d'}" {if !$gBitUser->hasPermission('p_commerce_admin')}readonly{/if}><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
				</div>
				{formhelp note="Date payment was received."}
{if $gBitUser->hasPermission('p_commerce_admin')}
<script>
$('.input-group.date').datepicker({
    format: "yyyy-mm-dd",
    clearBtn: true,
    todayHighlight: true
});
</script>
{/if}
			{/forminput}
		</div>
	</div>
</div>
		{*if $order->getField('currency') && $order->getField('currency') != $smarty.const.DEFAULT_CURRENCY}
		<div class="form-group">
			{formlabel label="Payment Owner" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="3" name="payment_currency" value="{$payment.payment_owner|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Exchange Rate" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="3" name="exchange_rate" value="{$payment.exchange_rate|escape:"htmlall"}" />
			{/forminput}
		</div>
		{/if*}
		<div class="form-group">
			{formfeedback error=$errors.payment_parent_ref_id}
			{formlabel label="Payment Reference ID" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="payment_parent_ref_id" value="{$payment.payment_parent_ref_id|default:$smarty.request.payment_parent_ref_id|escape:"htmlall"}" readonly required>
			{/forminput}
		</div>
{if $gBitUser->hasPermission('p_commerce_admin')}
		<div class="row">
			<div class="col-xs-8">
				<div class="form-group">
					{formfeedback error=$errors.payment_message}
					{formlabel label="Order Status" for=""}
					{forminput}
						{html_options class="form-control" name='status' options=$orderStatuses selected=$smarty.const.DEFAULT_ORDERS_STATUS_ID}
						{formhelp note="Change order status"}
					{/forminput}
				</div>
			</div>
			<div class="col-xs-4">
				<div class="form-group">
					{formfeedback error=$errors.name}
					{formlabel label="Success" for=""}
					{forminput}
						<input type="hidden" name="payment_result" value="i">
						<select name="is_success" class="form-control">
							<option value="y">{tr}Yes{/tr}</option>
							<option value="n">{tr}No{/tr}</option>
						</select>
					{/forminput}
				</div>
			</div>
		</div>
		<div class="form-group">
			<input type="hidden" name="payment_module" value="manual">
			<fieldset>
				<legend {if $selection}class="radio"{/if}>
			{if $selection}
				<input type="radio" name="payment_method" value="manual" {if $smarty.foreach.payment_selection.iteration==1}checked="checked"{/if} onclick="$('.payment-selection').hide();$('#payment-{$selection.id}').show();" /> 
			{else}
				<input type="hidden" name="payment_method" value="manual" />
			{/if}
					{tr}Manual Transaction{/tr}
				</legend>
			<div class="form-group">
				{formfeedback error=$errors.name}
				{formlabel label="Payment Type" for=""}
				{forminput}
					{html_options class="form-control" name="payment_type" options=$paymentTypes}
				{/forminput}
			</div>
			<div class="form-group">
				{formfeedback error=$errors.payment_number}
				{formlabel label="Payment Number" for=""}
				{forminput}
					<input class="form-control" type="text" maxlength="64" name="payment_number" value="{$payment.payment_number|default:$smarty.request.payment_number|escape:"htmlall"}">
					{formhelp note="Check, Cash Deposit, or Credit Card Reference Number"}
				{/forminput}
			</div>
			</fieldset>
		</div>
{/if}
		{if $selection}
		<div>
			<fieldset>
				<legend {if $selection}class="radio"{/if}>
			{if $selection && $gBitUser->hasPermission('p_commerce_admin')}
				<input type="radio" name="payment_method" value="{$selection.id}" {if $smarty.foreach.payment_selection.iteration==1}checked="checked"{/if} onclick="$('.payment-selection').hide();$('#payment-{$selection.id}').show();" /> 
			{else}
				<input type="hidden" name="payment_method" value="{$selection.id}" />
			{/if}
					{$selection.module}
				</legend>
{*	
			{if $gBitUser->hasPermission('p_commerce_admin')}
			{forminput label="checkbox"}
				<input type="checkbox" name="adjust_total" value="y" id="adjust_total"/>{booticon iname="fa-money-check-dollar-pen"} {tr}Adjust Order Total{/tr}
			{/forminput}
			{forminput label="checkbox"}
				<input type="checkbox" name="additional_charge" value="y">{booticon iname="fa fa-cash-register"} {tr}Make Additional Charge{/tr} {tr}<span class="small">(For Credit Card Only)</span>{/tr}
			{/forminput}
			{/if}
*}
				<div class="payment-selection" id="payment-{$selection.id}" {if $smarty.foreach.payment_selection.iteration>1}style="display:none"{/if}>
					{formfeedback error=$selection.error}
				{if $smarty.const.MODULE_ORDER_TOTAL_COD_STATUS == 'true' and $selection.id == 'cod'}
					<div class="alert alert-warning">{tr}<strong>Note:</strong> COD fees may apply{/tr}</div>
				{/if}
				{if $selection.fields && is_array($selection.fields)}
					{foreach from=$selection.fields item=selectionField}
					<div class="form-group{if $selectionField.error} has-error{/if}">
						{if $selectionField.title}<label class="control-label" for="{$selectionField.id}">{$selectionField.title}</label>{/if}
						{$selectionField.field}
						{if $selectionField.error}<div class="help-block">{$selectionField.error}</div>{/if}
					</div>
					{/foreach}
				{/if}
				</div>
			</fieldset>
		</div>
		{/if}
	</div>
</div>

<input type="submit" name="save_payment" value="{tr}Save{/tr}" class="btn btn-primary"> <button class="btn btn-default" onclick="$('#form-edit-payment').hide();return false;">{tr}Cancel{/tr}</button>
		</div>
	</div>
</div>

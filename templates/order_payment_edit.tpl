<input type="hidden" name="action" value="save_payment">
<input type="hidden" name="payment_module" value="manual">
{fieldset legend="Edit Payment"}
<div class="row">
	<div class="col-sm-6 col-xs-12">

<div class="row">
	<div class="col-sm-6">
		<div class="form-group">
			{formfeedback error=$errors.payment_date}
			{formlabel label="Date" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<div class="input-group date" id="payment-date">
					<input type="text" class="form-control" name="payment_date" value="{$payment.payment_date|default:time|date_format:'Y-m-d'}"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
				</div>
				{formhelp note="Date payment was received."}
<script>
$('.input-group.date').datepicker({
    format: "yyyy-mm-dd",
    clearBtn: true,
    todayHighlight: true
});
</script>
			{/forminput}
		</div>
	</div>
	<div class="col-sm-6">
		<div class="form-group">
			{formfeedback error=$errors.name}
			{formlabel label="Success" for=""}
			{forminput}
				<input type="hidden" name="payment_result" value="0">
				<select name="is_success" class="form-control">
					<option value="y">{tr}Yes{/tr}</option>
					<option value="n">{tr}No{/tr}</option>
				</select>
			{/forminput}
		</div>
	</div>
</div>
		<div class="form-group">
			{formlabel label="Payment Owner" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="128" name="payment_owner" value="{$payment.payment_owner|default:$smarty.request.payment_owner|default:$address.name|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.name}
			{formlabel label="Payment Type" for=""}
			{forminput}
				{html_options class="form-control" name="payment_type" options=$paymentTypes}
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.payment_amount}
			{formlabel label="Payment Amount" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput id="charge-amount"}
				{assign var=leftSymbol value=$gCommerceCurrencies->getLeftSymbol( $order->getField('currency') )}
				{assign var=rightSymbol value=$gCommerceCurrencies->getRightSymbol( $order->getField('currency') )}
				<input type="hidden" name="payment_currency" value="{$order->getField('currency', $smarty.const.DEFAULT_CURRENCY )}"/>
				<input type="hidden" name="exchange_rate" value="{$order->getField('currency_value', 1.0)}"/>
				<div class="input-group">
					{if $leftSymbol}<span class="input-group-addon">{$leftSymbol}</span>{/if}
					<input class="form-control input-sm text-right" type="text" name="payment_amount" value="{$payment.payment_amount|default:$smarty.request.payment_amount|default:$order->getField('amount_due')|escape:"htmlall"}" />
					{if $rightSymbol}<span class="input-group-addon">{$rightSymbol}</span>{/if}
				</div>
			{/forminput}
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
<div class="row">
	<div class="col-sm-8">
		<div class="form-group">
			{formfeedback error=$errors.payment_number}
			{formlabel label="Payment Number" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="payment_number" value="{$payment.payment_number|default:$smarty.request.payment_number|escape:"htmlall"}" required>
				{formhelp note="PO, Check, or Credit Card Number"}
			{/forminput}
		</div>
	</div>
	<div class="col-sm-4">
		<div class="form-group">
			{formfeedback error=$errors.payment_expires}
			{formlabel label="Expires" for=""}
			{forminput}
				<input class="form-control" type="number" maxlength="4" name="payment_expires" value="{$payment.payment_expires|default:$smarty.request.payment_expires|escape:"htmlall"}" />
				{formhelp note="YYMM; For cards"}
			{/forminput}
		</div>
	</div>
</div>
		<div class="form-group">
			{formfeedback error=$errors.payment_ref_id}
			{formlabel label="Payment Reference ID" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="payment_ref_id" value="{$payment.payment_ref_id|default:$smarty.request.payment_ref_id|escape:"htmlall"}">
				{formhelp note="Transaction ID used with Card transactions; leave empty unknown"}
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.payment_message}
			{formlabel label="Order Status" for=""}
			{forminput}
				{html_options class="form-control" name='status' options=$orderStatuses selected=$order->getStatus()}
				{formhelp note="Change order status"}
			{/forminput}
			{forminput label="checkbox"}
				<input type="checkbox" name="adjust_total" value="y" id="adjust_total"/>{booticon iname="fa-money-check-dollar-pen"} {tr}Adjust Order Total{/tr}
			{/forminput}
			{forminput label="checkbox"}
				<input type="checkbox" name="additional_charge" value="y">{booticon iname="fa fa-cash-register"} {tr}Make Additional Charge{/tr} {tr}<span class="small">(For Credit Card Only)</span>{/tr}
			{/forminput}
		</div>
	</div>
	<div class="col-sm-6 col-xs-12">
		<div class="form-group">
			{formlabel label="Company" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="128" name="address_company" value="{$address.company|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.street_address}
			{formlabel label="Street Address" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="250" name="address_street_address" value="{$address.street_address|escape:"htmlall"}" required>
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Address Line 2" for=""}
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="address_suburb" value="{$address.suburb|escape:"htmlall"}" />
			{/forminput}
		</div>
		<div class="form-group">
			{formfeedback error=$errors.city}
			{formlabel label="City" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="64" name="address_city" value="{$address.city|escape:"htmlall"}" required>
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
				<input class="form-control" type="text" name="state" value="">
				{/if}	
			{/forminput}
		</div>
	</div>
	<div class="col-sm-4">
		<div class="form-group">
			{formfeedback error=$errors.address_postcode}
			{formlabel label="Postal Code" for=""}<acronym title="{tr}Required{/tr}">*</acronym>
			{forminput}
				<input class="form-control" type="text" maxlength="10" name="address_postcode" value="{$address.postcode|escape:"htmlall"}" required>
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
				{formhelp note="Private note for store staff."}
			{/forminput}
		</div>
	</div>
</div>
<input type="submit" name="save_payment" value="{tr}Save{/tr}" class="btn btn-primary"> <button class="btn btn-default" onclick="this.form.innerHTML='';return false;">{tr}Cancel{/tr}</button>
{/fieldset}

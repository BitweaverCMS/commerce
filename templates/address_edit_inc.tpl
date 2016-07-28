{if $address.address_book_id}
<input type="hidden" name="address_book_id" value="{$address.address_book_id}"/>
{/if}
{formfeedback error=$addressErrors.customers_id}
<div class="row pv-2 display-block">
	<div class="col-xs-12 form-group {if $addressErrors.country_id}error{/if}">
	{formlabel label="<i class='icon-globe'></i> Country" for=""}
		{forminput}
			{$gBitCustomer->getCountryInputHtml($address)}
			{formhelp note=$addressErrors.country_id}
		{/forminput}
	</div>
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_GENDER')}
	<div class="form-group">
		{formfeedback error=$addressErrors.gender}
		{forminput}
			<label class="radio-inline width-auto"><input type="radio" value="m" {if $address.entry_gender == 'm'}checked="checked"{/if} name="gender"/> {tr}Mr.{/tr}</label> &nbsp; <label class="radio-inline width-auto"><input type="radio" {if $address.entry_gender == 'f'}checked="checked"{/if} value="f" name="gender"/> {tr}Ms.{/tr}</label>
		{/forminput}
	</div>
{/if}
<div class="row">
	<div class="col-xs-6 form-group {if $addressErrors.firstname}error{/if}">
		{formlabel label="First Name" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="64" name="firstname" value="{$address.entry_firstname|escape:"htmlall"}" />
			{formhelp note=$addressErrors.firstname}
		{/forminput}
	</div>
	<div class="col-xs-6 form-group {if $addressErrors.lastname}error{/if}">
		{formlabel label="Last Name" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="64" name="lastname" value="{$address.entry_lastname|escape:"htmlall"}" />
			{formhelp note=$addressErrors.lastname}
		{/forminput}
	</div>
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_COMPANY')}
	<div class="form-group">
	{formlabel label="Company" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="128" name="company" value="{$address.entry_company|escape:"htmlall"}" />
	{/forminput}
	</div>
{/if}
<div class="form-group {if $addressErrors.street_address}error{/if}">
	{formlabel label="Street Address" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="250" name="street_address" value="{$address.entry_street_address|escape:"htmlall"}" />
		{formhelp note=$addressErrors.street_address}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_SUBURB')}
<div class="form-group">
	{formlabel label="Address Line 2 (Optional)" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="64" name="suburb" value="{$address.entry_suburb|escape:"htmlall"}" />
	{/forminput}
</div>
{/if}
<div class="form-group {if $addressErrors.city}error{/if}">
	{formlabel label="City" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="64" name="city" value="{$address.entry_city|escape:"htmlall"}" />
		{formhelp note=$addressErrors.city}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_STATE')}
<div class="form-group {if $addressErrors.state}error{/if}">
	{formlabel label="State or Province" for=""}
	{forminput id="addr_state"}
		{$gBitCustomer->getStateInputHtml($address)}
		{formhelp note=$addressErrors.state}
	{/forminput}
</div>
{/if}
<div class="row">
	<div class="col-xs-6 form-group {if $addressErrors.postcode}error{/if}">
		{formlabel label="Postal Code" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="10" name="postcode" value="{$address.entry_postcode|escape:"htmlall"}" />
			{formhelp note=$addressErrors.postcode}
		{/forminput}
	</div>
	<div class="col-xs-6 form-group {if $addressErrors.telephone}error{/if}">
		{formlabel label="Telephone" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="32" name="telephone" value="{$address.entry_telephone|escape:"htmlall"}" />
			{formhelp note=$addressErrors.telephone}
		{/forminput}
	</div>
</div>
<div class="form-group">
	{forminput}
		<label class="checkbox-inline">
			<input type="checkbox" name="primary" values="on" {if $address.entry_primary=='t'}checked="checked"{/if} id="primary"> {tr}Set as Primary Address{/tr}
		<label>
	{/forminput}
</div>

{literal}
<script type="text/javascript">//<![CDATA[
function updateStates( pCountryId ) {
	var ajax = new BitBase.SimpleAjax();
	var donefn = function (r){
		document.getElementById('addr_state').innerHTML = r.responseText;
	};
	
	ajax.connect("{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}{literal}pages/address_book/states.php", "country_id="+pCountryId, donefn, "GET");
}
//]]></script>
{/literal}

{if $address.address_book_id}
<input type="hidden" name="address_book_id" value="{$address.address_book_id}"/>
{/if}
{formfeedback error=$addressErrors.customers_id}
<div class="row pv-1 display-block">
	<div class="col-xs-12 form-group {if $addressErrors.country_id}has-error{/if}">
	{formlabel label="<i class='fa fal fa-globe'></i> Country" for=""}
		{forminput}
			{$gBitCustomer->getCountryInputHtml($address, $sectionName)}
			{formhelp note=$addressErrors.country_id}
		{/forminput}
	</div>
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_GENDER')}
	<div class="form-group">
		{formfeedback error=$addressErrors.gender}
		{forminput}
			<label class="radio-inline width-auto"><input type="radio" value="m" {if $address.entry_gender == 'm'}checked="checked"{/if} autocomplete="{$sectionName} honorific-prefix" name="gender"/> {tr}Mr.{/tr}</label> &nbsp; <label class="radio-inline width-auto"><input type="radio" {if $address.entry_gender == 'f'}checked="checked"{/if} value="f" autocomplete="{$sectionName} honorific-prefix" name="gender"/> {tr}Ms.{/tr}</label>
		{/forminput}
	</div>
{/if}
<div class="row">
	<div class="col-xs-6 form-group {if $addressErrors.firstname}has-error{/if}">
		{formlabel label="First Name" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="64" autocomplete="{$sectionName} given-name" name="firstname" value="{$address.entry_firstname|escape:"htmlall"}" />
			{formhelp note=$addressErrors.firstname}
		{/forminput}
	</div>
	<div class="col-xs-6 form-group {if $addressErrors.lastname}has-error{/if}">
		{formlabel label="Last Name" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="64" autocomplete="{$sectionName} family-name" name="lastname" value="{$address.entry_lastname|escape:"htmlall"}" />
			{formhelp note=$addressErrors.lastname}
		{/forminput}
	</div>
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_COMPANY')}
	<div class="form-group">
	{formlabel label="Company" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="128" autocomplete="{$sectionName} organization" name="company" value="{$address.entry_company|escape:"htmlall"}" />
	{/forminput}
	</div>
{/if}
<div class="form-group {if $addressErrors.street_address}has-error{/if}">
	{formlabel label="Street Address" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="250" autocomplete="{$sectionName} address-line1" name="street_address" value="{$address.entry_street_address|escape:"htmlall"}" />
		{formhelp note=$addressErrors.street_address}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_SUBURB')}
<div class="form-group">
	{formlabel label="Address Line 2 (Optional)" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="64" autocomplete="{$sectionName} address-line2" name="suburb" value="{$address.entry_suburb|escape:"htmlall"}" />
	{/forminput}
</div>
{/if}
<div class="form-group {if $addressErrors.city}has-error{/if}">
	{formlabel label="City" for=""}
	{forminput}
		<input type="text" class="form-control" maxlength="64" autocomplete="{$sectionName} address-level2" name="city" value="{$address.entry_city|escape:"htmlall"}" />
		{formhelp note=$addressErrors.city}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_STATE')}
<div class="form-group {if $addressErrors.state}has-error{/if}">
	{formlabel label="State or Province" for=""}
	{forminput id="addr_state"}
		{$gBitCustomer->getStateInputHtml($address, $sectionName)}
		{formhelp note=$addressErrors.state}
	{/forminput}
</div>
{/if}
<div class="row">
	<div class="col-xs-6 form-group {if $addressErrors.postcode}has-error{/if}">
		{formlabel label="Postal Code" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="10" autocomplete="{$sectionName} postal-code" name="postcode" value="{$address.entry_postcode|escape:"htmlall"}" />
			{formhelp note=$addressErrors.postcode}
		{/forminput}
	</div>
	<div class="col-xs-6 form-group {if $addressErrors.telephone}has-error{/if}">
		{formlabel label="Telephone" for=""}
		{forminput}
			<input type="text" class="form-control" maxlength="32" autocomplete="{$sectionName} tel" name="telephone" value="{$address.entry_telephone|escape:"htmlall"}" />
			{formhelp note=$addressErrors.telephone}
		{/forminput}
	</div>
</div>
<div class="form-group">
	{forminput}
		<label class="checkbox-inline">
			<input type="checkbox" name="primary" values="on" {if $address.entry_primary=='t' || !$address.address_book_id}checked="checked"{/if} id="primary"> {tr}Set as Primary Address{/tr}
		<label>
	{/forminput}
</div>

{literal}
<script>//<![CDATA[
function updateStates( pCountryId ) {
	var ajax = new BitBase.SimpleAjax();
	var donefn = function (r){
		document.getElementById('addr_state').innerHTML = r.responseText;
	};
	
	ajax.connect("{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}{literal}pages/address_book/states.php", "country_id="+pCountryId, donefn, "GET");
}
//]]></script>
{/literal}

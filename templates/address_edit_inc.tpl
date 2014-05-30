{if $address.address_book_id}
<input type="hidden" name="address_book_id" value="{$address.address_book_id}"/>
{/if}
{formfeedback error=$addressErrors.customers_id}
{if $gCommerceSystem->isConfigActive('ACCOUNT_GENDER')}
	<div class="form-group">
		{formfeedback error=$addressErrors.gender}
		{formlabel label="Salutation" for=""}
		{forminput}
			<label class="radio inline-block"><input type="radio" value="m" name="gender"/> {tr}Mr.{/tr}</label> &nbsp; <label class="radio inline-block"><input type="radio" value="f" name="gender"/> {tr}Ms.{/tr}</label>
		{/forminput}
	</div>
{/if}
<div class="form-group {if $addressErrors.firstname}error{/if}">
	{formlabel label="First Name" for=""}
	{forminput}
		<input type="text" maxlength="64" name="firstname" value="{$address.entry_firstname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.firstname}
	{/forminput}
</div>
<div class="form-group {if $addressErrors.lastname}error{/if}">
	{formlabel label="Last Name" for=""}
	{forminput}
		<input type="text" maxlength="64" name="lastname" value="{$address.entry_lastname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.lastname}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_COMPANY')}
	<div class="form-group">
	{formlabel label="Company" for=""}
	{forminput}
		<input type="text" maxlength="128" name="company" value="{$address.entry_company|escape:"htmlall"}" />
	{/forminput}
	</div>
{/if}
<div class="form-group {if $addressErrors.street_address}error{/if}">
	{formlabel label="Street Address" for=""}
	{forminput}
		<input type="text" maxlength="250" name="street_address" value="{$address.entry_street_address|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.street_address}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_SUBURB')}
<div class="form-group">
	{formlabel label="Address Line 2" for=""}
	{forminput}
		<input type="text" maxlength="64" name="suburb" value="{$address.entry_suburb|escape:"htmlall"}" />
	{/forminput}
</div>
{/if}
<div class="form-group {if $addressErrors.city}error{/if}">
	{formlabel label="City" for=""}
	{forminput}
		<input type="text" maxlength="64" name="city" value="{$address.entry_city|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.city}
	{/forminput}
</div>
{if $gCommerceSystem->isConfigActive('ACCOUNT_STATE')}
<div class="form-group {if $addressErrors.state}error{/if}">
	{formlabel label="State/Province" for=""}
	{forminput id="addr_state"}
		{$gBitCustomer->getStateInputHtml($address)}<acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.state}
	{/forminput}
</div>
{/if}
<div class="form-group {if $addressErrors.postcode}error{/if}">
	{formlabel label="Postal Code" for=""}
	{forminput}
		<input type="text" maxlength="10" name="postcode" value="{$address.entry_postcode|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.postcode}
	{/forminput}
</div>
<div class="form-group {if $addressErrors.country_id}error{/if}">
	{formlabel label="Country" for=""}
	{forminput}
		{$gBitCustomer->getCountryInputHtml($address)}<acronym title="{tr}Required{/tr}">*</acronym>
		{formhelp note=$addressErrors.country_id}
	{/forminput}

</div>
<div class="form-group {if $addressErrors.telephone}error{/if}">
	{formlabel label="Telephone" for=""}
	{forminput}
		<input type="text" maxlength="32" name="telephone" value="{$address.entry_telephone|escape:"htmlall"}" />
		{formhelp note=$addressErrors.telephone}
	{/forminput}
</div>
<div class="form-group">
	{forminput}
		<label class="checkbox">
			{html_checkboxes name="primary" values="on" checked=$address.entry_primary labels=false id="primary"} {tr}Set as Primary Address{/tr}
		<label>
	{/forminput}
</div>

{literal}
<script type="text/javascript">//<![CDATA[
function updateStates( pCountryId ) {
	var ajax = new BitBase.SimpleAjax();
	var donefn = function (r){
		BitBase.hideSpinner();	
		document.getElementById('addr_state').innerHTML = r.responseText;
	};
	
	ajax.connect("{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}{literal}pages/address_book/states.php", "country_id="+pCountryId, donefn, "GET");
}
//]]></script>
{/literal}
{tr}<span class="inputrequirement">* Required information</span>{/tr}

{tr}<span class="inputrequirement">* Required information</span>{/tr}
{formfeedback error=$errors.customers_id}
{if $collectGender}
	<div class="control-group">
		{formfeedback error=$errors.gender}
		{formlabel label="Salutation" for=""}
		{forminput}
			{html_radios values="m" output="{tr}Mr.{/tr}" name="gender" checked=$address.entry_gender}
			{html_radios values="f" output="{tr}Ms.{/tr}" name="gender" checked=$address.entry_gender}<acronym title="{tr}Required{/tr}">*</acronym>
		{/forminput}
	</div>
{/if}
<div class="control-group">
	{formfeedback error=$errors.firstname}
	{formlabel label="First Name" for=""}
	{forminput}
		<input type="text" maxlength="64" name="firstname" value="{$address.entry_firstname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="control-group">
	{formfeedback error=$errors.lastname}
	{formlabel label="Last Name" for=""}
	{forminput}
		<input type="text" maxlength="64" name="lastname" value="{$address.entry_lastname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectCompany}
	<div class="control-group">
	{formlabel label="Company" for=""}
	{forminput}
		<input type="text" maxlength="128" name="company" value="{$address.entry_company|escape:"htmlall"}" />
	{/forminput}
	</div>
{/if}
<div class="control-group">
	{formfeedback error=$errors.street_address}
	{formlabel label="Street Address" for=""}
	{forminput}
		<input type="text" maxlength="250" name="street_address" value="{$address.entry_street_address|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectSuburb}
<div class="control-group">
	{formlabel label="Address Line 2" for=""}
	{forminput}
		<input type="text" maxlength="64" name="suburb" value="{$address.entry_suburb|escape:"htmlall"}" />
	{/forminput}
</div>
{/if}
<div class="control-group">
	{formfeedback error=$errors.city}
	{formlabel label="City" for=""}
	{forminput}
		<input type="text" maxlength="64" name="city" value="{$address.entry_city|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectState}
<div class="control-group">
	{formlabel label="State/Province" for=""}
	{formfeedback error=$errors.state}
	{forminput id="addr_state"}
		{$stateInput}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{/if}
<div class="control-group">
	{formfeedback error=$errors.postcode}
	{formlabel label="Postal Code" for=""}
	{forminput}
		<input type="text" maxlength="10" name="postcode" value="{$address.entry_postcode|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="control-group">
	{formfeedback error=$errors.country_id}
	{formlabel label="Country" for=""}
	{forminput}
		{$countryPullDown}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}

</div>
<div class="control-group">
	{formfeedback error=$errors.telephone}
	{formlabel label="Telephone" for=""}
	{forminput}
		<input type="text" maxlength="32" name="telephone" value="{$address.entry_telephone|escape:"htmlall"}" />
	{/forminput}
</div>
{if $primaryCheck}
<div class="control-group">
	{formlabel label="Set as Primary Address" for="" }
	{forminput}
		{html_checkboxes name="primary" values="on" checked=$address.entry_primary labels=false id="primary"}
	{/forminput}
</div>
{/if}

{literal}
<script type="text/javascript">//<![CDATA[
function updateStates( pCountryId ) {
	var ajax = new BitBase.SimpleAjax();
	var donefn = function (r){
		BitBase.hideSpinner();	
		document.getElementById('addr_state').innerHTML = r.responseText;
	};
	
	ajax.connect("{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}{literal}pages/address_new/states.php", "country_id="+pCountryId, donefn, "GET");
}
//]]></script>
{/literal}

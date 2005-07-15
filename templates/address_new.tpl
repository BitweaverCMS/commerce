{tr}<span class="inputrequirement">* Required information</span>{/tr}
{formfeedback error=$errors.customers_id}
<fieldset>
{if $collectGender}
	<div class="row">
		{formfeedback error=$errors.gender}
		{formlabel label="Salutation" for=""}
		{forminput}
			{html_radios values="m" output="{tr}Mr.{/tr}" name="gender" checked=$address.entry_gender}
			{html_radios values="f" output="{tr}Ms.{/tr}" name="gender" checked=$address.entry_gender}<acronym title="{tr}Required{/tr}">*</acronym>
		{/forminput}
	</div>
{/if}
<div class="row">
	{formfeedback error=$errors.firstname}
	{formlabel label="First Name" for=""}
	{forminput}
		<input type="text" maxlength="255" name="firstname" value="{$address.entry_firstname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="row">
	{formfeedback error=$errors.lastname}
	{formlabel label="Last Name" for=""}
	{forminput}
		<input type="text" maxlength="255" name="lastname" value="{$address.entry_lastname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectCompany}
	<div class="row">
	{formlabel label="Company" for=""}
	{forminput}
		<input type="text" maxlength="255" name="company" value="{$address.entry_company|escape:"htmlall"}" />
	{/forminput}
	</div>
{/if}
<div class="row">
	{formfeedback error=$errors.street_address}
	{formlabel label="Street Address" for=""}
	{forminput}
		<input type="text" maxlength="255" name="street_address" value="{$address.entry_street_address|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectSuburb}
<div class="row">
	{formlabel label="Address Line 2" for=""}
	{forminput}
		<input type="text" maxlength="255" name="suburb" value="{$address.entry_suburb|escape:"htmlall"}" />
	{/forminput}
</div>
{/if}
<div class="row">
	{formfeedback error=$errors.city}
	{formlabel label="City" for=""}
	{forminput}
		<input type="text" maxlength="255" name="city" value="{$address.entry_city|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectState}
<div class="row">
	{formlabel label="State" for=""}
	{formfeedback error=$errors.state}
	{forminput}
		{$statePullDown}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{/if}
<div class="row">
	{formfeedback error=$errors.postcode}
	{formlabel label="Post Code:" for=""}
	{forminput}
		<input type="text" maxlength="255" name="postcode" value="{$address.entry_postcode|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="row">
	{formfeedback error=$errors.country_id}
	{formlabel label="Country" for=""}
	{forminput}
		{$countryPullDown}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}

</div>
{if $primaryCheck}
<div class="row">
	{formlabel label="Set as Primary Address" for="" }
	{forminput}
		{html_checkboxes name="primary" values="on" checked=$address.entry_primary labels=false id="primary"}
	{/forminput}
</div>
{/if}
</fieldset>


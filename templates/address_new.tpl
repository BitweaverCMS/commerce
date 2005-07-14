{tr}<span class="inputrequirement">* Required information</span>{/tr}
<fieldset>
{if $collectGender}
	<div class="row">
		{formlabel label="Salutation" for=""}
		{formfeedback error=$errors.gender}
		{forminput}
			{html_radios values="m" output="{tr}Mr.{/tr}" name="gender" checked=$entry.gender}
			{html_radios values="f" output="{tr}Ms.{/tr}" name="gender" checked=$entry.gender}<acronym title="{tr}Required{/tr}">*</acronym>
		{/forminput}
	</div>
{/if}
<div class="row">
	{formlabel label="First Name" for=""}
	{formfeedback error=$errors.firstname}
	{forminput}
		<input type="text" maxlength="255" name="firstname" value="{$entry.firstname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="row">
	{formlabel label="Last Name" for=""}
	{formfeedback error=$errors.lastname}
	{forminput}
		<input type="text" maxlength="255" name="lastname" value="{$entry.lastname|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectCompany}
	<div class="row">
	{formlabel label="Company" for=""}
	{forminput}
		<input type="text" maxlength="255" name="company" value="{$entry.company|escape:"htmlall"}" />
	{/forminput}
	</div>
{/if}
<div class="row">
	{formlabel label="Street Address" for=""}
	{formfeedback error=$errors.street_address}
	{forminput}
		<input type="text" maxlength="255" name="street_address" value="{$entry.street_address|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
{if $collectSuburb}
<div class="row">
	{formlabel label="Address Line 2" for=""}
	{forminput}
		<input type="text" maxlength="255" name="suburb" value="{$entry.suburb|escape:"htmlall"}" />
	{/forminput}
</div>
{/if}
<div class="row">
	{formlabel label="City" for=""}
	{formfeedback error=$errors.city}
	{forminput}
		<input type="text" maxlength="255" name="city" value="{$entry.city|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
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
	{formlabel label="Post Code:" for=""}
	{formfeedback error=$errors.postcode}
	{forminput}
		<input type="text" maxlength="255" name="postcode" value="{$entry.postcode|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="row">
	{formlabel label="Country" for=""}
	{formfeedback error=$errors.country}
	{forminput}
		{$countryPullDown}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}

</div>
{if $primaryCheck}
<div class="row">
	{formlabel label="Set as Primary Address" for="" }
	{forminput}
		{html_checkboxes name="primary" values="on" checked=$entry.primary labels=false id="primary"}
	{/forminput}
</div>
{/if}
</fieldset>


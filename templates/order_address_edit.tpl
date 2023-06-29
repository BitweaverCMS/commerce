<form method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$smarty.request.oID}">
{tr}<span class="inputrequirement">* Required information</span>{/tr}
<input type="hidden" name="address_type" value="{$smarty.request.address_type}">
<input type="hidden" name="action" value="save_address">
{formfeedback error=$errors.customers_id}
<fieldset>
<div class="form-group">
	{formfeedback error=$errors.name}
	{formlabel label="Name" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="64" name="name" value="{$address.name|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
	<div class="form-group">
	{formlabel label="Company" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="128" name="company" value="{$address.company|escape:"htmlall"}" />
	{/forminput}
	</div>
<div class="form-group">
	{formfeedback error=$errors.street_address}
	{formlabel label="Street Address" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="250" name="street_address" value="{$address.street_address|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="form-group">
	{formlabel label="Address Line 2" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="64" name="suburb" value="{$address.suburb|escape:"htmlall"}" />
	{/forminput}
</div>
<div class="form-group">
	{formfeedback error=$errors.city}
	{formlabel label="City" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="64" name="city" value="{$address.city|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="form-group">
	{formlabel label="State/Province" for=""}
	{formfeedback error=$errors.state}
	{forminput}
		{$statePullDown}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="form-group">
	{formfeedback error=$errors.postcode}
	{formlabel label="Postal Code" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="10" name="postcode" value="{$address.postcode|escape:"htmlall"}" /><acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}
</div>
<div class="form-group">
	{formfeedback error=$errors.country_id}
	{formlabel label="Country" for=""}
	{forminput}
		{$countryPullDown}<acronym title="{tr}Required{/tr}">*</acronym>
	{/forminput}

</div>
<div class="form-group">
	{formfeedback error=$errors.telephone}
	{formlabel label="Telephone" for=""}
	{forminput}
		<input class="form-control" type="text" maxlength="32" name="telephone" value="{$address.telephone|escape:"htmlall"}" />
	{/forminput}
</div>
<div class="form-group submit">
	<input class="btn btn-default" type="submit" value="save" name="save_address" />
</div>
</fieldset>
</form>

<div class="page-header">
	<h1>
		{tr}Export Customers{/tr}
	</h1>
</div>

<div class="body customerexport">
{literal}
<style type="text/css">
.row .formlabel {text-align:right; width:14em;float:left; }
.row .forminput {margin-left: 15em;}
</style>
{/literal}

{form}
<div class="control-group">
	{formlabel label="First Name"}
	{forminput}
		<input type="checkbox" name="firstname" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Last Name"}
	{forminput}
		<input type="checkbox" name="lastname" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Company"}
	{forminput}
		<input type="checkbox" name="company" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Street Address"}
	{forminput}
		<input type="checkbox" name="street_address" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="City"}
	{forminput}
		<input type="checkbox" name="city" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="State"}
	{forminput}
		<input type="checkbox" name="state" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Postal Code"}
	{forminput}
		<input type="checkbox" name="zip" value="y" checked="checked" />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Country"}
	{forminput}
		<input type="checkbox" name="country" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Customer Id"}
	{forminput}
		<input type="checkbox" name="customers_id" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Registration Date"}
	{forminput}
		<input type="checkbox" name="registration_date" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="First Purchase Date"}
	{forminput}
		<input type="checkbox" name="first_purchase_date" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Last Purchase Date"}
	{forminput}
		<input type="checkbox" name="last_purchase_date" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Number of Purchases"}
	{forminput}
		<input type="checkbox" name="num_purchases" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Total Revenue"}
	{forminput}
		<input type="checkbox" name="total_revenue" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Content Created Count"}
	{forminput}
		<input type="checkbox" name="content_count" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
{if $gBitSystem->isPackageActive('stats')}
<div class="control-group">
	{formlabel label="Referrer URL"}
	{forminput}
		<input type="checkbox" name="referer_url" value="y" checked="checked" />
		{formhelp note=""}
	{/forminput}
</div>
{/if}
<div class="control-group">
	{formlabel label="Registration Interests"}
	{forminput}
		<input type="checkbox" name="interests" value="y" {if $gBitSystem->isFeatureActive('commerce_register_interests')}checked="checked"{/if} />
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Number of Records"}
	{forminput}
		<input type="text" name="num_records" />
		{formhelp note="Leave empty to export all records"}
	{/forminput}
</div>
<div class="control-group">
	{formlabel label="Format"}
	{forminput}
		CSV
		{formhelp note=""}
	{/forminput}
</div>
<div class="control-group submit">
	{forminput}
		<input type="submit" value="{tr}Export{/tr}" name="export"/>
	{/forminput}
</div>
{/form}

</div>


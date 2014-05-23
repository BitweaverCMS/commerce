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
	<label class="checkbox">
		<input type="checkbox" name="firstname" value="y" checked="checked" /> {tr}First Name{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="lastname" value="y" checked="checked" /> {tr}Last Name{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="company" value="y" checked="checked" /> {tr}Company{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="street_address" value="y" checked="checked" /> {tr}Street Address{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="city" value="y" checked="checked" /> {tr}City{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="state" value="y" checked="checked" /> {tr}State{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="zip" value="y" checked="checked" /> {tr}Postal Code{/tr}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="country" value="y" checked="checked" />Country
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="customers_id" value="y" checked="checked" />Customer Id
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="registration_date" value="y" checked="checked" />Registration Date
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="first_purchase_date" value="y" checked="checked" />First Purchase Date
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="last_purchase_date" value="y" checked="checked" />Last Purchase Date
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="num_purchases" value="y" checked="checked" />Number of Purchases
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="total_revenue" value="y" checked="checked" />Total Revenue
		{formhelp note=""}
	</label>
</div>
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="content_count" value="y" checked="checked" />Content Created Count
		{formhelp note=""}
	</label>
</div>
{if $gBitSystem->isPackageActive('stats')}
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="referer_url" value="y" checked="checked" />Referrer URL
		{formhelp note=""}
	</label>
</div>
{/if}
<div class="control-group">
	<label class="checkbox">
		<input type="checkbox" name="interests" value="y" {if $gBitSystem->isFeatureActive('commerce_register_interests')}checked="checked"{/if} /> {tr}Registration Interests{/tr}
	</label>
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
		<input type="submit" class="btn btn-default" value="{tr}Export{/tr}" name="export"/>
	{/forminput}
</div>
{/form}

</div>


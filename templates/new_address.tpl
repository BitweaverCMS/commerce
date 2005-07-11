<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: new_address.tpl,v 1.1 2005/07/11 07:11:39 spiderr Exp $
//
?>
{tr}<span class="inputrequirement">* Required information</span>{/tr}
<fieldset>
{if $collectGender}
	<div class="row">
		{formlabel label="Salutation" for=""}
		{forminput}
			{html_radios values="m" output="{tr}Mr.{/tr}" name="gender" checked=$entry.gender}
			{html_radios values="f" output="{tr}Ms.{/tr}" name="gender" checked=$entry.gender}<span class="inputrequirement">*</span>
		{/forminput}
	</div>
{/if}
<div class="row">
	{formlabel label="First Name" for=""}
	{forminput}
		<input type="text" maxlength="255" name="firstname" value="{$entry.firstname|escape:"htmlall"}" /><span class="inputrequirement">*</span>
	{/forminput}
</div>
<div class="row">
	{formlabel label="Last Name" for=""}
	{forminput}
		<input type="text" maxlength="255" name="lastname" value="{$entry.lastname|escape:"htmlall"}" /><span class="inputrequirement">*</span>
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
	{forminput}
		<input type="text" maxlength="255" name="street_address" value="{$entry.street_address|escape:"htmlall"}" /><span class="inputrequirement">*</span>
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
	{forminput}
		<input type="text" maxlength="255" name="city" value="{$entry.city|escape:"htmlall"}" /><span class="inputrequirement">*</span>
	{/forminput}
</div>
{if $collectState}
<div class="row">
	{formlabel label="State" for=""}
	{forminput}
		{$statePullDown}<span class="inputrequirement">*</span>
	{/forminput}
</div>
{/if}
<div class="row">
	{formlabel label="Post Code:" for=""}
	{forminput}
		<input type="text" maxlength="255" name="postcode" value="{$entry.postcode|escape:"htmlall"}" /><span class="inputrequirement">*</span>
	{/forminput}
</div>
<div class="row">
	{formlabel label="Country" for=""}
	{forminput}
		{$countryPullDown}<span class="inputrequirement">*</span>
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

{* Customer admin: commerce admin header, customer header with account links and sales standing, account details and addresses, saved purchase options. *}
{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

<div class="admin bitcommerce customer-edit">

<div class="customer-head">
	<div class="customer-identity">
		<h1 class="customer-path"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/customers.php">{tr}Customers{/tr}</a> <span class="customer-path-sep">&raquo;</span> {displayname hash=$customerUser} <span class="customer-id">({$cInfo->customers_id})</span></h1>
		<div class="customer-meta">
			<a href="mailto:{$cInfo->customers_email_address|escape}">{$cInfo->customers_email_address|escape}</a>
			{if $cInfo->customers_telephone}<span>{$cInfo->customers_telephone|escape}</span>{/if}
		</div>
		{if $customerStats.orders_count}
		<div class="customer-standing">
			{if $customerStats.orders_count == 1}
				<a href="list_orders.php?user_id={$cInfo->customers_id}&amp;orders_status_id=all&amp;list_filter=all">{tr}First order{/tr}</a>
			{else}
				<strong>{tr}Tier{/tr} {$customerStats.tier|default:0|round}</strong>
				<a href="list_orders.php?user_id={$cInfo->customers_id}&amp;orders_status_id=all&amp;list_filter=all">{$customerStats.orders_count} {tr}orders{/tr}, {$gCommerceCurrencies->format($customerStats.customers_total|default:0, false)}</a>
				{if $customerStats.customers_age}{tr}over{/tr} {$customerStats.customers_age|escape}{/if}
			{/if}
			{if $customerStats.gifts_balance|default:0|round:2}<span class="customer-gift">{tr}Gift balance{/tr} {$gCommerceCurrencies->format($customerStats.gifts_balance, false)}</span>{/if}
		</div>
		{if $customerStats.negative_orders}
		<div class="alert alert-danger customer-negative">
			{foreach from=$customerStats.negative_orders key=status item=negCount}
				<a href="list_orders.php?user_id={$cInfo->customers_id}">{$negCount} {$status|escape}</a>
			{/foreach}
		</div>
		{/if}
		{/if}
	</div>
	<div class="customer-links">
		<a href="list_orders.php?user_id={$cInfo->customers_id}" title="{tr}Sales history{/tr}"><i class="fa fal fa-clock"></i></a>
		<a href="{$smarty.const.LIBERTY_PKG_URL}list_content.php?user_id={$cInfo->customers_id}" title="{tr}User content{/tr}"><i class="fa fal fa-list"></i></a>
		<a href="{$smarty.const.USERS_PKG_URL}preferences.php?view_user={$cInfo->customers_id}" title="{tr}Edit user{/tr}"><i class="fa fal fa-pen-to-square"></i></a>
		<a href="{$smarty.const.USERS_PKG_URL}admin/user_activity.php?user_id={$cInfo->customers_id}" title="{tr}User activity{/tr}"><i class="fa fal fa-bolt"></i></a>
		<a href="{$smarty.const.USERS_PKG_URL}admin/index.php?assume_user={$cInfo->customers_id}" title="{tr}Assume user identity{/tr}"><i class="fa fal fa-user-doctor"></i></a>
	</div>
</div>

<div class="row customer-columns">
<div class="col-md-6">
<section class="customer-section customer-account" id="customer-account">
	<h2>{tr}Account{/tr} <a href="#customer-account-form" class="customer-edit-toggle" role="button" title="{tr}Edit account{/tr}" aria-expanded="{if $customerErrors}true{else}false{/if}" aria-controls="customer-account-form"><i class="fa fal fa-pen-to-square"></i><span class="sr-only">{tr}Edit account{/tr}</span></a></h2>
	{if $customerErrors}
	<div class="alert alert-danger customer-errors">
		<ul>
		{foreach from=$customerErrors item=err}
			<li>{$err|escape}</li>
		{/foreach}
		</ul>
	</div>
	{/if}
	<dl class="customer-account-view">
		<dt>{tr}Authorization{/tr}</dt><dd>{$customerAuth[$cInfo->customers_authorization]|default:'-'|escape}</dd>
		<dt>{tr}Discount pricing group{/tr}</dt><dd>{$groupPricing[$cInfo->customers_group_pricing]|default:'None'|escape}</dd>
		{if $gCommerceSystem->isConfigActive('ACCOUNT_GENDER')}
		<dt>{tr}Gender{/tr}</dt><dd>{if $cInfo->customers_gender=='f'}{tr}Female{/tr}{elseif $cInfo->customers_gender=='m'}{tr}Male{/tr}{else}-{/if}</dd>
		{/if}
		{if $gCommerceSystem->isConfigActive('ACCOUNT_DOB')}
		<dt>{tr}Date of birth{/tr}</dt><dd>{$cInfo->customers_dob|zen_date_short|default:'-'}</dd>
		{/if}
	</dl>
	<div id="customer-account-form" class="collapse{if $customerErrors} in{/if}">
	{form name="customer" method="post" action="`$smarty.server.SCRIPT_NAME`?user_id=`$cInfo->customers_id`" class="customer-account-form"}
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="default_address_id" value="{$cInfo->customers_default_address_id}">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="customers_authorization">{tr}Authorization{/tr}</label>
				{html_options class="form-control" id="customers_authorization" name="customers_authorization" options=$customerAuth selected=$cInfo->customers_authorization}
			</div>
			<div class="col-sm-6 form-group">
				<label for="customers_group_pricing">{tr}Discount pricing group{/tr}</label>
				{html_options class="form-control" id="customers_group_pricing" name="customers_group_pricing" options=$groupPricing selected=$cInfo->customers_group_pricing}
			</div>
		</div>
		{if $gCommerceSystem->isConfigActive('ACCOUNT_GENDER') || $gCommerceSystem->isConfigActive('ACCOUNT_DOB')}
		<div class="row">
			{if $gCommerceSystem->isConfigActive('ACCOUNT_GENDER')}
			<div class="col-sm-6 form-group">
				<label>{tr}Gender{/tr}</label>
				<div>
					<label class="radio-inline"><input type="radio" name="customers_gender" value="f" {if $cInfo->customers_gender=='f'}checked{/if}/> {tr}Female{/tr}</label>
					<label class="radio-inline"><input type="radio" name="customers_gender" value="m" {if $cInfo->customers_gender=='m'}checked{/if}/> {tr}Male{/tr}</label>
				</div>
			</div>
			{/if}
			{if $gCommerceSystem->isConfigActive('ACCOUNT_DOB')}
			<div class="col-sm-6 form-group">
				<label for="customers_dob">{tr}Date of birth{/tr}</label>
				<input type="text" class="form-control" id="customers_dob" name="customers_dob" value="{$cInfo->customers_dob|zen_date_short}"/>
			</div>
			{/if}
		</div>
		{/if}
		<div class="customer-actions">
			<input type="submit" class="btn btn-primary btn-sm" name="Save" value="{tr}Save changes{/tr}"/>
		</div>
	{/form}
	</div>
</section>

<section class="customer-section customer-addresses">
	<h2>{tr}Addresses{/tr}</h2>
	{if $addressErrors}
	<div class="alert alert-danger customer-errors">
		<ul>
		{foreach from=$addressErrors item=err}
			<li>{$err|escape}</li>
		{/foreach}
		</ul>
	</div>
	{/if}
	{foreach from=$customerAddresses item=addr}
	{if $addr.address_book_id == $addressEditId}{assign var=f value=$addressPosted}{else}{assign var=f value=$addr}{/if}
	<div class="customer-address{if $addr.entry_primary} is-default{/if}" id="address-{$addr.address_book_id}">
		<div class="customer-address-card">
			<div class="customer-address-text">
				{if $addr.company}<div class="customer-address-company">{$addr.company|escape}</div>{/if}
				<div>{$addr.firstname|escape} {$addr.lastname|escape}</div>
				<div>{$addr.street_address|escape}</div>
				{if $addr.suburb}<div>{$addr.suburb|escape}</div>{/if}
				<div>{$addr.city|escape}{if $addr.zone_name}, {$addr.zone_name|escape}{/if} {$addr.postcode|escape}</div>
				<div>{$addr.countries_name|escape}</div>
				{if $addr.telephone}<div class="customer-address-phone">{$addr.telephone|escape}</div>{/if}
			</div>
			<div class="customer-address-tools">
				{if $addr.entry_primary}<span class="customer-address-default">{tr}Default{/tr}</span>{/if}
				<a href="#address-form-{$addr.address_book_id}" class="btn btn-link btn-xs" data-toggle="collapse" aria-expanded="{if $addr.address_book_id == $addressEditId}true{else}false{/if}" aria-controls="address-form-{$addr.address_book_id}"><i class="fa fal fa-pen-to-square"></i> {tr}Edit{/tr}</a>
			</div>
		</div>
		<div id="address-form-{$addr.address_book_id}" class="collapse{if $addr.address_book_id == $addressEditId} in{/if} customer-address-form">
			{form method="post" action="`$smarty.server.SCRIPT_NAME`?user_id=`$cInfo->customers_id`"}
				<input type="hidden" name="action" value="address">
				<input type="hidden" name="address_book_id" value="{$addr.address_book_id}">
				<div class="row">
					<div class="col-sm-6 form-group">
						<label for="address-{$addr.address_book_id}-firstname">{tr}First name{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-firstname" name="firstname" value="{$f.firstname|escape}"/>
					</div>
					<div class="col-sm-6 form-group">
						<label for="address-{$addr.address_book_id}-lastname">{tr}Last name{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-lastname" name="lastname" value="{$f.lastname|escape}"/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12 form-group">
						<label for="address-{$addr.address_book_id}-company">{tr}Company{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-company" name="company" value="{$f.company|escape}"/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-7 form-group">
						<label for="address-{$addr.address_book_id}-street">{tr}Street address{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-street" name="street_address" value="{$f.street_address|escape}"/>
					</div>
					<div class="col-sm-5 form-group">
						<label for="address-{$addr.address_book_id}-suburb">{tr}Suite or unit{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-suburb" name="suburb" value="{$f.suburb|escape}"/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5 form-group">
						<label for="address-{$addr.address_book_id}-city">{tr}City{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-city" name="city" value="{$f.city|escape}"/>
					</div>
					<div class="col-sm-4 form-group">
						<label for="address-{$addr.address_book_id}-state">{tr}State{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-state" name="state" value="{$f.state|escape}"/>
					</div>
					<div class="col-sm-3 form-group">
						<label for="address-{$addr.address_book_id}-postcode">{tr}Post code{/tr}</label>
						<input type="text" class="form-control input-sm" id="address-{$addr.address_book_id}-postcode" name="postcode" value="{$f.postcode|escape}"/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-7 form-group">
						<label for="address-{$addr.address_book_id}-country">{tr}Country{/tr}</label>
						{html_options class="form-control input-sm" id="address-`$addr.address_book_id`-country" name="country_id" options=$countryChoices selected=$f.country_id}
					</div>
					<div class="col-sm-5 form-group">
						<label for="address-{$addr.address_book_id}-telephone">{tr}Telephone{/tr}</label>
						<input type="tel" class="form-control input-sm" id="address-{$addr.address_book_id}-telephone" name="telephone" value="{$f.telephone|escape}"/>
					</div>
				</div>
				<div class="customer-address-actions">
					<button type="submit" class="btn btn-primary btn-sm">{tr}Save address{/tr}</button>
					<a href="#address-form-{$addr.address_book_id}" class="btn btn-link btn-sm" data-toggle="collapse">{tr}Cancel{/tr}</a>
				</div>
			{/form}
		</div>
	</div>
	{foreachelse}
	<p class="customer-empty">{tr}No saved addresses. The customer adds one at checkout.{/tr}</p>
	{/foreach}
</section>
</div>
<div class="col-md-6">
<section class="customer-section">
	<h2><i class="fa fal fa-cart-plus"></i> {tr}Preferred Purchase Options{/tr} <a href="#purchase-options-help" class="customer-help-toggle" data-toggle="collapse" aria-expanded="false" aria-controls="purchase-options-help" title="{tr}About purchase options{/tr}"><i class="fa fal fa-circle-question"></i><span class="sr-only">{tr}About purchase options{/tr}</span></a>
		<a href="#purchase-option-add-form" class="btn btn-default btn-xs purchase-option-add-toggle pull-right" role="button" data-toggle="collapse" aria-expanded="false" aria-controls="purchase-option-add-form"><i class="fa fal fa-plus"></i> {tr}Add{/tr}</a></h2>
	<div id="purchase-options-help" class="collapse customer-help">
		<p>{tr}Standing choices this customer wants on every order. Each product page they open pre-selects these when the product offers them, and the order transfer tab warns when a line was bought without one.{/tr}</p>
	</div>
	{include file="bitpackage:bitcommerce/admin_purchase_options_inc.tpl"}
</section>

</div>
</div>

{literal}
<script>/* <![CDATA[ */
function purchaseOptionsUrl() {
	return "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/customers.php?user_id={$smarty.request.user_id|escape:'url'}{literal}";
}
function purchaseOptionAddState() {
	var ready = jQuery('#purchase-option-id').val() && jQuery('#purchase-option-value').val();
	jQuery('#purchase-option-add').prop('disabled', !ready);
}
jQuery(document).on('change', '#purchase-option-id', function() {
	var optionId = jQuery(this).val();
	var $value = jQuery('#purchase-option-value');
	$value.empty().prop('disabled', true);
	purchaseOptionAddState();
	if( !optionId ) {
		return;
	}
	jQuery.ajax({
		url: purchaseOptionsUrl(),
		data: { purchase_option_values: 1, products_options_id: optionId },
		timeout: 60000,
		success: function(r) {
			$value.html(r).prop('disabled', false);
			purchaseOptionAddState();
		}
	});
});
jQuery(document).on('change', '#purchase-option-value', purchaseOptionAddState);
jQuery(document).on('click', '.customer-edit-toggle', function(e) {
	e.preventDefault();
	var $section = jQuery(this).closest('.customer-section');
	var open = !$section.hasClass('is-editing');
	$section.toggleClass('is-editing', open);
	jQuery(this).attr('aria-expanded', open ? 'true' : 'false');
	jQuery($section.find('.collapse')).collapse(open ? 'show' : 'hide');
});
jQuery(function() {
	if( jQuery('#customer-account-form').hasClass('in') ) {
		jQuery('#customer-account').addClass('is-editing');
	}
});
jQuery(document).on('click', '#purchase-option-add', function() {
	var optionId = jQuery('#purchase-option-id').val();
	var valueId = jQuery('#purchase-option-value').val();
	if( !optionId || !valueId ) {
		return;
	}
	jQuery.ajax({
		url: purchaseOptionsUrl(),
		data: { save_purchase_option: 1, products_options_id: optionId, products_options_values_id: valueId },
		timeout: 60000,
		success: function(r) {
			jQuery('#purchase-options').replaceWith(r);
			jQuery('.purchase-option-add-toggle').attr('aria-expanded', 'false');
		}
	});
});
jQuery(document).on('click', '.purchase-option-remove', function() {
	jQuery.ajax({
		url: purchaseOptionsUrl(),
		data: { remove_purchase_option: 1, products_options_id: jQuery(this).data('option-id'), products_options_values_id: jQuery(this).data('value-id') },
		timeout: 60000,
		success: function(r) {
			jQuery('#purchase-options').replaceWith(r);
			jQuery('.purchase-option-add-toggle').attr('aria-expanded', 'false');
		}
	});
});
/* ]]> */</script>
{/literal}
</div>

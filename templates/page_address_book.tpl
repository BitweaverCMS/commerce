<div class="address-book">
<header class="page-header">
	<h1>{tr}Address book{/tr} {if $addresses}<small>{$addresses|@count} {tr}people{/tr}</small>{/if}</h1>
	<p class="lead">{tr}People you ship to. Pick them again at checkout or when sending to multiple addresses.{/tr}</p>
</header>

{$messageStack->output('addressbook')}
{formfeedback hash=$feedback}

{if $addressMapping}
	{form name='address_map' id='address-map' action="`$smarty.const.BITCOMMERCE_PKG_URL`address_book" method='post'}
	<div class="panel panel-info address-book-map">
		<div class="panel-heading"><h2 class="panel-title">{tr}Match the columns in{/tr} {$addressMapping.file|escape}</h2></div>
		<div class="panel-body">
			{include file="bitpackage:bitcommerce/address_import_map_inc.tpl" mapping=$addressMapping mapSubmitName="import_map"}
			<button type="submit" class="btn btn-primary" name="import_map" value="apply">{tr}Import with These Columns{/tr}</button>
			<button type="submit" class="btn btn-link" name="import_map" value="cancel">{tr}Cancel{/tr}</button>
		</div>
	</div>
	{/form}
{/if}

<div class="row">
	<div class="col-md-8">
		{if $importProblems}
		<div class="panel panel-warning">
			<div class="panel-heading"><h2 class="panel-title">{tr}Not saved{/tr}</h2></div>
			<ul class="list-group">
			{foreach from=$importProblems item=problem}
				<li class="list-group-item">{$problem}</li>
			{/foreach}
			</ul>
		</div>
		{/if}

		<div class="row address-book-cards">
		{foreach from=$addresses item=entry}
			<div class="col-sm-6 col-md-4">
				<div class="address-book-entry{if $smarty.request.edit == $entry.address_book_id} editing{/if}">
					<div class="entry-name">{$entry.firstname|escape} {$entry.lastname|escape}{if $entry.company}, {$entry.company|escape}{/if}{if $entry.address_book_id == $defaultAddressId} <span class="label label-default">{tr}default{/tr}</span>{/if}</div>
					<div class="entry-address">{$entry.street_address|escape}{if $entry.suburb}, {$entry.suburb|escape}{/if}<br>{$entry.city|escape}{if $entry.state}, {$entry.state|escape}{/if} {$entry.postcode|escape}{if $entry.countries_iso_code_2 != $storeCountryIso2}, {$entry.countries_name|escape}{/if}</div>
					{if $entry.telephone}<div class="entry-address">{booticon iname="fa-phone"} {$entry.telephone|escape}</div>{/if}
					<div class="entry-tools">
						<a class="btn btn-link btn-xs" href="{$smarty.const.BITCOMMERCE_PKG_URL}address_book?edit={$entry.address_book_id}">{tr}Edit{/tr}</a>
						<a class="btn btn-link btn-xs entry-delete" href="{$smarty.const.BITCOMMERCE_PKG_URL}address_book?delete={$entry.address_book_id}" title="{tr}Delete{/tr} {$entry.firstname|escape} {$entry.lastname|escape}" aria-label="{tr}Delete{/tr} {$entry.firstname|escape} {$entry.lastname|escape}">{booticon iname="fa-xmark"}</a>
					</div>
				</div>
			</div>
		{foreachelse}
			<div class="col-sm-12">
				<div class="address-book-entry address-book-empty">
					<div class="entry-name">{tr}No one here yet{/tr}</div>
					<div class="entry-address">{tr}Add a person on the right, or import a list from a spreadsheet or your contacts.{/tr}</div>
				</div>
			</div>
		{/foreach}
		</div>

		<div class="address-book-tools">
			<button type="button" class="btn btn-default btn-sm address-book-show-import">{booticon iname="fa-upload"} {tr}Import a Spreadsheet or vCard{/tr}</button>
		</div>

		{form name='address_import' id='address-import' action="`$smarty.const.BITCOMMERCE_PKG_URL`address_book" method='post' enctype='multipart/form-data'}
		<div class="address-book-import" {if !$addressMapping && !$importProblems}style="display:none"{/if}>
			<div class="form-inline">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">{booticon iname="fa-address-book"}</div>
						<input type="file" name="address_file" class="form-control" accept=".csv,.txt,.xlsx,.xlsm,.vcf,text/csv,text/vcard,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
					</div>
					<input class="btn btn-primary" type="submit" name="import_upload" value="{tr}Import{/tr}">
				</div>
			</div>
			<label class="small address-book-option"><input type="checkbox" name="import_check_columns" value="1"> {tr}Let me check the columns first{/tr}</label>
			<p class="help-block">{tr}A spreadsheet (.csv or Excel .xlsx) with one person per row, or a vCard (.vcf) from Contacts, Outlook or Google Contacts.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}pub/drop_ship_addresses.csv" download="{$gBitSystem->getSiteTitle()} Addresses.csv">{tr}Address Template{/tr}</a> &middot; <a href="#" class="address-book-help-toggle">{tr}What the File Needs{/tr}</a></p>
			<div class="address-book-help" style="display:none">
				<p class="help-block">{tr}Columns:{/tr} Name, Street, Address 2, City, State, Zip, Country, Phone. {tr}Plain headings in any order; country can be blank for the store country. When a heading is not recognized you match the columns on the next screen. An Excel workbook uses the first sheet with those headings. A vCard uses each contact's preferred or home address and phone. People already in your address book are skipped.{/tr}</p>
			</div>
		</div>
		{/form}
	</div>

	<div class="col-md-4">
		<div class="well address-book-rail">
			<h3>{$formTitle}</h3>
			{form action=address_book|zen_href_link}
				{include file="bitpackage:bitcommerce/address_edit_inc.tpl" address=$editAddress sectionName="shipping"}
				<input type="submit" class="btn btn-primary btn-block" name="save_address" value="{tr}Save Address{/tr}"/>
				{if $editAddress.address_book_id}<a class="btn btn-link btn-block" href="{$smarty.const.BITCOMMERCE_PKG_URL}address_book">{tr}Cancel{/tr}</a>{/if}
			{/form}
		</div>
	</div>
</div>
{literal}
<script>
$(document).ready(function() {
	$('.address-book-show-import').on('click', function() {
		$('.address-book-import').show().find('input[type=file]').first().focus();
	});
	$('.address-book-help-toggle').on('click', function(e) { e.preventDefault(); $('.address-book-help').toggle(); });
});
</script>
{/literal}
</div>

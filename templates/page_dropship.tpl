<div class="drop-ship">
{if $gBitUser->hasPermission('p_bitcommerce_dropship')}

{if $dropCreated}
	{include file="bitpackage:bitcommerce/page_dropship_header_inc.tpl" step=3}
	<header class="page-header">
		<h1>{$dropCreated|@count} {tr}orders created{/tr} <small>{tr}drop ship{/tr}</small></h1>
		<p class="lead">{if $dropCreated.0.po_number}{tr}All of them are on purchase order{/tr} <span class="drop-ship-file">{$dropCreated.0.po_number|escape}</span>, {tr}unpaid, and will not print until it is paid.{/tr}{else}{tr}Each one is listed below.{/tr}{/if} {if !$smarty.request.notify}{tr}No one was emailed.{/tr}{/if}</p>
	</header>
	{formfeedback hash=$feedback}
	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<table class="table">
					<thead><tr><th>{tr}Order{/tr}</th><th>{tr}Ship to{/tr}</th><th>{tr}Shipping{/tr}</th><th class="num">{tr}Total{/tr}</th></tr></thead>
					<tbody>
					{foreach from=$dropCreated item=created}
						<tr>
							<td><a href="{$created.url}">{$created.orders_id}</a></td>
							<td><div class="ship-to-name">{$created.name|escape}</div><div class="ship-to-address">{$created.place|escape}</div></td>
							<td>{$created.shipping|escape}</td>
							<td class="num">{$gCommerceCurrencies->format($created.total)}</td>
						</tr>
					{/foreach}
					</tbody>
					<tfoot><tr><td colspan="3">{tr}Due{/tr}{if $dropCreated.0.po_number} {tr}on{/tr} {$dropCreated.0.po_number|escape}{/if}</td><td class="num">{$dropCreatedTotalDisplay}</td></tr></tfoot>
				</table>
			</div>
			<p class="text-muted">{tr}Open an order to check its address. A wrong one can be deleted from Due Orders before you pay; the rest stay on the purchase order.{/tr}</p>
		</div>
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-body">
					<h3>{tr}Next{/tr}</h3>
					<a class="btn btn-primary btn-block" href="{$smarty.const.BITCOMMERCE_PKG_URL}invoices">{tr}Pay or Review in Due Orders{/tr}</a>
					<a class="btn btn-default btn-block" href="#dropship-addresses">{tr}Send Another List{/tr}</a>
					<p class="help-block">{tr}Sending two copies to some people? Change the cart to 2, then upload a file with just those addresses. The purchase order number will suggest the next number for today.{/tr}</p>
				</div>
			</div>
		</div>
	</div>
	<hr>
{else}
	{include file="bitpackage:bitcommerce/page_dropship_header_inc.tpl" step=1}
	<header class="page-header clearfix">
		<h1>{tr}Drop Ship to Multiple Addresses{/tr}</h1>
		{if $dropPreview}
			<p class="lead">{if $dropFileName}{tr}Your cart, sent to everyone in{/tr} <span class="drop-ship-file">{$dropFileName|escape}</span>.{else}{tr}Your cart, sent to everyone below.{/tr}{/if} {tr}Each address becomes its own order with its own shipping.{/tr}</p>
		{else}
			<p class="lead">{tr}Send your cart to several people at once. Each address becomes its own order with its own shipping, and they are billed together.{/tr}</p>
		{/if}
	</header>
	{formfeedback hash=$feedback}
{/if}

	{if $dropProblemCount}
		<div class="alert alert-warning">
			<strong>{$dropProblemCount} {tr}of{/tr} {$dropPreview|@count} {tr}addresses need a fix in the file.{/tr}</strong>
			{tr}They are left out below. Fix those rows, save as CSV, and upload again, or create the others now and send the rest in a second file.{/tr}
		</div>
	{/if}

	{if $dropMapping}
	{form name='dropship_map' id='dropship-map' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post'}
	<div class="panel panel-info drop-ship-map">
		<div class="panel-heading"><h2 class="panel-title">{tr}Match the columns in{/tr} {$dropMapping.file|escape}</h2></div>
		<div class="panel-body">
			{include file="bitpackage:bitcommerce/address_import_map_inc.tpl" mapping=$dropMapping mapSubmitName="dropship_map"}
			<button type="submit" class="btn btn-primary" name="dropship_map" value="apply">{tr}Use These Columns{/tr}</button>
			<button type="submit" class="btn btn-link" name="dropship_map" value="cancel">{tr}Cancel{/tr}</button>
		</div>
	</div>
	{/form}
	{/if}

	{form name='dropship_process' id='dropship-process' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post'}
	<input type="hidden" name="dropship_remove" value="">
	<input type="hidden" name="dropship_clear" value="">
	<input type="hidden" name="dropship_requote" value="">
	<input type="hidden" name="dropship_edit" value="">
	<input type="hidden" name="dropship_add" value="">
	<input type="hidden" name="dropship_addressbook" value="">
	<input type="hidden" name="dropship_map_show" value="">
	<input type="hidden" name="dropship_qty" value="">
	<input type="hidden" name="dropship_qty_reset" value="">
	<div class="row">
		<div class="col-md-8">

			<div class="panel panel-default">
				<div class="table-responsive">
				<table class="table" id="dropship-addresses">
					<thead>
						<tr>
							<th><input type="checkbox" id="dropship-check-all" checked title="{tr}Check or uncheck every address{/tr}"></th>
							<th>{tr}Ship to{/tr}</th>
							<th>{tr}Shipping{/tr}</th>
							<th class="num">{tr}Rate{/tr}</th>
							<th class="remove-cell">{if $dropPreview}<button type="button" class="btn btn-link btn-xs drop-ship-clear" title="{tr}Remove every address{/tr}" aria-label="{tr}Remove every address{/tr}">{booticon iname="fa-xmark"}</button>{/if}</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$dropPreview key=rowIdx item=preview}
						<tr class="drop-ship-row{if $preview.errors} danger{/if}" data-items="{$preview.cart_total}">
							<td><input type="checkbox" name="dropship_order[]" value="{$rowIdx}" class="dropship-row-check" {if $preview.errors}disabled{else}checked{/if}></td>
							<td>
								<div class="ship-to-view">
								{if $preview.errors}
									<div class="ship-to-name">{$preview.row.delivery_name|escape}{if $preview.row.delivery_company}, {$preview.row.delivery_company|escape}{/if} <span class="label label-default ship-to-line">{$preview.line_label}</span></div>
									<div class="ship-to-address">{$preview.row.delivery_street_address1|escape}{if $preview.row.delivery_street_address2}, {$preview.row.delivery_street_address2|escape}{/if}<br>{$preview.row.delivery_city|escape} {$preview.row.delivery_state|escape} {$preview.row.delivery_postcode|escape} {$preview.row.delivery_country_iso2|escape}</div>
									<div class="ship-to-why">{foreach from=$preview.errors item=rowError name=errs}{$rowError}{if !$smarty.foreach.errs.last}. {/if}{/foreach}</div>
								{else}
									<div class="ship-to-name">{$preview.delivery.firstname|escape} {$preview.delivery.lastname|escape}{if $preview.delivery.company}, {$preview.delivery.company|escape}{/if}</div>
									<div class="ship-to-address">{$preview.delivery.street_address|escape}{if $preview.delivery.suburb}, {$preview.delivery.suburb|escape}{/if}<br>{$preview.delivery.city|escape}{if $preview.delivery.state}, {$preview.delivery.state|escape}{/if} {$preview.delivery.postcode|escape}{if $preview.delivery.countries_iso_code_2 != 'US'}, {$preview.delivery.countries_name|escape}{/if}</div>
									{if !$preview.uses_active_cart}<div class="ship-to-address">{tr}Row cart:{/tr} {$preview.item_count} {tr}items{/tr}</div>{/if}
								{/if}
									{if $preview.row.delivery_telephone}<div class="ship-to-address">{booticon iname="fa-phone"} {$preview.row.delivery_telephone|escape}</div>{/if}
									{if $preview.row.comments}<div class="ship-to-address"><em>{$preview.row.comments|escape}</em></div>{/if}
									<button type="button" class="btn btn-link btn-xs drop-ship-edit-toggle">{tr}Edit{/tr}</button>
									{if $preview.uses_active_cart && !$dropCartEmpty && (!$preview.errors || $preview.custom)}<button type="button" class="btn btn-link btn-xs drop-ship-qty-toggle">{if $preview.custom}{tr}Change Quantities{/tr}{else}{tr}Customize Quantities{/tr}{/if}</button>{/if}
									{if !$preview.from_book && !$preview.errors}
										{if $preview.saved}
											<span class="small text-muted drop-ship-save">{booticon iname="fa-check"} {tr}In your address book{/tr}</span>
										{else}
											<label class="small drop-ship-save"><input type="checkbox" name="save_address[]" value="{$rowIdx}" {if $preview.save}checked{/if}> {tr}Save to address book{/tr}</label>
										{/if}
									{/if}
								</div>
								<div class="ship-to-edit" style="display:none">
									<div class="row">
										<div class="col-sm-6"><input class="form-control input-sm" name="edit[delivery_name]" value="{$preview.row.delivery_name|escape}" placeholder="{tr}Name{/tr}" aria-label="{tr}Name{/tr}" disabled></div>
										<div class="col-sm-6"><input class="form-control input-sm" name="edit[delivery_company]" value="{$preview.row.delivery_company|escape}" placeholder="{tr}Company{/tr}" aria-label="{tr}Company{/tr}" disabled></div>
										<div class="col-sm-7"><input class="form-control input-sm" name="edit[delivery_street_address1]" value="{$preview.row.delivery_street_address1|escape}" placeholder="{tr}Street{/tr}" aria-label="{tr}Street{/tr}" disabled></div>
										<div class="col-sm-5"><input class="form-control input-sm" name="edit[delivery_street_address2]" value="{$preview.row.delivery_street_address2|escape}" placeholder="{tr}Apt, suite, unit{/tr}" aria-label="{tr}Address line 2{/tr}" disabled></div>
										<div class="col-sm-4"><select class="form-control input-sm drop-ship-country" name="edit[delivery_country_iso2]" data-value="{$preview.row.delivery_country_iso2|escape}" aria-label="{tr}Country{/tr}" disabled></select></div>
										<div class="col-sm-5"><input class="form-control input-sm" name="edit[delivery_city]" value="{$preview.row.delivery_city|escape}" placeholder="{tr}City{/tr}" aria-label="{tr}City{/tr}" disabled></div>
										<div class="col-sm-3"><input class="form-control input-sm drop-ship-state" name="edit[delivery_state]" value="{$preview.row.delivery_state|escape}" placeholder="{tr}State{/tr}" aria-label="{tr}State{/tr}" disabled></div>
										<div class="col-sm-4"><input class="form-control input-sm" name="edit[delivery_postcode]" value="{$preview.row.delivery_postcode|escape}" placeholder="{tr}Zip{/tr}" aria-label="{tr}Postal code{/tr}" disabled></div>
										<div class="col-sm-8"><input class="form-control input-sm" name="edit[delivery_telephone]" value="{$preview.row.delivery_telephone|escape}" placeholder="{tr}Phone{/tr}" aria-label="{tr}Phone{/tr}" type="tel" disabled></div>
										<div class="col-sm-12"><input class="form-control input-sm" name="edit[comments]" value="{$preview.row.comments|escape}" placeholder="{tr}Note for this order{/tr}" aria-label="{tr}Note for this order{/tr}" disabled></div>
									</div>
									<button type="button" class="btn btn-primary btn-xs drop-ship-edit-save" data-row="{$rowIdx}">{tr}Save Address{/tr}</button>
									<button type="button" class="btn btn-default btn-xs drop-ship-edit-cancel">{tr}Cancel{/tr}</button>
								</div>
								{if $preview.uses_active_cart && !$dropCartEmpty}
								<div class="ship-to-qty" style="display:none">
									<p class="help-block">{tr}What this address gets. Zero leaves a product out of this order only.{/tr}</p>
									{include file="bitpackage:bitcommerce/dropship_row_cart_inc.tpl" cart=$dropLiveCart rowIdx=$rowIdx quantities=$preview.quantities}
									<button type="button" class="btn btn-primary btn-xs drop-ship-qty-save" data-row="{$rowIdx}">{tr}Save Quantities{/tr}</button>
									{if $preview.custom}<button type="button" class="btn btn-default btn-xs drop-ship-qty-reset" data-row="{$rowIdx}">{tr}Clear Custom Quantities{/tr}</button>{/if}
									<button type="button" class="btn btn-link btn-xs drop-ship-qty-cancel">{tr}Cancel{/tr}</button>
								</div>
								{/if}
							</td>
							<td>
								{if $preview.quotes}
									<select name="shipping_quote[{$rowIdx}]" class="form-control input-sm drop-ship-select" aria-label="{tr}Shipping for{/tr} {$preview.row.delivery_name|escape}">
									{foreach from=$preview.quote_groups key=carrier item=carrierQuotes}
										<optgroup label="{$carrier|escape}">
										{foreach from=$carrierQuotes item=quote}
											<option value="{$quote.id}" data-key="{$quote.key|escape}" data-cost="{$quote.cost}" data-display="{$quote.display|escape}" {if $quote.id == $preview.selected_quote}selected{/if}>{$quote.display} &nbsp;{$quote.method|escape}</option>
										{/foreach}
										</optgroup>
									{/foreach}
									</select>
									{if $preview.fixed_quote}<div class="ship-to-address">{tr}carrier set in file{/tr}</div>{/if}
									{if $dropOriginsVary && $preview.quotes[$preview.selected_quote].origin}<div class="ship-to-address drop-ship-origin">{tr}Ships from{/tr} {$preview.quotes[$preview.selected_quote].origin|escape}</div>{/if}
								{elseif $preview.errors}
									<select class="form-control input-sm drop-ship-select" disabled><option>{tr}Fix the address first{/tr}</option></select>
								{/if}
								{foreach from=$preview.quote_errors item=quoteError}<div class="ship-to-why">{$quoteError}</div>{/foreach}
								{if $preview.custom && $preview.cart}
								<div class="drop-ship-row-cart">
									<span class="label label-info">{tr}custom quantities{/tr}</span>
									{include file="bitpackage:bitcommerce/dropship_cart_inc.tpl" cart=$preview.cart}
								</div>
								{/if}
							</td>
							<td class="num rate-cell">{if $preview.selected_quote}{$preview.quotes[$preview.selected_quote].display}{else}<span class="text-muted">&mdash;</span>{/if}</td>
							<td class="remove-cell"><button type="button" class="btn btn-link btn-xs drop-ship-remove" data-row="{$rowIdx}" title="{tr}Remove{/tr} {$preview.row.delivery_name|escape}" aria-label="{tr}Remove{/tr} {$preview.row.delivery_name|escape}">{booticon iname="fa-xmark"}</button></td>
						</tr>
					{/foreach}
						<tr class="drop-ship-add" style="display:none">
							<td></td>
							<td colspan="4">
								<div class="row">
									<div class="col-sm-4"><input class="form-control input-sm" name="add[delivery_name]" placeholder="{tr}Name{/tr}" aria-label="{tr}Name{/tr}" autocomplete="section-dropship shipping name"></div>
									<div class="col-sm-4"><input class="form-control input-sm" name="add[delivery_company]" placeholder="{tr}Company{/tr}" aria-label="{tr}Company{/tr}" autocomplete="section-dropship shipping organization"></div>
									<div class="col-sm-4"><input class="form-control input-sm" name="add[delivery_telephone]" type="tel" placeholder="{tr}Phone{/tr}" aria-label="{tr}Phone{/tr}" autocomplete="section-dropship shipping tel"></div>
									<div class="col-sm-4">
										<select class="form-control input-sm drop-ship-country" name="add[delivery_country_iso2]" aria-label="{tr}Country{/tr}" autocomplete="section-dropship shipping country">
										{foreach from=$dropCountries item=country}
											<option value="{$country.iso2}" {if $country.iso2 == $dropStoreCountry}selected{/if}>{$country.name|escape}</option>
										{/foreach}
										</select>
									</div>
									<div class="col-sm-5"><input class="form-control input-sm" name="add[delivery_street_address1]" placeholder="{tr}Street{/tr}" aria-label="{tr}Street{/tr}" autocomplete="section-dropship shipping address-line1"></div>
									<div class="col-sm-3"><input class="form-control input-sm" name="add[delivery_street_address2]" placeholder="{tr}Apt, suite, unit{/tr}" aria-label="{tr}Address line 2{/tr}" autocomplete="section-dropship shipping address-line2"></div>
									<div class="col-sm-5"><input class="form-control input-sm" name="add[delivery_city]" placeholder="{tr}City{/tr}" aria-label="{tr}City{/tr}" autocomplete="section-dropship shipping address-level2"></div>
									<div class="col-sm-4"><input class="form-control input-sm drop-ship-state" name="add[delivery_state]" placeholder="{tr}State{/tr}" aria-label="{tr}State{/tr}" autocomplete="section-dropship shipping address-level1"></div>
									<div class="col-sm-3"><input class="form-control input-sm" name="add[delivery_postcode]" placeholder="{tr}Zip{/tr}" aria-label="{tr}Postal code{/tr}" autocomplete="section-dropship shipping postal-code"></div>
								</div>
								<label class="small drop-ship-save"><input type="checkbox" name="add[save]" value="1"> {tr}Also save it to my address book{/tr}</label>
								<p class="help-block">{tr}Your browser can fill in a saved address here.{/tr}</p>
								<button type="button" class="btn btn-info btn-sm drop-ship-add-save">{tr}Save Address{/tr}</button>
							</td>
						</tr>
						<tr class="drop-ship-book-row" style="display:none">
							<td></td>
							<td colspan="4">
								<div class="row drop-ship-book">
								{foreach from=$dropAddressBook item=entry}
									<div class="col-sm-6 col-md-4">
										<label class="drop-ship-book-entry">
											<input type="checkbox" name="address_book[]" value="{$entry.address_book_id}">
											<span><strong>{$entry.firstname|escape} {$entry.lastname|escape}</strong>{if $entry.company}, {$entry.company|escape}{/if}{if $entry.entry_primary == 't'} <span class="label label-default">{tr}default{/tr}</span>{/if}<br>
											<span class="ship-to-address">{$entry.street_address|escape}{if $entry.suburb}, {$entry.suburb|escape}{/if}<br>{$entry.city|escape}{if $entry.state}, {$entry.state|escape}{/if} {$entry.postcode|escape}{if $entry.countries_iso_code_2 != 'US'}, {$entry.countries_name|escape}{/if}</span></span>
										</label>
									</div>
								{/foreach}
								</div>
								<button type="button" class="btn btn-primary btn-sm drop-ship-book-save">{tr}Add Selected Addresses{/tr}</button>
								<a class="btn btn-link btn-sm" href="{$smarty.const.BITCOMMERCE_PKG_URL}address_book">{tr}Manage Address Book{/tr}</a>
							</td>
						</tr>
						<tr class="drop-ship-upload-row" style="display:none">
							<td></td>
							<td colspan="4">
								<div class="form-inline">
									<div class="form-group">
										<div class="input-group">
											<div class="input-group-addon">{booticon iname="fa-address-book"}</div>
											<input type="file" name="drop_csv" form="dropship-upload" class="form-control" accept=".csv,.txt,.xlsx,.xlsm,.vcf,text/csv,text/vcard,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
										</div>
										<input class="btn btn-primary" type="submit" name="dropship_upload" form="dropship-upload" value="{tr}Upload{/tr}">
									</div>
								</div>
								<label class="small drop-ship-save"><input type="checkbox" name="drop_save_book" value="1" form="dropship-upload"> {tr}Also add these people to my address book{/tr}</label>
								<label class="small drop-ship-save"><input type="checkbox" name="drop_check_columns" value="1" form="dropship-upload"> {tr}Let me check the columns first{/tr}</label>
								<p class="help-block">{tr}A spreadsheet (.csv or Excel .xlsx) with one person per row, or a vCard (.vcf) from Contacts, Outlook or Google Contacts.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}pub/drop_ship_addresses.csv" download="{$gBitSystem->getSiteTitle()} Drop Ship Addresses.csv">{tr}Address Template{/tr}</a> &middot; <a href="#" class="drop-ship-file-help-toggle">{tr}What the File Needs{/tr}</a></p>
								<div class="drop-ship-file-help" style="display:none">
									<p class="help-block">{tr}Columns:{/tr} Name, Street, Address 2, City, State, Zip, Country, Phone, Comments. {tr}Plain headings in any order; country can be blank for the store country. When a heading is not recognized you match the columns on the next screen; other columns are ignored and listed after upload. An Excel workbook uses the first sheet with those headings; Numbers and older .xls files need File, Export, then CSV or Excel. A vCard uses each contact's preferred or home address and phone. New people are added to the list; anyone already in it is skipped.{/tr}</p>
									<p class="help-block">{tr}Advanced:{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}pub/drop_ship.csv" download="{$gBitSystem->getSiteTitle()} Drop Ship Example.csv">{tr}the full example{/tr}</a> {tr}adds a cart column (product@qty:option) to ship different products per row, and per-row shipping or payment columns.{/tr}</p>
								</div>
							</td>
						</tr>
						<tr class="drop-ship-tools">
							<td></td>
							<td colspan="4">
								<button type="button" class="btn btn-default btn-sm drop-ship-show-add">{booticon iname="fa-plus"} {tr}Add an Address{/tr}</button>
								{if $dropAddressBook}<button type="button" class="btn btn-default btn-sm drop-ship-show-book">{booticon iname="fa-address-book"} {tr}From Your Address Book{/tr}</button>{/if}
								<button type="button" class="btn btn-default btn-sm drop-ship-show-upload">{booticon iname="fa-upload"} {tr}Upload a Spreadsheet or vCard{/tr}</button>
								{if $dropImportFile}<button type="button" class="btn btn-link btn-sm drop-ship-map-show" title="{tr}Change which column is which in the file you uploaded{/tr}">{tr}Check Columns of{/tr} {$dropImportFile|escape}</button>{/if}
							</td>
						</tr>
					</tbody>
					{if $dropPreview}
					<tfoot>
						<tr><td></td><td colspan="2"><span class="drop-ship-checked-count">{$dropReadyCount}</span> {tr}of{/tr} {$dropPreview|@count} {tr}addresses checked{/tr} &middot; {tr}shipping{/tr}</td><td class="num drop-ship-shipping-total">{$dropShippingTotalDisplay}</td><td></td></tr>
					</tfoot>
					{/if}
				</table>
				</div>
			</div>

			{if $dropShippingGroups}
			<h2 class="h3">{tr}Ship everything by{/tr} <button type="button" class="btn btn-link btn-xs drop-ship-requote" title="{tr}Rates are kept for 15 minutes and quoted again when the orders are created{/tr}">{tr}Refresh Rates{/tr}</button></h2>
			<p class="text-muted">{tr}Sets every address that offers it. Change one address in the table.{/tr}</p>
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="row">
					{foreach from=$dropShippingGroups item=group}
						<div class="col-sm-6 drop-ship-carrier">
							<h4>{$group.module|escape}</h4>
							{foreach from=$group.methods item=method}
							<label class="drop-ship-rate">
								<input type="radio" name="shipping_bulk" value="{$method.key|escape}" {if $method.key == $dropBulkDefault}checked{/if}>
								<span class="num">{$method.total_display}</span>
								<span>{$method.method|escape}{if $method.count < $dropReadyCount} <span class="text-muted">{$method.count} {tr}of{/tr} {$dropReadyCount} {tr}addresses{/tr}</span>{/if}</span>
							</label>
							{/foreach}
						</div>
					{/foreach}
					</div>
				</div>
			</div>
			{elseif $dropReadyCount}
				<div class="alert alert-danger">{tr}No shipping method could be quoted for these addresses.{/tr}</div>
			{/if}

			{if $dropPreview}
			<h2 class="h3">{tr}Payment{/tr}</h2>
			<div class="panel panel-default">
				<div class="panel-body">
				{if $dropPaymentSelections}
					{foreach from=$dropPaymentSelections item=selection name=payment_selection}
						<label class="drop-ship-rate">
							{if count($dropPaymentSelections) > 1}
								<input type="radio" name="payment_method" value="{$selection.id}" {if $selection.id == $dropPaymentDefault}checked{/if} onclick="$('.payment-selection').hide();$('#payment-{$selection.id}').show();">
							{else}
								<input type="hidden" name="payment_method" value="{$selection.id}"><input type="radio" checked disabled>
							{/if}
							<span><strong>{$selection.module|default:$selection.id}</strong>
							{if $selection.id == 'purchase_order'}<span class="text-muted">{tr}All of these orders go on one number. Pay it later in Due Orders.{/tr}</span>{/if}</span>
						</label>
						<div class="payment-selection payment-fields" id="payment-{$selection.id}" {if $selection.id != $dropPaymentDefault}style="display:none"{/if}>
							{if $selection.fields && is_array($selection.fields)}
							<div class="row">
								{foreach from=$selection.fields item=selectionField}
								<div class="col-sm-4">
									<div class="form-group">
										{if $selectionField.title}<label class="control-label">{$selectionField.title}</label>{/if}
										{$selectionField.field}
									</div>
								</div>
								{/foreach}
							</div>
							{/if}
						</div>
					{/foreach}
				{else}
					<div class="alert alert-warning">{tr}No payment method is available for your account. Contact us before creating these orders.{/tr}</div>
				{/if}
				</div>
			</div>
			{/if}

		</div>
		<div class="col-md-4">
			<div class="well drop-ship-rail">
					{if $dropActiveCartRows || !$dropPreview}
						<h3>{if $dropPreview}{tr}Every address gets{/tr}{else}{tr}Every address will get{/tr}{/if}</h3>
						{if $dropCartEmpty}
							<div class="alert alert-danger">{tr}Your cart is empty, so there is nothing to ship.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}">{tr}Add the products to your cart{/tr}</a>; {tr}these addresses stay loaded while you do.{/tr}</div>
						{else}
							<p class="text-muted">{tr}Your cart.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}shopping_cart">{tr}Change Quantities{/tr}</a></p>
							{include file="bitpackage:bitcommerce/dropship_cart_inc.tpl" cart=$dropLiveCart}
							{if $dropPreview && $dropBatchCopies}<p class="help-block">{tr}Prices include the quantity discount for all{/tr} {$dropBatchCopies} {tr}copies across these addresses.{/tr}</p>{/if}
						{/if}
					{else}
						<h3>{tr}Products{/tr}</h3>
						<p class="text-muted">{tr}Each row of your file names its own products in the cart column.{/tr}</p>
					{/if}
					<hr>
					{if !$dropPreview}
					<p class="text-muted">{tr}Shipping and the total appear once there is an address.{/tr}</p>
					{/if}
					{if ($dropActiveCartRows || !$dropPreview) && !$dropCartEmpty}
					<div class="total-line"><span><span class="drop-ship-checked-count">{$dropReadyCount}</span> {tr}orders{/tr}{if $dropCustomRows}, <span class="drop-ship-custom-count">{$dropCustomRows}</span> {tr}with custom quantities{/tr}{else} &times; {$dropCartTotalDisplay}{/if}</span><span class="num drop-ship-items-total">{$dropItemsTotalDisplay}</span></div>
					{/if}
					<div class="total-line"><span>{tr}Shipping{/tr}</span><span class="num drop-ship-shipping-total">{$dropShippingTotalDisplay}</span></div>
					<div class="total-line grand"><span>{tr}Total{/tr}{if $dropPaymentDefault == 'purchase_order' && $dropPoNumber} {tr}due on{/tr} {$dropPoNumber}{/if}</span><span class="num drop-ship-grand-total">{$dropOrdersTotalDisplay}</span></div>
					<p class="help-block drop-ship-batch-note">{tr}Quantity discounts count every address listed. The next step shows each order priced that way before anything is created.{/tr}</p>
					<input type="submit" name="dropship_finalize" value="{tr}Final Review{/tr}" formaction="{$smarty.const.BITCOMMERCE_PKG_URL}dropship_confirmation" class="btn btn-primary btn-lg btn-block" id="dropship-process-button" {if !$dropReadyCount || !$dropPaymentSelections || !$dropShippingMethods || ($dropActiveCartRows && $dropCartEmpty)}disabled{/if}>
					<p class="help-block">{tr}Next you check every order, priced as one batch. Nothing is created until you confirm there.{/tr}</p>
			</div>
		</div>
	</div>
	{/form}

{form name='dropship_upload' id='dropship-upload' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post' enctype='multipart/form-data'}<input type="hidden" name="dropship_upload_form" value="1">{/form}

<div class="drop-ship-wait" style="display:none" role="status" aria-live="polite">
	<div class="drop-ship-wait-box">
		<div class="drop-ship-spinner" aria-hidden="true"></div>
		<div class="drop-ship-wait-title">{tr}Please wait{/tr}</div>
		<div class="drop-ship-wait-text"></div>
	</div>
</div>
<select class="drop-ship-country-template" style="display:none" disabled aria-hidden="true">
{foreach from=$dropCountries item=country}
	<option value="{$country.iso2}">{$country.name|escape}</option>
{/foreach}
</select>
<script>
var dropShipSymbol = '{$dropCurrencySymbol|escape:'javascript'}';
var dropShipCartTotal = {$dropCartTotal|default:0};
var dropShipZones = {$dropZonesJson};
var dropShipStateLabel = '{tr}State{/tr}';
var dropShipWaitQuotes = '{tr}Getting individualized shipping quotes for each address. This takes a few seconds per address.{/tr}';
var dropShipWaitOrders = '{tr}Pricing every order as one batch and checking shipping.{/tr}';
var dropShipWaitRead = '{tr}Reading your file.{/tr}';
</script>
{literal}
<script>
$(document).ready(function() {
	var processButton = $('#dropship-process-button');
	var buttonLabel = processButton.val();
	// Every post re-renders the list and quotes each address live, so the page says so while it waits
	function showWait( text ) {
		$('.drop-ship-wait-text').text( text );
		$('.drop-ship-wait').css('display', 'flex');
	}
	function submitWait( form, text ) {
		showWait( text || dropShipWaitQuotes );
		form[0].submit();
	}
	$('#dropship-upload').on('submit', function() { showWait( dropShipWaitRead ); });
	$('#dropship-map').on('submit', function(e) {
		var button = $(document.activeElement);
		showWait( button.val() === 'cancel' ? dropShipWaitRead : dropShipWaitQuotes );
	});
	$('#dropship-process').on('submit', function() { showWait( dropShipWaitOrders ); });
	$(window).on('pageshow', function() { $('.drop-ship-wait').hide(); });
	function money( n ) { return dropShipSymbol + n.toFixed(2); }
	function refresh() {
		var checked = 0, shipping = 0, items = 0;
		$('.drop-ship-row').each(function() {
			var row = $(this);
			var opt = row.find('.drop-ship-select option:selected');
			if( row.find('.dropship-row-check').is(':checked') ) {
				checked++;
				shipping += parseFloat( opt.data('cost') ) || 0;
				items += parseFloat( row.data('items') ) || 0;
			}
			if( opt.length && opt.data('display') ) { row.find('.rate-cell').text( opt.data('display') ); }
		});
		$('.drop-ship-checked-count').text( checked );
		$('.drop-ship-shipping-total').text( money( shipping ) );
		$('.drop-ship-items-total').text( money( items ) );
		$('.drop-ship-grand-total').text( money( items + shipping ) );
		if( processButton.length ) {
			processButton.val( buttonLabel + ' (' + checked + ')' );
			processButton.prop( 'disabled', checked === 0 || processButton.data( 'locked' ) );
		}
	}
	function applyBulk() {
		var method = $('input[name="shipping_bulk"]:checked').val();
		if( !method ) { return; }
		$('.drop-ship-select').each(function() {
			var match = $(this).find('option').filter(function() { return $(this).data('key') === method; }).first();
			if( match.length ) { this.value = match.val(); }
		});
		refresh();
	}
	processButton.data( 'locked', processButton.prop( 'disabled' ) );
	$('#dropship-check-all').on('change', function() {
		$('.dropship-row-check:not(:disabled)').prop('checked', this.checked);
		refresh();
	});
	$('.drop-ship-remove').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_remove"]').val( $(this).data('row') );
		submitWait( form );
	});
	// Country first: the edit row's country menu is filled from the hidden template when it opens, and the
	// state field is a menu of that country's zones when it has any, else free text.
	function fillCountry( select ) {
		if( select.children().length ) { return; }
		select.html( $('.drop-ship-country-template').html() ).val( ( select.data('value') || '' ).toString().toUpperCase() );
	}
	function syncState( container ) {
		var country = container.find('.drop-ship-country');
		var state = container.find('.drop-ship-state');
		if( !country.length || !state.length ) { return; }
		var zones = dropShipZones[ country.val() ];
		var current = ( state.val() || '' ).toString();
		var attrs = { name: state.attr('name'), 'class': state.attr('class'), 'aria-label': state.attr('aria-label') };
		var replacement;
		if( zones && zones.length ) {
			if( state.is('select') ) { return; }
			replacement = $('<select>', attrs).append( $('<option>', { value: '', text: dropShipStateLabel }) );
			var match = '';
			$.each( zones, function( i, zone ) {
				replacement.append( $('<option>', { value: zone[0], text: zone[1] }) );
				if( current && ( zone[0].toUpperCase() === current.toUpperCase() || zone[1].toUpperCase() === current.toUpperCase() ) ) { match = zone[0]; }
			});
			replacement.val( match );
		} else {
			if( state.is('input') ) { return; }
			replacement = $('<input>', $.extend( attrs, { type: 'text', placeholder: dropShipStateLabel, value: '' } ) );
		}
		replacement.prop( 'disabled', state.prop('disabled') );
		state.replaceWith( replacement );
	}
	$(document).on('change', '.drop-ship-country', function() { syncState( $(this).closest('.row') ); });
	syncState( $('.drop-ship-add .row') );
	function closeEdits() {
		$('.ship-to-edit, .ship-to-qty').hide().find('input, select').prop('disabled', true);
		$('.ship-to-view').show();
	}
	// Custom quantities: one open block at a time, its inputs enabled only while open
	$('.drop-ship-qty-toggle').on('click', function() {
		closeEdits();
		var block = $(this).closest('td').find('.ship-to-qty');
		block.show().find('input').prop('disabled', false).first().focus();
	});
	$('.drop-ship-qty-cancel').on('click', closeEdits);
	$('.drop-ship-qty-save').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_qty"]').val( $(this).data('row') );
		submitWait( form );
	});
	$('.drop-ship-qty-reset').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_qty_reset"]').val( $(this).data('row') );
		submitWait( form );
	});
	$(document).on('keydown', '.ship-to-qty input', function(e) {
		if( e.which === 13 ) { e.preventDefault(); $(this).closest('.ship-to-qty').find('.drop-ship-qty-save').click(); }
	});
	$('.drop-ship-edit-toggle').on('click', function() {
		closeEdits();
		var cell = $(this).closest('td');
		cell.find('.ship-to-view').hide();
		var edit = cell.find('.ship-to-edit');
		fillCountry( edit.find('.drop-ship-country') );
		edit.show().find('input, select').prop('disabled', false);
		syncState( edit.find('.row') );
		edit.find('input').first().focus();
	});
	$('.drop-ship-map-show').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_map_show"]').val( '1' );
		submitWait( form );
	});
	$('.drop-ship-edit-cancel').on('click', closeEdits);
	function showEntryRow( selector, focusSelector ) {
		$('.drop-ship-add, .drop-ship-book-row, .drop-ship-upload-row').not( selector ).hide();
		var row = $( selector ).show();
		if( focusSelector ) { row.find( focusSelector ).first().focus(); }
	}
	$('.drop-ship-show-add').on('click', function() { showEntryRow( '.drop-ship-add', 'input' ); });
	$('.drop-ship-show-book').on('click', function() { showEntryRow( '.drop-ship-book-row', 'input[type=checkbox]' ); });
	$('.drop-ship-show-upload').on('click', function() { showEntryRow( '.drop-ship-upload-row', 'input[type=file]' ); });
	$('.drop-ship-file-help-toggle').on('click', function(e) { e.preventDefault(); $('.drop-ship-file-help').toggle(); });
	$('.drop-ship-book-save').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_addressbook"]').val( '1' );
		submitWait( form );
	});
	$('.drop-ship-add-save').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_add"]').val( '1' );
		submitWait( form );
	});
	$('.drop-ship-add input').on('keydown', function(e) {
		if( e.which === 13 ) { e.preventDefault(); $('.drop-ship-add-save').click(); }
	});
	$('.drop-ship-edit-save').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_edit"]').val( $(this).data('row') );
		submitWait( form );
	});
	$(document).on('keydown', '.ship-to-edit input', function(e) {
		if( e.which === 13 ) { e.preventDefault(); $(this).closest('.ship-to-edit').find('.drop-ship-edit-save').click(); }
	});
	$('.drop-ship-requote').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_requote"]').val( '1' );
		submitWait( form );
	});
	$('.drop-ship-clear').on('click', function() {
		if( window.confirm( {/literal}'{tr}Remove every address from the list?{/tr}'{literal} ) ) {
			var form = $('#dropship-process');
			form.find('input[name="dropship_clear"]').val( '1' );
			submitWait( form );
		}
	});
	$('.dropship-row-check, .drop-ship-select').on('change', refresh);
	$('input[name="shipping_bulk"]').on('change', applyBulk);
	refresh();
});
</script>
{/literal}
{/if}
</div>

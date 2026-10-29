<div class="drop-ship">
{if $gBitUser->hasPermission('p_bitcommerce_dropship')}

{if $dropCreated}
	<header class="page-header">
		<h1>{$dropCreated|@count} {tr}orders created{/tr}</h1>
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
					<a class="btn btn-primary btn-block" href="{$smarty.const.BITCOMMERCE_PKG_URL}invoices">{tr}Pay or review in Due Orders{/tr}</a>
					<a class="btn btn-default btn-block" href="#dropship-upload">{tr}Send another list{/tr}</a>
					<p class="help-block">{tr}Sending two copies to some people? Change the cart to 2, then upload a file with just those addresses. The purchase order number will suggest the next number for today.{/tr}</p>
				</div>
			</div>
		</div>
	</div>
	<hr>
{else}
	<header class="page-header clearfix">
		{if $dropPreview}<a class="btn btn-default pull-right" href="#dropship-upload">{tr}Add more addresses{/tr}</a>{/if}
		<h1>{tr}Drop Ship{/tr}</h1>
		{if $dropPreview}
			<p class="lead">{tr}Your cart, mailed to every address in{/tr} <span class="drop-ship-file">{$dropFileName|escape}</span>. {tr}Each address becomes its own order.{/tr}</p>
		{else}
			<p class="lead">{tr}Mail your cart to a list of people. Upload the addresses and each one becomes its own order, with its own shipping, on one purchase order.{/tr}</p>
		{/if}
	</header>
	{formfeedback hash=$feedback}
{/if}

{if $dropPreview}
	{if $dropProblemCount}
		<div class="alert alert-warning">
			<strong>{$dropProblemCount} {tr}of{/tr} {$dropPreview|@count} {tr}addresses need a fix in the file.{/tr}</strong>
			{tr}They are left out below. Fix those rows, save as CSV, and upload again, or create the others now and send the rest in a second file.{/tr}
		</div>
	{/if}

	{form name='dropship_process' id='dropship-process' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post'}
	<input type="hidden" name="dropship_remove" value="">
	<input type="hidden" name="dropship_clear" value="">
	<input type="hidden" name="dropship_requote" value="">
	<input type="hidden" name="dropship_edit" value="">
	<div class="row">
		<div class="col-md-8">

			<h2 class="h3">{tr}Addresses{/tr} <small>{$dropReadyCount} {tr}of{/tr} {$dropPreview|@count} {tr}ready{/tr}</small> <button type="button" class="btn btn-link btn-xs drop-ship-clear">{tr}Remove all{/tr}</button></h2>
			<div class="panel panel-default">
				<div class="table-responsive">
				<table class="table" id="dropship-addresses">
					<thead>
						<tr>
							<th><input type="checkbox" id="dropship-check-all" checked title="{tr}Check or uncheck every address{/tr}"></th>
							<th>{tr}Ship to{/tr}</th>
							<th>{tr}Shipping{/tr}</th>
							<th class="num">{tr}Rate{/tr}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$dropPreview key=rowIdx item=preview}
						<tr class="drop-ship-row{if $preview.errors} danger{/if}">
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
								</div>
								<div class="ship-to-edit" style="display:none">
									<div class="row">
										<div class="col-sm-6"><input class="form-control input-sm" name="edit[delivery_name]" value="{$preview.row.delivery_name|escape}" placeholder="{tr}Name{/tr}" aria-label="{tr}Name{/tr}" disabled></div>
										<div class="col-sm-6"><input class="form-control input-sm" name="edit[delivery_company]" value="{$preview.row.delivery_company|escape}" placeholder="{tr}Company{/tr}" aria-label="{tr}Company{/tr}" disabled></div>
										<div class="col-sm-7"><input class="form-control input-sm" name="edit[delivery_street_address1]" value="{$preview.row.delivery_street_address1|escape}" placeholder="{tr}Street{/tr}" aria-label="{tr}Street{/tr}" disabled></div>
										<div class="col-sm-5"><input class="form-control input-sm" name="edit[delivery_street_address2]" value="{$preview.row.delivery_street_address2|escape}" placeholder="{tr}Apt, suite, unit{/tr}" aria-label="{tr}Address line 2{/tr}" disabled></div>
										<div class="col-sm-5"><input class="form-control input-sm" name="edit[delivery_city]" value="{$preview.row.delivery_city|escape}" placeholder="{tr}City{/tr}" aria-label="{tr}City{/tr}" disabled></div>
										<div class="col-sm-3"><input class="form-control input-sm" name="edit[delivery_state]" value="{$preview.row.delivery_state|escape}" placeholder="{tr}State{/tr}" aria-label="{tr}State{/tr}" disabled></div>
										<div class="col-sm-4"><input class="form-control input-sm" name="edit[delivery_postcode]" value="{$preview.row.delivery_postcode|escape}" placeholder="{tr}Zip{/tr}" aria-label="{tr}Postal code{/tr}" disabled></div>
										<div class="col-sm-4"><input class="form-control input-sm" name="edit[delivery_country_iso2]" value="{$preview.row.delivery_country_iso2|escape}" placeholder="{tr}Country{/tr}" aria-label="{tr}Country{/tr}" disabled></div>
										<div class="col-sm-8"><input class="form-control input-sm" name="edit[delivery_telephone]" value="{$preview.row.delivery_telephone|escape}" placeholder="{tr}Phone{/tr}" aria-label="{tr}Phone{/tr}" type="tel" disabled></div>
										<div class="col-sm-12"><input class="form-control input-sm" name="edit[comments]" value="{$preview.row.comments|escape}" placeholder="{tr}Note for this order{/tr}" aria-label="{tr}Note for this order{/tr}" disabled></div>
									</div>
									<button type="button" class="btn btn-primary btn-xs drop-ship-edit-save" data-row="{$rowIdx}">{tr}Save address{/tr}</button>
									<button type="button" class="btn btn-default btn-xs drop-ship-edit-cancel">{tr}Cancel{/tr}</button>
								</div>
							</td>
							<td>
								{if $preview.quotes}
									<select name="shipping_quote[{$rowIdx}]" class="form-control input-sm drop-ship-select" aria-label="{tr}Shipping for{/tr} {$preview.row.delivery_name|escape}">
									{foreach from=$preview.quote_groups key=carrier item=carrierQuotes}
										<optgroup label="{$carrier|escape}">
										{foreach from=$carrierQuotes item=quote}
											<option value="{$quote.id}" data-cost="{$quote.cost}" data-display="{$quote.display|escape}" {if $quote.id == $preview.selected_quote}selected{/if}>{$quote.display} &nbsp;{$quote.method|escape}</option>
										{/foreach}
										</optgroup>
									{/foreach}
									</select>
									{if $preview.fixed_quote}<div class="ship-to-address">{tr}carrier set in file{/tr}</div>{/if}
								{elseif $preview.errors}
									<select class="form-control input-sm drop-ship-select" disabled><option>{tr}Fix the address first{/tr}</option></select>
								{/if}
								{foreach from=$preview.quote_errors item=quoteError}<div class="ship-to-why">{$quoteError|escape}</div>{/foreach}
							</td>
							<td class="num rate-cell">{if $preview.selected_quote}{$preview.quotes[$preview.selected_quote].display}{else}<span class="text-muted">&mdash;</span>{/if}</td>
							<td class="remove-cell"><button type="button" class="btn btn-link btn-xs drop-ship-remove" data-row="{$rowIdx}" title="{tr}Remove{/tr} {$preview.row.delivery_name|escape}" aria-label="{tr}Remove{/tr} {$preview.row.delivery_name|escape}">{booticon iname="fa-xmark"}</button></td>
						</tr>
					{/foreach}
					</tbody>
					<tfoot>
						<tr><td></td><td colspan="2">{tr}Shipping for{/tr} <span class="drop-ship-checked-count">{$dropReadyCount}</span> {tr}addresses{/tr}</td><td class="num drop-ship-shipping-total">{$dropShippingTotalDisplay}</td><td></td></tr>
					</tfoot>
				</table>
				</div>
			</div>

			{if $dropShippingGroups}
			<h2 class="h3">{tr}Ship everything by{/tr} <button type="button" class="btn btn-link btn-xs drop-ship-requote" title="{tr}Rates are kept for 15 minutes and quoted again when the orders are created{/tr}">{tr}Refresh rates{/tr}</button></h2>
			<p class="text-muted">{tr}Sets every address that offers it. Change one address in the table.{/tr}</p>
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="row">
					{foreach from=$dropShippingGroups item=group}
						<div class="col-sm-6 drop-ship-carrier">
							<h4>{$group.module|escape}</h4>
							{foreach from=$group.methods item=method}
							<label class="drop-ship-rate">
								<input type="radio" name="shipping_bulk" value="{$method.id}" {if $method.id == $dropBulkDefault}checked{/if}>
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

		</div>
		<div class="col-md-4">
			<div class="well drop-ship-rail">
					{if $dropActiveCartRows}
						<h3>{tr}Every address gets{/tr}</h3>
						{if $dropCartEmpty}
							<div class="alert alert-danger">{tr}Your cart is empty, so there is nothing to ship.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}">{tr}Add the products to your cart{/tr}</a>; {tr}these addresses stay loaded while you do.{/tr}</div>
						{else}
							<p class="text-muted">{tr}Your cart.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}shopping_cart">{tr}Change quantities{/tr}</a></p>
							{include file="bitpackage:bitcommerce/dropship_cart_inc.tpl" cart=$dropLiveCart}
							<p class="help-block">{tr}Need two copies for some people? Create these orders, change the cart, then upload a second file with those addresses.{/tr}</p>
						{/if}
					{else}
						<h3>{tr}Products{/tr}</h3>
						<p class="text-muted">{tr}Each row of your file names its own products in the cart column.{/tr}</p>
					{/if}
					<hr>
					{if $dropActiveCartRows && !$dropCartEmpty}
					<div class="total-line"><span><span class="drop-ship-checked-count">{$dropReadyCount}</span> {tr}orders{/tr} &times; {$dropCartTotalDisplay}</span><span class="num drop-ship-items-total">{$dropItemsTotalDisplay}</span></div>
					{/if}
					<div class="total-line"><span>{tr}Shipping{/tr}</span><span class="num drop-ship-shipping-total">{$dropShippingTotalDisplay}</span></div>
					<div class="total-line grand"><span>{tr}Total{/tr}{if $dropPaymentDefault == 'purchase_order' && $dropPoNumber} {tr}due on{/tr} {$dropPoNumber}{/if}</span><span class="num drop-ship-grand-total">{$dropOrdersTotalDisplay}</span></div>
					<input type="submit" name="dropship_process" value="{tr}Create orders{/tr}" class="btn btn-primary btn-lg btn-block" id="dropship-process-button" {if !$dropReadyCount || !$dropPaymentSelections || !$dropShippingMethods || ($dropActiveCartRows && $dropCartEmpty)}disabled{/if}>
					<div class="checkbox">
						<label><input name="notify" type="checkbox" value="1"> {tr}Email me a receipt for each order. Recipients are never emailed.{/tr}</label>
					</div>
					<p class="help-block">{tr}Nothing is charged now. The orders wait unpaid in{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}invoices">{tr}Due Orders{/tr}</a>, {tr}where a wrong one can be deleted before you pay.{/tr}</p>
			</div>
		</div>
	</div>
	{/form}
	<hr>
{/if}

<div class="row" id="dropship-upload">
	<div class="{if $dropPreview || $dropCreated}col-md-12{else}col-md-8{/if}">
		{form name='dropship_upload' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post' enctype='multipart/form-data'}
		<div class="{if !$dropPreview && !$dropCreated}drop-ship-upload-box{/if}">
			{if $dropPreview}<h3>{tr}Add more addresses{/tr}</h3><p class="text-muted">{tr}Another CSV or vCard. New people are added to the list above; anyone already in it is skipped.{/tr}</p>{elseif $dropCreated}<h3>{tr}Send another list{/tr}</h3>{else}<h2 class="h3" style="margin-top:0">{tr}Upload your address list{/tr}</h2>{/if}
			<p class="text-muted">{tr}A CSV file with one person per row, or a vCard (.vcf) exported from Contacts, Outlook or Google Contacts with any number of people in it.{/tr} {tr}From Excel, Numbers or Sheets: File, then Save As or Export, then CSV.{/tr}</p>
			<div class="form-inline">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">{booticon iname="fa-address-book"}</div>
						<input type="file" name="drop_csv" class="form-control" accept=".csv,.vcf,text/csv,text/vcard,text/plain">
					</div>
					<input class="btn btn-primary" type="submit" name="dropship_upload" value="{tr}Upload{/tr}">
				</div>
			</div>
		</div>
		{/form}
		<div class="drop-ship-columns" style="margin-top:16px">
			<h4>{tr}Columns the file needs{/tr}</h4>
			<ul class="list-inline">
				<li>Name</li><li>Street</li><li>Address 2</li><li>City</li><li>State</li><li>Zip</li><li>Country</li><li>Phone</li><li>Comments</li>
			</ul>
			<p class="text-muted">{tr}Plain headings are fine, in any order. Country can be blank for the store country. Other columns are ignored and listed after upload. A vCard uses each contact's preferred or home address and phone; contacts without a postal address are listed and left out.{/tr}</p>
			<p><a href="{$smarty.const.BITCOMMERCE_PKG_URL}pub/drop_ship_addresses.csv" download="{$gBitSystem->getSiteTitle()} Drop Ship Addresses.csv">{booticon iname="fa-download"} {tr}Download the address template{/tr}</a> <span class="text-muted">&mdash; {tr}two example rows to replace with your own.{/tr}</span></p>
			<p class="text-muted small">{tr}Advanced:{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}pub/drop_ship.csv" download="{$gBitSystem->getSiteTitle()} Drop Ship Example.csv">{tr}the full example{/tr}</a> {tr}adds a cart column to ship different products per row, and per-row shipping or payment columns.{/tr}</p>
		</div>
	</div>
	{if !$dropPreview && !$dropCreated}
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-body">
				<h3>{tr}Every address will get{/tr}</h3>
				{if $dropLiveCart->count_contents()}
					<p class="text-muted">{tr}Your cart as it is now.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}shopping_cart">{tr}Change quantities{/tr}</a></p>
					{include file="bitpackage:bitcommerce/dropship_cart_inc.tpl" cart=$dropLiveCart}
					<hr>
					<div class="total-line grand"><span>{tr}Per address{/tr}</span><span class="num">{$dropCartTotalDisplay}</span></div>
					<p class="help-block">{tr}Shipping is quoted for each address after you upload. Quantity discounts apply per order, not across the list.{/tr}</p>
				{else}
					<div class="alert alert-warning">{tr}Your cart is empty.{/tr} <a href="{$smarty.const.BITCOMMERCE_PKG_URL}">{tr}Add the products first{/tr}</a>, {tr}then upload the addresses.{/tr}</div>
				{/if}
			</div>
		</div>
	</div>
	{/if}
</div>

<script>
var dropShipSymbol = '{$dropCurrencySymbol|escape:'javascript'}';
var dropShipCartTotal = {$dropCartTotal|default:0};
</script>
{literal}
<script>
$(document).ready(function() {
	var processButton = $('#dropship-process-button');
	var buttonLabel = processButton.val();
	function money( n ) { return dropShipSymbol + n.toFixed(2); }
	function refresh() {
		var checked = 0, shipping = 0;
		$('.drop-ship-row').each(function() {
			var row = $(this);
			var opt = row.find('.drop-ship-select option:selected');
			if( row.find('.dropship-row-check').is(':checked') ) {
				checked++;
				shipping += parseFloat( opt.data('cost') ) || 0;
			}
			if( opt.length && opt.data('display') ) { row.find('.rate-cell').text( opt.data('display') ); }
		});
		$('.drop-ship-checked-count').text( checked );
		$('.drop-ship-shipping-total').text( money( shipping ) );
		$('.drop-ship-items-total').text( money( checked * dropShipCartTotal ) );
		$('.drop-ship-grand-total').text( money( checked * dropShipCartTotal + shipping ) );
		if( processButton.length ) {
			processButton.val( buttonLabel + ' (' + checked + ')' );
			processButton.prop( 'disabled', checked === 0 || processButton.data( 'locked' ) );
		}
	}
	function applyBulk() {
		var method = $('input[name="shipping_bulk"]:checked').val();
		if( !method ) { return; }
		$('.drop-ship-select').each(function() {
			if( $(this).find('option[value="' + method + '"]').length ) { this.value = method; }
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
		form.submit();
	});
	function closeEdits() {
		$('.ship-to-edit').hide().find('input').prop('disabled', true);
		$('.ship-to-view').show();
	}
	$('.drop-ship-edit-toggle').on('click', function() {
		closeEdits();
		var cell = $(this).closest('td');
		cell.find('.ship-to-view').hide();
		cell.find('.ship-to-edit').show().find('input').prop('disabled', false).first().focus();
	});
	$('.drop-ship-edit-cancel').on('click', closeEdits);
	$('.drop-ship-edit-save').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_edit"]').val( $(this).data('row') );
		form.submit();
	});
	$('.ship-to-edit input').on('keydown', function(e) {
		if( e.which === 13 ) { e.preventDefault(); $(this).closest('.ship-to-edit').find('.drop-ship-edit-save').click(); }
	});
	$('.drop-ship-requote').on('click', function() {
		var form = $('#dropship-process');
		form.find('input[name="dropship_requote"]').val( '1' );
		form.submit();
	});
	$('.drop-ship-clear').on('click', function() {
		if( window.confirm( {/literal}'{tr}Remove every address from the list?{/tr}'{literal} ) ) {
			var form = $('#dropship-process');
			form.find('input[name="dropship_clear"]').val( '1' );
			form.submit();
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

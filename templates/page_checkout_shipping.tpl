{strip}{assign var=checkoutStep value=1}
{include file="bitpackage:bitcommerce/page_checkout_header_inc.tpl" title="Delivery Information" step=$checkoutStep}

<section class="body">
{assign var=cartDelivery value=$gBitCustomer->mCart->getDelivery()}
{if !$gBitUser->isRegistered() || $changeAddress || !$cartDelivery}
	{form name='checkout_address' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=checkout_shipping"}
		<input type="hidden" name="main_page" value="checkout_shipping" />
		<div class="row">
			{if !$gBitUser->isRegistered()}
			<div class="col-md-6">
				{include file="bitpackage:bitcommerce/register_customer.tpl"}
			</div>
			{/if}

			{if $addresses}
			<div class="col-md-6">
				{fieldset legend="Choose Shipping Address"}
					{include file="bitpackage:bitcommerce/address_list_inc.tpl"}
				{/fieldset}
				<div class="form-group clear mv-1">
					<input type="submit" class="btn btn-primary" name="choose_address" value="Continue" /> <input type="submit" class="btn btn-default  pr-1" name="" value="Cancel" /> <a class="btn btn-default pull-right" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=address_book">{tr}Address Book{/tr}</a>
				</div>
			</div>
			{/if}
			
			<div class="col-md-6">
				{fieldset legend="Enter a New Address"}
					{include file="bitpackage:bitcommerce/address_edit_inc.tpl" sectionName="shipping"}
				{/fieldset}
				<div class="form-group clear pull-right">
					 <input type="submit" class="btn btn-primary" name="save_address" value="Continue" /> <input type="submit" class="btn btn-default pr-1" name="" value="Cancel" />
				</div>
			</div>
		</div>

	{/form}
{else}
	{include file="bitpackage:bitcommerce/page_checkout_message_top_inc.tpl" step=$checkoutStep}
	{form name='checkout_address' enctype="multipart/form-data"}
	{formfeedback error=$errors}
			<div class="row">
		<div class="col-md-6">	
			<fieldset>
				<legend>{tr}Shipping Address{/tr}</legend>
				<input type="hidden" name="action" value="process" />
				<input type="hidden" name="main_page" value="checkout_shipping" />
				<div class="pull-right text-right">
					{if $gBitUser->hasPermission('p_bitcommerce_bulk_ordering')}
						<span class="btn btn-default btn-sm ship-address-direct" onclick="$('.ship-address-bulk').toggle();$('.ship-address-direct').toggle();">{tr}Drop Ship{/tr} {booticon iname="fa-boxes-stacked"}</span>
						<span class="btn btn-default btn-sm ship-address-bulk display-none" onclick="$('.ship-address-bulk').toggle();$('.ship-address-direct').toggle();">{tr}Single Shipment{/tr} {booticon iname="fa-box"}</span>
					{/if}
				</div>

				<div class="ship-address-direct">
					<p>{tr}Your order will be shipped to the following address:{/tr}</p>
					{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$cartDelivery}
<button class="btn btn-default btn-sm" name="change_address">{tr}Change{/tr} {booticon iname="fa-truck"}</button> 
				</div>
				{if $gBitUser->hasPermission('p_bitcommerce_bulk_ordering')}
				<div class="ship-address-bulk" style="display:none">
					{formlabel label="Drop Shipping" for="dropship"}

					<p>If you have would like to have the same cart mailed to multiple recipients, use our <a href="{$smarty.const.BITCOMMERCE_PKG_URL}dropship">Drop Ship feature</a>. A unique order with unique shipping charges will be made for each entry in the CSV file.</p>
				</div>
				{/if}
			</fieldset>
			{include file="bitpackage:bitcommerce/page_checkout_deadline_inc.tpl" deadline=$smarty.session.deadline_date}
		</div>
		<div class="col-md-6">
			{fieldset legend="Order Comments"}
				<div class="form-group">
					{forminput}
						<textarea name="comments" wrap="soft" class="form-control special-instructions" rows="5" placeholder="Please include any special instructions, or information on your DEADLINE if you entered one above.">{$smarty.session.comments}</textarea>
					{/forminput}
				</div>
			{/fieldset}
		</div>
	</div>
	<div class="row">
		{if $shippingModules}
		<div class="col-md-12">
			<fieldset>
			<legend>{tr}Select Shipping Method{/tr}</legend>
			{if count( $quotes ) > 1}
				<p>{tr}Please select the preferred shipping method to use on this order.{/tr}</p>
			{elseif !$freeShipping}
				<p>{tr}This is currently the only shipping method available to use on this order.{/tr}</p>
			{/if}
			{if $freeShipping}
				<div class="alert alert-success">
					{tr}This order qualifies for free shipping.{/tr}
					<input type="hidden" name="shipping_method" value="free_free"/>
				</div>
			{else}
				{include file="bitpackage:bitcommerce/checkout_javascript.tpl"}
				{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl"}
			{/if}
			</fieldset>
		</div>
		{/if}
	</div>
	{include file="bitpackage:bitcommerce/page_checkout_message_bot_inc.tpl" step=$checkoutStep}
	<div class="pull-right">
		<h3>{tr}Continue to Step 2{/tr}</h3>
		<!--<p>{tr}Payment Method{/tr}</p>-->
		<div class="form-group submit">
			<a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=shopping_cart" class="btn btn-default"><i class="fa fal fa-arrow-left"></i> {tr}Back{/tr}</a> <button class="btn btn-primary pull-right" value="Continue"/>{tr}Continue{/tr} <i class='fa fal fa-arrow-right'></i></button>
		</div>
	</div>
	{/form}
{/if}
</section><!-- end .body -->
{/strip}

{strip}
<div class="edit bitcommerce">

	<header class="page-header">
		<h1>{tr}Step 1 of 3 - Delivery Information{/tr}</h1>
	</header>

	<section class="body">
	{if !$gBitUser->isRegistered() || !$order->delivery || $changeAddress}
		{form name='checkout_address' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=checkout_shipping"}
			<input type="hidden" name="main_page" value="checkout_shipping" />
			<div class="row">
				{if !$gBitUser->isRegistered()}
				<div class="col-md-6">
					{include file="bitpackage:bitcommerce/register_customer.tpl"}
				</div>
				{/if}

				{if count( $addresses )}
				<div class="col-md-6">
					{legend legend="Choose Shipping Address"}
						{include file="bitpackage:bitcommerce/address_list_inc.tpl"}
					{/legend}
					<div class="form-group clear">
						<input type="submit" class="btn btn-primary" name="choose_address" value="Continue" /> <input type="submit" class="btn btn-default" name="" value="Cancel" /> <a class="btn btn-default pull-right" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=address_book">{tr}Address Book{/tr}</a>
					</div>
				</div>
				{/if}
				
				<div class="col-md-6">
					{legend legend="Enter a New Address"}
						{include file="bitpackage:bitcommerce/address_edit_inc.tpl"}
					{/legend}
					<div class="form-group clear">
						 <input type="submit" class="btn btn-primary" name="save_address" value="Continue" /> <input type="submit" class="btn btn-default" name="" value="Cancel" />
					</div>
				</div>
			</div>

		{/form}
	{else}
		{form name='checkout_address' }
		{formfeedback error=$errors}
		<div class="row">
			<div class="col-md-6">
				<fieldset>
					<legend>{tr}Shipping Address{/tr}</legend>
					<input type="hidden" name="action" value="process" />
					<input type="hidden" name="main_page" value="checkout_shipping" />
							<p>{tr}Your order will be shipped to the following address:{/tr}</p>
							{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$order->delivery}

					<div class="form-group submit">
						<button class="btn btn-default btn-sm" name="change_address"><i class="icon-home"></i> {tr}Change Shipping Address{/tr}</button>
					</div>
				</fieldset>

				<fieldset>
					<div class="form-group">
						{formlabel label="Special Instructions or Comments About Your Order" for=""}
						{forminput}
							<textarea name="comments" wrap="soft" class="form-control" rows="4">{$smarty.session.comments}</textarea>
						{/forminput}
					</div>
				</fieldset>

			</div>

			{if $shippingModules}
			<div class="col-md-6">
				<fieldset>
				<legend>{tr}Shipping Method{/tr}</legend>
				{if count( $quotes ) > 1}
					<p>{tr}Please select the preferred shipping method to use on this order.{/tr}</p>
				{elseif !$freeShipping}
					<p>{tr}This is currently the only shipping method available to use on this order.{/tr}</p>
				{/if}
				{if $freeShipping}
					<div class="alert alert-success">
						{tr}This order qualifies for free shipping.{/tr}
						<input type="hidden" name="shipping" value="free_free"/>
					</table>
				{else}
					{include file="bitpackage:bitcommerce/checkout_javascript.tpl"}
					{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl"}
				{/if}
				</fieldset>
			</div>
			{/if}
		</div>

			<h3>{tr}Continue to Step 2{/tr}</h3>
			<p>{tr}- choose your payment method.{/tr} </p>
			<div class="form-group submit">
				<a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=shopping_cart" class="btn btn-default"><i class="icon-arrow-left"></i> {tr}Back{/tr}</a> <button class="btn btn-primary" value="Continue"/>{tr}Continue{/tr} <i class='icon-arrow-right'></i></button>
			</div>
		{/form}
	{/if}
	</section><!-- end .body -->
</div>
{/strip}

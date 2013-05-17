{strip}

<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	{if !$gBitUser->isRegistered() || !$order->delivery || $changeAddress}
		<div class="body">
			{form name='checkout_address' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=checkout_shipping"}
				<input type="hidden" name="main_page" value="checkout_shipping" />
				{if !$gBitUser->isRegistered()}
					{include file="bitpackage:bitcommerce/register_customer.tpl"}
				{/if}

				{if count( $addresses )}
				<div class="width50p floatleft">
					<h1>{tr}Choose From Your Address Book or...{/tr}</h1>
					{tr}Please select the preferred shipping address if this order is to be delivered elsewhere.{/tr}
					{include file="bitpackage:bitcommerce/address_list.tpl"}
				</div>
				{/if}
				
				<div class="width50p floatleft">
					{legend legend="Enter a New Shipping Address"}
						{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`pages/address_new/address_new.php"}
					{/legend}
				</div>

				<div class="control-group clear">
					{forminput}
						 <input type="submit" class="btn btn-primary" name="submit_address" value="Continue" /> <input type="submit" class="btn" name="" value="Cancel" />
					{/forminput}
				</div>
			{/form}
		</div><!-- end .body -->
	{else}
		<header class="page-header">
			<h1>{tr}Step 1 of 3 - Delivery Information{/tr}</h1>
		</header>

		<section class="body">
			{form name='checkout_address' }
			{formfeedback error=$errors}
			<div class="row-fluid">
				<div class="span6">
					<fieldset>
						<legend>{tr}Shipping Address{/tr}</legend>
						<input type="hidden" name="action" value="process" />
						<input type="hidden" name="main_page" value="checkout_shipping" />
						<div class="control-group">
							{forminput}
								{assign var=address value=$order->delivery}
		<fieldset>
								{include file="bitpackage:bitcommerce/address_display.tpl"}
		</fieldset>
								{formhelp note="Your order will be shipped to the following address or you may change the shipping address by clicking the Change Address button."}
							{/forminput}
						</div>

						<div class="control-group submit">
							<input type="submit" class="btn" name="change_address" value="{tr}Change address{/tr}" />
						</div>
					</fieldset>

					<fieldset>
						<div class="control-group">
							{formlabel label="Special Instructions or Comments About Your Order" for=""}
							{forminput}
								<textarea name="comments" wrap="soft" class="width95p" rows="4">{$smarty.session.comments}</textarea>
							{/forminput}
						</div>
					</fieldset>

				</div>

				{if $shippingModules}
				<div class="span6">
					<fieldset>
					<legend>{tr}Shipping Method{/tr}</legend>
					{if count( $quotes ) > 1}
						<p>{tr}Please select the preferred shipping method to use on this order.{/tr}</p>
					{elseif !$freeShipping}
						<p>{tr}This is currently the only shipping method available to use on this order.{/tr}</p>
					{/if}
					{if $freeShipping}
						<table border="1" width="100%" cellspacing="2" cellpadding="2">
							<tr>
								<td colspan="3" width="100%">
									<table border="0" width="100%" cellspacing="0" cellpadding="2">
										<tr>
											<td colspan="3">{tr}Free Shipping{/tr}&nbsp;{$quotes.$i.icon}</td>
										</tr>
										<tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, 0)">
											<td width="100%">sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . zen_draw_hidden_field('shipping', 'free_free')</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					{else}
						{include file="bitpackage:bitcommerce/checkout_javascript.tpl"}
						{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl"}
					{/if}
					</fieldset>
				</div>
				{/if}

				<div class="clear"></div>

				<h3>{tr}Continue to Step 2{/tr}</h3>
				<p>{tr}- choose your payment method.{/tr} </p>
				<div class="control-group submit">
					<input type="submit" class="btn btn-primary" value="{tr}Continue{/tr}" />
				</div>
			{/form}
		</section><!-- end .body -->
	{/if}
</div>
{/strip}

{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	{if !$gBitUser->isRegistered() || !$order->delivery || $changeAddress}
		<div class="header">
			<h1>{tr}Enter a New Shipping Address{/tr}</h1>
		</div>

		<div class="body">
			{form name='checkout_address' }
				<input type="hidden" name="main_page" value="checkout_shipping" />
				{if !$gBitUser->isRegistered()}
					{include file="bitpackage:bitcommerce/register_customer.tpl"}
				{/if}

				{php} require BITCOMMERCE_PKG_PATH."templates/address_new.php"; {/php}

				{if count( $addresses )}
					<h3>{tr}...Or Choose From Your Address Book Entries{/tr}</h3>
					{tr}Please select the preferred shipping address if this order is to be delivered elsewhere.{/tr}

					<div class="row">
						{include file="bitpackage:bitcommerce/address_list.tpl"}
					</div>
				{/if}

				<div class="row">
					{forminput}
						<input type="submit" name="" value="Cancel" />
						<input type="submit" name="submit_address" value="Continue" />
					{/forminput}
				</div>
			{/form}
		</div><!-- end .body -->
	{else}
		<div class="header">
			<h1>{tr}Step 1 of 3 - Delivery Information{/tr}</h1>
		</div>

		<div class="body">
			{form name='checkout_address' }
				<input type="hidden" name="action" value="process" />
				<input type="hidden" name="main_page" value="checkout_shipping" />
				<div class="row">
					{formlabel label="Shipping Address"}
					{forminput}
						{assign var=address value=$order->delivery}
<fieldset>
						{include file="bitpackage:bitcommerce/address_display.tpl"}
</fieldset>
						{formhelp note="Your order will be shipped to the following address or you may change the shipping address by clicking the Change Address button."}
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="change_address" value="{tr}Change address{/tr}" />
				</div>

				<div class="clear"></div>

				{if $shippingModules}
					<h3>{tr}Shipping Method{/tr}</h3>
					{if count( $quotes ) > 1}
						<p>{tr}Please select the preferred shipping method to use on this order.{/tr}</p>
					{elseif !$freeShipping}
						<p>{tr}This is currently the only shipping method available to use on this order.{/tr}</p>
					{/if}
<fieldset>
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
						{section name=ix loop=$quotes}
							{counter assign=radioButtons start=0}
							<div class="row">
								<div class="formlabel">{$quotes[ix].module for=""}<br />{$quotes[ix].icon}</div>
								{forminput}
									{if $quotes[ix].error}
										{formfeedback error=$quotes[ix].error}
									{else}
										{section name=jx loop=$quotes[ix].methods}
											{* set the radio button to be checked if it is the method chosen *}
											{if ("$quotes[ix].id`_`$quotes[ix].methods[jx].id`" == $sessionShippingId) || ($smarty.section.ix.index == 1 && $smarty.section.jx.index == 1)}
												{assign var=checked value="$quotes[ix].id`_`$quotes[ix].methods[jx].id`"}
											{/if}

											{if $smarty.section.ix.total > 1 || $smarty.section.jx.total > 1 }
												{html_radios name="shipping" values="`$quotes[ix].id`_`$quotes[ix].methods[jx].id`" output=$quotes[ix].methods[jx].name checked=$checked}
											{else}
												{$quotes[ix].methods[jx].format_add_tax} <input type="hidden" name="shipping" value="`$quotes[ix].id`_ `$quotes[ix].methods[jx].id`" />
											{/if}
											{$quotes[ix].methods[jx].title}
											{$quotes[ix].methods[jx].format_add_tax}
											<br/>
										{/section}
									{/if}
								{/forminput}
							</div>
						{/section}
					{/if}
				{/if}
</fieldset>

<fieldset>
				<div class="row">
					{formlabel label="Special Instructions or Comments About Your Order" for=""}
					{forminput}
						<textarea name="comments" wrap="soft" cols="60" rows="5"></textarea>
					{/forminput}
				</div>
</fieldset>

				<div class="clear"></div>

				<h3>{tr}Continue to Step 2{/tr}</h3>
				<p>{tr}- choose your payment method.{/tr} </p>
				<div class="row submit">
					<input type="submit" value="Continue" />
				</div>
			{/form}
		</div><!-- end .body -->
	{/if}
</div>
{/strip}

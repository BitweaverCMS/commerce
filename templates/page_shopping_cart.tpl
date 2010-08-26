<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	<div class="header">
		<h1>{tr}Your Shopping Cart Contains:{/tr}</h1>
	{if $smarty.const.SHOW_TOTALS_IN_CART}
		<div class="smallText">
		{tr}Total Items:{/tr} {$gBitCustomer->mCart->count_contents()} 
		{if $gBitCustomer->mCart->show_weight()}
			{tr}Weight:{/tr} {$gBitCustomer->mCart->show_weight()|round:2}  {$smarty.const.TEXT_SHIPPING_WEIGHT} ( {$gBitCustomer->mCart->show_weight('kg')|round:2} {tr}Kg{/tr} ) {tr}Amount:{/tr} {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}
		{/if}
		</div>
	{/if}

	</div>

	<div class="body">
		{form name='cart_quantity' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=shopping_cart"}

{if $gBitCustomer->mCart->count_contents()}

	{if $gBitCustomer->mCart->mErrors.checkout}
		{formfeedback error=$gBitCustomer->mCart->mErrors.checkout}
	{/if}

<table class="shoppingcart">
<tr>
	<th>{tr}&nbsp;{/tr}</th>
	<th>{tr}Product(s){/tr}</th>
	<th>{tr}Qty.{/tr}</th>
	<th class="currency">{tr}Total{/tr}
	</th>
</tr>
{foreach from=$gBitCustomer->mCart->contents key=productsKey item=basket}
<tr class="{cycle values="odd,even"}">
	{assign var=product value=$gBitCustomer->mCart->getProductObject($productsKey)}
	{assign var=productHash value=$gBitCustomer->mCart->getProductHash($productsKey)}
	<td class="productListing-data">{if $gCommerceSystem->getConfig('IMAGE_SHOPPING_CART_STATUS')}<a href="{$product->getDisplayUrl()}"><img src="{$product->getThumbnailUrl('avatar')}" alt="{$product->getTitle()|escape}"/></a>{/if}</td>
	<td class="productListing-data" valign="top"><a href="{$product->getDisplayUrl()}"><span class="cartproductname">{$product->getTitle()|escape}</span></a>
		{if $basket.attributes}
			<ul>
			{foreach from=$basket.attributes key=optionKey item=valueId}
				{assign var=option value=$product->getOptionValue('',$valueId)}
				<li>{$option.products_options_values_name|escape}</li>
			{/foreach}
			</ul>
		{/if}
	</td>
	<td>
		<input type="text" name="cart_quantity[{$productsKey}]" value="{$basket.products_quantity}" size="4">
		{* remove multiple checkbox - can't find good home for now, but still should work <input type="checkbox" name="cart_delete[]" value="{$productsKey}"> *}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=shopping_cart&remove_product={$productsKey}">{biticon package="icons" iname="edit-delete" iexplain="Remove from cart"}</a>
	</td>
	<td class="currency">{$productHash.final_price_display}{if $productHash.onetime_charges}<br/>{$productHash.onetime_charges_display}{/if}</td>
</td>
</tr>
{/foreach}
	{if $gCommerceSystem->getConfig('SHOW_SHOPPING_CART_UPDATE')}
<tr class="subtotal">
	<td colspan="2">
			<select name="currency" onchange="this.form.submit()">
				<option value="">Change Currency...</option>
				{foreach from=$gCommerceCurrencies->currencies item=currencyHash key=currencyCode}
					<option value="{$currencyCode}" {if $smarty.session.currency==$currencyCode}selected="selected"{/if}>{$currencyHash.title|escape:html}</option>
				{/foreach}
			</select>
	</td>
	<td>
		{tr}Sub-Total:{/tr}
	</td>
	<td class="currency">
		 {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}
	<td>
	</td>
</tr>
	{/if}

<tr>
	<td>
	</td>
	<td colspan="2" style="text-align:right">
	{if $gBitCustomer->mCart->getWeight() && $smarty.const.SHOW_SHIPPING_ESTIMATOR_BUTTON}
			<a href="javascript:popupWindow('{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=popup_shipping_estimator&&site_style=basic')" class="button">{tr}Shipping Estimator{/tr}</a>
	{/if}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}" class="button">{tr}Continue Shopping{/tr}</a>
		<input type="submit" name="update_cart" value="{tr}Update Cart{/tr}" />
	</td>
	<td>
		{*<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}?main_page=checkout_shipping" class="button">{tr}Checkout{/tr}</a>*}
		<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}?main_page=checkout_proof" class="button">{tr}Checkout{/tr}</a>
	</td>
<tr>
</table>

{else}
	<div>
		{tr}Your Shopping Cart is empty.{/tr}
	</div>
{/if}


	{/form}
	</div><!-- end .body -->
</div>

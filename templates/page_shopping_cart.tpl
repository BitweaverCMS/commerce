<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	<div class="page-header">
		<h1>{tr}Your Shopping Cart Contains:{/tr}</h1>
	{if $smarty.const.SHOW_TOTALS_IN_CART}
		<div class="smallText">
		{tr}Total Items:{/tr} {$gBitCustomer->mCart->count_contents()} 
		{if $gBitCustomer->mCart->show_weight()}
			{tr}Weight:{/tr} {$gBitCustomer->mCart->show_weight()|round:2}  {tr}lbs{/tr} ( {$gBitCustomer->mCart->show_weight('kg')|round:2} {tr}Kg{/tr} ) {tr}Amount:{/tr} {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}
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

<table class="shoppingcart table">
<tr>
	<th>{tr}&nbsp;{/tr}</th>
	<th>{tr}Product(s){/tr}</th>
	<th>{tr}Qty.{/tr}</th>
	<th class="currency">{tr}Total{/tr}</th>
</tr>
{foreach from=$gBitCustomer->mCart->contents key=productsKey item=basket}
<tr class="{cycle values="odd,even"}">
	{assign var=product value=$gBitCustomer->mCart->getProductObject($productsKey)}
	{assign var=productHash value=$gBitCustomer->mCart->getProductHash($productsKey)}
	<td class="productListing-data">{if $gCommerceSystem->getConfig('IMAGE_SHOPPING_CART_STATUS')}<a href="{$product->getDisplayUrl()}"><img src="{$product->getThumbnailUrl('avatar')}" alt="{$product->getTitle()|escape}"/></a>{/if}</td>
	<td class="productListing-data" valign="top"><a href="{$product->getDisplayUrl()}"><span class="cartproductname">{$product->getTitle()|escape}</span></a>
		{if $basket.attributes}
			<ul class="unstyled">
			{foreach from=$basket.attributes key=optionKey item=valueId}
				{assign var=option value=$product->getOptionValue('',$valueId)}
				<li>{$option.products_options_values_name|escape}</li>
			{/foreach}
			</ul>
		{/if}
	</td>
	<td>
		<input type="text" class="input-mini" name="cart_quantity[{$productsKey}]" value="{$basket.products_quantity}">
		{* remove multiple checkbox - can't find good home for now, but still should work <input type="checkbox" name="cart_delete[]" value="{$productsKey}"> *}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=shopping_cart&remove_product={$productsKey}">{booticon iname="icon-trash"  package="icons"  iexplain="Remove from cart"}</a>
	</td>
	<td class="currency text-right">{$productHash.final_price_display}{if $productHash.onetime_charges}<br/>{$productHash.onetime_charges_display}{/if}</td>
</td>
</tr>
{/foreach}
	{if $gCommerceSystem->getConfig('SHOW_SHOPPING_CART_UPDATE')}
<tr class="subtotal">
	<td colspan="2">
		<select name="currency" onchange="this.form.submit()">
			<option value="">{tr}Change Currency{/tr}...</option>
			{foreach from=$gCommerceCurrencies->currencies item=currencyHash key=currencyCode}
				<option value="{$currencyCode}" {if $smarty.session.currency==$currencyCode}selected="selected"{/if}>{$currencyHash.title|tra|escape:html}</option>
			{/foreach}
		</select>
	</td>
	<td>
		{tr}Sub-Total:{/tr}
	</td>
	<td class="currency">
		 {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}
	</td>
</tr>
	{/if}
</table>
<div class="text-right">
	{if $gBitCustomer->mCart->getWeight() && $smarty.const.SHOW_SHIPPING_ESTIMATOR_BUTTON}
			<a href="javascript:popupWindow('{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=popup_shipping_estimator&&site_style=basic')" class="btn">{tr}Shipping Estimator{/tr}</a>
	{/if}
		<input type="submit" class="btn" name="update_cart" value="{tr}Update Cart{/tr}" />
		{*<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}?main_page=checkout_shipping" class="btn">{tr}Checkout{/tr}</a>*}
		<a href="{$smarty.const.BITCOMMERCE_PKG_SSL_URI}?main_page=checkout_proof" class="btn btn-primary">{tr}Checkout{/tr}</a>
</div>

{else}
	<div>
		{tr}Your Shopping Cart is empty.{/tr}
	</div>
{/if}


	{/form}
	</div><!-- end .body -->
</div>

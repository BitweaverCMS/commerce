<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	<div class="header">
		<h1>{tr}The Shopping Cart Contains:{/tr}</h1>
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
		{form name='cart_quantity' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=update_product"}

{if $gBitCustomer->mCart->count_contents()}

	{if $gBitCustomer->mCart->mErrors.checkout}
		{formfeedback error=$gBitCustomer->mCart->mErrors.checkout}
	{/if}

<table class="shoppingcart">
<tr>
	<th class="main">{tr}Remove{/tr}</th>
	<th class="main">{tr}&nbsp;{/tr}</th>
	<th class="main">{tr}Product(s){/tr}</th>
	<th class="main">{tr}Qty.{/tr}</th>
	<th class="main">{tr}Total{/tr}</th>
</tr>
{foreach from=$gBitCustomer->mCart->contents key=basketId item=basket}
<tr class="{cycle values="odd,even"}">
	{assign var=product value=$gBitCustomer->mCart->getProductObject($basketId)}
	{assign var=productHash value=$gBitCustomer->mCart->getProductHash($basketId)}
	<td class="productListing-data"></td>
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
	<td>{$basket.customers_basket_quantity}</td>
	<td>{$productHash.final_price_display}{if $productHash.onetime_charges_display}<br/>{$productHash.onetime_charges_display}{/if}</td>
</td>
</tr>
{/foreach}
	{if $smarty.const.SHOW_SHOPPING_CART_UPDATE == 2 || $smarty.const.SHOW_SHOPPING_CART_UPDATE == 3}
<tr>
	<td colspan="2">
	</td>
	<td>
	</td>
	<td>
		<input type="submit" name="submit_address" value="{tr}Update Cart{/tr}" />
	</td>
	<td>
		<div class="subtotal">{tr}Sub-Total:{/tr} {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}</div>
	<td>
	<td>
	</td>
</tr>
	{/if}
</table>

<div>
		{if	$smarty.const.SHOW_SHIPPING_ESTIMATOR_BUTTON}
			<a href="javascript:popupWindow('{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=popup_shipping_estimator&&site_style=basic')" class="button">{tr}Shipping Estimator{/tr}</a>
		{/if}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}" class="button">{tr}Continue Shopping{/tr}</a>
		<a href="{$smarty.const.BITCOMMERCE_SSL_PKG_URI}?main_page=checkout_shipping" class="button">{tr}Checkout{/tr}</a>
</div>
{else}
	<div>
		{tr}Your Shopping Cart is empty.{/tr}
	</div>
{/if}


	{/form}
</div><!-- end .body -->

{strip}
{assign var=cartHasContents value=$gBitCustomer->mCart->count_contents()}
<div class="edit bitcommerce">
<header class="page-header">
{if $cartHasContents && $smarty.const.SHOW_TOTALS_IN_CART}
	<div class="smallText pull-right">
	<strong>{$cartHasContents} {tr}Items{/tr}</strong>
	{if $gBitCustomer->mCart->show_weight()}
		, {$gBitCustomer->mCart->show_weight()|round:2} {tr}lbs{/tr} ( {$gBitCustomer->mCart->show_weight('kg')|round:2} {tr}Kg{/tr} )
	{/if}
	, {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}
	</div>
{/if}
	<h1>{tr}Your Shopping Cart Contains:{/tr}</h1>
</header>

<div class="body shopping-cart">
	{form name='cart_quantity' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=shopping_cart"}
	{if $cartHasContents}
		{if $gBitCustomer->mCart->mErrors.checkout}
			{formfeedback error=$gBitCustomer->mCart->mErrors.checkout}
		{/if}

		{foreach from=$gBitCustomer->mCart->contents key=productsKey item=basket}
		<div class="row cart-item checkout-individual-item pv-2 {cycle values="odd,even"}">
			{assign var=product value=$gBitCustomer->mCart->getProductObject($productsKey)}
			<div class="col-xs-4 col-sm-2 col-md-3">{if $gCommerceSystem->getConfig('IMAGE_SHOPPING_CART_STATUS')}<a href="{$product->getDisplayUrl()}"><img src="{$product->getThumbnailUrl('avatar')}" class="img-responsive center-block" alt="{$product->getTitle()|escape}"/></a>{/if}</div>
			<div class="col-xs-8 col-sm-6"><a href="{$product->getDisplayUrl()}"><span class="cartproductname">{$product->getTitle()}</span></a>
				{if $basket.attributes}
					<ul class="list-unstyled">
					{foreach from=$basket.attributes key=optionKey item=$attrHash}
						<li>
							{if $attrHash.products_options_values_text}
								<div class="alert alert-info"><strong>{$attrHash.products_options_values_name}:</strong> {$attrHash.products_options_values_text}</div>
							{else}
								{$attrHash.products_options_values_name}
							{/if}
						</li>
					{/foreach}
					</ul>
				{/if}
			</div>
			<div class="col-xs-4 col-sm-2 col-md-1 text-right">
				<input type="number" class="form-control input-mini" name="cart_quantity[{$productsKey}]" value="{$basket.products_quantity}">
			</div>
			{assign var=productHash value=$gBitCustomer->mCart->getProductHash($productsKey)}
			<div class="col-xs-4 col-sm-1 currency text-right"><strong>{$productHash.final_price_display}</strong>{if $productHash.onetime_charges}<br/>{$productHash.onetime_charges_display}{/if}
				{if $basket.products_quantity > 1}<div class="small nowrap">{$gCommerceCurrencies->format($productHash.final_price)}&nbsp;{tr}Each{/tr}</div>{/if}
			{if $productHash.quantity_discount}
				<div class="small nowrap">{$productHash.quantity_discount}%&nbsp;{tr}Discount{/tr}</div>
			{/if}
			</div>
			<div class="col-xs-4 col-sm-1">
				{forminput label="checkbox"}
					<input type="checkbox" name="cart_delete[]" value="{$productsKey}">
					<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=shopping_cart&remove_product={$productsKey}">{booticon iname="fa-trash" class="fa-large" iexplain="Remove from cart"}</a>
				{/forminput}
			</div>
		</div>
		{/foreach}
	{else}
		<div>
			{tr}Your Shopping Cart is empty.{/tr}
		</div>
	{/if}


	<div class="row subtotal">
		<div class="col-sm-6 col-sm-offset-6 currency">
			{if $cartHasContents}{tr}Sub-Total:{/tr} {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}{/if}
			<select class="form-control inline-block width-auto ml-1" name="currency" onchange="this.form.submit()">
			{foreach from=$gCommerceCurrencies->currencies item=currencyHash key=currencyCode}
				<option value="{$currencyCode}" {if $smarty.session.currency==$currencyCode || (!$smarty.session.currency && $currencyCode==$smarty.const.DEFAULT_CURRENCY)}selected="selected"{/if}>{$currencyHash.code|escape:html}</option>
			{/foreach}
			</select>
		</div>
		<div class="col-xs-12 text-right">
			{if $gCommerceSystem->getConfig('CONTINUE_SHOPPING_URL')}<a class="pull-left btn btn-default" href="{$gCommerceSystem->getConfig('CONTINUE_SHOPPING_URL')}">{tr}Continue Shopping{/tr}</a>{/if}
			{if $cartHasContents}{if $gCommerceSystem->getConfig('SHOW_SHOPPING_CART_UPDATE')}<input type="submit" class="btn btn-default" name="update_cart" value="{tr}Update Cart{/tr}" />{/if} <input type="submit" class="btn btn-primary" name="checkout" value="{tr}Checkout{/tr}" />{/if} 
		</div>
	</div>

{/form}
</div><!-- end .body -->
</div><!-- end .edit -->
{/strip}

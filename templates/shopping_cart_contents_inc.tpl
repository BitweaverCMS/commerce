{foreach from=$cart->contents key=cartProductsKey item=basket}
<div class="row cart-item checkout-individual-item pv-2 {cycle values="odd,even"}">
	{assign var=cartProduct value=$cart->getProductObject($basket.products_id)}
	{if $cartProduct}
	<div class="col-xs-4 col-sm-2 col-md-3">{if $gCommerceSystem->getConfig('IMAGE_SHOPPING_CART_STATUS')}<a href="{$cartProduct->getDisplayUrl()}"><img src="{$cartProduct->getThumbnailUrl('avatar')}" class="img-responsive center-block" alt="{$cartProduct->getTitle()|escape}"/></a>{/if}</div>
	<div class="col-xs-8 col-sm-6"><a href="{$cartProduct->getDisplayUrl()}"><span class="cartproductname">{$cartProduct->getTitle()}</span></a>
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
	<div class="col-xs-3 col-sm-2 col-md-1 text-right no-padding">
		<input type="number" class="form-control input-mini" name="cart_quantity[{$cartProductsKey}]" value="{$basket.products_quantity}">
	</div>
	{assign var=cartProductHash value=$cart->getProductHash($cartProductsKey)}
	<div class="col-xs-4 col-sm-2 col-md-2 currency text-right"><div class="inline-block"><strong>{$cartProductHash.final_price_display}</strong>{if $cartProductHash.onetime_charges}<br/>{$cartProductHash.onetime_charges_display}{/if}
		{if $basket.products_quantity > 1}<div class="small nowrap">{$gCommerceCurrencies->format($cartProductHash.final_price)}&nbsp;{tr}Each{/tr}</div>{/if}
	{if $cartProductHash.quantity_discount}
		<div class="small nowrap">{$cartProductHash.quantity_discount}%&nbsp;{tr}Discount{/tr}</div>
	{/if}
		</div>
		{forminput label="checkbox inline-block"}
			<input type="checkbox" name="cart_delete[]" value="{$cartProductsKey}">
			<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=shopping_cart&remove_product={$cartProductsKey}">{booticon iname="fa-trash" class="fa-large" iexplain="Remove from cart"}</a>
		{/forminput}
	</div>
	{/if}
</div>
{/foreach}


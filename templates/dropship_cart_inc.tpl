{* Read-only cart listing for the drop ship rail. Expects cart=<CommerceShoppingCart>. Mirrors shopping_cart_contents_inc.tpl without the quantity and delete controls. *}
{foreach from=$cart->contents key=cartProductsKey item=basket}
	{assign var=cartProduct value=$cart->getProductObject($basket.products_id)}
	{if $cartProduct}
	{assign var=cartProductHash value=$cart->getProductHash($cartProductsKey)}
	<div class="media cart-line">
		{if $gCommerceSystem->getConfig('IMAGE_SHOPPING_CART_STATUS')}
		<div class="media-left">
			<a href="{$cartProduct->getDisplayUrl()}"><img class="media-object" src="{$cartProduct->getThumbnailUrl('avatar')}" alt="{$cartProduct->getTitle()|escape}"></a>
		</div>
		{/if}
		<div class="media-body">
			<a href="{$cartProduct->getDisplayUrl()}" class="cartproductname">{$cartProduct->getTitle()|escape}</a>
			{if $basket.attributes}
			<ul class="list-unstyled cart-line-options">
				{foreach from=$basket.attributes item=attrHash}
				<li><strong>{$attrHash.products_options_name|escape}:</strong> {$attrHash.products_options_values_name|escape}{if $attrHash.products_options_values_text} &mdash; {$attrHash.products_options_values_text|escape}{/if}</li>
				{/foreach}
			</ul>
			{/if}
			<div class="cart-line-qty">{$basket.products_quantity} &times; {$gCommerceCurrencies->format($cartProductHash.final_price)}{if $cartProductHash.quantity_discount} &middot; {$cartProductHash.quantity_discount}% {tr}discount{/tr}{/if}{if $cartProductHash.onetime_charges} &middot; {$cartProductHash.onetime_charges_display} {tr}one time{/tr}{/if}</div>
		</div>
		<div class="media-right num">{$cartProductHash.final_price_display}</div>
	</div>
	{/if}
{/foreach}

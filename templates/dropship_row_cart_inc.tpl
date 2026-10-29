{* Custom quantities for one drop ship address: the live cart's lines (thumbnail, title, options) with a quantity box each.
   Expects cart=<the live CommerceShoppingCart>, rowIdx, and quantities (line key → quantity, empty when the row sends the cart as is).
   Inputs are disabled until the block is opened so only the open row posts. *}
{foreach from=$cart->contents key=cartProductsKey item=basket}
	{assign var=cartProduct value=$cart->getProductObject($basket.products_id)}
	{if $cartProduct}
	<div class="media cart-line">
		{if $gCommerceSystem->getConfig('IMAGE_SHOPPING_CART_STATUS')}
		<div class="media-left">
			<img class="media-object" src="{$cartProduct->getThumbnailUrl('avatar')}" alt="{$cartProduct->getTitle()|escape}">
		</div>
		{/if}
		<div class="media-body">
			<div class="cartproductname">{$cartProduct->getTitle()|escape}</div>
			{if $basket.attributes}
			<ul class="list-unstyled cart-line-options">
				{foreach from=$basket.attributes item=attrHash}
				<li><strong>{$attrHash.products_options_name|escape}:</strong> {$attrHash.products_options_values_name|escape}{if $attrHash.products_options_values_text} &mdash; {$attrHash.products_options_values_text|escape}{/if}</li>
				{/foreach}
			</ul>
			{/if}
			<div class="cart-line-qty">{tr}In the cart:{/tr} {$basket.products_quantity}</div>
		</div>
		<div class="media-right">
			<input type="number" class="form-control input-sm drop-ship-qty-input" name="qty[{$rowIdx}][{$cartProductsKey|escape}]" value="{if isset($quantities[$cartProductsKey])}{$quantities[$cartProductsKey]}{else}{$basket.products_quantity}{/if}" min="0" step="1" aria-label="{tr}Quantity of{/tr} {$cartProduct->getTitle()|escape}" disabled>
		</div>
	</div>
	{/if}
{/foreach}

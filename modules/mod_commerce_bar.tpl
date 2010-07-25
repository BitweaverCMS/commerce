<div class="commercebar">

		{include file="bitpackage:bitcommerce/commerce_nav.tpl"}

	<span class="floaticon">
	<strong>{tr}Your Cart{/tr}:</strong>
	{if $sessionCart}
	{if count($gCommerceCurrencies->currencies) > 1}
	<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=shopping_cart">{$sessionCart->count_contents()} {if $sessionCart->count_contents()==1}{tr}Item{/tr}{else}{tr}Items{/tr}{/if}</a>
	( <span class="clickable" onclick="BitBase.toggleElementDisplay('currencychooser','inline',false);return false;">{$gCommerceCurrencies->format($sessionCart->show_total())} ) 
	{/if}
	{else}
	{tr}<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=shopping_cart">Empty</a>{/tr}
	{/if}

	</span>
</div>

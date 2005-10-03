{strip}
<ul>
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=shopping_cart">{tr}My Cart{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=products_new">{tr}New Products{/tr}</a></li>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=account">{tr}My Orders and Addresses{/tr}</a></li>
	{/if}
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=checkout_shipping">{tr}Checkout{/tr}</a></li>
</ul>
{/strip}

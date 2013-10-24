{strip}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=shopping_cart">{tr}My Cart{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=checkout_shipping">{tr}Checkout{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=products_new">{tr}New Products{/tr}</a></li>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=account">{tr}My Orders and Addresses{/tr}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_bitcommerce_retailer')}
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=commissions">{tr}My Sales and Commissions{/tr}</a></li>
	{/if}
</ul>
{/strip}

{strip}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}account">{tr}My Orders{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}address_book">{tr}Address Book{/tr}</a></li>
	{/if}
	<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}shopping_cart">{tr}Shopping Cart{/tr}</a></li>
	{if $gBitUser->hasPermission('p_bitcommerce_retailer')}
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}commissions">{tr}My Sales and Commissions{/tr}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_bitcommerce_whitelabel')}
		<li><a class="item" href="{$smarty.const.BITCOMMERCE_PKG_URL}whitelabel">{tr}Customize My Store{/tr}</a></li>
	{/if}
</ul>
{/strip}

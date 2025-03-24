{strip}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
	<li><a class="item" href="{"shopping_cart"|zen_href_link}">{tr}My Cart{/tr}</a></li>
	<li><a class="item" href="{"checkout_shipping"|zen_href_link}">{tr}Checkout{/tr}</a></li>
	<li><a class="item" href="{"products_new"|zen_href_link}">{tr}New Products{/tr}</a></li>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{"account"|zen_href_link}">{tr}My Orders and Addresses{/tr}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_bitcommerce_retailer')}
		<li><a class="item" href="{"commissions"|zen_href_link}">{tr}My Sales and Commissions{/tr}</a></li>
	{/if}
</ul>
{/strip}

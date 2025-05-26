<h1>{$smart.const.HEADING_TITLE}</h1>
<p class="greetUser">
	{tr}Welcome{/tr} 	
	{if $gBitUser->isRegistered()}
    	{$gBitUser->getDisplayName()}
    {/if}
</p>

{foreach from=$mainDisplayBlocks item=displayBlock}
	{if !empty($displayBlock.configuration_value)}
		{if $displayBlock.configuration_key == "SHOW_PRODUCT_INFO_MAIN_COMMISSIONED_PRODUCTS"}
			{*include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/modules/`$smarty.const.FILENAME_COMMISSIONED_PRODUCTS_MODULE`"*}
		{/if}
		{if $displayBlock.configuration_key == "SHOW_PRODUCT_INFO_MAIN_FEATURED_PRODUCTS"}
			{*include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/modules/`$smarty.const.FILENAME_FEATURED_PRODUCTS_MODULE`"*}
		{/if}
		{if $displayBlock.configuration_key == "SHOW_PRODUCT_INFO_MAIN_SPECIALS_PRODUCTS"}
			{*include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/modules/`$smarty.const.FILENAME_SPECIALS_INDEX`"*}
		{/if}
		{if $displayBlock.configuration_key == "SHOW_PRODUCT_INFO_MAIN_NEW_PRODUCTS"}
			{*include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/modules/`$smarty.const.FILENAME_NEW_PRODUCTS`"*}
		{/if}
		{if $displayBlock.configuration_key == "SHOW_PRODUCT_INFO_MAIN_UPCOMING"}
			{*include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/modules/`$smarty.const.FILENAME_UPCOMING_PRODUCTS`"*}
		{/if}
	{/if}
{/foreach}


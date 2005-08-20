{if $sideboxSpecial}
{bitmodule title=$moduleTitle name="specials"}
<div align="center">
	<a href="{$sideboxSpecial.display_url}"><img src="{$sideboxSpecial.products_image_url}" alt="{$sideboxSpecial.products_name|escape:html}" /></a><br/><a href="{$sideboxSpecial.display_url}">{$sideboxSpecial.products_name}</a>
	<div class="specialprice">{$sideboxSpecial.display_special_price}</div>
</div>
<div><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=specials">{tr}See more...{/tr}</a></div>
{/bitmodule}
{/if}

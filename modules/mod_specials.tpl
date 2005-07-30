{if $sideboxSpecial}
{bitmodule title=$moduleTitle name="specials"}
<div align="center">
	<a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page={$sideboxSpecial.info_page}&products_id={$sideboxSpecial.products_id}"><img src="{$sideboxSpecial.products_image_url}" alt="{$sideboxSpecial.products_name|escape:html}" /></a><br/><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_info&products_id={$sideboxSpecial.products_id}">{$sideboxSpecial.products_name}</a>
	<div class="specialprice">{$sideboxSpecial.display_special_price}</div>
</div>
{/bitmodule}
{/if}
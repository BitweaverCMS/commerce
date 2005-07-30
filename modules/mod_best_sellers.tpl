{if $sideboxBestSellers}
{bitmodule title=$moduleTitle name="bestsellers"}

<ol>
{section name=ix loop=$sideboxBestSellers}
	<li><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page={$bestsellers_list[ix].info_page}&products_id={$bestsellers_list[ix].products_id}"> {$sideboxBestSellers[ix].products_name}</a></li>
{/section}
</ol>

{/bitmodule}
{/if}
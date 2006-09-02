{strip}
{if $sideboxFeature}
	{bitmodule title=$moduleTitle name="bc_featured"}
		<a href="{$sideboxFeature.display_url}"><img class="thumb" src="{$sideboxFeature.products_image_url}" alt="{$sideboxFeature.products_name|escape:html}" /></a>
		<h4><a href="{$sideboxFeature.display_url}">{$sideboxFeature.products_name}</a></h4>
		{$sideboxFeature.display_price}
		<br/><a class="moreinfo" href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=featured_products">{tr}See more...{/tr}</a>
	{/bitmodule}
{/if}
{/strip}

{strip}
{if $sideboxSpecial}
	{bitmodule title=$moduleTitle name="bc_specials"}
		<a href="{$sideboxSpecial.display_url}"><img class="thumb" src="{$sideboxSpecial.products_image_url}" alt="{$sideboxSpecial.products_name|escape:html}" /></a>
		<h4><a href="{$sideboxSpecial.display_url}">{$sideboxSpecial.products_name}</a></h4>
		{$sideboxSpecial.display_special_price}
		<br/><a class="moreinfo" href="{'specials'|zen_get_page_url}">{tr}See more...{/tr}</a>
	{/bitmodule}
{/if}
{/strip}

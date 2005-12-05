{bitmodule title=$moduleTitle name="bc_whatsnew"}
	<a href="{$newProduct.display_url}"><img class="thumb" src="{$newProduct.products_image_url}" alt="{$newProduct.products_name|escape:html}" /></a>
	<h4><a href="{$newProduct.display_url}">{$newProduct.products_name}</a></h4>
	{$newProduct.display_price}
	<br/>
	<a class="moreinfo" href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=products_new">{tr}See more...{/tr}</a>
{/bitmodule}

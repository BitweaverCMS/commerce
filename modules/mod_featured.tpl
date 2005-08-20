{if $sideboxFeature}
{bitmodule title=$moduleTitle name="featured"}
<div align="center">

<a href="{$sideboxFeature.display_url}"><img src="{$sideboxFeature.products_image_url}" alt="{$sideboxFeature.products_name|escape:html}" /></a><br /><a href="{$sideboxFeature.display_url}">
<br />{$sideboxFeature.products_name}</a><br />
{if $sideboxFeature.specials_new_products_price}
      <span class="normalprice">{$sideboxFeature.display_price}</span><br />
      <span class="specialprice">{$sideboxFeature.display_special_price}</span>
{else}
      <span class="price">{$sideboxFeature.display_price}</span>
{/if}

<div><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=featured_products">{tr}See more...{/tr}</a></div>
</div>
{/bitmodule}
{/if}

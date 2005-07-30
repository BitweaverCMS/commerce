{if $sideboxFeature}
{bitmodule title=$moduleTitle name="featured"}
<div align="center">

<a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page={$sideboxFeature.info_page}&products_id={$sideboxFeature.products_id}"><img src="{$sideboxFeature.products_image_url}" alt="{$sideboxFeature.products_name|escape:html}" /></a><br /><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_info&products_id={$sideboxFeature.products_id}">
<br />{$sideboxFeature.products_name}</a><br />
{if $sideboxFeature.specials_new_products_price}
      <span class="normalprice">{$sideboxFeature.display_price}</span><br />
      <span class="specialprice">{$sideboxFeature.display_special_price}</span>
{else}
      <span class="price">{$sideboxFeature.display_price}</span>
{/if}

</div>
{/bitmodule}
{/if}
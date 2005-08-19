{bitmodule title=$moduleTitle name="whatsnew"}
<center>
<a href="{$newProduct.display_url}"><img src="{$newProduct.products_image_url}" alt="{$newProduct.products_name|escape:html}" /></a><br /><a href="{$newProduct.display_url}">
<br />{$newProduct.products_name}</a><br />
{if $newProduct.specials_new_products_price}
      <span class="normalprice">{$newProduct.display_price}</span><br />
      <span class="specialprice">{$newProduct.display_special_price}</span>
{else}
      <span class="price">{$newProduct.display_price}</span>
{/if}
<div><a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=products_new">{tr}See more...{/tr}</a></div>
</center>
{/bitmodule}

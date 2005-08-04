{bitmodule title=$moduleTitle name="whatsnew"}
<center>
<a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page={$newProduct.info_page}&products_id={$newProduct.products_id}"><img src="{$newProduct.products_image_url}" alt="{$newProduct.products_name|escape:html}" /></a><br /><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_info&products_id={$newProduct.products_id}">
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

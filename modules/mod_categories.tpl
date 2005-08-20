{bitmodule title="$moduleTitle" name="categories"}

<ul>

{section name=ix loop=$box_categories_array}
<li><a {if $box_categories_array[ix].current}id="active"{/if} href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=index&amp;{$box_categories_array[ix].path}">
{$box_categories_array[ix].name}
{if $box_categories_array[ix].has_sub_cat}
-&gt;
{/if}
{if $smarty.const.SHOW_COUNTS == 'true'} ({$box_categories_array[ix].count}) {/if}
</a>

</li>
{/section}
<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=specials">{tr}Specials{/tr}</a></li>
<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=products_new">{tr}New Products{/tr}</a></li>

</ul>
{/bitmodule}
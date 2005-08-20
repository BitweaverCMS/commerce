{if $sideboxDocumentCategories}
{bitmodule title=$moduleTitle name="documentcategories"}
{section name=ix loop=$sideboxDocumentCategories}
{if $sideboxDocumentCategories[ix].top == 'true'}
        {assign var=new_style value='category-top'}
{elseif $sideboxDocumentCategories[ix].has_sub_cat}
        {assign var=new_style value='category-subs'}
{else}
        {assign var=new_style value='category-products'}
{/if}

<a class="{$new_style}" href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=index&amp;{$sideboxDocumentCategories[ix].path}">

{if $sideboxDocumentCategories[ix].current}
{if $sideboxDocumentCategories[ix].has_sub_cat}
<span class="category-subs-parent">{$sideboxDocumentCategories[ix].name}</span>
{else}
<span class="category-subs-selected">{$sideboxDocumentCategories[ix].name}</span>
{/if}
{else}
        {$sideboxDocumentCategories[ix].name}
{/if}

{if $sideboxDocumentCategories[ix].has_sub_cat}
        {$smart.const.CATEGORIES_SEPARATOR}
{/if}
</a>

{if $smarty.const.SHOW_COUNTS == 'true'}
    {if ($smart.const.CATEGORIES_COUNT_ZERO == '1' && $sideboxDocumentCategories[ix].count == 0) || $sideboxDocumentCategories[$i].count >= 1}
		{$smarty.const.CATEGORIES_COUNT_PREFIX}{$sideboxDocumentCategories[ix].count}{$smarty.const.CATEGORIES_COUNT_SUFFIX}
	{/if}
{/if}

<br />
{/section}
{/bitmodule}
{/if}
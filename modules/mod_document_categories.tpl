{if $box_categories_array}
{bitmodule title=$moduleTitle name="documentcategories"}
{section name=ix loop=$box_categories_array}
{if $box_categories_array[ix].top == 'true'}
        $new_style = 'category-top';
{elseif $box_categories_array[ix].has_sub_cat}
        $new_style = 'category-subs';
{else}
        $new_style = 'category-products
{/if}

<a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, $box_categories_array[ix].path) . '">';

{if $box_categories_array[ix].current}
{if $box_categories_array[ix].has_sub_cat}
span class="category-subs-parent">' . $box_categories_array[ix].name . '</span>';
{else}
span class="category-subs-selected">' . $box_categories_array[ix].name . '</span>';
{/if}
{else}
        $content .= $box_categories_array[ix].name;
{/if}

{if $box_categories_array[ix].has_sub_cat}
        $content .= CATEGORIES_SEPARATOR;
{/if}
</a>';

{if SHOW_COUNTS == 'true'}
        if ((CATEGORIES_COUNT_ZERO == '1' and $box_categories_array[ix].count == 0) or $box_categories_array[$i]['count'] >= 1) {
          $content .= CATEGORIES_COUNT_PREFIX . $box_categories_array[ix].count . CATEGORIES_COUNT_SUFFIX;
{/if}
{/if}

<br />';
{/section}
{/bitmodule}
{/if}
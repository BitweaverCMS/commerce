{strip}
{bitmodule title="$moduleTitle" name="bc_categories"}
	<ul>
		{section name=ix loop=$box_categories_array}
			<li>
				<a {if $box_categories_array[ix].current}id="active"{/if} href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=index&amp;{$box_categories_array[ix].path}">
					{$box_categories_array[ix].name}
					{if $box_categories_array[ix].has_sub_cat} -&gt; {/if}
					{if $smarty.const.SHOW_COUNTS == 'true'} ({$box_categories_array[ix].count}) {/if}
				</a>
			</li>
		{/section}

		{if $smarty.const.SHOW_CATEGORIES_BOX_PRODUCTS_NEW=='true'}
			<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=specials">{tr}Specials{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_CATEGORIES_BOX_SPECIALS=='true'}
			<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=products_new">{tr}New Products{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS=='true'}
			<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=featured_products">{tr}Featured Products{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_CATEGORIES_BOX_PRODUCTS_ALL=='true'}
			<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=products_new">{tr}All Products{/tr}</a></li>
		{/if}
	</ul>
{/bitmodule}
{/strip}

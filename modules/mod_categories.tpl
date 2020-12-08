{strip}
{bitmodule title="$moduleTitle" name="bc_categories"}
	<ul class="list-unstyled">
		{section name=ix loop=$box_categories_array}
			<li>
				<a {if $box_categories_array[ix].current}id="active"{/if} href="{'index'|zen_get_page_url:$box_categories_array[ix].path}">
					{$box_categories_array[ix].name}
					{if $box_categories_array[ix].has_sub_cat} -&gt; {/if}
					{if $smarty.const.SHOW_COUNTS == 'true'} ({$box_categories_array[ix].count}) {/if}
				</a>
			</li>
		{/section}

		{if $smarty.const.SHOW_CATEGORIES_BOX_PRODUCTS_NEW=='true'}
			<li><a href="{'specials|zen_get_page_url}">{tr}Specials{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_CATEGORIES_BOX_SPECIALS=='true'}
			<li><a href="{'products_new|zen_get_page_url}">{tr}New Products{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS=='true'}
			<li><a href="{'featured_products|zen_get_page_url}">{tr}Featured Products{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_CATEGORIES_BOX_PRODUCTS_ALL=='true'}
			<li><a href="{'products_new|zen_get_page_url}">{tr}All Products{/tr}</a></li>
		{/if}
	</ul>
{/bitmodule}
{/strip}

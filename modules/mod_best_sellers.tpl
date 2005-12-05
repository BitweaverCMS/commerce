{if $sideboxBestSellers}
	{bitmodule title=$moduleTitle name="bc_bestsellers"}
		<ol>
			{section name=ix loop=$sideboxBestSellers}
				<li><a href="{$bestsellers_list[ix].display_url}"> {$sideboxBestSellers[ix].products_name}</a></li>
			{/section}
		</ol>
	{/bitmodule}
{/if}

{strip}
{if $sideboxBannersAll}
	{bitmodule title=$moduleTitle name="bc_bannerboxall"}
		<div style="text-align:center;">
			{section name=ix loop=$sideboxBannersAll}
				{$sideboxBannersAll.banner}<br />
			{/section}
		</div>
	{/bitmodule}
{/if}
{/strip}

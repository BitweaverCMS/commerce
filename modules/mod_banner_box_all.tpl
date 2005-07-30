{if $sideboxBannersAll}
{bitmodule title=$moduleTitle name="bannerboxall"}
<div align="center">';

{section name=ix loop=$sideboxBannersAll}
	<div>{$sideboxBannersAll.banner}</div>
{/section}

</div>
{/bitmodule}
{/if}

{if $sideboxWhosOnline}
	{bitmodule title=$moduleTitle name="bc_whosonline"}
		{section name=ix loop=$sideboxWhosOnline}
			{$sideboxWhosOnline[ix]}
		{/section}
	{/bitmodule}
{/if}

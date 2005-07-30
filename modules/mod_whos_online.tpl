{if $sideboxWhosOnline}
{bitmodule title=$moduleTitle name="whosonline"}
{section name=ix loop=$sideboxWhosOnline}
    {$sideboxWhosOnline[ix]}
{/section}
{/bitmodule}
{/if}

<h2 class="centerboxheading">{$productListTitle|default:"Products"}</h2>

<table width="100%" border="0" cellspacing="0" cellpadding="5">
{section name=ix loop=$listBoxContents}
	<tr
    {if !empty($listBoxContents[ix].align)}align="{$listBoxContents[ix].align}"{/if}
    {if !empty($listBoxContents[ix].params)} {$listBoxContents[ix].params}{/if}
	>

    {section name=jx loop=$listBoxContents[ix]}
    <td
      {if !empty($listBoxContents[ix][jx].align)} align="{$listBoxContents[ix][jx].align}"{/if}
      {if !empty($listBoxContents[ix][jx].params)} {$listBoxContents[ix][jx].params}{/if}
	>
      {if !empty($listBoxContents[ix][jx].text)}{$listBoxContents[ix][jx].text}{/if}
    </td>
	{/section}
	</tr>
{/section}

</table>


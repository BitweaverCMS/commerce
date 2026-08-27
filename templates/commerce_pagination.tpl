{strip}
{*
	P2 crawl path: paginator hrefs are identity query_string + page only by default.
	Do not emit defaultable sort_mode / max_records (spider-trap matrix).
	When listInfo.pagination_append_sort / pagination_append_max are set (non-default
	human controls, or admin lists), those params are appended; pair with
	listInfo.pagination_nofollow so bots stay on the default-sort path.
	Omit page=1 from hrefs (page 1 URL = identity only).
*}
<nav class="paginator">
{if $listInfo.total_pages == 1}
<div class="">
	{tr}Displaying all {$listInfo.item_name|default:products}{/tr}
</div>
{elseif $listInfo.page_records}
	{assign var=pageUrl value="`$smarty.server.SCRIPT_NAME`?`$listInfo.query_string`"}
	{capture name=pgnExtra}{if !empty($listInfo.pagination_append_max) && !empty($listInfo.max_records)}&amp;max_records={$listInfo.max_records|escape:'url'}{/if}{if !empty($listInfo.pagination_append_sort) && !empty($listInfo.sort_mode)}&amp;sort_mode={$listInfo.sort_mode|escape:'url'}{/if}{/capture}
	{assign var=pgnExtra value=$smarty.capture.pgnExtra}
	{assign var=pgnRel value=""}
	{if !empty($listInfo.pagination_nofollow)}{assign var=pgnRel value=' rel="nofollow"'}{/if}
	<ul class="pagination">
		{if $listInfo.current_page > 1}
			{assign var=blockStart value=1}
			{if $listInfo.current_page-1 <= 1}
			<li><a href="{$pageUrl}{$pgnExtra}"{$pgnRel}>{booticon iname="fa-chevron-left"}</a></li>
			{else}
			<li><a href="{$pageUrl}&amp;page={$listInfo.current_page-1}{$pgnExtra}"{$pgnRel}>{booticon iname="fa-chevron-left"}</a></li>
			{/if}
		{/if}
		{if $listInfo.current_page-$listInfo.block_pages > 0}
			{assign var=blockStart value=$listInfo.current_page-$listInfo.block_pages}
		{else}
			{assign var=blockStart value=1}
		{/if}
		{if $blockStart > 1}
			<li><a href="{$pageUrl}{$pgnExtra}"{$pgnRel}>1</a></li>
			<li><a href="{$pageUrl}&amp;page={$listInfo.current_page-$listInfo.block_pages}{$pgnExtra}"{$pgnRel}>...</a></li>
		{/if}

		{section name=current_page start=$blockStart loop=$blockStart+$listInfo.block_pages*2+1}
		{if $smarty.section.current_page.index <= $listInfo.total_pages}
			{if $smarty.section.current_page.index != $listInfo.current_page}<li>{else}<li class="active">{/if}
			{if $smarty.section.current_page.index <= 1}
			<a href="{$pageUrl}{$pgnExtra}"{$pgnRel}>{$smarty.section.current_page.index}</a></li>
			{else}
			<a href="{$pageUrl}&amp;page={$smarty.section.current_page.index}{$pgnExtra}"{$pgnRel}>{$smarty.section.current_page.index}</a></li>
			{/if}
		{/if}
		{/section}
		{if $blockStart+$listInfo.block_pages*2 < $listInfo.total_pages}
			<li><a href="{$pageUrl}&amp;page={$listInfo.current_page+1}{$pgnExtra}"{$pgnRel}>...</a></li>
			<li><a href="{$pageUrl}&amp;page={$listInfo.total_pages}{$pgnExtra}"{$pgnRel}>{$listInfo.total_pages}</a></li>
		{/if}

		{if $listInfo.current_page < $listInfo.total_pages}
		<li><a href="{$pageUrl}&amp;page={$listInfo.current_page+1}{$pgnExtra}"{$pgnRel}>{booticon iname="fa-chevron-right"}</a></li>
		{/if}
	</ul>
	<div class="small">{tr}Displaying <strong>{$listInfo.offset+1}</strong> to <strong>{math equation="x + y" x=$listInfo.offset y=$listInfo.page_records}</strong> (of <strong>{$listInfo.total_records}</strong> {$listInfo.item_name|default:products}){/tr}</div>
{/if}
</nav>
{/strip}

{strip}
{if $listInfo.total_pages == 1}
<div class="pagination">
		{tr}Displaying all {$listInfo.item_name|default:products}{/tr}
</div>
{elseif $listInfo.page_records}
<div class="pagination">
		{tr}Displaying <strong>{$listInfo.offset+1}</strong> to <strong>{math equation="x + y" x=$listInfo.offset y=$listInfo.page_records}</strong> (of <strong>{$listInfo.total_records}</strong> {$listInfo.item_name|default:products}){/tr}
		<br/>
		{assign var=pageUrl value="`$smarty.server.SCRIPT_NAME`?`$listInfo.query_string`"}
		{if $listInfo.current_page > 1}
			{assign var=blockStart value=1}
			{tr}<a href="{$pageUrl}&amp;page={$listInfo.current_page-1}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">&laquo; {tr}Prev{/tr}</a>{/tr}&nbsp;
		{/if}
		{if $listInfo.current_page-$listInfo.block_pages > 0}
			{assign var=blockStart value=$listInfo.current_page-$listInfo.block_pages}
		{else}
			{assign var=blockStart value=1}
		{/if}
		{if $blockStart > 1} <a href="{$pageUrl}&amp;page=1&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">1</a>  <a href="{$pageUrl}&amp;page={$listInfo.current_page-$listInfo.block_pages}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">...</a> {/if}

		{section name=current_page start=$blockStart loop=$blockStart+$listInfo.block_pages*2+1}
		{if $smarty.section.current_page.index <= $listInfo.total_pages}
			&nbsp;
			{if $smarty.section.current_page.index != $listInfo.current_page}
			<a href="{$pageUrl}&amp;page={$smarty.section.current_page.index}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">{$smarty.section.current_page.index}</a>
			{else}
			<strong>{$listInfo.current_page}</strong>
			{/if}
		{/if}
		{/section}
		{if $blockStart+$listInfo.block_pages*2 < $listInfo.total_pages} <a href="{$pageUrl}&amp;page={$listInfo.current_page+1}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">...</a>  <a href="{$pageUrl}&amp;page={$listInfo.total_pages}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">{$listInfo.total_pages}</a>{/if}

		{if $listInfo.current_page < $listInfo.total_pages}
		&nbsp;<a href="{$pageUrl}&amp;page={$listInfo.current_page+1}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">{tr}Next{/tr} &raquo;</a>
	{/if}
</div>
{/if}
{/strip}

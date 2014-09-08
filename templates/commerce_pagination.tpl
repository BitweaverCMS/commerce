{strip}
<nav class="paginator">
{if $listInfo.total_pages == 1}
<div class="">
	{tr}Displaying all {$listInfo.item_name|default:products}{/tr}
</div>
{elseif $listInfo.page_records}
	{tr}Displaying <strong>{$listInfo.offset+1}</strong> to <strong>{math equation="x + y" x=$listInfo.offset y=$listInfo.page_records}</strong> (of <strong>{$listInfo.total_records}</strong> {$listInfo.item_name|default:products}){/tr}
	<ul class="pagination">
		{assign var=pageUrl value="`$smarty.server.SCRIPT_NAME`?`$listInfo.query_string`"}
		{if $listInfo.current_page > 1}
			{assign var=blockStart value=1}
			<li>{tr}<a href="{$pageUrl}&amp;page={$listInfo.current_page-1}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">&laquo; {tr}Prev{/tr}</a>{/tr}</li>
		{/if}
		{if $listInfo.current_page-$listInfo.block_pages > 0}
			{assign var=blockStart value=$listInfo.current_page-$listInfo.block_pages}
		{else}
			{assign var=blockStart value=1}
		{/if}
		{if $blockStart > 1}
			<li><a href="{$pageUrl}&amp;page=1&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">1</a></li>
			<li><a href="{$pageUrl}&amp;page={$listInfo.current_page-$listInfo.block_pages}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">...</a></li>
		{/if}

		{section name=current_page start=$blockStart loop=$blockStart+$listInfo.block_pages*2+1}
		{if $smarty.section.current_page.index <= $listInfo.total_pages}
			&nbsp;
			{if $smarty.section.current_page.index != $listInfo.current_page}
			<li><a href="{$pageUrl}&amp;page={$smarty.section.current_page.index}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">{$smarty.section.current_page.index}</a></li>
			{else}
			<li class="active">{$listInfo.current_page}</li>
			{/if}
		{/if}
		{/section}
		{if $blockStart+$listInfo.block_pages*2 < $listInfo.total_pages}
			<li><a href="{$pageUrl}&amp;page={$listInfo.current_page+1}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">...</a></li>
			<li><a href="{$pageUrl}&amp;page={$listInfo.total_pages}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">{$listInfo.total_pages}</a></li>
		{/if}

		{if $listInfo.current_page < $listInfo.total_pages}
		<li><a href="{$pageUrl}&amp;page={$listInfo.current_page+1}&amp;max_records={$listInfo.max_records}&amp;sort_mode={$listInfo.sort_mode}">{tr}Next{/tr} &raquo;</a></li>
		{/if}
	</ul>
{/if}
</nav>
{/strip}

	{if $listInfo.total_pages == 1}
		{tr}Displaying all products{/tr}
	{else}
		{tr}Displaying <strong>{$listInfo.offset + 1}</strong> to <strong>{math equation="x * y" x=$listInfo.page y=$listInfo.max_records}</strong> (of <strong>{$listInfo.total_count}</strong> products){/tr}
		<br/>
		{assign var=pageUrl value="`$smarty.server.PHP_SELF`?main_page=`$smarty.request.main_page`&sort_mode=`$listInfo.sort_mode`"}
		{if $listInfo.page > 1}
			{assign var=blockStart value=1}
			{tr}<a href="{$pageUrl}&page={$listInfo.page-1}">&laquo; {tr}Prev{/tr}</a>{/tr}&nbsp;
		{/if}
		{if $listInfo.page-$listInfo.block_pages > 0}
			{assign var=blockStart value=$listInfo.page-$listInfo.block_pages+1}
		{else}
			{assign var=blockStart value=1}
		{/if}
		{if $blockStart > 1} <a href="{$pageUrl}&page={$listInfo.page-1}">1</a>  <a href="{$pageUrl}&page={$listInfo.page-$listInfo.block_pages}">...</a> {/if}

		{section name=pager start=$blockStart loop=$blockStart+$listInfo.block_pages*2}
		{if $smarty.section.pager.index <= $listInfo.total_pages}
			{if $smarty.section.pager.index != $listInfo.page}
			<a href="{$pageUrl}&page={$smarty.section.pager.index}">{$smarty.section.pager.index}</a>
			{else}
			<strong>{$listInfo.page}</strong>
			{/if}
		{/if}
		{/section}
		{if $blockStart+$listInfo.block_pages*2 < $listInfo.total_pages} <a href="{$pageUrl}&page={$listInfo.page+1}">...</a>  <a href="{$pageUrl}&page={$listInfo.total_pages}">{$listInfo.total_pages}</a>{/if}

		{if $listInfo.page < $listInfo.total_pages}
		<a href="{$pageUrl}&page={$listInfo.page+1}">{tr}Next{/tr} &raquo;</a>
		{/if}
	{/if}
	<div style="clear:both;">&nbsp;</div>

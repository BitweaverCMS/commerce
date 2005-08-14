<div class="commercebar">
{if $navPosition}
<span style="float:right">{tr}Product{/tr} {$navPosition}/{$navCounter}</span>
{/if}
	<span class="path">{$breadcrumb->trail(' &raquo; ')}</span>
	<span class="navigation">
		{if $navPreviousUrl}
		<span class="left"><a href="{$navPreviousUrl}">&laquo; {tr}Previous{/tr}</a></span>
		{/if}
		{if $navNextUrl}
		<span class="right"><a href="{$navNextUrl}">{tr}Next{/tr} &raquo;</a></span>
		{/if}
	</span>
</div>
<div style="clear:both"></div>
	{include file="bitpackage:bitcommerce/breadcrumbs_inc.tpl"}
	<div class="navigation">
		<span style="display:inline-block">
		{if $navPreviousUrl}
			<a href="{$navPreviousUrl}">&laquo;</a>
		{/if}
		{if $navPosition}
			<span>{tr}Product{/tr} {$navPosition}/{$navCounter}</span>
		{/if}
		{if $navNextUrl}
			<a href="{$navNextUrl}">&raquo;</a>
		{/if}
		</span>
	</div>

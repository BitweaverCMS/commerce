<div class="nav-commerce">
	{include file="bitpackage:bitcommerce/breadcrumbs_inc.tpl" breadcrumbs=$gCommerceBreadcrumbs->mTrail}
	<div class="navbar">
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
</div>

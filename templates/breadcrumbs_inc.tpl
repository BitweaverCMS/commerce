{if $gCommerceBreadcrumbs->hasCrumbs()}
<ul class="breadcrumbs">
	<li class="crumb0"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}">{tr}Shopping{/tr}</a></li>
	{section name="crumb" loop=$gCommerceBreadcrumbs->mTrail}
		<li class="crumb{$smarty.section.crumb.iteration}" >
		{if $gCommerceBreadcrumbs->mTrail[crumb].link} <a href="{$gCommerceBreadcrumbs->mTrail[crumb].link}"> {/if}
		{$gCommerceBreadcrumbs->mTrail[crumb].title|escape}			
		{if $gCommerceBreadcrumbs->mTrail[crumb].link} </a> {/if}
		</li>
	{/section}
</ul>
{/if}

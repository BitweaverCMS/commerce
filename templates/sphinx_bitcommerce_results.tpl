{if $sphinxResults}
<div class="searchresults">

	<p class="resultsheader"><strong style="float:left">{$searchIndex.index_title}</strong>&nbsp;
		{if !empty($sphinxResults.total)}
			{tr}Results{/tr} <strong>{$smarty.request.start+1} - {$sphinxResults.matches|@count}</strong> {tr}of exactly{/tr} <strong>{$sphinxResults.total}</strong> {tr}for{/tr} <strong>{$smarty.request.ssearch|escape}</strong> ({$sphinxResults.time|round:2} {tr}seconds{/tr})
		{/if}
	</p>

	<ol>
		{foreach from=$sphinxResults.matches item=result}
			{if $smarty.request.js_target}
				{assign var=productsUrl value="javascript:setPopupTarget('`$smarty.request.js_target`','`$result.page_id`');"}
			{else}
				{assign var=productsUrl value=$result.display_url}
			{/if}
			<li class="clear pb-2">
{$result|vd}
				<a href="{$productsUrl}"><img src="{$result.products_image_url}" class="floatleft thumb img-responsive pr-1" style="max-width:200px;" /></a>
				<div style="margin-left:60px;">
					<h3><a href="{$productsUrl}{if !$smarty.request.js_target && $result.content_type_guid != 'bitcomment'}?highlight={$smarty.request.q|escape:url}{/if}">{$result.products_name|escape|default:"[ no title ]"}</a></h3>
					<div>{tr}By{/tr} {displayname hash=$result}</div>
					<p>{$result.excerpt}</p>
					<div class="date">[{tr}Relevance{/tr}: {$result.weight}] {tr}{$result.products_type}{/tr}, {tr}Last Modified{/tr} {$result.last_modified|bit_long_datetime}</small></div>
				</div>
			</li>
		{foreachelse}
			<li class="norecords">
				<p>{tr}No pages matched your search terms{/tr} <strong>"{$smarty.request.ssearch|escape}"</strong></p>

				<div>{tr}Suggestions{/tr}:
					<ul>
						<li>{tr}Check the spelling of your search terms.{/tr}</li>
						<li>{tr}Try simpler search terms.{/tr}</li>
						<li>{tr}Try fewer search terms.{/tr}</li>
					</ul>
				</div>

			</li>
		{/foreach}
	</ol>

	{pagination highlight=$smarty.request.highlight join=$smarty.request.join contentTypes=$smarty.request.contentTypes}

</div>
{/if}

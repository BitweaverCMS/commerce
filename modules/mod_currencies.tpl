{if $sideboxCurrenciesPulldown}
{bitmodule title=$moduleTitle name="currencies"}
<form action="{$smarty.server.REQUEST_URI}" name="currencies" method="post">
	<div class="row submit">
		{$sideboxCurrenciesPulldown}
	</div>
</form>
{/bitmodule}
{/if}
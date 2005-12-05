{strip}
{if $sideboxCurrenciesPulldown}
	{bitmodule title=$moduleTitle name="bc_currencies"}
		<form action="{$smarty.server.REQUEST_URI}" name="currencies" method="post">
			<div class="row submit">
				{$sideboxCurrenciesPulldown}
			</div>
		</form>
	{/bitmodule}
{/if}
{/strip}

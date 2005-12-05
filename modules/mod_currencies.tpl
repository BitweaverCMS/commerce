{strip}
{if $modCurrencies}
	{bitmodule title=$moduleTitle name="bc_currencies"}
		{form action=`$smarty.server.REQUEST_URI`}
			{html_options name=currency values=$modCurrencies options=$modCurrencies selected=$modSelectedCurrency}
			<input type="submit" value="{tr}Set{/tr}" />
		{/form}
	{/bitmodule}
{/if}
{/strip}

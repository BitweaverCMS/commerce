{if $manufacturers}
	{bitmodule title=$moduleTitle name="bc_manufacturersselect"}
	<form name="manufacturers">
			{html_options options=$manufacturers selected=$smarty.get.manufacturers_id name="manufacturers_id" onchange="this.form.submit();"}
			{$manufacturers}
	</form>
	{/bitmodule}
{/if}
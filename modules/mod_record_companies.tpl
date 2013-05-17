{strip}
	{bitmodule title=$moduleTitle name="bc_record_companyselect"}
	<form name="record_companies">
		{html_options options=$record_company selected=$smarty.get.record_company_id name="record_company_id" onchange="this.form.submit();"}
	</form>
	{/bitmodule}
{/strip}

<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce {$smarty.request.main_page}">
	<div class="page-header">
		<h1>{tr}Sales and Commissions{/tr}</h1>
	</div>

	<div class="body">

	{include file="bitpackage:bitcommerce/commissions_payment_options_inc.tpl"}

	</div>
</div>

<div class="edit bitcommerce {$smarty.request.main_page}">
	<div class="body">

<div class="control-group">
	{formlabel label="Commission History"}

	{forminput}

		{include file="bitpackage:bitcommerce/commissions_list_inc.tpl"}

	{/forminput}

</div>

	</div>
</div>


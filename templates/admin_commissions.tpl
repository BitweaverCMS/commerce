{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_ADMIN_PATH`includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Commissions{/tr}</h1>
	</div>
	<div class="body">

		{formfeedback error=$errors}

		{include file="bitpackage:bitcommerce/admin_commissions_list_inc.tpl"}

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

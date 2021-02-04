{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_ADMIN_PATH`includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Revenue{/tr} : {$smarty.request.timeframe|escape} :  {$interestsName|escape}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:bitcommerce/admin_list_orders_inc.tpl"}
	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

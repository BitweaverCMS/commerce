{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Revenue{/tr} : {$smarty.request.timeframe|escape} :  {$interestsName|escape}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:bitcommerce/admin_list_orders_inc.tpl"}
	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

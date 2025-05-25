
<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Revenue{/tr} : {$smarty.request.timeframe|escape} :  {$interestsName|escape}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:bitcommerce/admin_list_orders_inc.tpl"}
	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

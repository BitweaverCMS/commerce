
<div class="header">
{form method="get"}
Update Listing
	<div class="floatright"> <strong>{tr}Status{/tr}</strong> {html_options name='orders_status_id' options=$commerceStatuses selected=$smarty.request.orders_status_id onchange="this.form.submit();"}</div>
	<div class="floatright"> <strong>{tr}Product Type{/tr}</strong> {html_options name='products_type' options=$commerceProductTypes selected=$smarty.request.products_type onchange="this.form.submit();"}</div>
{/form}
</div>

<div>
{include file="bitpackage:bitcommerce/admin_list_orders_inc.tpl"}
</div>

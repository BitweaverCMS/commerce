{if $searchScopes}
{form}
{assign var=currentScope value=$smarty.session.search_scope|default:'all'}
{assign var=currentComparison value=$smarty.session.orders_status_comparison|default:''}
<input type="checkbox" class="sr-only" id="oss-show"{if $smarty.session.search} checked{/if}>
{foreach from=$searchScopes key=scopeKey item=scopeLabel}<input type="radio" class="sr-only" name="search_scope" id="oss-{$scopeKey}" value="{$scopeKey}"{if $scopeKey == $currentScope} checked{/if}>{/foreach}
{foreach from=$statusComparisons key=compKey item=comp}<input type="radio" class="sr-only" name="orders_status_comparison" id="osc-{$compKey}" value="{$comp.value|escape}"{if $comp.value == $currentComparison} checked{/if}>{/foreach}
<style>
.oss-label { display:none; }
form:has(#oss-show:not(:checked)) .order-search { display:none; }
{foreach from=$searchScopes key=scopeKey item=scopeLabel}form:has(#oss-{$scopeKey}:checked) .oss-label-{$scopeKey} { display:inline; }
{/foreach}{foreach from=$statusComparisons key=compKey item=comp}form:has(#osc-{$compKey}:checked) .osc-label-{$compKey} { display:inline; }
{/foreach}
</style>
{if $orderFilters && $listOrders}{assign var=hasOrderFilters value=TRUE}{else}{assign var=hasOrderFilters value=FALSE}{/if}
{if $hasOrderFilters && $orderFilters.products}{assign var=hasProductFilters value=TRUE}{else}{assign var=hasProductFilters value=FALSE}{/if}
<div class="row">
	<div class="{if $hasProductFilters}col-xs-9{else}col-xs-10{/if} {if !$hasOrderFilters}col-sm-11{elseif $hasProductFilters}col-sm-5{else}col-sm-6{/if} form-group">
		<div class="input-group">
			<div class="input-group-btn">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">{foreach from=$statusComparisons key=compKey item=comp}<span class="oss-label osc-label-{$compKey}">{$comp.label|escape}</span>{/foreach} <span class="caret"></span></button>
				<ul class="dropdown-menu">{foreach from=$statusComparisons key=compKey item=comp}<li><label for="osc-{$compKey}" class="btn btn-link">{$comp.label|escape}</label></li>{/foreach}</ul>
			</div>
			{html_options class="form-control" name="orders_status_id" options=$commerceStatuses selected=$smarty.session.orders_status_id|default:'all'}
			<label class="input-group-addon"><input type="checkbox" name="orders_products" value="1" {if $smarty.session.orders_products}checked{/if}> <i class="fa fal fa-list"></i></label>
			<span class="input-group-btn"><input class="btn btn-default" type="submit" value="{tr}Show{/tr}" name="list_filter"/></span>
		</div>
	</div>
	<div class="{if $hasProductFilters}col-xs-3 col-sm-2 col-sm-push-5{else}col-xs-2 col-sm-1{if $hasOrderFilters} col-sm-push-5{/if}{/if} form-group text-right">
		<label for="oss-show" class="btn btn-link" title="{tr}Search{/tr}"><i class="fa fal fa-search"></i></label>{if $hasProductFilters}<label for="olf-show" class="btn btn-link" title="{tr}Product Options{/tr}"><i class="fa fal fa-filter"></i></label>{/if}
	</div>
	{if $hasOrderFilters}
	<div class="col-xs-12 col-sm-5 {if $hasProductFilters}col-sm-pull-2{else}col-sm-pull-1{/if} form-group order-filters">
		<input type="checkbox" id="olf-deadline" class="sr-only">{if $hasProductFilters}<input type="checkbox" id="olf-show" class="sr-only">{/if}
		{foreach from=$orderFilters.groups key=groupKey item=group}
		<input type="radio" name="olf_{$groupKey}" id="olf-{$groupKey}-any" class="sr-only" checked>
		{foreach from=$group.options key=optionKey item=option}<input type="radio" name="olf_{$groupKey}" id="olf-{$groupKey}-{$optionKey}" class="sr-only">{/foreach}
		{/foreach}
		{foreach from=$orderFilters.groups key=groupKey item=group}{if !$group.product}
		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">{$group.title} <span class="caret"></span></button>
			<ul class="dropdown-menu">{foreach from=$group.options key=optionKey item=option}<li><label for="olf-{$groupKey}-{$optionKey}" class="btn btn-link btn-sm btn-block"><span class="badge pull-right">{$option.count}</span> {$option.label|escape}</label></li>{/foreach}</ul>
		</div>
		{/if}{/foreach}
		{if $orderFilters.deadline}
		<label for="olf-deadline" class="btn btn-default olf-deadline" title="{tr}Deadline{/tr}: {$orderFilters.deadline}"><i class="fa fal fa-clock"></i></label>
		<label for="olf-deadline" class="btn btn-primary olf-chip olf-chip-deadline" title="{tr}Deadline{/tr}"><i class="fa fal fa-clock"></i> <i class="fa fal fa-times"></i></label>
		{/if}
		{foreach from=$orderFilters.groups key=groupKey item=group}{foreach from=$group.options key=optionKey item=option}<label for="olf-{$groupKey}-any" class="btn btn-primary olf-chip olf-chip-{$groupKey}-{$optionKey}">{if $group.product}{$group.title|escape}: {/if}{$option.label|escape} <i class="fa fal fa-times"></i></label> {/foreach}{/foreach}
	</div>
	{/if}
</div>
{if $hasProductFilters}
<div class="row order-options">
	<div class="col-xs-12 form-group">
		{foreach from=$orderFilters.groups key=groupKey item=group}{if $group.product}
		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">{$group.title|escape} <span class="caret"></span></button>
			<ul class="dropdown-menu">{foreach from=$group.options key=optionKey item=option}<li><label for="olf-{$groupKey}-{$optionKey}" class="btn btn-link btn-sm btn-block"><span class="badge pull-right">{$option.count}</span> {$option.label|escape}</label></li>{/foreach}</ul>
		</div>
		{/if}{/foreach}
	</div>
</div>
{/if}
<div class="row order-search">
	<div class="col-xs-12 form-group">
		<div class="input-group">
			<div class="input-group-btn">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">{foreach from=$searchScopes key=scopeKey item=scopeLabel}<span class="oss-label oss-label-{$scopeKey}">{$scopeLabel|escape}</span>{/foreach} <span class="caret"></span></button>
				<ul class="dropdown-menu">{foreach from=$searchScopes key=scopeKey item=scopeLabel}<li><label for="oss-{$scopeKey}" class="btn btn-link">{$scopeLabel|escape}</label></li>{/foreach}</ul>
			</div>
			<input type="text" class="form-control" name="search" value="{$smarty.session.search|default:$smarty.request.search}"/>
			<span class="input-group-btn"><input class="btn btn-default" type="submit" value="{tr}Search{/tr}" name="list_filter"/></span>
		</div>
	</div>
</div>
{/form}
{if $hasOrderFilters}
<style>
.order-filters .dropdown-menu .btn, .order-options .dropdown-menu .btn { text-align:left; }
form:has(#olf-show:not(:checked)) .order-options { display:none; }
.order-filters .olf-chip, .order-filters:has(#olf-deadline:checked) .olf-deadline { display:none; }
.order-filters:has(#olf-deadline:checked) .olf-chip-deadline { display:inline-block; }
form:has(#olf-deadline:checked) ~ .order-list tr.order-entry:not(.has-deadline) { display:none; }
{foreach from=$orderFilters.groups key=groupKey item=group}{foreach from=$group.options key=optionKey item=option}
.order-filters:has(#olf-{$groupKey}-{$optionKey}:checked) .olf-chip-{$groupKey}-{$optionKey} { display:inline-block; }
form:has(#olf-{$groupKey}-{$optionKey}:checked) ~ .order-list tr.order-entry:not(.{$groupKey}-{$optionKey}) { display:none; }
{/foreach}{/foreach}
</style>
{/if}
{/if}


<table class="table data order-list" style="table-layout:fixed">
{assign var=grossTotal value=0}
{assign var=wholesaleProfitTotal value=0}
{assign var=distributorIncomeTotal value=0}
{assign var=cogsTotal value=0}
{assign var=storeCountryName value=$smarty.const.STORE_COUNTRY|zen_get_country_name}
{foreach from=$listOrders key=orderId item=order}
	{assign var=grossTotal value=$grossTotal+$order.order_total}
	{assign var=displayName value=$order|getDisplayNameFromHash}
	<tr class="order-entry {$order.filter_classes}">		
		<td colspan="6"><div class="pull-left">{if $order.orders_status_id == $smarty.const.DEFAULT_ORDERS_STATUS_ID}<a class="btn btn-default btn-xs" href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URL}orders.php?oID={$orderId}&action=process">{tr}Process{/tr}</a> {/if}<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$orderId}" class="contentlink"><strong>{$orderId}</strong> - {$displayName}</A><BR><SPAN CLASS="SMALL">{$ORDER.ORDERS_STATUS_NAME}</SPAN></DIV><DIV CLASS="date pull-right text-right">{if $displayName != $order.delivery_name && $order.delivery_name != $order.billing_name}<em>{$order.delivery_name}</em>, {/if}{$order.delivery_city}, {$order.delivery_state}{if $order.delivery_country != $storeCountryName} {$order.delivery_country}{/if}{if $order.shipping_method_code}<br><span class="small">{$order.shipping_method_code}</span>{/if}</div></td>
		<td class="text-right"><div class="date"><span style="white-space:nowrap">{$order.purchase_time}</span>{if $order.deadline_date} <br><span class="badge alert-danger">{$order.deadline_date|cal_date_format:'%a %b %e, %Y'}</span>{/if}</div></td>
		<td class="text-right">{$gCommerceCurrencies->format($order.order_total, TRUE, $order.currency|default:DEFAULT_CURRENCY, $order.currency_value|default:1)}</td>
	</tr>
	{if $order.comments && $order.comments!='Credit Card processed'}
	<tr class="comments order-entry {$order.filter_classes}">
		<td colspan="8"><code class="date inline-block mr-1">{$order.comments_time}</code> {if $order.comments_html}{$order.comments_html}{else}{$order.comments|escape}{/if}</td>
	</tr>
	{/if}
	{if $order.products}
	{foreach from=$order.products item=product key=ordersProductsId name="orderproducts"}
	{assign var=orderProduct value=CommerceProduct::getCommerceObject($product.products_id)}
		{if $orderProduct}
	<tr class="order-entry {$order.row_filter_classes} {$product.filter_classes}">
		<td colspan="6">
			<div class="row">
				<div class="col-xs-3 text-center">
					<img src="{CommerceProduct::getImageUrlFromHash($product)}" class="img-responsive"/><div class="small">{$product.products_id}</div>
				</div>
				<div class="col-xs-9">		
					#{$smarty.foreach.orderproducts.iteration} - <a href="{$orderProduct->getDisplayUrl()}">{$product.products_name}</a>
					<span class="small">{$orderProduct->getProductsModel()}</span>
					{if $product.attributes}
						<ul class="small">
							{foreach from=$product.attributes item=attrName key=optionId}
								<li>{$attrName}</li>
							{/foreach}
						</ul>
					{/if}
				</div>
			</div>
		</td>
		<td colspan="2" class="text-right no-wrap">
			{assign var=quantityTotal value=$quantityTotal+$product.products_quantity}
			{$product.products_quantity} x 
			{assign var=finalIncome value=$product.products_quantity*$product.final_price}
			{assign var=finalTotal value=$finalTotal+$finalIncome}
			${$product.final_price}{if $product.products_quantity>1} : ${$finalIncome} {/if}&nbsp; =<br>
			{math equation="n*(x-y)" assign=wholesaleProfit n=$product.products_quantity x=$product.final_price|default:0 y=$product.products_wholesale|default:0}
			{assign var=wholesaleProfitTotal value=$wholesaleProfitTotal+$wholesaleProfit}
			+ {$wholesaleProfit|number_format:2}<br>
			{if $gBitUser->hasPermission('p_admin')}
			{math equation="n*(x-y)" assign=distributorIncome n=$product.products_quantity x=$product.products_wholesale|default:0 y=$product.products_cogs|default:0}
			{assign var=distributorIncomeTotal value=$distributorIncomeTotal+$distributorIncome}
			+ [{$distributorIncome|number_format:2}]<br>
			{math equation="n*(x)" assign=cogs n=$product.products_quantity x=$product.products_cogs|default:0}
			{assign var=cogsTotal value=$cogsTotal+$cogs}
			+ ({$cogs|number_format:2})
			{/if}
		</td>
		{if $gBitUser->hasPermission('p_admin')}
			{math equation="round( s-x-y-z, 4)" assign=auditValue s=$finalIncome x=$wholesaleProfit|default:0 y=$distributorIncome z=$cogs}
			{if $auditValue != 0}
			<td class="error">
				Audit error {$auditValue}
			</td>
			{/if}
		{/if}
	</tr>
		{/if}
	{/foreach}
	{/if}
{/foreach}
<tr>
	<th class="item text-left"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/export_orders.php?{$smarty.server.QUERY_STRING}" class="btn btn-xs btn-default">{tr}Export{/tr}</a></th>
	{if $wholesaleProfitTotal}
	<th class="item text-right" colspan="2"></th>
	<th class="item text-right">{tr}Total{/tr}: {$quantityTotal}</th>
	<th class="item text-right">${$finalTotal|round:2}</th>
	<th class="item text-right">${$wholesaleProfitTotal|round:2}</th>
	<th class="item text-right">{if $gBitUser->hasPermission('p_admin')}${$distributorIncomeTotal|round:2}{/if}</th>
	<th class="item text-right">{if $gBitUser->hasPermission('p_admin')}${$cogsTotal|round:2}{/if}</th>
	{else}
	<th class="item text-right" colspan="7">{tr}Total{/tr}: ${$grossTotal|round:2}</th>
	{/if}
</tr>

</table>

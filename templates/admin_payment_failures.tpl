<div class="page-header">
	{form method="get" class="form-inline pull-right"}
		{html_options class="form-control input-sm" name='days' options=$paymentFailureDays selected=$smarty.request.days|default:7}
		{html_options class="form-control input-sm" name='payment_status' options=$paymentFailureStatuses selected=$smarty.request.payment_status}
		<input class="form-control input-sm" type="text" name="customers_id" value="{$smarty.request.customers_id|escape}" placeholder="{tr}Customer ID{/tr}" />
		<input class="form-control input-sm" type="text" name="customers_email" value="{$smarty.request.customers_email|escape}" placeholder="{tr}Email{/tr}" />
		<input class="form-control input-sm" type="text" name="ip_address" value="{$smarty.request.ip_address|escape}" placeholder="{tr}IP{/tr}" />
		<input class="form-control input-sm" type="text" name="payment_module" value="{$smarty.request.payment_module|escape}" placeholder="{tr}Module{/tr}" />
		<button class="btn btn-default btn-sm" type="submit">{tr}Filter{/tr}</button>
	{/form}
	<h1>{tr}Payment Failures{/tr}</h1>
</div>

<p class="help-block">{tr}Unsuccessful payment attempts, including checkout tries that never created an order. Customer declines are logged here instead of sending a critical PAYMENT ERROR email.{/tr}</p>

<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th>{tr}When{/tr}</th>
			<th>{tr}Class{/tr}</th>
			<th>{tr}Customer{/tr}</th>
			<th>{tr}IP{/tr}</th>
			<th>{tr}Module{/tr}</th>
			<th>{tr}Amount{/tr}</th>
			<th>{tr}Result{/tr}</th>
			<th>{tr}Message{/tr}</th>
			<th>{tr}Order{/tr}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$paymentFailures item=row}
		<tr>
			<td class="nowrap">{$row.payment_date|escape}</td>
			<td>{$row.payment_status|escape}</td>
			<td>
				{if $row.customers_id}<a href="{$smarty.const.DIR_WS_HTTPS_ADMIN}customers.php?cID={$row.customers_id|escape}">{$row.customers_id|escape}</a>{/if}
				{if $row.customers_email}<div>{$row.customers_email|escape}</div>{/if}
			</td>
			<td>{$row.ip_address|escape}</td>
			<td>{$row.payment_module|escape}</td>
			<td class="text-right">{$row.payment_amount|escape} {$row.payment_currency|escape}</td>
			<td>{$row.payment_result|escape}</td>
			<td>{$row.payment_message|escape}</td>
			<td>
				{if $row.existing_orders_id}
					<a href="{$smarty.const.DIR_WS_HTTPS_ADMIN}orders.php?oID={$row.existing_orders_id|escape}">{$row.existing_orders_id|escape}</a>
				{elseif $row.orders_id}
					{$row.orders_id|escape}
				{/if}
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="9">{tr}No failed payment attempts in this window.{/tr}</td></tr>
	{/foreach}
	</tbody>
</table>

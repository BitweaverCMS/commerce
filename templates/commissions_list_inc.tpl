
{if $commissionList}
	<table class="table data commissions">
	<tr>
		<th style="text-align:left">{tr}Date Purchased{/tr}</th>
		<th style="text-align:left">{tr}Product Sold{/tr}</th>
		<th colspan="2" style="text-align:right">{tr}Earnngs{/tr}</th>
		<th style="text-align:right">{tr}Payment{/tr}</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$commissionList key=orderId item=commission}
		{if $commission.orders_products_id}
	<tr class="sale">
		{assign var=totalUnits value=$totalUnits+$commission.products_quantity}
		{assign var=totalSales value=$totalSales+$commission.products_quantity*$commission.unit_commission_earned}
		<td class="item text-left">{$commission.purchased_epoch|date_format:"%Y-%m-%d %H:%I:%S"}</td>
		<td class="item text-left"><a href="{$commission.products_link}">{$commission.products_name}</a></td>
		<td class="item text-right">{$commission.products_quantity} @ ${$commission.unit_commission_earned}</td>
		<td class="item text-right">${$commission.products_quantity*$commission.unit_commission_earned}</td>
		<td class="item text-right"></td>
		<td class="item text-left"></td>
	</tr>
		{elseif $commission.commissions_payments_id}
	<tr class="payment success">
		{assign var=totalCommissions value=$totalCommissions+$commission.payment_amount}
		<td>{$commission.period_end_date}</td>
		<td colspan="2" class="item text-left">{tr}Payment for sales ending on this date. Payment was made on{/tr} {$commission.payment_date|strtotime|date_format:"%Y-%m-%d"}
		</td>
		<td class="item text-right">${$commission.payment_amount}</td>
		<td class="item text-left">{$commission.payment_method}</td>
	</tr>
		{/if}
	{/foreach}
    <tr class="summary">
		<th colspan="2" style="text-align:left">Total</th><th style="text-align:right">{$totalUnits}</th><th style="text-align:right">${$totalSales}</th><th style="text-align:right">${$totalCommissions}</th><th>&nbsp;</th>
    </tr>
	</table>
{else}
	<div>
		<em>{tr}No sales with commissions.{/tr}</em>
	</div>
{/if}

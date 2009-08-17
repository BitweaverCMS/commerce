
{if $commissionList}
	<table class="data commissions">
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
		{assign var=totalUnits value=`$totalUnits+$commission.products_quantity`}
		{assign var=totalSales value=`$totalSales+$commission.products_quantity*$commission.unit_commission_earned`}
		<td style="text-align:left" class="item">{$commission.purchased_epoch|date_format:"%Y-%m-%d %H:%I:%S"}</td>
		<td style="text-align:left" class="item"><a href="{$commission.products_link}">{$commission.products_name}</a></td>
		<td style="text-align:right" class="item">{$commission.products_quantity} @ ${$commission.unit_commission_earned}</td>
		<td style="text-align:right" class="item">${$commission.products_quantity*$commission.unit_commission_earned}</td>
		<td style="text-align:right" class="item"></td>
		<td style="text-align:left" class="item"></td>
	</tr>
		{elseif $commission.commissions_payments_id}
	<tr class="payment">
		{assign var=totalCommissions value=`$totalCommissions+$commission.payment_amount`}
		<td>{$commission.period_end_date}</td>
		<td colspan="3" style="text-align:left" class="item">{tr}Payment for sales ending on this date. Payment was made on{/tr} {$commission.payment_date|strtotime|date_format:"%Y-%m-%d"}
		</td>
		<td style="text-align:right" class="item">${$commission.payment_amount}</td>
		<td style="text-align:left" class="item">{$commission.payment_method}</td>
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

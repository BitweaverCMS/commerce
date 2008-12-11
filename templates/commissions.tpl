<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce {$smarty.request.main_page}">
	<div class="header">
		<h1>{tr}Sales and Commissions{/tr}</h1>
	</div>

	<div class="body">

	{include file="bitpackage:bitcommerce/commissions_payment_options_inc.tpl"}

	</div>
</div>

<div class="edit bitcommerce {$smarty.request.main_page}">
	<div class="body">

<div class="row">
	{formlabel label="Commission History"}

	{forminput}

{if $commissionList}
	<table class="data">
	<tr>
		<th style="text-align:left">{tr}Date Purchased{/tr}</th>
		<th style="text-align:left">{tr}Product Sold{/tr}</th>
		<th colspan="2" style="text-align:right">{tr}Commission Earned{/tr}</th>
		<th colspan="2" style="text-align:right">{tr}Commission Received{/tr}</th>
	</tr>
	{foreach from=$commissionList key=orderId item=commission}
	<tr>
		{if $commission.orders_products_id}
		{assign var=totalUnits value=`$totalUnits+$commission.products_quantity`}
		{assign var=totalSales value=`$totalSales+$commission.products_quantity*$commission.products_commission`}
		<td style="text-align:left" class="item">{$commission.date_purchased}</td>
		<td style="text-align:left" class="item"><a href="{$commission.products_id}">{$commission.products_name}</a></td>
		<td style="text-align:right" class="item">{$commission.products_quantity} @ ${$commission.products_commission}</td>
		<td style="text-align:right" class="item">${$commission.products_quantity*$commission.products_commission}</td>
		<td style="text-align:right" class="item"></td>
		<td style="text-align:left" class="item"></td>
		{elseif $commission.commissions_payments_id}
		{assign var=totalCommissions value=`$totalCommissions+$commission.payment_amount`}
		<td style="text-align:left" class="item">{$commission.period_end_date}</td>
		<td style="text-align:left" class="item">{tr}Commission Payment{/tr}</td>
		<td style="text-align:right" class="item"></td>
		<td class="item"></td>
		<td style="text-align:right" class="item">${$commission.payment_amount}</td>
		<td style="text-align:left" class="item">{$commission.payment_method}</td>
		{/if}
	</tr>
	{/foreach}
    <tr>
		<th colspan="2" style="text-align:left">Total</th><th style="text-align:right">{$totalUnits}</th><th style="text-align:right">${$totalSales}</th><th style="text-align:right">{$totalCommissions}</th>
    </tr>
	</table>
{else}
	<div>
{tr}No sales with commissions.{/tr}
	</div>
{/if}
	{/forminput}

</div>

	</div>
</div>


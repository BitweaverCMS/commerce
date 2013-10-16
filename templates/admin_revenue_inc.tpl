{if $stats}
<table class="table data">
<tr>
	<th colspan="2">{tr}Revenue{/tr}: {$revenueTitle}</th>
	<th class="text-center">{tr}#{/tr}</th>
	<th class="text-center">{tr}Avg Size{/tr}</th>
</tr>
{foreach from=$stats item=statRow key=statKey}
	{if $statKey != 'stats'}
	<tr style="text-align:right">
		<td class="text-left"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/revenue.php?period={$statPeriod|escape}&amp;timeframe={$statKey}">{$statKey}</td>
		<td class="text-right">${$statRow.gross_revenue}</td>
		<td class="text-right">{$statRow.order_count}</td>
		<td class="text-right">${$statRow.avg_order_size}</td>
	</tr>
	{/if}
{/foreach}
<tr>
	<td colspan="4" style=""><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/revenue.php?period={$statPeriod|escape}">{tr}More...{/tr}</a></td>
</tr>
</table>
{/if}

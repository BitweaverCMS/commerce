<table class="data">
<tr>
	<th colspan="2">{tr}Revenue{/tr}: {$revenueTitle}</th>
	<th>{tr}Orders{/tr}</th>
	<th>{tr}Avg Size{/tr}</th>
</tr>
{foreach from=$stats item=statRow key=statKey}
	{if $statKey != 'stats'}
	<tr style="text-align:right">
		<td style="text-align:left">{$statKey}</td>
		<td>${$statRow.gross_revenue}</td>
		<td>{$statRow.order_count}</td>
		<td>${$statRow.avg_order_size}</td>
	</tr>
	{/if}
{/foreach}
<tr>
	<td colspan="4" style=""><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/revenue.php?period={$statPeriod|escape}">{tr}More...{/tr}</a></td>
</tr>
</table>

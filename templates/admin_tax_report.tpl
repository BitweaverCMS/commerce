{formfeedback hash=$feedback}

<div class="page-header">
	{form method="get" class="form-inline pull-right"}
		<label for="tax-year">{tr}Year{/tr}</label>
		{html_options class="form-control input-sm" id="tax-year" name="year" options=$taxYearChoices selected=$taxYear}
		<button class="btn btn-default btn-sm" type="submit">{tr}Show{/tr}</button>
	{/form}
	<h1>{tr}Sales Tax{/tr}</h1>
</div>

<p class="help-block">{tr}Tax collected and the order subtotal are summed from orders whose status is above cancelled. Amounts are in the default currency. A closed period is due the day after it ends when it starts on or after Track from, tax collected is not zero, and no payment is recorded.{/tr}</p>

<h2>{tr}Filing schedule{/tr}</h2>
<p class="help-block">{tr}Quarterly uses the first month of the cycle. January is Jan, Apr, Jul, Oct. February is Feb, May, Aug, Nov. March is Mar, Jun, Sep, Dec. Monthly is each calendar month. Annual starts in the selected month.{/tr}</p>

{form}
	<input type="hidden" name="action" value="save_filing" />
	<input type="hidden" name="year" value="{$taxYear|escape}" />
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>{tr}Tax{/tr}</th>
				<th>{tr}Frequency{/tr}</th>
				<th>{tr}First month of the cycle{/tr}</th>
				<th>{tr}Track from{/tr}</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$taxFilings item=filing}
			<tr>
				<td>
					{if $filing.rate_url}<a href="{$filing.rate_url|escape}">{$filing.tax_title|escape}</a>{else}{$filing.tax_title|escape}{/if}
					<input type="hidden" name="tax_title[]" value="{$filing.tax_title|escape}" />
				</td>
				<td>{html_options class="form-control input-sm" name="frequency[]" options=$taxFrequencies selected=$filing.frequency}</td>
				<td>{html_options class="form-control input-sm" name="cycle_start_month[]" options=$taxCycleMonths selected=$filing.cycle_start_month}</td>
				<td><input class="form-control input-sm" type="date" name="track_from[]" value="{$filing.track_from|escape}" /></td>
				<td><button class="btn btn-default btn-sm" type="submit" name="delete_filing" value="{$filing.tax_title|escape}">{tr}Delete{/tr}</button></td>
			</tr>
		{foreachelse}
			<tr><td colspan="5">{tr}No filing schedules.{/tr}</td></tr>
		{/foreach}
		</tbody>
	</table>
	<button class="btn btn-primary btn-sm" type="submit">{tr}Save schedule{/tr}</button>
{/form}

{if $taxUnscheduledTitles}
<p><button type="button" class="btn btn-default btn-sm" onclick="$('#new-filing-schedule').toggle();">{tr}New Schedule{/tr}</button></p>
<div id="new-filing-schedule" style="display:none">
{form}
	<input type="hidden" name="action" value="add_filing" />
	<input type="hidden" name="year" value="{$taxYear|escape}" />
	<div class="row">
		<div class="col-sm-3 form-group">
			<label for="new-tax-title">{tr}Tax{/tr}</label>
			{html_options class="form-control" id="new-tax-title" name="tax_title" options=$taxUnscheduledTitles}
		</div>
		<div class="col-sm-2 form-group">
			<label for="new-tax-frequency">{tr}Frequency{/tr}</label>
			{html_options class="form-control" id="new-tax-frequency" name="frequency" options=$taxFrequencies selected="quarterly"}
		</div>
		<div class="col-sm-3 form-group">
			<label for="new-tax-month">{tr}First month of the cycle{/tr}</label>
			{html_options class="form-control" id="new-tax-month" name="cycle_start_month" options=$taxCycleMonths selected=1}
		</div>
		<div class="col-sm-2 form-group">
			<label for="new-tax-track">{tr}Track from{/tr}</label>
			<input class="form-control" id="new-tax-track" type="date" name="track_from" />
		</div>
		<div class="col-sm-2 form-group">
			<label>&nbsp;</label>
			<button class="btn btn-primary btn-block" type="submit">{tr}Create{/tr}</button>
		</div>
	</div>
{/form}
</div>
{else}
<p class="help-block">{tr}Every tax title already has a filing schedule.{/tr}</p>
{/if}

<h2>{tr}Report{/tr} {$taxYear|escape}</h2>
<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th>{tr}Period{/tr}</th>
			<th>{tr}Tax{/tr}</th>
			<th class="text-right">{tr}Subtotal{/tr}</th>
			<th class="text-right">{tr}Tax collected{/tr}</th>
			<th>{tr}Status{/tr}</th>
			<th>{tr}Payment{/tr}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$taxReportRows item=row}
		<tr>
			<td class="nowrap">{$row.label|escape}<div class="help-block">{$row.period_start|escape} – {$row.period_end|escape}</div></td>
			<td>{if $row.rate_url}<a href="{$row.rate_url|escape}">{$row.tax_title|escape}</a>{else}{$row.tax_title|escape}{/if}</td>
			<td class="text-right">{$gCommerceCurrencies->format($row.subtotal, false)}</td>
			<td class="text-right">{$gCommerceCurrencies->format($row.tax_collected, false)}</td>
			<td>
				{if $row.status == 'due'}<span class="label label-danger">{tr}Due{/tr}</span>
				{elseif $row.status == 'paid'}<span class="label label-success">{tr}Paid{/tr}</span>
				{elseif $row.status == 'open'}<span class="label label-default">{tr}Open{/tr}</span>
				{else}<span class="label label-default">{tr}Closed{/tr}</span>
				{/if}
			</td>
			<td>
				{if $row.status == 'due' || ($row.status != 'open' && $taxReopenTitle == $row.tax_title && $taxReopenPeriod == $row.period_start)}
				{form class="form-inline"}
					<input type="hidden" name="tk" value="{$gBitUser->mTicket}" />
					<input type="hidden" name="tax_title" value="{$row.tax_title|escape}" />
					<input type="hidden" name="period_start" value="{$row.period_start|escape}" />
					<input type="hidden" name="year" value="{$taxYear|escape}" />
					<input class="form-control input-sm" type="text" name="amount" value="{if $row.payment}{$row.payment.amount|escape}{else}{$row.tax_collected|escape}{/if}" />
					<input class="form-control input-sm" type="date" name="paid_on" value="{if $row.payment && $row.payment.paid_on}{$row.payment.paid_on|escape}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}" />
					<input class="form-control input-sm" type="text" name="reference" value="{if $row.payment}{$row.payment.reference|escape}{/if}" placeholder="{tr}Reference{/tr}" />
					<input class="form-control input-sm" type="text" name="note" value="{if $row.payment}{$row.payment.note|escape}{/if}" placeholder="{tr}Note{/tr}" />
					<button class="btn btn-primary btn-sm" type="submit" name="action" value="record_payment">{if $row.payment}{tr}Update{/tr}{else}{tr}Record{/tr}{/if}</button>
					{if $row.payment}<button class="btn btn-default btn-sm" type="submit" name="action" value="delete_payment">{tr}Delete{/tr}</button>{/if}
					{if $row.status != 'due'}<a class="btn btn-default btn-sm" href="?year={$taxYear|escape:'url'}">{tr}Cancel{/tr}</a>{/if}
				{/form}
				{elseif $row.status == 'paid' || $row.status == 'closed'}
					{if $row.payment}
					<div>{$gCommerceCurrencies->format($row.payment.amount, false)} <span class="help-block">{$row.payment.paid_on|escape} {$row.payment.reference|escape} {$row.payment.note|escape}</span></div>
					{/if}
					<a class="btn btn-default btn-sm" href="?year={$taxYear|escape:'url'}&amp;reopen_title={$row.tax_title|escape:'url'}&amp;reopen_period={$row.period_start|escape:'url'}">{tr}Re-open{/tr}</a>
				{/if}
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="6">{tr}No sales tax in this year.{/tr}</td></tr>
	{/foreach}
	</tbody>
</table>

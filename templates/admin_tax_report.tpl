{* Sales tax: due summary, ledger grouped by filing period, in-place payment forms, filing schedules. *}
{formfeedback hash=$feedback}

<div class="tax-report">

<div class="tax-report-head clearfix">
	{form method="get" class="form-inline tax-year-nav" id="tax-year-nav"}
		{if $taxYear > $taxYearMin}
			<a class="btn btn-default btn-sm" href="?year={$taxYear-1}" title="{tr}Previous year{/tr}"><i class="fa fal fa-chevron-left"></i><span class="sr-only">{tr}Previous year{/tr}</span></a>
		{else}
			<span class="btn btn-default btn-sm disabled"><i class="fa fal fa-chevron-left"></i></span>
		{/if}
		<label class="sr-only" for="tax-year">{tr}Year{/tr}</label>
		{html_options class="form-control input-sm" id="tax-year" name="year" options=$taxYearChoices selected=$taxYear onchange="this.form.submit()"}
		{if $taxYear < $taxYearMax}
			<a class="btn btn-default btn-sm" href="?year={$taxYear+1}" title="{tr}Next year{/tr}"><i class="fa fal fa-chevron-right"></i><span class="sr-only">{tr}Next year{/tr}</span></a>
		{else}
			<span class="btn btn-default btn-sm disabled"><i class="fa fal fa-chevron-right"></i></span>
		{/if}
		<noscript><button class="btn btn-default btn-sm" type="submit">{tr}Show{/tr}</button></noscript>
	{/form}
	<h1>{tr}Sales Tax{/tr}</h1>
	<p class="tax-lede">{tr}Tax collected on completed orders, grouped by each tax's filing period. A period is due the day after it ends until its payment is recorded here.{/tr}</p>
	<div class="tax-how">
		<a href="#tax-how-body" data-toggle="collapse" aria-expanded="false" aria-controls="tax-how-body"><i class="fa fal fa-circle-info"></i> {tr}How these amounts are calculated{/tr}</a>
		<div id="tax-how-body" class="collapse tax-how-body">
			{tr}Tax collected sums the tax line of every order whose status is past cancelled; Subtotal sums those orders' merchandise subtotals. Amounts are in the default currency. A period is marked due only when it has ended, it starts on or after the schedule's Track from date, and it has tax collected but no recorded payment. Recording a payment is bookkeeping for this report; nothing is sent to a tax authority.{/tr}
		</div>
	</div>
</div>

{if $taxDue}
<div class="alert alert-danger tax-due-alert" role="alert">
	<strong>{tr}Due now{/tr}: {$gCommerceCurrencies->format($taxTotals.due, false)}</strong>
	{if $taxTotals.due_count == 1}{tr}for one period{/tr}{else}{tr}across{/tr} {$taxTotals.due_count} {tr}periods{/tr}{/if}
	<ul>
	{foreach from=$taxDue item=dueRow}
		<li><a href="#tax-row-{$dueRow.index}">{$dueRow.tax_title|escape} {$dueRow.display_label|escape}</a> &mdash; {$gCommerceCurrencies->format($dueRow.tax_collected, false)}, {tr}due since{/tr} {$dueRow.due_since_label|escape}</li>
	{/foreach}
	</ul>
</div>
{elseif $taxReportGroups}
<p class="tax-all-clear"><i class="fa fal fa-circle-check"></i>{tr}Nothing is due for{/tr} {$taxYear|escape}.</p>
{/if}

<div class="table-responsive">
<table class="table table-condensed tax-ledger">
	<thead>
		<tr>
			<th>{tr}Tax{/tr}</th>
			<th class="text-right">{tr}Subtotal{/tr}</th>
			<th class="text-right">{tr}Tax collected{/tr}</th>
			<th>{tr}Payment{/tr}</th>
		</tr>
	</thead>
{foreach from=$taxReportGroups item=group}
	<tbody class="tax-period">
		<tr class="tax-period-row">
			<th colspan="4"><span class="tax-period-label">{$group.label|escape}</span>{if $group.range}<span class="tax-period-range">{$group.range|escape}</span>{/if}</th>
		</tr>
	{foreach from=$group.rows item=row}
		{assign var=canPay value=($row.status != 'open' && $row.closed_reason != 'unscheduled')}
		{assign var=payOpen value=($canPay && $taxReopenTitle == $row.tax_title && $taxReopenPeriod == $row.period_start)}
		<tr id="tax-row-{$row.index}" class="tax-row tax-{$row.status|escape}">
			<td class="tax-title">{if $row.rate_url}<a href="{$row.rate_url|escape}" title="{tr}Edit tax rate{/tr}">{$row.tax_title|escape}</a>{else}{$row.tax_title|escape}{/if}</td>
			<td class="text-right tax-money">{$gCommerceCurrencies->format($row.subtotal, false)}</td>
			<td class="text-right tax-money">{$gCommerceCurrencies->format($row.tax_collected, false)}</td>
			<td class="tax-payment">
			{if $row.status == 'open'}
				<span class="label label-default">{tr}Open{/tr}</span><span class="text-muted">{tr}ends{/tr} {$row.ends_label|escape}</span>
			{elseif $row.status == 'due'}
				<span class="label label-danger">{tr}Due{/tr}</span><span class="text-muted">{tr}since{/tr} {$row.due_since_label|escape}</span>
				<button type="button" class="btn btn-primary btn-xs tax-pay-toggle" data-toggle="collapse" data-target="#tax-pay-{$row.index}" aria-expanded="{if $payOpen}true{else}false{/if}" aria-controls="tax-pay-{$row.index}">{tr}Record payment{/tr}</button>
			{elseif $row.status == 'paid'}
				<span class="label label-success">{tr}Paid{/tr}</span><span class="tax-paid-amount">{$gCommerceCurrencies->format($row.payment.amount, false)}</span>
				<span class="text-muted">{if $row.payment.paid_on_label}{tr}on{/tr} {$row.payment.paid_on_label|escape}{/if}{if $row.payment.reference} &middot; {tr}ref{/tr} {$row.payment.reference|escape}{/if}</span>
				{if $row.variance !== NULL}<span class="tax-variance" title="{tr}Payment differs from tax collected{/tr}">({if $row.variance > 0}+{/if}{$gCommerceCurrencies->format($row.variance, false)} {tr}vs collected{/tr})</span>{/if}
				<button type="button" class="btn btn-link btn-xs tax-pay-toggle" data-toggle="collapse" data-target="#tax-pay-{$row.index}" aria-expanded="{if $payOpen}true{else}false{/if}" aria-controls="tax-pay-{$row.index}">{tr}Edit{/tr}</button>
				{if $row.payment.note}<div class="tax-note">{$row.payment.note|escape}</div>{/if}
			{else}
				<span class="label label-default">{tr}Closed{/tr}</span>
				<span class="text-muted">
				{if $row.closed_reason == 'unscheduled'}<a href="#tax-schedules" class="text-muted">{tr}No filing schedule{/tr}</a>
				{elseif $row.closed_reason == 'untracked'}{tr}Before Track from{/tr}
				{elseif $row.closed_reason == 'no_tax'}{tr}No tax collected{/tr}
				{/if}
				</span>
				{if $canPay}<button type="button" class="btn btn-link btn-xs tax-pay-toggle" data-toggle="collapse" data-target="#tax-pay-{$row.index}" aria-expanded="{if $payOpen}true{else}false{/if}" aria-controls="tax-pay-{$row.index}">{tr}Add payment{/tr}</button>{/if}
			{/if}
			</td>
		</tr>
		{if $canPay}
		<tr class="tax-pay-row">
			<td colspan="4">
				<div id="tax-pay-{$row.index}" class="collapse{if $payOpen} in{/if}">
				{form class="form-inline tax-pay-form"}
					<input type="hidden" name="tk" value="{$gBitUser->mTicket}" />
					<input type="hidden" name="tax_title" value="{$row.tax_title|escape}" />
					<input type="hidden" name="period_start" value="{$row.period_start|escape}" />
					<input type="hidden" name="year" value="{$taxYear|escape}" />
					<div class="form-group">
						<label for="tax-amount-{$row.index}">{tr}Amount paid{/tr}</label>
						<input class="form-control input-sm tax-pay-amount" id="tax-amount-{$row.index}" type="text" inputmode="decimal" name="amount" required="required" value="{if $payOpen && isset($taxPostedPayment.amount)}{$taxPostedPayment.amount|escape}{elseif $row.payment}{$row.payment.amount_input|escape}{else}{$row.tax_collected|escape}{/if}" />
					</div>
					<div class="form-group">
						<label for="tax-paid-on-{$row.index}">{tr}Paid on{/tr}</label>
						<input class="form-control input-sm" id="tax-paid-on-{$row.index}" type="date" name="paid_on" required="required" value="{if $payOpen && isset($taxPostedPayment.paid_on)}{$taxPostedPayment.paid_on|escape}{elseif $row.payment && $row.payment.paid_on}{$row.payment.paid_on|escape}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}" />
					</div>
					<div class="form-group">
						<label for="tax-reference-{$row.index}">{tr}Reference{/tr}</label>
						<input class="form-control input-sm tax-pay-reference" id="tax-reference-{$row.index}" type="text" name="reference" maxlength="64" placeholder="{tr}Confirmation number{/tr}" value="{if $payOpen && isset($taxPostedPayment.reference)}{$taxPostedPayment.reference|escape}{elseif $row.payment}{$row.payment.reference|escape}{/if}" />
					</div>
					<div class="form-group">
						<label for="tax-note-{$row.index}">{tr}Note{/tr}</label>
						<input class="form-control input-sm tax-pay-note" id="tax-note-{$row.index}" type="text" name="note" placeholder="{tr}Optional{/tr}" value="{if $payOpen && isset($taxPostedPayment.note)}{$taxPostedPayment.note|escape}{elseif $row.payment}{$row.payment.note|escape}{/if}" />
					</div>
					<div class="form-group tax-pay-actions">
						<button class="btn btn-primary btn-sm" type="submit" name="action" value="record_payment">{if $row.payment}{tr}Save changes{/tr}{else}{tr}Record payment{/tr}{/if}</button>
						<button type="button" class="btn btn-default btn-sm" data-toggle="collapse" data-target="#tax-pay-{$row.index}">{tr}Cancel{/tr}</button>
						{if $row.payment}<button class="btn btn-link btn-sm" type="submit" name="action" value="delete_payment" formnovalidate="formnovalidate" onclick="return confirm('{tr}Delete this payment record? The period becomes due again.{/tr}');">{tr}Delete payment{/tr}</button>{/if}
					</div>
				{/form}
				</div>
			</td>
		</tr>
		{/if}
	{/foreach}
	</tbody>
{foreachelse}
	<tbody>
		<tr><td colspan="4" class="text-muted">{tr}No sales tax was collected in{/tr} {$taxYear|escape}.</td></tr>
	</tbody>
{/foreach}
{if $taxReportGroups}
	<tfoot>
		<tr class="tax-totals">
			<th>{tr}Totals{/tr} {$taxYear|escape}</th>
			<th class="text-right tax-money">{$gCommerceCurrencies->format($taxTotals.subtotal, false)}</th>
			<th class="text-right tax-money">{$gCommerceCurrencies->format($taxTotals.tax_collected, false)}</th>
			<th class="tax-money">
				<span class="tax-totals-paid">{tr}Paid{/tr}</span> {$gCommerceCurrencies->format($taxTotals.paid, false)}
				{if $taxTotals.due_count}<span class="tax-totals-due">{tr}Due{/tr} {$gCommerceCurrencies->format($taxTotals.due, false)}</span>{/if}
			</th>
		</tr>
	</tfoot>
{/if}
</table>
</div>

<div class="panel panel-default tax-schedules" id="tax-schedules">
	<div class="panel-heading clearfix">
		{if $taxUnscheduledTitles}
		<button type="button" class="btn btn-default btn-xs pull-right" data-toggle="collapse" data-target="#new-filing-schedule" aria-expanded="false" aria-controls="new-filing-schedule"><i class="fa fal fa-plus"></i> {tr}Add schedule{/tr}</button>
		{/if}
		<h2 class="panel-title">{tr}Filing schedules{/tr}</h2>
	</div>
	<div class="panel-body tax-schedules-help">
		{tr}Each tax files on its own cadence. Periods that start before Track from stay on the report but are never marked due.{/tr}
	</div>
	{if $taxUnscheduledTitles}
	<div id="new-filing-schedule" class="collapse">
		<div class="panel-body tax-new-schedule">
		{form class="form-inline"}
			<input type="hidden" name="action" value="add_filing" />
			<input type="hidden" name="year" value="{$taxYear|escape}" />
			<div class="form-group">
				<label for="new-tax-title">{tr}Tax{/tr}</label>
				{html_options class="form-control input-sm" id="new-tax-title" name="tax_title" options=$taxUnscheduledTitles}
			</div>
			<div class="form-group">
				<label for="new-tax-frequency">{tr}Frequency{/tr}</label>
				{html_options class="form-control input-sm tax-frequency-select" id="new-tax-frequency" name="frequency" options=$taxFrequencies selected="quarterly"}
			</div>
			<div class="form-group">
				<label for="new-tax-month">{tr}Cycle starts{/tr}</label>
				{html_options class="form-control input-sm tax-month-select" id="new-tax-month" name="cycle_start_month" options=$taxCycleMonths selected=1}
				<span class="tax-month-na">&mdash;</span>
			</div>
			<div class="form-group">
				<label for="new-tax-track">{tr}Track from{/tr}</label>
				<input class="form-control input-sm" id="new-tax-track" type="date" name="track_from" />
			</div>
			<div class="form-group tax-pay-actions">
				<button class="btn btn-primary btn-sm" type="submit">{tr}Add schedule{/tr}</button>
			</div>
		{/form}
		</div>
	</div>
	{/if}
	{form}
		<input type="hidden" name="action" value="save_filing" />
		<input type="hidden" name="year" value="{$taxYear|escape}" />
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>{tr}Tax{/tr}</th>
					<th>{tr}Frequency{/tr}</th>
					<th>{tr}Cycle starts{/tr}</th>
					<th>{tr}Track from{/tr}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$taxFilings item=filing}
				<tr class="tax-schedule-fields{if $filing.frequency == 'monthly'} is-monthly{/if}">
					<td>
						{if $filing.rate_url}<a href="{$filing.rate_url|escape}" title="{tr}Edit tax rate{/tr}">{$filing.tax_title|escape}</a>{else}{$filing.tax_title|escape}{/if}
						<input type="hidden" name="tax_title[]" value="{$filing.tax_title|escape}" />
					</td>
					<td>{html_options class="form-control input-sm tax-frequency-select" name="frequency[]" options=$taxFrequencies selected=$filing.frequency}</td>
					<td>{html_options class="form-control input-sm tax-month-select" name="cycle_start_month[]" options=$taxCycleMonths selected=$filing.cycle_start_month}<span class="tax-month-na">&mdash;</span></td>
					<td><input class="form-control input-sm" type="date" name="track_from[]" value="{$filing.track_from|escape}" /></td>
					<td class="text-right"><button class="btn btn-link btn-xs tax-delete" type="submit" name="delete_filing" value="{$filing.tax_title|escape}" formnovalidate="formnovalidate" title="{tr}Delete schedule{/tr}" onclick="return confirm('{tr}Delete this filing schedule? Its periods stop being marked due. Recorded payments are kept.{/tr}');"><i class="fa fal fa-trash-can"></i><span class="sr-only">{tr}Delete schedule{/tr}</span></button></td>
				</tr>
			{foreachelse}
				<tr><td colspan="5" class="text-muted">{tr}No filing schedules yet. Add one for each tax you file.{/tr}</td></tr>
			{/foreach}
			</tbody>
		</table>
		<div class="panel-footer">
			<button class="btn btn-primary btn-sm" type="submit"{if !$taxFilings} disabled="disabled"{/if}>{tr}Save schedules{/tr}</button>
			{if $taxFilings && !$taxUnscheduledTitles}<span class="text-muted">{tr}Every tax has a schedule.{/tr}</span>{/if}
		</div>
	{/form}
</div>

</div>

<script type="text/javascript">
/* <![CDATA[ */
(function ($) {
	// Quarterly stores only the phase month (1-3); monthly ignores the cycle month.
	function syncCycleMonth(scope) {
		var freq = $(scope).find('.tax-frequency-select').val();
		var $month = $(scope).find('.tax-month-select');
		$(scope).toggleClass('is-monthly', freq === 'monthly');
		$month.find('option').each(function () {
			var m = parseInt(this.value, 10);
			this.disabled = (freq === 'quarterly' && m > 3);
		});
		if (freq === 'quarterly' && parseInt($month.val(), 10) > 3) {
			$month.val(String(((parseInt($month.val(), 10) - 1) % 3) + 1));
		}
	}
	$(function () {
		$('.tax-schedule-fields').each(function () { syncCycleMonth(this); });
		$('.tax-schedules').on('change', '.tax-frequency-select', function () {
			syncCycleMonth($(this).closest('.tax-schedule-fields'));
		});
		// The add-schedule fields share one form; keep them in sync as a group.
		$('#new-tax-frequency').on('change', function () { syncCycleMonth($('#new-filing-schedule')); });
		syncCycleMonth($('#new-filing-schedule'));
		// Focus the amount when a payment form opens.
		$('.tax-ledger').on('shown.bs.collapse', '.collapse', function () {
			$(this).find('input[name="amount"]').trigger('focus').trigger('select');
		});
	});
})(jQuery);
/* ]]> */
</script>

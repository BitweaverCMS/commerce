# Sales tax report

Admin **Sales Tax** (`admin/tax_report.php`, `CommerceTaxReport`) rolls up order totals the same way as a quarterly tax export.

## Rollup

Include `com_orders_total` rows with `class = 'ot_tax'`. Group by `title` and by the filing period that contains `com_orders.date_purchased`. Sum that row's `orders_value` as tax collected. Sum the order's `ot_subtotal` `orders_value` as the subtotal. Keep orders with `orders_status_id > 3` (pending and later; cancelled, refunded, fraud, and testing statuses are negative). Amounts are the stored default-currency values.

`title` is the tax description copied onto the order. It is not `tax_rates_id`. A description that no longer has a rate row still reports. Several rates joined as `"A + B"` are one title. When `com_tax_rates.tax_description` matches the title, the filing schedule and the report link to that tax rate. The lowest `tax_rates_id` is used when more than one rate shares the description.

## Filing schedule

`com_tax_filing` is one row per tax title:

| Column | Meaning |
|---|---|
| `tax_title` | Unique. Matches `com_orders_total.title`. |
| `frequency` | `quarterly`, `monthly`, or `annual`. |
| `cycle_start_month` | 1–12. Quarterly is stored as 1, 2, or 3. |
| `track_from` | Closed periods that start before this date stay on the report and are not due. |

Quarterly cycle start is the first month of the quarter:

- 1 → Jan, Apr, Jul, Oct (labeled `YYYY-Qn`)
- 2 → Feb, May, Aug, Nov
- 3 → Mar, Jun, Sep, Dec

Monthly is each calendar month. Annual is twelve months starting in `cycle_start_month`.

When `com_tax_filing` is empty, the first admin request seeds a quarterly, January-cycle row for each title that has tax in the current or previous calendar quarter. `track_from` is the start of that previous quarter. After that, **New Schedule** shows the form that adds a tax title that does not already have one, and a row can be deleted. A deleted title stays unscheduled, including titles that still have recent tax. Recorded payments for that title are kept. Unscheduled titles still appear on the report and are not due.

## Due and payments

A period is due when it has ended, it starts on or after `track_from`, tax collected is not zero, and `com_tax_payments` has no row for that title and `period_start`. Due starts the day after `period_end`. The open period is shown and cannot be marked paid.

`com_tax_payments` stores `amount`, `paid_on`, `reference`, `note`, and the user who recorded it. Unique on `(tax_title, period_start)`. A due period shows the payment form. A paid or closed period shows **Re-open**, which loads that form so the amount, date, reference, or note can be corrected. Saving again updates that period. Nothing is sent to a tax authority.

Bookstore admin pages that use the commerce nav show `alert-danger` feedback while any of the last eight tracked periods for a title is due. The Sales Tax page assigns that feedback empty and shows its own due summary instead, with a link to each due row.

## Page layout

`admin_tax_report.tpl` is a ledger for one year, picked with the year control in the heading (previous, select, next). `CommerceTaxReport::groupReport()` turns `getReport()` rows into what the template needs: rows grouped by period, year totals, and the due list. Rows are grouped under a period heading. A quarter heading is the `YYYY-Qn` label with its date range; a calendar month is named (`September 2026`); any other span is its date range. The Payment column holds the status and the action for the row:

| Status | Shown | Action |
|---|---|---|
| `open` | end date | none |
| `due` | due since date | **Record payment** opens the form |
| `paid` | amount, paid-on date, reference, note, and the difference from tax collected when they differ | **Edit** opens the form with the saved values |
| `closed` | `closed_reason`: `unscheduled`, `untracked` (before `track_from`), or `no_tax` | **Add payment** opens the form; an unscheduled title gets no form because `recordPayment()` rejects it |

The payment form is a collapsed row under its period row (Bootstrap collapse). It is open on load for the row named by `reopen_title` and `reopen_period`, and for the row a rejected `record_payment` post targeted, with the posted values filled back in (`taxPostedPayment`). The amount defaults to tax collected. The footer row totals subtotal, tax collected, paid, and due for the year.

Filing schedules sit in a panel under the ledger. **Add schedule** reveals the form for an unscheduled title. Delete is the trash icon on the row and asks for confirmation. Page script keeps the cycle month in step with the frequency: quarterly offers months 1–3 only, monthly hides the month.

Styles are in `css/admin.css` under `.tax-report`.

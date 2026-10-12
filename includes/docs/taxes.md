# Sales tax report

Admin **Sales Tax** (`admin/tax_report.php`, `CommerceTaxReport`) rolls up order totals the same way as a quarterly tax export.

## Rollup

Include `com_orders_total` rows with `class = 'ot_tax'`. Group by `title` and by the filing period that contains `com_orders.date_purchased`. Sum that row's `orders_value` as tax collected. Sum the order's `ot_subtotal` `orders_value` as the subtotal. Keep orders with `orders_status_id > 3` (pending and later; cancelled, refunded, fraud, and testing statuses are negative). Amounts are the stored default-currency values.

`title` is the tax description copied onto the order. It is not `tax_rates_id`. A description that no longer has a rate row still reports. Several rates joined as `"A + B"` are one title.

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

The first admin request seeds a quarterly, January-cycle row for each title that has tax in the current or previous calendar quarter. `track_from` is the start of that previous quarter. Other titles are listed and stay unscheduled until saved.

## Due and payments

A period is due when it has ended, it starts on or after `track_from`, tax collected is not zero, and `com_tax_payments` has no row for that title and `period_start`. Due starts the day after `period_end`. The open period is shown and cannot be marked paid.

`com_tax_payments` stores `amount`, `paid_on`, `reference`, `note`, and the user who recorded it. Unique on `(tax_title, period_start)`. Saving again updates that period. Nothing is sent to a tax authority.

Bookstore admin pages that use the commerce nav show `alert-danger` feedback while any of the last eight tracked periods for a title is due.

# Drop ship

Upload a CSV of delivery addresses and create one order per row from the
shopper's cart (or from explicit product strings). There is no checkout
payment page and no checkout confirmation. **Process** is the purchase.

Permission: `p_bitcommerce_dropship`. Page: `/bookstore/dropship`
(`pages/dropship/dropship.php`, `templates/page_dropship.tpl`). Example
file: `pub/drop_ship.csv`.

Cart clone: `CommerceTemporaryCart::loadFromHash()`. Quote helper:
`bc_dropship_quote()`. Parser: `parseCSVToHash()` (`fgetcsv` only).

## CSV

Header row, then one address per data row. Column names are the hash keys
merged into the process session. Unknown columns are ignored. `.xlsx` is
not read.

| Column | Role |
|---|---|
| `cart` | Line items. `active_cart` copies the shopper's current cart. Otherwise `productId@qty:povid,povid;…` (semicolon-separated lines). |
| `delivery_name` | Split on the first space into first / last. |
| `delivery_company` | Optional. |
| `delivery_street_address1` | Street. |
| `delivery_street_address2` | Suburb / unit. |
| `delivery_city` | City. |
| `delivery_state` | Zone name or code; resolved with `zen_get_zone_by_name()`. |
| `delivery_postcode` | Postcode. |
| `delivery_country_iso2` | ISO-3166-1 alpha-2 (`US`, not `USA`). |
| `delivery_telephone` | Phone. |
| `comments` | Order comment. |
| `shipping_method` | Module code (`usps`, `fedex`, `ups`, …). |
| `shipping_method_code` | Method id inside that module (`1`, `GROUND_HOME_DELIVERY`, …). |
| `payment_method` | Plugin code (`purchase_order`, `cc`, …). Required at Process. |
| `payment_po_number` | Purchase-order number stored as `com_orders_payments.payment_number`. |
| `payment_po_org` | Company on the PO payment row. |
| `payment_po_contact` | Contact / payment owner on the PO payment row. |
| `payment_owner`, `payment_number`, `payment_expires_month`, `payment_expires_year`, `payment_cvv` | Card collect-only (`cc`) fields. |

Billing is the shopper's default address. Delivery is built from the
`delivery_*` columns.

Quantity for `active_cart` is the cart quantity at Process time. Two
quantity bands need two uploads (change the cart, then Process again).

## Upload vs Process

**Upload** stores the parsed rows in `$_SESSION['dropship']` and renders
each row: address, cart contents, shipping quotes. If
`shipping_method_code` is set, only that module is quoted; otherwise every
enabled carrier is quoted.

**Process** walks checked `dropship_order[]` indices. For each row it:

1. Copies the CSV hash into a session merge (`$dropSession`).
2. Sets `shipping_quote` to `shipping_method` + `_` + `shipping_method_code`.
3. Builds a temporary cart from `cart`.
4. `CommerceOrder::orderFromCart()` then `process($checkoutHash, $dropSession)`.

The preview radios and qty fields are **not** posted. Shipping and payment
come from the CSV row. Unchecking **Email receipt for each order** omits
`notify` and sets `no_order_email`, which skips `sendOrderEmail()`. The
checkbox is checked in the template by default. Receipts go to the
shopper, not the delivery names.

Each row is a distinct `orders_id`. Order totals (including bulk /
quantity discounts) are computed **per resulting order**, not across the
file.

## Payment

`CommerceOrder::process()` constructs `CommercePaymentManager` from
`$pSessionParams['payment_method']` (the CSV value). There is no payment
`selection()` on the dropship form.

Purchase order (`p_bitcommerce_purchase_order` on the shopper, module
enabled) writes `payment_number` from `payment_po_number`. Due Orders
(`/bookstore/invoices`) groups unpaid rows that share that number so one
`payInvoice` covers the set. Until that payment, status is the PO pending
status (`MODULE_PAYMENT_PURCHASE_ORDER_ORDERS_STATUS_ID`) and `amount_due`
is set, so a bad row can still be deleted.

Card collect-only (`cc`) stores the number without a processor charge.

## What is missing

These are product gaps, not CSV tips for the shopper.

1. **Empty `cart` is not `active_cart`.** `loadFromHash()` only copies the
   live cart when the cell equals `active_cart`. A blank cell builds an
   address with no lines. Process can still create a `$0` order when
   shipping also has no charge.
2. **No payment UI on Process.** Staff who want the shopper's file to stay
   addresses-only must add `payment_method` (and PO or card columns) on a
   working copy before Process, or Process has no method.
3. **Preview shipping is display-only.** Process concatenates the CSV
   shipping columns. Empty columns yield `shipping_quote` `_`.
4. **`.xlsx` is not parsed.** `fgetcsv` on a workbook is junk or empty.
5. **`payment_method` is read with a direct index** in
   `CommerceOrder::process()` after a successful create path
   (`$paymentParams['payment_method']`). A missing key is a PHP 8 warning.
   `hasPaymentDue()` false (empty cart, `$0`) still enters that path and
   stores an order with an empty `payment_method`.
6. **`cc::processPayment()`** used to index `payment_cvv` / expires /
   owner the same way; those reads now go through `BitBase::getParameter()`.
   `purchase_order::processPayment()` still `trim()`s
   `payment_po_contact`, `payment_po_org`, and `payment_po_number` without
   `getParameter()`.
7. **Due Orders card `selection()`** indexes `$order->billing['firstname']`
   / `lastname` on an empty `CommerceOrder()`. That dumps when the invoices
   page renders a card form above the due list.
8. **`CommercePluginShippingBase::getShippingDate()`** must not pass a null
   ship date into `DateTime`. A guard that sets `$shipDate = date('Y-m-d')`
   when the date is empty is the FedEx quote path.

## Issues in the current process

- A file of addresses with a blank `cart` column looks like a valid upload
  (quotes still run) and then creates empty orders if Process is clicked.
- The example CSV includes payment and shipping columns; a sheet that only
  has delivery columns is enough for **preview** and not enough for
  **Process**.
- `parseCSVToHash()` does not trim headers. A BOM or trailing space on
  `cart` means `active_cart` never matches.
- Session `dropship` is the uploaded file. Process unsets each index on
  success. A second Process without a new Upload has nothing left.
- The first `<form>` on the page is Upload. Submitting the wrong form
  (or the first form in a script) does not Process.
- `bit_error_email()` fires on Process failure with the row hash. Do not
  treat that as a customer receipt.
- Combining several titles is `active_cart` after the shopper adds every
  title. There is no “use these product ids” column besides the
  `productId@qty:povid` string.
- Country must be ISO2. Full names and alpha-3 fail `zen_get_countries()`
  in this path.

## Files

| Path | Role |
|---|---|
| `pages/dropship/dropship.php` | Upload, Process, `parseCSVToHash()`, `bc_dropship_cart()`. |
| `templates/page_dropship.tpl` | Upload widget, per-row preview, notify checkbox, Process. |
| `includes/classes/CommerceTemporaryCart.php` | Address + cart from the row hash. |
| `includes/classes/CommerceOrder.php` | `process()` payment and create. |
| `includes/modules/payment/purchase_order.php` | PO payment row. |
| `includes/modules/payment/cc.php` | Collect-only card. |
| `pages/invoices/invoices.php` | Due list grouped by `payment_number`. |
| `pub/drop_ship.csv` | Example headers. |

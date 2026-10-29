# Drop ship

Upload a CSV of delivery addresses and create one order per row from the
shopper's cart (or from explicit product strings). There is no checkout
payment page and no checkout confirmation. **Create orders** (the Process
form) is the purchase.

Permission: `p_bitcommerce_dropship`. Page: `/bookstore/dropship`
(`pages/dropship/dropship.php`, `templates/page_dropship.tpl`). Files:
`pub/drop_ship_addresses.csv` (the address-only template a shopper fills)
and `pub/drop_ship.csv` (the full example with cart, shipping, and payment
columns).

Cart clone: `CommerceTemporaryCart::loadFromHash()`. Parser:
`bc_dropship_parse_csv()`. Preview: `bc_dropship_preview_row()`. Payment
prefills: `bc_dropship_payment_defaults()`. All in `dropship.php`.

## Files

Two formats share one row shape and one validator,
`bc_dropship_validate_row()`. An upload **adds** its rows to the list in
the session; a row whose name, street, and postcode already match one in
the list is skipped and counted. Each row keeps `_file`, `_line`, and
`_line_label` for the table; the label carries the file name once more
than one file is loaded. Per-row **Remove** and **Remove all** post
`dropship_remove=<idx>` / `dropship_clear=1` through hidden inputs on the
Process form (the buttons are `type="button"` so Enter in a payment field
cannot trigger them). **Edit** on a row swaps the address for inputs
named `edit[<field>]` for every field in `bc_dropship_editable_fields()`
(name, company, street, line 2, city, state, postcode, country, phone,
comment); the inputs are `disabled` until opened so only the open row
posts. Save sets `dropship_edit=<idx>`; the controller copies the fields
into the session row, clears `_errors`, and runs
`bc_dropship_validate_row()` again, so a problem row is fixed on the page
rather than in the file. The address and phone are shown in the row.

### vCard

`.vcf` / `.vcard`, or any file that starts with `BEGIN:VCARD`
(`bc_dropship_is_vcard()`), goes to `bc_dropship_parse_vcard()`. It
unfolds continuation lines, then walks lines to split cards (a regex
across the file fails silently once embedded photos push a card past
the PCRE backtrack limit) and keeps only `FN`, `N`, `ORG`, `ADR`, `TEL`,
and `NOTE`, so `PHOTO` data is never parsed. It reads versions 2.1 (bare
type words, quoted-printable, a legacy `CHARSET` converted with iconv),
3.0 (Apple `item1.ADR` groups), and 4.0
(`TYPE=home,pref`, `tel:` URIs), and unescapes `\,` `\;` `\n`. Per
card: name from `FN`, else `N` (first last); company from `ORG`; the
address is the `PREF` `ADR`, else `HOME`, else the first, split as
pobox / extended / street / city / region / postal / country with extra
street lines and the extended part joined into `delivery_street_address2`;
phone is the `PREF` `TEL`, else `CELL`, else the first; `NOTE` becomes the
order comment. A card with no `ADR` is left out and named in a warning.
Country names such as "USA", "United States", "UK" are mapped by
`bc_dropship_country_alias()` before the lookup.

### CSV

Header row, then one address per data row. `.xlsx`, `.xls`, `.numbers`,
and `.ods` are refused with a "save as CSV" message; a workbook renamed
`.csv` is caught by the binary-header check.

Headers are normalized (BOM, quotes, and spaces stripped; lower-cased;
non-alphanumerics become `_`) and then mapped through
`bc_dropship_header_aliases()`. A shopper's `Name`, `Address`, `City`,
`State`, `Zip`, `Country`, `Phone` file needs no editing. `First Name` +
`Last Name` are joined into `delivery_name`. Canonical names and any
`delivery_*`, `billing_*`, `payment_*`, `shipping_*`, `ot_*`, `cart`,
`comments`, `dc_redeem_code`, `deadline_date` column pass through. Any
other column is ignored and named in a warning. The header row must
contain a name column and a street column or the upload is refused.

| Column | Role |
|---|---|
| `cart` | Line items. Blank or `active_cart` copies the shopper's current cart. Otherwise `productId@qty:povid,povid;…` (semicolon-separated lines). |
| `delivery_name` | Split on the first space into first / last. A single word is a row error. |
| `delivery_company` | Optional. |
| `delivery_street_address1` | Street. Required. |
| `delivery_street_address2` | Suburb / unit. |
| `delivery_city` | City. Required. |
| `delivery_state` | Zone name or code, resolved with `zen_get_zone_by_name()` and stored as the code. Required when the country has zones. |
| `delivery_postcode` | Postcode. Required unless the country's address format has none. |
| `delivery_country_iso2` | ISO2, ISO3, or the country name. Blank is the store country. Stored as ISO2. |
| `delivery_telephone` | Phone. |
| `comments` | Order comment. |
| `shipping_method`, `shipping_method_code` | Per-row override of the batch shipping choice (module code and method id). |
| `payment_method`, `payment_po_*`, `payment_owner`, `payment_number`, `payment_expires_*`, `payment_cvv` | Per-row override of the batch payment choice. Not needed in a shopper file. |

Row problems (missing name, street, city, state, postcode; unknown
country or state) are kept in `_errors` on the session row. The row is
listed with its line number and the error, unchecked and disabled, and
Process skips it even if posted.

Billing is the shopper's default address. Delivery is built from the
`delivery_*` columns.

Quantity for `active_cart` is the cart quantity at Process time. Two
quantity bands need two uploads (create the first batch, change the
cart, upload the second file). The page says so.

## Upload vs Process

**Upload** appends the parsed rows to `$_SESSION['dropship']` and the
file name to the `$_SESSION['dropship_file']` list. The page is a
Bootstrap 3 two-column layout (`css/dropship.css` adds the flex rows,
tabular figures, and the sticky rail):

- Header: title, one sentence naming the loaded files, and an "Add more
  addresses" button that jumps to the upload form at the bottom.
- Main column: the address table (checkbox, name and address, a
  shipping menu `shipping_quote[rowIdx]` grouped by carrier with the
  price first, a right-aligned rate column with a footer total, and a
  remove button; "Remove all" sits in the heading). Prices in the menus,
  the rate column, the bulk block, and the rail totals are the plain-text
  form of `currencies->format()` (`$pHtml = FALSE`) because option labels
  and the script's text updates cannot carry the formatter's HTML; the
  bulk shipping panel (`shipping_bulk` radios grouped under carrier
  headings, price in a fixed column, "n of m addresses" when partial);
  the payment panel (`CommercePaymentManager::userSelection()`, purchase
  order first and open, its three fields in one row, other methods
  collapsed). Rows with `_errors` are Bootstrap `danger` rows with the
  line number and the reasons, unchecked and disabled; a warning above
  the layout counts them.
- Rail (sticky on desktop): the live cart with quantities and prices,
  the two-uploads note, items × ready orders, shipping, total due on the
  prefilled PO number, the Create orders button, the notify checkbox
  (off), and the Due Orders note. An empty live cart replaces the cart
  lines with an alert and disables the button.
- Upload form last (`#dropship-upload`), with the column list and the
  two template downloads. Before any upload it is the whole page, with
  the cart in the rail.

Quotes run per row (`$gCommerceShipping->quote()`; a row with
`shipping_method` is quoted for that module only) and are cached in
`$_SESSION['dropship_quotes']` for 900 seconds (hardcoded in
`bc_dropship_quote_ttl()`; not yet a configuration value), keyed
by the cart lines, the delivery address, and any fixed carrier. Removing
a row, adding a file, or reloading reuses the cache; "Refresh rates"
(`dropship_requote=1`) and "Remove all" drop it. Process does not use the
cache: `CommerceOrder::loadFromCart()` quotes the chosen method live for
each order. Each menu is
preselected to the file's carrier when it has one, else the first bulk
method the address can use. Bulk methods are ordered by how many
addresses can use them, then by total; carriers follow their best
method. Choosing a bulk method sets every menu that has it. The script
keeps the rate column, checked count, shipping and grand totals, and the
button label current.

After Process the page shows the orders created (id link, recipient,
shipping, total, PO number) with links to Due Orders and the upload
form; any rows that were skipped stay listed below for another pass.

**Process** walks checked `dropship_order[]` indices. For each row it:

1. Skips rows with `_errors`, and rows that use the live cart while the
   cart is empty, with a feedback line each. No `$0` order is created.
2. Fills blank `payment_*` keys from the posted form. Shipping is the
   row's posted menu value, else the row's own `shipping_method` +
   `shipping_method_code` columns, else `shipping_bulk`; the chosen
   `module_method` id is split back into those two keys. A row with none
   is skipped with a feedback line.
3. Copies the row into a session merge (`$dropSession`), sets
   `shipping_quote`, builds the temporary cart, and refuses a cart with no
   contents.
4. `CommerceOrder::orderFromCart()` then `process($checkoutHash, $dropSession)`.

Before the loop, Process refuses the whole batch when no payment method
was posted and the rows do not carry their own.
Unchecking the notify box omits `notify` and sets `no_order_email`, which
skips `sendOrderEmail()`. When the last row is processed the session rows
and file name are cleared.

Each row is a distinct `orders_id`. Order totals (including bulk /
quantity discounts) are computed **per resulting order**, not across the
file.

## Payment

`CommerceOrder::process()` constructs `CommercePaymentManager` from
`$pSessionParams['payment_method']` (the form value merged into the row).

Purchase order (`p_bitcommerce_purchase_order` on the shopper, module
enabled) writes `payment_number` from `payment_po_number`. Due Orders
(`/bookstore/invoices`) groups unpaid rows that share that number so one
`payInvoice` covers the set. Until that payment, status is the PO pending
status (`MODULE_PAYMENT_PURCHASE_ORDER_ORDERS_STATUS_ID`) and `amount_due`
is set, so a bad row can still be deleted.

`CommercePluginPaymentBase::selection( $pDefaults )` takes field
prefills; every override carries the same signature (PHP 8 requires it).
`purchase_order::selection()` prefills the three PO fields from
`$pDefaults`; the card base prefills the owner name from the order billing
address when there is one, else from `$pDefaults['payment_owner']`.

Card collect-only (`cc`) stores the number without a processor charge.

## Remaining gaps

1. **`.xlsx` is not parsed.** The upload is refused with instructions to
   save as CSV.
2. **Shipping menus list only quoted methods.** A method that failed to
   quote for an address is not offered for it; the quote error is shown
   under the menu.
3. **Quotes run on every render** of the loaded rows (one carrier call
   per enabled module per row). Large files are slow to display.
4. **Session rows are the uploaded file.** A second Upload replaces them;
   Process removes each row on success.
5. `bit_error_email()` fires on Process failure with the row hash. It is
   not a customer receipt.

## Files

| Path | Role |
|---|---|
| `pages/dropship/dropship.php` | Upload, Process, parser, header aliases, per-row preview, payment prefills. |
| `templates/page_dropship.tpl` | Header, address table, bulk shipping, payment, the rail with totals and Create, the created-orders view, then Upload. |
| `templates/dropship_cart_inc.tpl` | Read-only cart listing for the rail: thumbnail, title, options, quantity and unit price, line total. |
| `css/dropship.css` | Flex rate rows, tabular figures, the sticky rail (a Bootstrap `well`), problem rows, cart media rows. |
| `includes/classes/CommerceTemporaryCart.php` | Address + cart from the row hash; blank `cart` is the live cart. |
| `includes/classes/CommerceOrder.php` | `process()` payment and create. |
| `includes/classes/CommercePaymentManager.php` | `selection()` / `userSelection()` with prefills. |
| `includes/modules/payment/purchase_order.php` | PO payment row and prefilled fields. |
| `includes/modules/payment/cc.php` | Collect-only card. |
| `pages/invoices/invoices.php` | Due list grouped by `payment_number`. |
| `pub/drop_ship_addresses.csv` | Address-only template for shoppers. |
| `pub/drop_ship.csv` | Full example with cart, shipping, and payment columns. |

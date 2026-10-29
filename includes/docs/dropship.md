# Drop ship (ship to multiple addresses)

Customer-facing copy calls this **Drop Ship to Multiple Addresses**
(the page titles and the checkout button); "drop ship" is also the
code name. The checkout shipping step shows that button (`btn-info`) to
customers with `p_bitcommerce_dropship` (it used to be a "Drop Ship"
toggle gated on `p_bitcommerce_bulk_ordering`, which described the old
CSV-only page).

Load a list of delivery addresses and create one order per row from the
shopper's cart (or from explicit product strings). **Final Review**
on the drop ship page leads to `/bookstore/dropship_confirmation`, a
read-only summary priced as one batch; **Create Orders** there is the
purchase. There is no checkout payment page.

Permission: `p_bitcommerce_dropship`. Page: `/bookstore/dropship`
(`pages/dropship/dropship.php`, `templates/page_dropship.tpl`). Files:
`pub/drop_ship_addresses.csv` (the address-only template a shopper fills)
and `pub/drop_ship.csv` (the full example with cart, shipping, and payment
columns).

Cart clone: `CommerceTemporaryCart::loadFromHash()`. Preview:
`bc_dropship_preview_row()` in `dropship.php`. The cart, quote cache,
payment prefills, custom quantities, batch collection, and batch pricing
helpers are `includes/dropship_lib.php` (`bc_dropship_*`), shared with
the confirmation page. File reading, header mapping, row validation,
vCard parsing, and address-book storage are the shared
`includes/address_import_lib.php` (`bc_address_*`), described in
[address-import.md](address-import.md); the address book page uses the
same functions.

## Three ways in: typed, address book, file

The address table is always on the page. Its last row is a tools row
with up to three buttons, and nothing else shows until one is pressed:

- **Add an address** reveals a row of `add[<field>]` inputs (browser
  autofill hints), posted with `dropship_add=1`. The country is a
  select (`bc_address_country_options()`, store country preselected)
  and comes first; the state field is a select of that country's
  `com_zones` codes when it has any, else free text (the page script
  swaps it from `bc_address_zones_by_country()` JSON on country change).
  The row is validated like a file row, checked for a duplicate, and
  appended; a failing one is appended as a problem row to fix in place.
- **From your address book** (only when the customer has entries)
  reveals the entries from `CommerceCustomer::getAddresses()` as
  checkboxes; `dropship_addressbook=1` with `address_book[]` ids maps
  each to a row in `bc_address_book_rows()` (zone code looked up from
  `zone_id` when the free-text state is empty).
- **Upload a spreadsheet or vCard** reveals the file input and Upload
  button, bound with `form="dropship-upload"` to an otherwise empty
  multipart form rendered outside the Process form, so the upload lives
  in the table without nesting forms. Two options sit under it: **Also
  add these people to my address book** (`drop_save_book`) and **Let me
  check the columns first** (`drop_check_columns`). The column help is
  behind a "What the file needs" toggle; the full example with cart and
  shipping columns is linked there as the advanced path.

### Saving addresses for later

Rows that did not come from the address book carry a **Save to address
book** checkbox (`save_address[]` = row index; the typed row has
`add[save]` for an immediate save). Every post from the Process form
re-syncs those ticks onto the session rows as `_save`, so they survive
edits and removals. At Process a ticked row is stored after its order is
created through `bc_address_save_to_book()` (name split on the first
space, country id from the ISO code, state as the zone code; an entry
with the same name, street, and postcode already in the book counts as
saved). With the upload option ticked, valid rows are saved as they are
added (`_saved`, counted in the upload message) and problem rows get
`_save` so they are saved at Process once fixed. Saved rows show "In
your address book"; address-book rows (`_book`) are never offered. Row
meta keys all start with `_` and are stripped before the row is merged
into the order session.

## Files

Spreadsheets and vCards share one row shape and one validator,
`bc_address_validate_row()`. An upload **adds** its rows to the list in
the session (`bc_dropship_append_rows()`); a row whose name, street, and
postcode already match one in the list is skipped and counted. Each row
keeps `_file`, `_line`, and `_line_label` for the table; the label
carries the file name once more than one file is loaded, and the sheet
name when a workbook has several. Per-row **Remove** and **Remove all**
post `dropship_remove=<idx>` / `dropship_clear=1` through hidden inputs
on the Process form (the buttons are `type="button"` so Enter in a
payment field cannot trigger them). **Edit** on a row swaps the address
for inputs named `edit[<field>]` for every field in
`bc_address_fields()` (name, company, street, line 2, city, state,
postcode, country, phone, comment); the country is a select filled by
the script from a hidden template when the row opens, the state swaps
to a zone select the same way as the typed row, and the inputs are
`disabled` until opened so only the open row posts. Save sets
`dropship_edit=<idx>`; the controller copies the fields into the session
row, clears `_errors`, and runs `bc_address_validate_row()` again, so a
problem row is fixed on the page rather than in the file. A row from
the address book also carries **Update in Address Book** (checked;
`edit[update_book]`), and a valid save then writes the change to that
entry through `bc_address_save_to_book()` with the entry id taken from
the session row (`_book_id`, never from the post; ownership is checked
again before the update). The address and phone are shown in the row.

### Spreadsheets: CSV and XLSX, with a column mapping step

`bc_address_read_table()` reads `.csv` / `.txt` / `.tsv` (delimiter
sniffed from the header line: comma, semicolon, or tab; BOM stripped)
and `.xlsx` / `.xlsm` (every visible sheet, read with `ZipArchive` and
SimpleXML, no library). A workbook renamed `.csv` is recognized by its
zip header. `.xls`, `.numbers`, `.ods` are refused with a save-as
message. The header row is the first row with two or more filled cells,
so a title line above the headings is skipped.

Headings are normalized (BOM, quotes, and spaces stripped; lower-cased;
non-alphanumerics become `_`) and matched through
`bc_address_header_aliases()` by `bc_address_guess_mapping()`. A
shopper's `Name`, `Address`, `City`, `State`, `Zip`, `Country`, `Phone`
file needs no editing. `First Name` + `Last Name` are joined into
`delivery_name`. Canonical names and any `delivery_*`, `billing_*`,
`payment_*`, `shipping_*`, `ot_*`, `cart`, `comments`, `dc_redeem_code`,
`deadline_date` column pass through. The first sheet whose guess is
complete (a name or first/last column and a street column) is used.

When no sheet's guess is complete, or the shopper ticked **Let me check
the columns first**, the page shows the **mapping panel** instead of an
error: one row per field we take (Name, First, Last, Company, Street,
Address line 2, City, State, Zip, Country, Phone, Comments), a menu of
the file's own headings prefilled from the guess, and the first data
row's value beside each menu (`bc_address_mapping_view()`,
`templates/address_import_map_inc.tpl`). A workbook with several sheets
gets a sheet menu and "Show this sheet". The panel is its own form
(`dropship-map`): `dropship_map=apply` re-reads the stored upload with
the posted `map[<field>]` menus (`bc_address_mapping_from_post()`,
which keeps the guessed power-user columns the menus do not offer),
`dropship_map=sheet` re-guesses for the chosen sheet, `cancel` drops the
upload. After an upload that matched on its own, the tools row offers
**Check columns of <file>** (`dropship_map_show=1`); applying a new
mapping replaces that file's rows in the list rather than skipping them
as duplicates. Columns the mapping does not use are named in a warning.

Between the two steps the parsed sheets wait in
`$_SESSION['address_import']` (`bc_address_import_store()`, at most 5000
rows per sheet); Remove all, finishing Process, or the next upload
clears it.

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

### vCard

`.vcf` / `.vcard`, or any file that starts with `BEGIN:VCARD`
(`bc_address_is_vcard()`), goes to `bc_address_parse_vcard()`; see
[address-import.md](address-import.md) for what it reads from a card.
Country names such as "USA", "United States", "UK" are mapped by
`bc_address_country_alias()` before the lookup.

Billing is the shopper's default address. Delivery is built from the
`delivery_*` columns.

Quantity for `active_cart` is the cart quantity at Process time, unless
the row has custom quantities (below). Two quantity bands can also be
two uploads (create the first batch, change the cart, upload the second
file); the page says so.

### Custom quantities per address

A row that uses the live cart has a **Customize quantities** button next
to Edit. It opens a block listing the live cart's lines
(`templates/dropship_row_cart_inc.tpl`: thumbnail, title, options, the
cart quantity) with a number box per line, named
`qty[<rowIdx>][<lineKey>]` and `disabled` until opened so only the open
row posts. **Save quantities** posts `dropship_qty=<idx>`;
`bc_dropship_custom_quantities()` reads the boxes against the live cart
and stores the map as `cart_quantities` on the session row (a real key,
not meta, so it survives the strip at Process and the inline Edit, which
copies only the address fields). Boxes equal to the cart mean "send as
is": the key is dropped and the row follows the cart again. All zero is
refused. **Send the cart as is** (`dropship_qty_reset=<idx>`) drops the
key. A customized row shows a `custom quantities` label with "2 × Title"
per line and the button reads Change quantities.

`bc_dropship_cart()` applies the map after `loadFromHash()` copies the
live cart: it sets `products_quantity` on the copied `contents` directly
and unsets a zero line, then `calculate( TRUE )`. It must not call
`updateQuantity()` or `cleanup()` on the temporary cart: those write to
the customer's basket table by line key and would change the live cart.
A line key the cart no longer has is ignored; a new cart line keeps its
cart quantity. The quote cache key includes quantities, so a customized
row quotes on its own; the rail's items total is the sum of each ready
row's `cart_total` (`data-items` on the row, summed by the script), and
the "orders × price" line becomes "N orders, M with custom quantities"
when any row is customized. A row with a `cart` column of its own does
not get the button.

## Upload vs Process

**Upload** appends the parsed rows to `$_SESSION['dropship']` and the
file name to the `$_SESSION['dropship_file']` list. The page is a
Bootstrap 3 two-column layout (`css/dropship.css` adds the flex rows,
tabular figures, and the sticky rail):

- Header: title and one sentence naming the loaded files (or "every
  address below" for a typed list).
- Main column: the address table (checkbox, name, address and phone, a
  shipping menu `shipping_quote[rowIdx]` grouped by carrier with the
  price first and the quote's origin country under it, a right-aligned
  rate column with a footer total, and a remove button; the header's ×
  removes every address; the last row is the typed-address form). Prices
  in the menus, the rate column, the bulk block, and the rail totals are
  the plain-text form of `currencies->format()` (`$pHtml = FALSE`)
  because option labels and the script's text updates cannot carry the
  formatter's HTML. Then the bulk shipping panel (`shipping_bulk` radios
  grouped under carrier headings, price in a fixed column, "n of m
  addresses" when partial). Methods there are keyed by **carrier code +
  method title**, never by `module_method` id: USPS and other modules
  number methods positionally, so `usps_1` is Ground Advantage for a
  domestic address and Priority Mail International abroad. The radio
  value is that `carrier|title` key, the script sets each menu by the
  option's `data-key`, and the posted menu value is the row's own id;
  without script Process resolves the key per row by quoting that
  carrier (`bc_dropship_resolve_method()`). Methods are ordered real
  carriers first (zero-cost modules last), then by how many addresses can
  use them, then by total; carriers follow their best method. Then the
  payment panel (`CommercePaymentManager::userSelection()`, purchase
  order first and open, its three fields in one row, other methods
  collapsed). Rows with `_errors` are Bootstrap `danger` rows with the
  line label and the reasons, unchecked and disabled; a warning above
  the layout counts them.
- Which carriers quote for a row is the normal checkout path: the
  fulfillment modules choose the origin for the cart and destination
  (`getShippingOrigin()`), and a carrier with an `_ORIGIN_COUNTRY_CODE`
  (Canada Post CA, Australia Post AU) quotes only when the chosen origin
  matches. A list mixing countries therefore shows different menus per
  row, and "Ships from" names the origin the fulfillment layer picked.
- Rail (sticky on desktop): the live cart with quantities and prices,
  the copies the quantity discount counts, items × ready orders (or "N
  orders, M with custom quantities" when any row is customized),
  shipping,
  total due on the prefilled PO number, and the **Final Review**
  button, a submit with `formaction` set to `dropship_confirmation` so
  the Process form's fields post to that page. An empty live cart
  replaces the cart lines with an alert and disables the button.
- No separate upload section: the three entry points are the tools row
  in the table. "Ships from" appears under a menu only when the rows'
  quote origins differ.

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

Every post re-renders the list and quotes each address, so the script
shows a **wait overlay** (`.drop-ship-wait`, a CSS spinner and a line of
text) as soon as a form is submitted: "Getting individualized shipping
quotes for each address" for adds, edits, removals, re-quotes, and the
mapping step; "Reading your file" for the upload; "Creating your orders"
for Create orders. The script's own submits go through `submitWait()`
because a programmatic `form.submit()` does not fire the submit handler;
`pageshow` hides the overlay again when the page comes back from the
browser cache.

After Process the page shows the orders created (id link, recipient,
shipping, total, PO number) with links to Due Orders and the upload
form; any rows that were skipped stay listed below for another pass.

## Finalize, confirm, create

Both pages open with the checkout progress bar component
(`templates/page_dropship_header_inc.tpl`, the `checkout-progressbar`
list from `css/bitcommerce.css`): Addresses, Review, Orders Created,
with `step` 1 on the drop ship page, 2 on the confirmation page, and 3
on the created view. The cart is the step before the path, as in
checkout. Only Addresses is a link (the confirmation page needs the
Finalize post), which is also the way back from the confirmation page.

`bc_dropship_collect( $_POST )` turns the Process form's post into the
batch, and both the confirmation page and Process call it so they see
the same thing. It refuses the whole batch (no addresses loaded, none
checked, no payment method and no per-row payment columns) or skips a
row with a reason (row `_errors`; live cart empty; no shipping method).
For each surviving row it strips the meta keys, fills blank `payment_*`
keys from the form, resolves shipping (`bc_dropship_row_shipping()`: the
posted menu, else the row's own `shipping_method` + `shipping_method_code`
columns, else the `shipping_bulk` carrier|title key quoted for that row)
and splits the `module_method` id back into those two keys. It returns
the orders (row, label, save-to-book flag, quote), the payment fields,
the notify flag, and `quantities`: each product's total across the
batch (`bc_dropship_batch_quantities()`, from each row's cart).

**Quantity discounts are for the batch, not the address.**
`bc_dropship_apply_batch_pricing()` hands those totals to
`CommerceProduct::setDiscountQuantityFloor()`, a per-request map that
`getQuantityPrice()` consults after its mixed-attribute check, so every
cart and order line in the request is priced at the combined quantity's
tier. The floor has to reach two places: `getQuantityPrice()`, and the
tier gate in `getPriceReduction()`, which option-priced lines (nearly
every book: base price empty, price on the binding option,
`attributes_discounted`) go through first. That gate asks whether a tier
exists at the quantity before it calls `getQuantityPrice()`, so without
the floor a two-copy line in an eighteen-copy batch never got there.
Both now use `getDiscountQuantity()`. The tiers themselves come from the
product class's `loadDiscounts()`; this install's print products carry
none in `com_products_discount_quantity` and fall back to the override's
5 / 10 / 50 / 100 / 250 copies → 5 / 10 / 15 / 20 / 25 percent.
`CommerceShoppingCart::getProductHash()` suspends the floor while
it computes the one-unit comparison price, so `quantity_discount` shows
the tier on every line, including a one-copy line; it no longer requires
a quantity above one. An order's item subtotal is the order object's
`subtotal` property (`info['subtotal']` is never set). Ten addresses at one copy each get the ten-copy price. The order
comment records it: `DROP SHIP ORDER (12 copies across 10 orders)`
(`bc_dropship_batch_label()`), the same place the `EXPEDITE ORDER`
prefix goes. The drop ship page sets the floor too, from every row
without errors (and with a cart to ship), before the preview loop, so
its table, rail cart lines, and totals show the batch tier and the rail
says how many copies the discount counts; unchecking a row in the
browser changes the batch only on the next post, and the confirmation
page recomputes from the checked rows. The confirmation page and
Process set it from the collected batch.

**`/bookstore/dropship_confirmation`**
(`pages/dropship_confirmation/dropship_confirmation.php`,
`templates/page_dropship_confirmation.tpl`) accepts only a post carrying
`dropship_finalize`; anything else goes back to the drop ship page. It
syncs the save ticks, collects the batch, and on a refused batch or no
orders puts the reasons in `$_SESSION['dropship_feedback']` and
redirects to the drop ship page, which prints them. Otherwise it sets
the floor and builds every order the way Process will, without creating
it: `bc_dropship_cart()`, `CommerceOrder::orderFromCart()` (this quotes
the chosen method live), `otCollectPosts()`, `otProcess()`. The page is
read-only: a two-column table, one row per order: the address on the
left; on the right the cart lines from `dropship_cart_inc.tpl` at the
tier price (each line's "n% discount" is the tier against the one-unit
price) with subtotal, shipping (method and rate), any other order-total
line, and total under them. A row that failed is a `danger` row with
its reasons and is left out of the post. The rail has "Priced as One
Batch" per cart line (product and options, since many products are
priced by their options: copies across the batch, the tier price, the
one-address price struck through and the percent), items, shipping,
total due on the PO number,
the payment module's `confirmation()` fields, the **Create Orders**
button, the receipt checkbox (`notify`, off), and a link back. The
Create Orders form posts to the drop ship page with `dropship_process`,
`dropship_order[]`, `shipping_quote[idx]`, `save_address[]`, and every
`payment_*` field as hidden inputs, so Process reads exactly what
checkout would have.

**Process** (`dropship_process` on the drop ship page) collects the
batch again, applies the floor, and for each order sets
`no_order_email` unless `notify` was posted, prefixes the comment with
the batch label, merges the row over a copy of the session
(`$dropSession`, with `shipping_quote`), builds the temporary cart and
refuses one with no contents, then `CommerceOrder::orderFromCart()`,
`otCollectPosts()`, and `process( $checkoutHash, $dropSession )`. A
created order's row leaves the session; ticked rows go to the address
book. When the last row is processed the session rows, file name, and
stored upload are cleared.

Each row is a distinct `orders_id`. Shipping and the other order totals
are per resulting order; the product discount tier is the batch's.

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

1. **Shipping menus list only quoted methods.** A method that failed to
   quote for an address is not offered for it; the module's error is
   shown under the menu unescaped, because modules build it with price
   markup and links (the free-shipping threshold message, for one).
2. **Quotes run on every render** of the loaded rows (one carrier call
   per enabled module per row). Large files are slow to display.
3. **Field order and labels do not follow the country.** The country is
   chosen first and the state follows it, but a German row still says
   "Zip" after the city. The per-country layout table is a planned
   second slice (see the address-input notes in the developer
   workspace).
4. `bit_error_email()` fires on Process failure with the row hash. It is
   not a customer receipt.

## Files

| Path | Role |
|---|---|
| `pages/dropship/dropship.php` | Upload, mapping step, custom quantities, per-row preview, list append, Process. |
| `pages/dropship_confirmation/dropship_confirmation.php` | Read-only summary of the batch priced as one; posts Create Orders back to the drop ship page. |
| `includes/dropship_lib.php` | Temporary cart with custom quantities, quote cache, payment prefills, batch collection, batch quantity pricing, the comment label. |
| `templates/page_dropship_confirmation.tpl` | The confirmation table, the batch pricing rail, payment summary, Create Orders. |
| `templates/page_dropship_header_inc.tpl` | The Addresses / Finalize / Orders Created progress bar on both pages. |
| `includes/address_import_lib.php` | Table reading (CSV / XLSX), header aliases and mapping, row validation, vCard, address-book rows and storage, country and zone lists. Shared with the address book page. |
| `templates/page_dropship.tpl` | Header, mapping panel, address table, bulk shipping, payment, the rail with totals and Create, the created-orders view, then Upload. |
| `templates/address_import_map_inc.tpl` | The field-to-column mapping table (shared with the address book page). |
| `templates/dropship_cart_inc.tpl` | Read-only cart listing for the rail: thumbnail, title, options, quantity and unit price, line total. |
| `templates/dropship_row_cart_inc.tpl` | The live cart's lines with a quantity box each, for one address's custom quantities. |
| `css/dropship.css` | Flex rate rows, tabular figures, the sticky rail (a Bootstrap `well`), problem rows, cart media rows, the mapping panel. |
| `includes/classes/CommerceTemporaryCart.php` | Address + cart from the row hash; blank `cart` is the live cart. |
| `includes/classes/CommerceOrder.php` | `process()` payment and create. |
| `includes/classes/CommerceProduct.php` | `setDiscountQuantityFloor()` and its use in `getQuantityPrice()`. |
| `includes/classes/CommercePaymentManager.php` | `selection()` / `userSelection()` with prefills. |
| `includes/modules/payment/purchase_order.php` | PO payment row and prefilled fields. |
| `includes/modules/payment/cc.php` | Collect-only card. |
| `pages/invoices/invoices.php` | Due list grouped by `payment_number`. |
| `pub/drop_ship_addresses.csv` | Address-only template for shoppers. |
| `pub/drop_ship.csv` | Full example with cart, shipping, and payment columns. |

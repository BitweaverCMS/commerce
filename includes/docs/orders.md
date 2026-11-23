# Orders and order processing

Commerce (checkout directory `bookstore`, package `bitcommerce`) owns generic
orders, line items, and status history. Production PDFs, press jobs, and
print-specific tools live in Products; they must not invent a second history
table.

This file is the concrete order API. Checkout pipeline and plugins are in
[commerce-lifecycle.md](commerce-lifecycle.md).

## Identity

| Identifier | Role |
|---|---|
| `orders_id` | Durable order. Table `com_orders` (`TABLE_ORDERS`). |
| `orders_products_id` | Purchased line snapshot. Table `com_orders_products` (`TABLE_ORDERS_PRODUCTS`). Not a pointer to live editor state. |
| `products_id` | Catalogue / customer project. One product can appear on more than one order (reorder). |
| `orders_status_id` | Current status on the order row. Names in `com_orders_status` (`TABLE_ORDERS_STATUS`). |
| `orders_status_history_id` | One history row. Table `com_orders_status_history` (`TABLE_ORDERS_STATUS_HISTORY`). |

Do not substitute `content_id` or `products_id` for `orders_id`.

## Customer purchase options

A customer's preferred catalogue choices live on that user's
`liberty_content.options_json` branch `bitcommerce.purchase_options`.
Each leaf name is a `products_options_id`. The leaf is a JSON list of
acceptable `products_options_values_id` numbers; rows written before
2026-10-15 hold one number, and `CommerceCustomer::purchaseOptionLeafValues()`
reads both shapes. Dropdown, radio, and checkbox options
(`CommerceCustomer::purchaseOptionTypes()`). Text, file, and read-only
groups are omitted. `storePurchaseOption()` adds a value to the list;
`expungePurchaseOption()` removes one value, or the whole option when no
value is given. A checkbox leaf is every extra that customer wants; a
dropdown or radio leaf is the acceptable values for that group.

Staff edit the list on the customer admin page,
`admin/customers.php?user_id=`. The order page links there from the
customer icon bar. `CommerceCustomer::storePurchaseOption()` checks
that the value belongs to the option, then calls
`LibertyContent::storePackageOption()`. The Products purchase page
pre-selects the branch for the logged-in viewer (every saved checkbox
value the product offers; the first saved dropdown or radio value), and
the order flight check warns when a line misses those values (Products
`purchase-options.md` and `flightcheck.md`). Dropdown and radio leaves
are satisfied by any listed value on the line. A checkbox leaf is
satisfied only when every listed value the product offers is on the line.

The customer page (`admin_customer_edit.tpl`, styles under
`.customer-edit` in `css/admin.css`) is a header with the customer's
name, email, id, telephone, sales standing from
`zen_get_customers_stats()`, and the same account links as the order
header (cart-plus icon, Customers link, `{displayname}` of the user, id),
under the standard commerce admin header include; then two `col-md-6`
columns. Left: Account as a read-only list with an edit icon that
reveals the form (`action=update`, posting to `?user_id=`), then the
address book. Only authorization, discount pricing group, gender, and
date of birth are editable; name, email, and telephone duplicate the
users table, so a field that is not posted keeps its stored value and is
not validated, and address fields are validated and written only when
`entry_street_address` is posted. Right: the saved options grouped by
option like `admin/products_options.php`, with a caret toggle per option
(open by default) and one line per value id
(`admin_purchase_options_inc.tpl`, replaced in place by the add and
remove requests). An `Add Preferred Option` button reveals the add form;
its `Save option` button stays disabled until both selects have a
value. Validation failures on save are assigned as `customerErrors`.

Each address shows formatted with a Default mark and an Edit toggle that
opens an inline form (`action=address`, `address_book_id`). The
controller checks `CommerceCustomer::isAddressOwner()` and saves
through `storeAddress()`, so `verifyAddress()` rules apply: State is a
free-text field that resolves to a zone by name or code for countries
with zones. A failed save re-renders with `addressErrors`, the form
open (`addressEditId`), and the posted values (`addressPosted`).
`getAddressesFromId()` returns `telephone` for this list. The default
address is the customer's choice; the admin form does not change it.

## Classes

- `CommerceOrder` — load, status, totals, contents. File
  `includes/classes/CommerceOrder.php`.
- `order` — thin subclass; `new order( $ordersId )` is the usual constructor
  (loads immediately when the id is valid).
- `CommerceOrder::getObjectByOrdersProduct( $ordersProductsId )` — resolve
  order from a line id. Admin or owning customer only.

Load Commerce through `includes/bitcommerce_start_inc.php` after Kernel setup.
Use `BITCOMMERCE_PKG_*` constants, not the `bookstore` directory name.

## Order list search

`CommerceOrder::getList()` name and email matches are `LIKE` on
`delivery_name`, `billing_name`, `users_users.email`, and `real_name`.
The same term is also matched against `customers_telephone`,
`billing_telephone`, and `delivery_telephone`. When the term contains at
least 7 digits, those columns are compared again with `-`, space, `(`, `)`,
`+`, and `.` removed, so `5551234567` matches `(555) 123-4567`.

Equality filters are added only when the term fits the column:

| Column | Type | Bound when |
|--------|------|------------|
| `co.orders_id` | `I4` (signed 32-bit) | Digits only, value `0`..`2147483647` |
| `co.order_total` | `N(14,2)` | Optional leading minus, at most 12 digits before the decimal and 2 after |

A longer all-digit string is not an order id. Do not bind it to `orders_id`
or `order_total`; PostgreSQL aborts the statement with `value is out of range
for type integer` (or numeric overflow) before the `LIKE` clauses can run.

With `orders_products`, `getList()` loads line items and attributes for the
whole result, not once per order. Each line's thumbnail is
`type_class::getImageUrlFromHash()` with `skip_thumb_refresh` set, so an
existing thumb is used without loading the product. Display URLs still come
from `getDisplayUrlFromHash()` on the line hash.

## Order list search bar

The status row is one Bootstrap input group: a comparison dropdown
(Exactly / At Least / At Most), the status select, the products checkbox
addon, and Show (the `list_filter` submit). A plain search icon (`btn-link`
label) at the row's end toggles the search row, `[Orders ▾][text][Search]`.
The search row is hidden unless the `sr-only` checkbox `#oss-show` is checked,
and it starts checked when the session has a search term.

The scope and comparison dropdowns are Bootstrap `input-group-btn` menus,
not `<select>`s. A `select.form-control` inside `input-group-btn` collapses
to its arrow, and a `select.btn` does not match the group. Picking a value
must not submit the form. Each value is an `sr-only` radio
(`search_scope` as `#oss-<key>`, `orders_status_comparison` as `#osc-<key>`
from `$statusComparisons`). Menu items are `<label for=...>`. The button
holds one span per value, and inline `form:has(#id:checked)` rules show the
selected one. Do not make the menu items submit buttons.

## Order list filters

`admin/index.php` builds the filters. They are a grid column (`col-sm-5
col-sm-pull-1`) inside the search form, on the same row as the status group,
with the search icon pushed to the end (`col-sm-1 col-sm-push-5`). On a
phone they wrap below the status row. The column has Shipping and Price
dropdowns, followed by an icon-only Deadline toggle (clock; the name and
count are in the tooltip). Deadline includes expedited orders: those with a
paid `ot_expedite` total line (one `IN (...)` query over the listed ids) or
the Expediting status (matched by normalized name, as the sidebar
`tr.expediting` CSS is). Picking a dropdown option adds a `label ×` chip,
and the chip's × clears that group. Price bands are under 100, 100–250,
250–500, and 500+ on `co.order_total` in the default currency, lower bound
inclusive. Options with no orders in the current list are dropped, and so is
a dropdown left empty, or Deadline when no order has one. The labels are
plain text built from `getLeftSymbol()` / `getRightSymbol()`. Do not use
`$currencies->format()` here: it returns HTML, which the template escapes
into visible markup.

Filtering runs without JavaScript, using only existing Bootstrap classes.
The dropdowns are Bootstrap `dropdown-menu`. Chips are `btn btn-primary`
labels, and clicking anywhere on a chip clears it. The hidden inputs are
`sr-only`: one checkbox, plus one radio group per dropdown with an `-any`
default. Menu labels point `for=` at an option, and chip labels point at the
group's `-any` radio. A chip must be a `<label>`, because `for=` does
nothing on a `<button>`. The controller gives each order `filter_classes`
(`has-deadline`, `ship-<slug>`, `price-<band>`). The template adds
`order-entry` and those classes to every `<tr>` of the order. The only CSS
is the show/hide rules, generated inline from `$orderFilters.groups`; add a
group there, not in a stylesheet. The inputs live inside the form, so the
row rules are `form:has(#id:checked) ~ .order-list` (the table is the form's
sibling) and a submit also posts the unused `olf_*` radios. The other pages
that include `admin_list_orders_inc.tpl` never show the filters. The footer
total still sums the whole result.

When line items are listed (`orders_products`), a filter icon follows the
search icon (the icon column widens to `col-sm-2`, the status column narrows
to `col-sm-5`). The icon toggles the `order-options` row via the `#olf-show`
checkbox. That row has one dropdown per product option
(`products_options_id`), listing its values (POVID) with order counts. Text
and File options are skipped: their values are free text. Values come from
one `orders_products_attributes` query over the listed orders. Each one is
a group in `$orderFilters.groups` with `product` set (key `opt<id>`, class
`opt<id>-<povid>`), so it shares the radio, chip, and CSS loops. Its chips
read `Option: Value`. The order and comment rows get every option class in
the order (`filter_classes`). A product row gets only the order-level
classes (`row_filter_classes`) plus its own line's classes, so a value
filter shows the order header and only the matching lines. Site-specific
option views belong in a site package, not here.

## Invoice

`admin/invoice.php` renders `templates/order_invoice.tpl`. The Payment panel
lists each `CommerceOrder::$mPayments` row (`com_orders_payments`): date,
type, mode, name, number, expiration, transaction id, and amount in
`payment_currency`. Card charges store those fields on the payment row.
`com_orders.payment_method` is the module title (shown as Method). The order
row's `payment_type`, `payment_owner`, `payment_number`, and `payment_expires`
are often empty, and `com_orders` has no transaction id. When `$mPayments` is
empty, the panel falls back to those order columns. `type=packing` sets
`showPricing` false and omits the panel.

The order admin payments table (`templates/admin_order_header_inc.tpl`,
`table.table.table-condensed`) leaves `is_success='y'` rows uncolored.
Any other `is_success` value, including `'n'`, gets Bootstrap's `warning`
row class. `bookstore/css/admin.css` paints those cells `#fcf8e3` and
keeps the text `#333`, because the theme's `.warning` rule would
otherwise turn the text orange.

## Status history

Order history is the staff/customer audit log for an order. It is **not** a
job queue (press/render state stays in Products).

`CommerceOrder::loadHistory()` reads history joined to status names and
`users_users` (actor). Each row includes source `comments`, optional
`format_guid`, and display HTML in `comments_html` from
`CommerceOrder::formatHistoryComment()`.

`format_guid` on `com_orders_status_history` is nullable `C(16)` with **no**
plugin FK. NULL (and omitted) is the legacy default. Existing rows stay NULL;
do not backfill. A NULL comment that contains an HTML tag displays as HTML.
A NULL comment with no tag displays as escaped plain text.

| `format_guid` | Stored source | Display (`comments_html`) |
|---------------|---------------|---------------------------|
| `simpletext` | Plain text | `nl2br( htmlspecialchars( comments ) )` |
| NULL / `''` with no HTML tag | Legacy plain text | Same escaped `nl2br` |
| NULL / `''` that already contains a tag | Legacy HTML (currency spans, `<br/>`) | Sanitized HTML, then `nl2br`. Do not backfill these rows. |
| `html` | HTML source | Same sanitized HTML path. Active content (`script`, event handlers, `javascript:` URLs) is removed. Inline tags from `currencies::format()` (`span`, `sup`, `class`) are kept. |
| `markdown` | Markdown | Parsedown with `setSafeMode(true)` and `setMarkupEscaped(true)` |
| unknown | Treated as plain | Escaped `nl2br`. Unknown values are not stored (see below). |

Do **not** parse comments through `LibertyContent::parseDataHash()` (Liberty
data plugins such as `{attachment}` must not run on order notes). Commerce
calls Parsedown in Util (`includes/parsedown/`) directly. ShipStation XML,
fulfiller ILIKE scans, and similar consumers keep raw `comments`.

Deployed databases need:

```sql
ALTER TABLE com_orders_status_history ADD COLUMN IF NOT EXISTS format_guid VARCHAR(16);
```

### `CommerceOrder::updateStatus( $pParamHash )`

This is the only write path for a history row. It:

1. Optionally checks `last_status_id` (optimistic semaphore against a stale
   admin screen).
2. Defaults `status` to the order’s current status when omitted.
3. Writes `com_orders.orders_status_id` and `last_modified`.
4. Inserts `com_orders_status_history` with `orders_id`, `orders_status_id`,
   `date_added`, `customer_notified`, `comments`, `user_id` (current
   `$gBitUser`), and optional `format_guid`.
5. Emails the customer **only** when `notify` is the string `'on'`. The HTML
   part uses `comments_html`. The text part is `strip_tags()` of the source.

Keys:

| Key | Effect |
|---|---|
| `status` | New `orders_status_id`. Omit to keep the current status. |
| `comments` | History **source** (plain, Markdown, or HTML). A comment with no status change still inserts a row. Store source. `html` rows store the HTML; display sanitizes it. |
| `format_guid` | Optional. Empty/omitted → NULL. Allowed: `markdown`, `simpletext`, `html`. Unknown values are stored as NULL and logged. |
| `notify` | Customer email. Must be `'on'` to send. Any other value (including `FALSE`, `0`, or omitted) does **not** notify. |
| `last_status_id` | If set, must match the latest history id or the update is rejected. |

Staff-only notes (production tools, internal recovery, PDF edits):

```php
$order = new order( $ordersId );
$order->updateStatus( array(
	'comments' => $staffComment,
	'notify' => FALSE,
	'format_guid' => 'markdown',
) );
```

Omit `format_guid` for legacy plain text. Pass `simpletext` when the note
must stay escaped even if it contains a `<`. Pass `html` when the note
embeds markup (`currencies::format()`, which wraps amounts in
`<span class="formatted-price">`, or an explicit `<br/>`). `changeShipping()`
and a payment adjustment whose comment contains a tag store `html`. Do not
pass `notify => 'on'` unless the customer is meant to receive the status
email. The text part of that email is `strip_tags()` of the source; the HTML
part uses `comments_html`. Admin production mutations must log history and
must not notify.

A comment-only update still sets `orders_status_id` to the current status on
both the order row and the new history row. That is expected.

The admin order-history form uses a Format menu under the comment: Plain
(default, stores `simpletext`), Markdown, or HTML. Checkout customer
comments stay NULL. A NULL comment with no tag still displays escaped. A
NULL comment that already contains a tag displays as HTML, so existing
shipping-change notes keep their prices without a backfill.

## Payment adjustment

`CommerceOrder::adjustOrder()` with `adjust_total=y` rewrites the order total.
The history comment shows the entered amount in the order currency
(`currencies::format()` with conversion off). `com_orders.order_total` and
`com_orders_total.orders_value` stay in the default currency. The order page
displays `orders_value * currency_value`.

The Adjust Order Total amount is entered in the order currency. For a
non-default currency, divide by that order's `currency_value` (the rate stored
on the order, not the live currency row) before adding it to the previous
total. Keep four decimal places on the adjustment `orders_value` (`N(15,4)`)
so formatting back through the order rate rounds to the amount entered. Round
`order_total` and the new `ot_total` row to the default currency's decimal
places. The gateway still receives the entered amount in the order currency.

A negative Braintree adjustment looks up the selected transaction. The credit
is full when it is within one cent of that transaction amount and the
currencies match. A full credit is voided while the status is `authorized`,
`submitted_for_settlement`, or `settlement_pending`, and refunded once the
status is `settling` or `settled`. A partial credit is refunded only in those
two settled statuses. A partial credit before settlement is not sent to the
gateway. The refusal is stored with `payment_result` `unsettled` and
classified as a customer decline, so it does not raise an infrastructure
alert, and the order total is left unchanged.

## Product log

`com_products_log` (`TABLE_PRODUCTS_LOG`) is an append-only note list on a
catalogue product. It is not order status and it does not send mail. Existing
databases apply `admin/install_com_products_log.sql`.

| Column | Role |
|---|---|
| `products_log_id` | One row. |
| `products_id` | Owning product. |
| `user_id` | Actor. NULL when the writer is not a registered user. |
| `date_added` | When the row was stored. |
| `owner_visible` | `0` hidden from the owner (admin still sees it). `1` the owner can read it. |
| `log_code` | Optional short token (`[a-z0-9_]`, at most 32). Not a lookup table. |
| `comments` | Source text, at most 4000 characters. Empty is allowed when `log_code` is set. |
| `format_guid` | Same allow-list as order history: `simpletext`, `markdown`, `html`. Empty is stored as NULL. |

`CommerceProduct` methods, separate from product `load` / `verify` / `store` / `expunge`:

| Method | Behavior |
|---|---|
| `verifyLog( &$pParamHash )` | Fills `log_store`. Requires `comments` or `log_code`. |
| `storeLog( &$pParamHash )` | Inserts one row. Caller must be `p_bitcommerce_admin` or the product owner. Returns the new id. |
| `loadLog()` | Fills `mLog`, oldest first, with `comments_html`. Admin sees every row. The owner sees `owner_visible = 1`. Anyone else gets an empty list. Not called from `load()`. |
| `expungeLog( $pLogId )` | Admin deletes one row on this product. |

Deleting a product that has never been purchased deletes its log rows first.
A purchased product is marked deleted and keeps its log.

`format_guid` display uses `CommerceOrder::formatHistoryComment()`. The
product-log dialog uses the same Format menu: Plain (`simpletext`), Markdown,
or HTML.

An owner-visible row with a `log_code` is a message the owner can read and a token another program can read later through `loadLog()`. This table does not email the owner.

## Admin line-item options (`admin/orders.php`)

Staff can add or delete snapshot rows in `com_orders_products_att`.

- **Same `products_options_values_id` on a line is a no-op.** Do not insert a
  second copy. History notes that the value is already on the line.
- **Replace existing option** (checkbox, on by default) when the line already
  has any value for that `products_options_id`. The picker lists those values
  under the checkbox. Saving with it checked deletes the previous row(s) for
  that group, inserts the new one, and writes **one** `updateStatus()` comment
  listing what was deleted and what was added (`notify => FALSE`). Unchecked
  adds the new value alongside the existing ones.
- Picker labels and attribute lines include `products_options_values_id` so
  two values that share a display name stay distinguishable.
- Optional text for a value (`add_order_povid_text`) is trimmed as a string.
  Do not `trim( null )` when the field is omitted.
- Writes go through `CommerceOrder::storeOrderProductAttribute()` (existing
  lines) and `lookupOrderAttribute()` (catalogue snapshot).

The Add Product form (`#new-product-form`, `action=save_new_product`) uses
the same `newOrderOptionType` picker. Choosing an option type loads values
through the existing AJAX (`new_option_id` with `orders_products_id=new`).
**Add option** queues `new_order_povid[]` (and optional
`new_order_povid_text[povid]`) on the form; a second value for the same
`products_options_id` replaces the queued one. **Add** inserts the line
through `CommerceOrder::addProductToOrder()` with those values, last value
per option group wins, and the history comment lists the product id plus
each option. A value still selected in the picker (not yet queued) is
included. Unknown POV ids refuse the whole add. Price helpers receive the
catalogue option hashes (`products_options_id` /
`products_options_values_id`), the same shape as a cart line.
`getOneTimeCharges()` and `getWeight()` also accept a cart `$optionId =>
$valueId` list.

## Staff address edit

`admin/orders.php` `action=save_address` updates the order snapshot
(`delivery_*` or `billing_*`). The edit form lists customer
`address_book_id` rows that match that snapshot on street, city, and
postcode. Suburb and state must also match when both sides have a value.
The checkbox **Also update the customer's saved address** is on by
default. Saving it writes the same fields onto those address-book rows
(and the zone id when the state name resolves) so a later order can reuse
the correction. Uncheck it to change only this order. When nothing
matches, only the order row is updated.

## Order again

Account history and the order receipt (`orderAgainForm`) replace the product
link with an inline form. **Add To Cart** posts `action=add_product`,
`products_id`, `cart_quantity`, and the line's `id` fields.
When the order has more than one line, **Reorder All Items** posts
`action=reorder_order` and `orders_id`. The viewer must be allowed to see
that order. Each line is added to the current cart at its ordered quantity
and options (`CommerceOrder::addLinesToCart()`). The cart is not emptied
first. The browser is sent to the shopping cart.

**Customize** posts that same body to the product URL. Numeric option values
are also on the query string (`id[optionId]=valueId`, or
`id[optionId][valueId]=valueId` for checkboxes). Free text stays in the POST
body only.

`CommerceOrder::reorderCartFields()` builds those fields from the line's
attribute rows. File and read-only options are omitted. Text uses
`products_options_values_text` when that column is set.
`CommerceOrder::mergeRequestOptionIds()` merges query-string `id` with posted
`id` (posted text wins per option). The product page passes that hash into
`getProductOptions()` so dropdowns, radios, checkboxes, and text inputs match
the purchased line. A later script reapplies the same hash after binding
controls reset checkbox defaults.

Checkout stores typed option text on `com_orders_products_att.products_options_values_text`
(`X` / SQL `text`). Older rows leave it null, so only the option value id can
be restored.

## Finding the order from a product

`com_orders_products.products_id` links a project to purchased lines:

```sql
SELECT DISTINCT orders_id
FROM com_orders_products
WHERE products_id = ?
ORDER BY orders_id
```

A product that is not yet purchased has no order; skip history rather than
inventing an `orders_id`. A product on several orders (reorder) should update
**each** matching order if the change affects that project’s files.

From a line item, prefer `CommerceOrder::getObjectByOrdersProduct()`.

## Processing (summary)

Checkout persists the order, products, totals, and the first status-history
row in one transaction. After that, fulfillment and admin tools change state
with `updateStatus()`. Payment success and production submission are separate
transitions. Provider timeout is unknown, not failure. See
[commerce-lifecycle.md](commerce-lifecycle.md).

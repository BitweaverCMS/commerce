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

## Classes

- `CommerceOrder` — load, status, totals, contents. File
  `includes/classes/CommerceOrder.php`.
- `order` — thin subclass; `new order( $ordersId )` is the usual constructor
  (loads immediately when the id is valid).
- `CommerceOrder::getObjectByOrdersProduct( $ordersProductsId )` — resolve
  order from a line id. Admin or owning customer only.

Load Commerce through `includes/bitcommerce_start_inc.php` after Kernel setup.
Use `BITCOMMERCE_PKG_*` constants, not the `bookstore` directory name.

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

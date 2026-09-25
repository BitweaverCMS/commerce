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

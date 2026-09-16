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
`users_users` (actor).

### `CommerceOrder::updateStatus( $pParamHash )`

This is the only write path for a history row. It:

1. Optionally checks `last_status_id` (optimistic semaphore against a stale
   admin screen).
2. Defaults `status` to the order’s current status when omitted.
3. Writes `com_orders.orders_status_id` and `last_modified`.
4. Inserts `com_orders_status_history` with `orders_id`, `orders_status_id`,
   `date_added`, `customer_notified`, `comments`, and `user_id` (current
   `$gBitUser`).
5. Emails the customer **only** when `notify` is the string `'on'`.

Keys:

| Key | Effect |
|---|---|
| `status` | New `orders_status_id`. Omit to keep the current status. |
| `comments` | History text. A comment with no status change still inserts a row. |
| `notify` | Customer email. Must be `'on'` to send. Any other value (including `FALSE`, `0`, or omitted) does **not** notify. |
| `last_status_id` | If set, must match the latest history id or the update is rejected. |

Staff-only notes (production tools, internal recovery, PDF edits):

```php
$order = new order( $ordersId );
$order->updateStatus( array(
	'comments' => $staffComment,
	'notify' => FALSE,
) );
```

Do not pass `notify => 'on'` unless the customer is meant to receive the
status email. Admin production mutations (for example Products PDF Tackle Box)
must log history and must not notify.

A comment-only update still sets `orders_status_id` to the current status on
both the order row and the new history row. That is expected.

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

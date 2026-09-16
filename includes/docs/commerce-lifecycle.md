# Commerce lifecycle and plugin architecture

## Package identity and startup

The checkout directory is `bookstore`; the registered package name is
`bitcommerce`. `includes/bit_setup_inc.php` registers the package and Commerce
Liberty service. Runtime domain initialization is concentrated in
`includes/bitcommerce_start_inc.php`; controllers that need Commerce objects
load it after Kernel setup.

Do not derive package constants from the directory name. Use
`BITCOMMERCE_PKG_*`.

## Domain layers

| Layer | Primary classes |
|---|---|
| Catalogue | `CommerceProduct`, `CommerceCategory`, `CommerceProductManager` |
| Customer | `CommerceCustomer` |
| Cart | `CommerceShoppingCart`, `CommerceTemporaryCart` |
| Order | `CommerceOrderBase`, `CommerceOrder`, `CommerceOrderManager` |
| Payment | `CommercePaymentManager`, payment plugins |
| Shipping | `CommerceShipping`, shipping plugins |
| Totals | order-total plugins |
| Fulfilment | `CommercePluginFulfillmentBase` implementations |
| Reviews/vouchers | `CommerceReview`, `CommerceVoucher` |
| System/config | `CommerceSystem`, `CommerceBase` |

`CommerceProduct` extends `LibertyMime`: generic product content, ownership,
attachments, parsing, and access remain Liberty concerns.

## Cart-to-order flow

The established checkout pipeline spans page controllers and shared includes:

1. Load/synchronize the customer and shopping cart.
2. Validate product availability, quantities, options, and prices.
3. Resolve billing and shipping addresses.
4. Obtain eligible shipping methods/quotes. Session `shipping` is a
   quote hash (`id`, `title`, `cost`, optional dates), never a bare
   string. `free_free` / `freeshipper_free` is not a shipping module —
   `CommerceOrder::loadFromCart()` must not re-quote it. Confirmation
   templates must test `is_array($smarty.session.shipping)` before
   reading `delivery_date` / `ship_date` (PHP 8 TypeError on `''['key']`).
5. Run order-total modules in configured order.
6. Select and validate payment.
7. Persist the order, products, totals, and status history transactionally.
8. Execute payment/notification handoffs.
9. Empty or convert temporary cart state only after durable success.

Never trust client totals, currency conversion, tax, shipping, or product
options. Recompute them from authoritative catalogue/configuration state.

## Plugin base classes

All module families derive from `CommercePluginBase` through a specialized base:

- `CommercePluginPaymentBase`
- `CommercePluginPaymentCardBase`
- `CommercePluginShippingBase`
- `CommercePluginShippingRateTableBase`
- `CommercePluginOrderTotalBase`
- `CommercePluginFulfillmentBase`

Modules are discovered/configured by type and sort order. A plugin must expose
the established identifier/configuration surface and be safe when disabled.

### Payment

Payment adapters must distinguish authorization, capture, settlement, void,
refund, and local order state. Remote timeout is not proof of failure; use
provider idempotency/reconciliation where supported. Never store sensitive card
data outside the adapter/provider contract.

Failed attempts (including checkout tries that never create an order) are
inserted into `com_orders_payments` with `is_success='n'` by
`CommercePaymentManager::recordFailedPayment()` after any plugin
`RollbackTrans()`. CVV is never stored; PAN is privatized.

Failures are classified by the payment plugin (`classifyPaymentFailure()`):

| Class | `payment_status` | Email |
|---|---|---|
| Customer (issuer decline, CVV/AVS, bad card data, Payflow 7/12/23/24/114, Braintree 2xxx) | `declined` or `invalid` | None, unless the same `customers_id` (else `user_id`, else IP) reaches `PAYMENT_FAIL_ALERT_THRESHOLD` (default 5) unsuccessful rows in `PAYMENT_FAIL_ALERT_WINDOW_MINUTES` (default 15). That sends one `PAYMENT FAILURES` email. Config keys live in Customer Details (`configuration_group_id` 5). |
| Infra (CURL, merchant auth, malformed response, Payflow unknown RESULT, Braintree 3xxx/exception) | `infra` | Immediate `PAYMENT INFRA` to `ERROR_EMAIL`. Unknown gateway codes stay infra. |

Staff inspect the log at `admin/payment_failures.php`. Do not call
`bit_error_email('PAYMENT ERROR…')` from a payment plugin for ordinary
declines. Card-base does not validate CVV length locally; Payflow may return
“CVV must be 4 digits…” as a customer field error (RESULT 7).

### Shipping

Shipping adapters receive an order-derived shipment description. Eligibility,
origin/destination, cutoff, handling, weight, dimensions, currency, and tax can
affect quotes. Keep quoting free of durable shipment side effects;
`createShipment()` is a separate operation.

`CommerceShipping::quote()` splits a shipment into `shipping_num_boxes` when
cart weight exceeds `SHIPPING_MAX_WEIGHT`, then sets `shipping_weight_box` to
the per-box weight. Plugins that rate a single package (USPS REST domestic and
international) must quote that box weight. `usps::quote()` then multiplies the
returned rate by `shipping_num_boxes`. Do not `eb()` / `emergency_break()` on
multi-box international quotes — that aborts checkout instead of returning
rates.

USPS REST international `productName` values include Machinable, Nonstandard,
or Large Envelope. Admin `_TYPES` strings often omit those tokens (for example
`Priority Mail International ISC Single-piece`). `usps::quote()` matches them
after normalizing those tokens and prefers the package rate over Large Envelope.

### Order totals

`CommerceOrderBase::otProcess()` coordinates configured total modules.
Ordering is semantic: subtotal, discount/credit, shipping, tax, fees, and final
total can affect one another. Changes require a mixed-cart regression matrix.

### Fulfilment

Fulfilment adapters choose delivery capability, priority, completion behavior,
origin, and estimated ship date. Generic Commerce models the contract; concrete
production systems belong outside this upstream package.

## Order state

Order status history is business/audit state. The write API is
`CommerceOrder::updateStatus()` — see [orders.md](orders.md). Status changes
should:

- Validate the transition and actor.
- Persist history with the order update (`updateStatus()`, not ad-hoc INSERTs).
- Avoid duplicate notifications/remote side effects. Customer email is sent
  only when `notify` is `'on'`; staff comments pass `notify => FALSE`.
- Preserve terminal and financial state.
- Remain recoverable after partial provider failure.

## Liberty and Users services

Commerce service callbacks prevent unsafe product/user expunge and synchronize
customer behavior at registration/deletion. Completed orders can make customer
expunge invalid. These callbacks run from other package lifecycles; do not
assume a storefront controller context.

## Data-model cautions

`admin/schema_inc.php` is large and authoritative for fresh installs. Major
families include catalogue/category descriptions, customers/addresses, carts,
orders/order products/status history, currencies, tax, shipping/payment/module
configuration, vouchers/coupons, reviews, commissions, and operational logs.

Many tables use legacy naming and denormalized snapshots intentionally. Order
rows must preserve purchase-time address, price, tax, and product descriptions
even when catalogue/customer records later change.

`CommerceOrder::load()` loads all `TABLE_ORDERS_PRODUCTS_ATTRIBUTES` rows for
the order in one query and groups them in PHP. Do not restore a per-line-item
attributes SELECT inside that load loop.

## Testing matrix

- Anonymous and registered carts.
- Multiple quantities/options and unavailable products.
- Taxable/non-taxable and domestic/international destinations.
- Zero-total, credit, and paid orders.
- Payment timeout/retry/callback duplication.
- Shipping quote versus shipment creation.
- Currency rounding.
- Order status transition and notification idempotency.

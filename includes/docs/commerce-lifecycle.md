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
4. Obtain eligible shipping methods/quotes.
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

### Shipping

Shipping adapters receive an order-derived shipment description. Eligibility,
origin/destination, cutoff, handling, weight, dimensions, currency, and tax can
affect quotes. Keep quoting free of durable shipment side effects;
`createShipment()` is a separate operation.

### Order totals

`CommerceOrderBase::otProcess()` coordinates configured total modules.
Ordering is semantic: subtotal, discount/credit, shipping, tax, fees, and final
total can affect one another. Changes require a mixed-cart regression matrix.

### Fulfilment

Fulfilment adapters choose delivery capability, priority, completion behavior,
origin, and estimated ship date. Generic Commerce models the contract; concrete
production systems belong outside this upstream package.

## Order state

Order status history is business/audit state. Status changes should:

- Validate the transition and actor.
- Persist history with the order update.
- Avoid duplicate notifications/remote side effects.
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

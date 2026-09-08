# Commerce architecture

## Package role

Commerce provides catalogue, cart, checkout, order, payment, shipping, tax, and fulfilment infrastructure.

## Initialization

The package bootstrap is `includes/bit_setup_inc.php`. Bitweaver discovers package bootstraps during
Kernel package scanning. Treat bootstrap files as registration and wiring code:
they can define constants, register the package, load shared classes, attach
Liberty services, and expose values to Smarty.

Do not call a package bootstrap as a web endpoint. All normal controllers must
load `kernel/includes/setup_inc.php` before using framework globals.

## Architectural responsibilities

Owns the generic commerce domain model and its pluggable payment, shipping, order-total, and fulfilment modules.

## Dependency direction

This package depends on kernel, liberty, users, themes, languages, util. Calls into shared packages should
use their public classes, globals, services, and registered content types rather
than duplicating their persistence.

Does not define deployment-specific products, production workflows, or storefront branding.

## Orders

Commerce owns `com_orders`, line items, and `com_orders_status_history`.
Construct with `new order( $ordersId )`. Append history only through
`CommerceOrder::updateStatus()`. Customer email is opt-in: `notify` must be
the string `'on'`. Staff/production comments use `notify => FALSE` and omit
`status` (keeps the current status). Full API, tables, and product→order
lookup: [orders.md](orders.md). Checkout pipeline:
[commerce-lifecycle.md](commerce-lifecycle.md).

## Request and rendering pattern

Most package controllers follow Bitweaver's established flow:

1. Load Kernel setup.
2. Resolve and validate request identifiers.
3. Construct or load the domain object.
4. Enforce global and content-level permissions before mutation or disclosure.
5. Perform the domain operation.
6. Assign data to Smarty and render a package template.

Package-specific controllers and templates are enumerated in
[source-reference.md](source-reference.md). Read the controller and its included
files together; many older controllers delegate substantial behavior to
`*_inc.php` files.

## Persistence

When `admin/schema_inc.php` exists it is the canonical install-time declaration
of tables, sequences, indexes, constraints, permissions, and default
preferences. Runtime SQL must be checked against that file and relevant upgrade
scripts. Never infer a deployed database's exact migration state from the base
schema alone.

## Presentation

Templates belong to the package but are resolved through Themes/Smarty.
Controllers own request handling; templates should render assigned state rather
than perform domain mutations.

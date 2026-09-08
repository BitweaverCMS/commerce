# Commerce package documentation

> Engineering documentation derived from the source in this package. The
> package's `includes/` directory must be denied to direct HTTP requests.

## Purpose

Commerce provides catalogue, cart, checkout, order, payment, shipping, tax, and fulfilment infrastructure.

## Responsibility

Owns the generic commerce domain model and its pluggable payment, shipping, order-total, and fulfilment modules.

## Dependencies

kernel, liberty, users, themes, languages, util.

Dependency direction matters: this package may depend on the packages above;
the dependencies do not thereby depend on this package.

## Boundary

Does not define deployment-specific products, production workflows, or storefront branding.

## Admin presentation / APCu gotchas

Commerce admin (for example `admin/orders.php` and `admin/includes/application_top.php`)
often needs a fluid body and admin-only CSS/JS (admin.css, datepicker, colorbox).

- Fluid width: `$gBitSystem->setRequestConfig('layout-body', '-fluid')`. Do not
  assign `$gBitSystem->mConfig['layout-body']` — that path previously required
  save/restore because APCu can serialize `BitSystem` and poison public pages
  with `container-fluid` (intermittent per FPM worker).
- Admin/page CSS/JS: pass `$pPersistent = FALSE` on `BitThemes::loadCss` /
  `loadJavascript` / `loadAjax`. A cache-miss store of those assets into the
  `BitThemes` singleton was observed leaking bookstore admin CSS onto public
  Search HTML.

See Themes `includes/docs/development.md` and Kernel
`includes/docs/core-runtime.md`.

## Documentation map

- [Orders and order processing](orders.md) — `orders_id` / line items, status
  history, `updateStatus()` (including `notify => FALSE` for staff comments),
  and resolving an order from `products_id`.
- [Architecture](architecture.md) — initialization, components, and request flow.
- [Source reference](source-reference.md) — source-derived files, classes,
  controllers, schema artifacts, plugins, and templates.
- [Development guide](development.md) — safe change workflow, extension points,
  validation, and maintenance guidance.
- [Security](security.md) — trust boundaries and direct-HTTP access requirements.
- [Commerce lifecycle and plugins](commerce-lifecycle.md) — initialization,
  catalogue/cart/order flow, module families, fulfilment, and consistency rules.

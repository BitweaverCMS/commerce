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

## Documentation map

- [Architecture](architecture.md) — initialization, components, and request flow.
- [Source reference](source-reference.md) — source-derived files, classes,
  controllers, schema artifacts, plugins, and templates.
- [Development guide](development.md) — safe change workflow, extension points,
  validation, and maintenance guidance.
- [Security](security.md) — trust boundaries and direct-HTTP access requirements.
- [Commerce lifecycle and plugins](commerce-lifecycle.md) — initialization,
  catalogue/cart/order flow, module families, fulfilment, and consistency rules.

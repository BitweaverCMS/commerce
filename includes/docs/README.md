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

Index: [toc.md](toc.md).

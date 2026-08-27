# Commerce security notes

## Direct HTTP access

The entire `includes/` subtree is private implementation material. The
package-level `.htaccess` denies Apache access recursively and explicitly
protects both `.htaccess` and `web.config`. The package-level `web.config`
denies all IIS users.

Nginx and Caddy do not consume directory-local access files. Their site
configuration must deny any URI path segment named `includes`:

### Nginx

```nginx
location ~ (^|/)includes(?:/|$) {
    deny all;
    return 403;
}
```

### Caddy

```caddyfile
@packageIncludes path_regexp packageIncludes (^|/)includes(?:/|$)
respond @packageIncludes 403
```

After deployment, request a known file beneath this package's `includes/`
directory and require HTTP 403 or 404. A PHP 500 response is a failure because
it proves that the server executed a directly requested implementation file.

## Application trust boundaries

- Request, cookie, header, upload, webhook, and API data are untrusted.
- Authentication does not imply authorization.
- Content-level access can be stricter than a global package permission.
- Identifiers must be validated before use in SQL, paths, redirects, or object
  construction.
- Secrets and credentials must remain in protected configuration, never in
  templates, responses, logs, or these documents.
- File operations must use validated storage helpers and must prevent traversal.

## List URL query strings and crawl paths (P2)

`CommerceProduct::prepGetList()` builds `$pListHash['query_string']` for
pagination and sort forms from an **allowlist** of identity filters only
(`main_page`, `category_id`, `user_id`, `tag`, …). Values are `rawurlencode()`’d.
Never concatenate raw `$_GET` into that string or into HTML attributes.

**Public list pagination** (`templates/commerce_pagination.tpl`):

- Crawlable `href`s = identity `query_string` + `page` when page &gt; 1.
- Do **not** emit defaultable `sort_mode` / `max_records` (avoids a spider-trap
  matrix of sort × page-size × page).
- Non-default sort/page-size: append those params for humans, mark links
  `rel="nofollow"`, set `metaNoIndex` (`noindex,follow`), and keep
  **canonical** on the default-sort path with `page=N` when N&gt;1 (P2 — large
  categories must not all canonicalize to page 1).
- Sort UI should use POST against the identity URL (not crawlable GET sorts).
- Admin lists that need sort in pager links set `pagination_append_sort` and
  `pagination_append_max` on `listInfo`.

Related: `{form}` escapes `action` with `htmlspecialchars` in
`themes/smartyplugins/block.form.php` (defense-in-depth against attribute XSS).

## Package boundary

Does not define deployment-specific products, production workflows, or storefront branding.

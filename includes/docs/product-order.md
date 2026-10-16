# User catalog order

`templates/center_list_products.php` lists one user's products when `gQueryUser` is set. If the layout does not pass `sort_mode`, the order is `products_sort_order` ascending, then product name, then `products_id`. `getList()` applies a limit and that limited query does not keep `ORDER BY`, so `CommerceProduct::sortUserCatalog()` sorts the rows after the query. An array of product-column modes is sent through the database sort converter. The content-list field check would replace that array with `last_modified_desc`.

`products_sort_order` is the integer column on `com_products`. The admin product form already edits it. Equal values stay in name order, so a zero-padded volume title stays numerical.

An explicit `sort_mode` on the layout module or its params still wins. A list that is not scoped to one user still uses `random` when no sort is set.

`CommerceProduct::getList()` itself still defaults an empty `sort_mode` to newest-first. This order applies only to the user-scoped center list.

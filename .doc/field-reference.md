# Export Field Reference

Every column produced by `AdminExportProductsController::postProcess()`, in export order, with its data source and anything non-obvious about it. Field keys match the array keys in `$this->available_fields`.

Most fields are copied directly from a `Product` object property (populated by `ObjectModel` from the `ps_product` / `ps_product_lang` row). Fields marked "switch case" are computed separately in the `postProcess()` `switch ($field)` block because no such property exists, or because the raw property isn't the value the column actually needs.

| Field key | CSV label | Source | Notes |
|---|---|---|---|
| `id` | Product ID | `Product` property | |
| `active` | Active (0/1) | `Product` property | |
| `name` | Name | `Product` property | |
| `categories` | Categories | switch case: `Product::getProductCategoriesFull()` | Comma-joined category names. |
| `price_tex` | Price tax excluded | switch case: `Product::getPrice()` | Only one of `price_tex`/`price_tin` is present per export, based on the "Price tax included or excluded" option. |
| `price_tin` | Price tax included | switch case: `Product::getPrice()` | See above. |
| `id_tax_rules_group` | Tax rules ID | `Product` property | |
| `wholesale_price` | Wholesale price | `Product` property | |
| `on_sale` | On sale (0/1) | `Product` property | |
| `reduction_price` | Discount amount | switch case: `SpecificPrice::getSpecificPrice()` | Computes all four `reduction_*` columns at once (see below). |
| `reduction_percent` | Discount percent | switch case (no-op) | Populated by the `reduction_price` case; see [development.md](./development.md#the-field-loop-and-the-switch) for why this needs its own no-op case. |
| `reduction_from` | Discount from (yyyy-mm-dd) | switch case (no-op) | Same as above. |
| `reduction_to` | Discount to (yyyy-mm-dd) | switch case (no-op) | Same as above. |
| `reference` | Reference # | `Product` property | |
| `supplier_reference` | Supplier reference # | switch case (forced): `ProductSupplier::getProductSupplierReference()` | `Product::$supplier_reference` exists but is usually empty; the real value lives per supplier/combination in `ps_product_supplier`. Forced through the switch, see development.md. |
| `supplier_name` | Supplier | `Product` property | |
| `manufacturer_name` | Manufacturer | `Product` property | |
| `ean13` | EAN13 | `Product` property | |
| `isbn` | ISBN | `Product` property | |
| `upc` | UPC | switch case (forced): `$p->upc` | Forced through the switch only to pad an empty UPC with a single space instead of an empty string; value itself is the plain property. |
| `mpn` | MPN | `Product` property | |
| `ecotax` | Ecotax | `Product` property | |
| `width` / `height` / `depth` / `weight` | Width / Height / Depth / Weight | `Product` property | |
| `delivery_in_stock` / `delivery_out_stock` | Delivery time texts | `Product` property | |
| `quantity` | Quantity | `Product` property | |
| `minimal_quantity` | Minimal quantity | `Product` property | |
| `low_stock_threshold` / `low_stock_alert` | Low stock level / alert | `Product` property | |
| `stock_location` | Stock location | switch case: `ps_stock_available.location` | Direct query, base combination (`id_product_attribute = 0`) for the current shop. |
| `visibility` | Visibility | `Product` property | |
| `additional_shipping_cost` | Additional shipping cost | `Product` property | |
| `unity` | Unit for the unit price | `Product` property | |
| `unit_price` | Unit price | `Product` property | |
| `description_short` / `description` | Short description / Description | `Product` property | |
| `tags` | Tags | `Product` property or switch case | `$p->tags` is populated by `Tag::getProductTags()` in the `Product` constructor; it's `false` (no tags) or an array (has tags). The array case routes through `Product::getTags($id_lang)` in the switch, which returns a joined string for the given language. |
| `meta_title` | Meta title | `Product` property | |
| `meta_description` | Meta description | `Product` property | |
| `link_rewrite` | URL rewritten | `Product` property | |
| `available_now` / `available_later` | Text when in stock / backorder | `Product` property | |
| `available_for_order` | Available for order | `Product` property | |
| `available_date` | Product available date | `Product` property | |
| `date_added` | Product creation date | switch case: `$p->date_add` reformatted to `Y-m-d` | |
| `show_price` | Show price | `Product` property | |
| `image` | Image URLs | switch case: `Product::getImages()` | Comma-joined image URLs; also populates `legend` (see below). |
| `legend` | Image alt texts (x,y,z...) | switch case (no-op) | Populated by the `image` case, from the same `getImages()` rows - `legend` is `il.legend` from `ps_image_lang`, already present in that result set. |
| `delete_existing_images` | Delete existing images | switch case: hardcoded `0` | Always exported as `0`; this module doesn't offer a way to set it. |
| `features` | Feature (Name:Value:Position:Customized) | switch case: `Product::getFrontFeatures()` | Joined with the configurable "Feature delimiter" export option; `Customized` is always hardcoded to `1`. |
| `online_only` | Available online only | `Product` property | |
| `condition` | Condition | `Product` property | |
| `customizable` | Customizable | `Product` property | |
| `uploadable_files` / `text_fields` | Uploadable files / Text fields | `Product` property | |
| `out_of_stock` | Action when out of stock | `Product` property | |
| `is_virtual` | Virtual product | `Product` property | |
| `file_url` | File URL | switch default: always `''` | Intentionally always empty - PrestaShop stores downloadable files under a protected path with no stable public URL to export. |
| `nb_downloadable` | Number of allowed downloads | switch case: `ProductDownload` | Only looked up when `is_virtual` is true. Also populates `date_expiration` and `nb_days_accessible`. |
| `date_expiration` | Expiration date | switch case (no-op) | Populated by the `nb_downloadable` case. |
| `nb_days_accessible` | Number of days | switch case (no-op) | Populated by the `nb_downloadable` case. |
| `shop` | ID / Name of shop | switch case: `$id_shop` | Always the current shop context's ID, not a name despite the label; no shop picker in the export form. |
| `advanced_stock_management` | Advanced Stock Management | `Product` property | |
| `depends_on_stock` | Depends on stock | switch case (forced): `ps_stock_available.depends_on_stock` | `Product::$depends_on_stock` exists but PrestaShop core never assigns it (always `false`); forced through the switch to read the real value directly. |
| `warehouse` | Warehouse | switch case: `Warehouse::getWarehousesByProductId()` | Comma-joined warehouse IDs. |
| `accessories` | Accessories (x,y,z...) | switch case: `Product::getAccessories()` | Comma-joined references of linked products - the same identifier format PrestaShop's own import accepts for this column. |

## Options that affect the whole export

| Option | Effect |
|---|---|
| Language | `$id_lang` passed to every lookup above. |
| Delimiter | CSV field delimiter (`fputcsv`'s `$delimiter` argument). Default `,`. |
| Feature delimiter | Separator between individual `Name:Value:Position:Customized` entries within the `features` column. Default `\|`. |
| Export only active products | Filters `Product::getProducts()`. |
| Product Category | Filters `Product::getProducts()`; `99999` means "All". |
| Price tax included or excluded | Selects whether `price_tin` or `price_tex` is present in the export at all (see above). |

All cell values are also passed through `escapeCsvFormula()` before being written, which prefixes values starting with `=`, `+`, `-`, `@`, tab or CR with a single quote to prevent formula injection when the CSV is opened directly in a spreadsheet application.

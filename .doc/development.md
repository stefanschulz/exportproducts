# Development Notes

## Architecture

The module has two files:

- `exportproducts.php` - bare module bootstrap. `install()`/`uninstall()` just register/remove the `AdminExportProducts` admin tab via `Tab::getIdFromClassName()`. There is no configuration stored anywhere (no `Configuration::updateValue()` calls) - every export option is a one-off form field read directly in `postProcess()`.
- `controllers/admin/AdminExportProducts.php` - the entire module. `$this->available_fields` (constructor) is the single source of truth for both the CSV header row and the export order; `postProcess()` streams the CSV directly to `php://output` (no temp file), one `Product` object per row.

### The field loop and the switch

For each product, `postProcess()` iterates `$this->available_fields` once and, for each field key, either:

1. copies it straight from the `Product` object (`$p->$field`), if that property exists and isn't an array, or
2. falls into a `switch ($field)` block that computes the value some other way.

This shortcut is fragile in two specific ways worth knowing before touching it:

- **A field can exist as a `Product` property without holding the value you actually want.** `upc`, `supplier_reference` and `depends_on_stock` are all real properties, so they'd normally take path (1) and never reach their `switch` case - even though the case exists specifically to look the real value up elsewhere (a per-supplier reference, a `stock_available` row, etc.). These three are listed explicitly in `$force_switch_fields` to route them through the `switch` regardless. If you add a new field and its `switch` case never seems to run, check whether `Product` already declares that property name.
- **Multiple CSV columns can be computed by a single `switch` case.** `reduction_price` computes all four `reduction_*` columns at once; `image` also fills `legend`; `nb_downloadable` also fills `date_expiration` and `nb_days_accessible`. Each of those secondary field keys still gets its own turn through the outer loop later, and by default that would hit `default: $line[$field] = '';` and silently wipe out the value just computed. Every such secondary field has its own explicit `switch` case that's just a comment and a `break;`, to opt out of the default overwrite. **If you add a field that's computed alongside another one, you must add this no-op case too**, or it will always be blank.

See [field-reference.md](./field-reference.md) for the full field-by-field breakdown.

## PrestaShop 9.x pitfalls found while working on this module

- **`$this->get(TabRepository::class)` doesn't work from a legacy `Module` class.** `PrestaShopBundle\Entity\Repository\TabRepository` is a Symfony service that becomes private/inlined when the container is compiled in PS 9.1, so `Module::get()` throws `ServiceNotFoundException`. This isn't a niche edge case - it breaks module installation outright, including the shop's own automatic module install during first setup if the module folder is present at install time. Use the legacy static `Tab::getIdFromClassName()` instead (still present, still fully functional, and used by PrestaShop's own bundled modules such as `dashgoals` for exactly this purpose).
- **`Tools::date_format()` no longer exists.** `date_create()` already returns a `DateTime`, which has its own `format()` method - use that directly.
- **Multistore-scoped columns live in `ps_product_shop`, not `ps_product`.** `advanced_stock_management` (and several other flags) are per-shop overrides; updating only the base `ps_product` row (e.g. via raw SQL while testing) has no effect on what `Product` actually returns for a given shop context.
- **Not every DB column PrestaShop exposes on `Product` is actually populated.** `Product::$depends_on_stock` is declared with a hardcoded `= false` default and nothing in `classes/Product.php` ever assigns it from the database - the real per-product/shop value lives in `ps_stock_available.depends_on_stock` and has to be queried directly.
- **`meta_keywords` no longer exists as a column at all** (`ps_product_lang` has no such field on PS 8.x/9.x), and it isn't part of PrestaShop's own current product import template either.

## Testing against a real PrestaShop instance

There's no automated test suite (the module has no framework/composer dependency to run one against). Changes were verified against a disposable Docker PrestaShop 9.1 instance:

```yaml
# docker-compose.yml
services:
  db:
    image: mariadb:10.11
    environment:
      MYSQL_ROOT_PASSWORD: prestashop
      MYSQL_DATABASE: prestashop
      MYSQL_USER: prestashop
      MYSQL_PASSWORD: prestashop
    volumes:
      - db_data:/var/lib/mysql

  prestashop:
    image: prestashop/prestashop:9.1-apache
    depends_on: [db]
    ports: ["8091:80"]
    environment:
      DB_SERVER: db
      DB_NAME: prestashop
      DB_USER: prestashop
      DB_PASSWD: prestashop
      PS_INSTALL_AUTO: 1
      PS_DOMAIN: localhost:8091
      PS_FOLDER_ADMIN: admin
      PS_FOLDER_INSTALL: install
      ADMIN_MAIL: admin@example.com
      ADMIN_PASSWD: Admin1234!
      PS_DEV_MODE: 1
    volumes:
      - ps_data:/var/www/html

volumes:
  db_data:
  ps_data:
```

Notes:

- Let PrestaShop finish its own automatic installation *before* mounting/copying the module in - the shop's install process auto-installs every module already present in `modules/`, and an uncaught exception there aborts the entire install, not just that module.
- `PS_DEV_MODE: 1` surfaces PHP warnings as an `x-debug-exception` response header (and HTTP 500) instead of silently swallowing them - essential for catching bugs like the ones in the 2.8.0 changelog below, which produced a valid-looking CSV with no visible error in production mode.
- The most reliable way to validate a release zip end to end is to replicate what PrestaShop's own installer does: `ZipSourceHandler` (`src/Core/Module/SourceHandler/ZipSourceHandler.php`) matches `^(.*)/\1\.php$` against zip entries to find the module name, then extracts the whole archive into `modules/`. Running that same `ZipArchive` logic against a built package (see `build.xml`) inside the container is a fast way to confirm the package structure is actually installable, without needing a real file-upload flow in a browser.

## Changelog

### 2.8.1

Packaging/documentation only, no functional changes:

- Restructured the repository so the module lives in an `exportproducts/` subfolder, matching what PrestaShop's module zip installer requires (a `<name>/<name>.php` path inside the archive).
- Added a Phing `build.xml` (`phing package`) to build an installable zip.
- Added the `LICENSE` (OSL-3.0, matching the license already declared in the source headers since the 2010 original) - this repository is a git-verified fork of [oavea/exportproducts](https://github.com/oavea/exportproducts) (identical commit history up to `6847fa3`, unmaintained upstream since 2015).

### 2.8.0

- Fixed module installation being broken on PrestaShop 9.1 (`TabRepository` service access, see pitfalls above).
- Fixed several fields that were silently exported empty or wrong on modern PrestaShop despite the code intending to compute them: `reduction_percent`/`reduction_from`/`reduction_to`, `supplier_reference`, `upc`, `depends_on_stock`, `legend` (image alt text).
- Fixed a fatal crash exporting any product with a dated discount (`Tools::date_format()` no longer exists).
- Fixed an "undefined array key" warning (and resulting HTTP 500 in dev mode) for products without an active discount.
- Removed `meta_keywords` (column doesn't exist in PS 8.x/9.x, and isn't part of PrestaShop's own current import template).
- Added `ISBN`, `MPN`, `Stock location`, virtual/downloadable product fields (`Virtual product`, `File URL`, `Number of allowed downloads`, `Expiration date`, `Number of days`) and `Accessories`, closing the gap against PrestaShop's own current product import template.
- Mitigated CSV/formula injection in exported text fields.

### 2.7.2 and earlier

See git history - versions before 2.8.0 predate this documentation.

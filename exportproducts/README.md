# Prestashop Export Products Module

This module was built to fill a gap in Prestashop's current code base. It offers a full product export which matches Prestashop's native product import exactly.

## Installation
To install this module either upload the module as a zip file using the Prestashop module page. Or upload the module folder straight to your server within the Prestashop module directory.

Once on the server navigate to the Prestashop admin module page and find Export Products and click install.

## Usage
Using the module is very simple. The module will add a new menu item called "Export Products" under "Advanced Parameters" within the Prestashop Admin menu.

To begin exporting just hit the Export button if you require a more refined export adjust the export options accordingly.

### Export Options
- Language select
- Category select
- Delimiter (choose the character which separates the fields in the CSV output)
- Feature delimiter (choose the character which separates individual feature entries within a field)
- Export only active products
- Price tax included or excluded

### Compatibility

Requires PrestaShop 8.0 or later.

### Notes

- The exported columns are kept aligned with PrestaShop's own current product import template, including ISBN, MPN, stock location, virtual/downloadable product fields and accessories.
- The "File URL" column for virtual products is always left empty: PrestaShop does not expose a stable public URL for downloadable product files, so there is nothing meaningful to export there.

## License

Released under the [Open Software License 3.0](./LICENSE.md).


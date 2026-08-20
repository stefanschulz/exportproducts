# Export Products Module for PrestaShop

## About

A module to export all products to CSV, matching PrestaShop's native product import template so the file can be re-imported directly.

## Compatibility

Requires PrestaShop 8.0 or later.

## Usage and Installation

See the [module README](./exportproducts/README.md).

## Development and Packaging

The module implementation itself lives in the `exportproducts` subfolder, as the module must not contain any non-module files such as build scripts. A Phing build file is provided for creating an installable module package:

```
phing package
```

This zips the `exportproducts` folder into `exportproducts-<version>.zip` at the repository root, ready to upload via the PrestaShop module page. Keep the `version` property in `build.xml` in sync with `exportproducts/config.xml` and `exportproducts/exportproducts.php`.

## License

Released under the [Open Software License 3.0](http://www.opensource.org/licenses/osl-3.0.php), as declared in the module's source headers.

<?php

/**
 * Export Products
 * @category export
 *
 * @author Oavea - Oavea.com
 * @copyright Oavea / PrestaShop
 * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
 * @version 2.7.1
 */
class AdminExportProductsController extends ModuleAdminController
{

    public $available_fields;

    public function __construct()
    {
        $this->bootstrap = true;

        $this->meta_title = 'Export Products';
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->available_fields = array(
            'id' => array('label' => 'Product ID'),
            'active' => array('label' => 'Active (0/1)'),
            'name' => array('label' => 'Name'),
            'categories' => array('label' => 'Categories'),
            'price_tex' => array('label' => 'Price tax excluded'),
            'price_tin' => array('label' => 'Price tax included'),
            'id_tax_rules_group' => array('label' => 'Tax rules ID'),
            'wholesale_price' => array('label' => 'Wholesale price'),
            'on_sale' => array('label' => 'On sale (0/1)'),
            'reduction_price' => array('label' => 'Discount amount'),
            'reduction_percent' => array('label' => 'Discount percent'),
            'reduction_from' => array('label' => 'Discount from (yyyy-mm-dd)'),
            'reduction_to' => array('label' => 'Discount to (yyyy-mm-dd)'),
            'reference' => array('label' => 'Reference #'),
            'supplier_reference' => array('label' => 'Supplier reference #'),
            'supplier_name' => array('label' => 'Supplier'),
            'manufacturer_name' => array('label' => 'Manufacturer'),
            'ean13' => array('label' => 'EAN13'),
            'upc' => array('label' => 'UPC'),
            'ecotax' => array('label' => 'Ecotax'),
            'width' => array('label' => 'Width'),
            'height' => array('label' => 'Height'),
            'depth' => array('label' => 'Depth'),
            'weight' => array('label' => 'Weight'),
            'delivery_in_stock' => array('label' => 'Delivery time of in-stock products'),
            'delivery_out_stock' => array('label' => 'Delivery time of out-of-stock products with allowed orders'),
            'quantity' => array('label' => 'Quantity'),
            'minimal_quantity' => array('label' => 'Minimal quantity'),
            'low_stock_threshold' => array('label' => 'Low stock level'),
            'low_stock_alert' => array('label' => 'Send me an email when the quantity is under this level'),
            'visibility' => array('label' => 'Visibility'),
            'additional_shipping_cost' => array('label' => 'Additional shipping cost'),
            'unity' => array('label' => 'Unit for the unit price'),
            'unit_price' => array('label' => 'Unit price'),
            'description_short' => array('label' => 'Short description'),
            'description' => array('label' => 'Description'),
            'tags' => array('label' => 'Tags'),
            'meta_title' => array('label' => 'Meta title'),
            'meta_keywords' => array('label' => 'Meta keywords'),
            'meta_description' => array('label' => 'Meta description'),
            'link_rewrite' => array('label' => 'URL rewritten'),
            'available_now' => array('label' => 'Text when in stock'),
            'available_later' => array('label' => 'Text when backorder allowed'),
            'available_for_order' => array('label' => 'Available for order (0 = No, 1 = Yes)'),
            'available_date' => array('label' => 'Product available date'),
            'date_added' => array('label' => 'Product creation date'),
            'show_price' => array('label' => 'Show price (0 = No, 1 = Yes)'),
            'image' => array('label' => 'Image URLs'),
            'legend' => array('label' => 'Image alt texts (x,y,z...)'),
            'delete_existing_images' => array(
                'label' => 'Delete existing images (0 = No, 1 = Yes)'
            ),
            'features' => array('label' => 'Feature (Name:Value:Position:Customized)'),
            'online_only' => array('label' => 'Available online only (0 = No, 1 = Yes)'),
            'condition' => array('label' => 'Condition'),
            'customizable' => array('label' => 'Customizable (0 = No, 1 = Yes)'),
            'uploadable_files' => array('label' => 'Uploadable files (0 = No, 1 = Yes)'),
            'text_fields' => array('label' => 'Text fields (0 = No, 1 = Yes)'),
            'out_of_stock' => array('label' => 'Action when out of stock'),
            'shop' => array(
                'label' => 'ID / Name of shop',
                'help' => 'Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.',
            ),
            'advanced_stock_management' => array(
                'label' => 'Advanced Stock Management',
                'help' => 'Enable Advanced Stock Management on product (0 = No, 1 = Yes).',
            ),
            'depends_on_stock' => array(
                'label' => 'Depends on stock',
                'help' => '0 = Use quantity set in product, 1 = Use quantity from warehouse.',
            ),
            'warehouse' => array(
                'label' => 'Warehouse',
                'help' => 'ID of the warehouse to set as storage.'
            ),
        );

    }

    public function renderView()
    {

        return $this->renderConfigurationForm();

    }

    public function renderConfigurationForm()
    {
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int)$this->context->shop->id;

        foreach ($langs as $key => $language) {
            $options[] = array('id_option' => $language['id_lang'], 'name' => $language['name']);
        }

        $cats = $this->getCategories(
            $lang->id,
            true,
            $id_shop
        );

        $pricetax = array(
            array('id_option' => 'price_tin', 'name' => 'Price Tax Included'),
            array('id_option' => 'price_tex', 'name' => 'Price Tax Excluded')
        );

        $categories[] = array('id_option' => 99999, 'name' => 'All');

        foreach ($cats as $key => $cat) {
            $categories[] = array('id_option' => $cat['id_category'], 'name' => $cat['name']);
        }

        $inputs = array(
            array(
                'type' => 'select',
                'label' => $this->l('Language'),
                'desc' => $this->l('Choose a language you wish to export'),
                'name' => 'export_language',
                'class' => 't',
                'options' => array(
                    'query' => $options,
                    'id' => 'id_option',
                    'name' => 'name'
                ),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Delimiter'),
                'name' => 'export_delimiter',
                'value' => ',',
                'desc' => $this->l('The character to separate the fields')
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Feature delimiter'),
                'name' => 'export_feature_delimiter',
                'value' => '|',
                'desc' => $this->l('Character to seperate single feature entries')
            ),
            array(
                'type' => 'radio',
                'label' => $this->l('Export active products?'),
                'name' => 'export_active',
                'values' => array(
                    array('id' => 'active_off', 'value' => 0, 'label' => 'no, export all products.'),
                    array('id' => 'active_on', 'value' => 1, 'label' => 'yes, export only active products'),
                ),
                'is_bool' => true,
            ),
            array(
                'type' => 'select',
                'label' => $this->l('Product Category'),
                'desc' => $this->l('Choose a product category you wish to export'),
                'name' => 'export_category',
                'class' => 't',
                'options' => array(
                    'query' => $categories,
                    'id' => 'id_option',
                    'name' => 'name'
                ),
            ),
        );


        $pricetintex = array(
            array(
                'type' => 'select',
                'label' => $this->l('Price tax included or excluded'),
                'desc' => $this->l('Choose if you want to export the price with or without tax.'),
                'name' => 'export_tax',
                'class' => 't export_tax',
                'options' => array(
                    'query' => $pricetax,
                    'id' => 'id_option',
                    'name' => 'name'
                )
            )
        );
        $inputs = array_merge(
            $inputs,
            $pricetintex
        );


        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Export Options'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Export'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitExport';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminExportProducts');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }


    public function getConfigFieldsValues()
    {
        return array(
            'export_active' => false,
            'export_category' => 'all',
            'export_delimiter' => ',',
            'export_feature_delimiter' => '|',
            'export_language' => (int)Configuration::get('PS_LANG_DEFAULT'),
            'export_tax' => 'price_tin'
        );
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitExport')) {
            $delimiter = Tools::getValue('export_delimiter');
            $feature_delimiter = Tools::getValue('export_feature_delimiter');
            $id_lang = Tools::getValue('export_language');
            $id_shop = (int)$this->context->shop->id;

            set_time_limit(0);
            $fileName = 'products_' . date("Y_m_d_H_i_s") . '.csv';
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Description: File Transfer');
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename={$fileName}");
            header("Expires: 0");
            header("Pragma: public");
            echo "\xEF\xBB\xBF";

            $f = fopen(
                'php://output',
                'w'
            );

            $export_tax = Tools::getValue('export_tax');
            if ($export_tax == 'price_tin') {
                unset($this->available_fields['price_tex']);
            } elseif ($export_tax == 'price_tex') {
                unset($this->available_fields['price_tin']);
            }

            $titles = array();
            foreach ($this->available_fields as $field => $array) {
                $titles[$field] = $array['label'];
            }

            fputcsv(
                $f,
                $titles,
                $delimiter,
                '"'
            );

            $export_active = !(Tools::getValue('export_active') == 0);
            $export_category = (Tools::getValue('export_category') == 99999 ? false : Tools::getValue(
                'export_category'
            ));

            $products = Product::getProducts(
                $id_lang,
                0,
                0,
                'id_product',
                'ASC',
                $export_category,
                $export_active
            );

            foreach ($products as $product) {
                $line = array();
                    $p = new Product(
                        $product['id_product'],
                        true,
                        $id_lang,
                        $id_shop,
                        Context::getContext()
                    );

                foreach ($this->available_fields as $field => $array) {
                    if (isset($p->$field) && !is_array($p->$field)) {
                        $line[$field] = $p->$field;
                    } else {
                        switch ($field) {
                            case 'categories':
                                $cats = $p->getProductCategoriesFull(
                                    $p->id,
                                    $id_lang
                                );
                                $cat_array = array();
                                foreach ($cats as $cat) {
                                    $cat_array[] = $cat['name'];
                                }

                                $line['categories'] = implode(
                                    ",",
                                    $cat_array
                                );
                                break;
                            case 'price_tex':
                            case 'price_tin':
                                $line['price_tex'] = $p->getPrice(
                                    false,
                                    null,
                                    2,
                                    null,
                                    false,
                                    false,
                                    1
                                );
                                $line['price_tin'] = $p->getPrice(
                                    true,
                                    null,
                                    2,
                                    null,
                                    false,
                                    false,
                                    1
                                );


                                if ($export_tax == 'price_tin') {
                                    unset($line['price_tex']);
                                } else {
                                    unset($line['price_tin']);
                                }

                                break;
                            case 'upc':
                                $line['upc'] = $p->upc ? $p->upc : ' ';

                                break;
                            case 'supplier_reference':
                                if(version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                                    $line['supplier_reference'] = ProductSupplier::getProductSupplierReference($p->id, 0, $p->id_supplier);
                                } else {
                                    $line['supplier_reference'] = $p->supplier_reference;
                                }

                                break;
                            case 'features':
                                $features = $p->getFrontFeatures($id_lang);
                                $position = 1;
                                $features_array = array();
                                foreach ($features as $feature) {
                                    $features_array[] = $feature['name'] . ':' . $feature['value'] . ':' . $position . ':1';
                                    $position++;
                                }
                                $line['features'] = implode(
                                    $feature_delimiter,
                                    $features_array
                                );

                                break;
                            case 'reduction_price':
                                $specificPrice = SpecificPrice::getSpecificPrice(
                                    $p->id,
                                    $id_shop,
                                    0,
                                    0,
                                    0,
                                    0
                                );

                                $line['reduction_price'] = '';
                                $line['reduction_percent'] = '';
                                $line['reduction_from'] = '';
                                $line['reduction_to'] = '';

                                if ($specificPrice['reduction_type'] == 'amount') {
                                    $line['reduction_price'] = $specificPrice['reduction'];
                                } elseif ($specificPrice['reduction_type'] == 'percentage') {
                                    $line['reduction_percent'] = $specificPrice['reduction'] * 100;
                                }

                                if ($line['reduction_price'] != '' || $line['reduction_percent'] != '') {
                                    if ($specificPrice['from'] != '0000-00-00 00:00:00') {
                                        $line['reduction_from'] = Tools::date_format(
                                            date_create($specificPrice['from']),
                                            'Y-m-d'
                                        );
                                    }

                                    if ($specificPrice['to'] != '0000-00-00 00:00:00') {
                                        $line['reduction_to'] = Tools::date_format(
                                            date_create($specificPrice['to']),
                                            'Y-m-d'
                                        );
                                    }
                                }

                                break;
                            case 'tags':
                                $tags = $p->getTags($id_lang);

                                $line['tags'] = $tags;

                                break;
                            case 'image':

                                $link = new Link();
                                $imagelinks = array();
                                $images = $p->getImages($id_lang);
                                foreach ($images as $image) {
                                    $imagelinks[] = Tools::getShopProtocol() . $link->getImageLink(
                                            $p->link_rewrite,
                                            $p->id_product . '-' . $image['id_image']
                                        );
                                }
                                $line['image'] = implode(
                                    ",",
                                    $imagelinks
                                );

                                break;
                            case 'delete_existing_images':
                                $line['delete_existing_images'] = 0;

                                break;
                            case 'shop':
                                $line['shop'] = $id_shop;

                                break;
                            case 'warehouse':
                                $warehouses = Warehouse::getWarehousesByProductId($p->id);
                                $line['warehouse'] = '';
                                if (!empty($warehouses)) {
                                    $line['warehouse'] = implode(
                                        ',',
                                        array_map(
                                            array($this, 'getWarehouses'),
                                            $warehouses
                                        )
                                    );
                                }

                                break;
                            case 'date_added':
                                $date = new DateTime($p->date_add);
                                $line['date_add'] = $date->format("Y-m-d");
                                break;

                            default:
                                $line[$field] = '';
                        }
                    }
                }

                $line = preg_replace("/\r|\n/", "", $line);

                fputcsv(
                    $f,
                    $line,
                    $delimiter,
                    '"'
                );
            }
            fclose($f);
            die();
        }
    }

    public function initContent()
    {
        $this->content = $this->renderView();
        parent::initContent();
    }

    public function getWarehouses($id_warehouses)
    {
        return $id_warehouses['id_warehouse'];
    }

    public function getCategories(
        $id_lang,
        $active,
        $id_shop
    ) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
			SELECT *
			FROM `' . _DB_PREFIX_ . 'category` c
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`
			WHERE ' . ($id_shop ? 'cl.`id_shop` = ' . (int)$id_shop : '') . ' ' . ($id_lang ? 'AND `id_lang` = ' . (int)$id_lang : '') . '
			' . ($active ? 'AND `active` = 1' : '') . '
			' . (!$id_lang ? 'GROUP BY c.id_category' : '') . '
			ORDER BY c.`level_depth` ASC, c.`position` ASC'
        );

        return $result;
    }
}

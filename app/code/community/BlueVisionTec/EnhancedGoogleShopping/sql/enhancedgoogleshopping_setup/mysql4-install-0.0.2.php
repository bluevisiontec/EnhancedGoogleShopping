<?php
/**
 * Magento Module BlueVisionTec_EnhancedGoogleShopping
 *
 * @category   BlueVisionTec
 * @package    BlueVisionTec_EnhancedPdfInvoice
 * @copyright   Copyright (c) 2014 BlueVisionTec UG (haftungsbeschränkt) (http://www.bluevisiontec.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();
// add product attribute google shopping image
$this->addAttribute('catalog_product','google_shopping_image',
    array (
        'group'             => 'Images',
        'type'              => 'varchar',
        'frontend'          => 'catalog/product_attribute_frontend_image',
        'label'             => 'Google Shopping Image',
        'input'             => 'media_image',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'default'           => '',
        'class'             => '',
        'source'            => ''
    )
);

$installer->endSetup();
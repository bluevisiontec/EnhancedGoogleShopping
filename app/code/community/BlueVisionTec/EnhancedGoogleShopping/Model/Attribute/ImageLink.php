<?php
/**
 * Magento Module BlueVisionTec_EnhancedGoogleShopping
 *
 * @category   BlueVisionTec
 * @package    BlueVisionTec_EnhancedPdfInvoice
 * @copyright   Copyright (c) 2014 BlueVisionTec UG (haftungsbeschränkt) (http://www.bluevisiontec.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Image link attribute model
 *
 * @category   BlueVisionTec
 * @package    BlueVisionTec_EnhancedPdfInvoice
 * @author     BlueVisionTec UG (haftungsbeschränkt) <magedev@bluevisiontec.eu>
 */
class BlueVisionTec_EnhancedGoogleShopping_Model_Attribute_ImageLink extends Mage_GoogleShopping_Model_Attribute_ImageLink
{
    /**
     * Set current attribute to entry (for specified product)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @return Varien_Gdata_Gshopping_Entry
     */
    public function convertAttribute($product, $entry)
    {
        $url = $product->getGoogleShoppingImage();
        if($url && $url != "no_selection") {
        
			$url = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getGoogleShoppingImage());
			$this->_setAttribute($entry, 'image_link', self::ATTRIBUTE_TYPE_URL, $url);
			return $entry;
        }
        
        $url = Mage::helper('catalog/product')->getImageUrl($product);

        if ($product->getImage() && $product->getImage() != 'no_selection' && $url) {
            $this->_setAttribute($entry, 'image_link', self::ATTRIBUTE_TYPE_URL, $url);
        }
        return $entry;
    }
}

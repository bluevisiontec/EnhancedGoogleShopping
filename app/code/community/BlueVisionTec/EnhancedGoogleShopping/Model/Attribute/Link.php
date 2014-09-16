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
 * Link attribute model
 *
 * @category   BlueVisionTec
 * @package    BlueVisionTec_EnhancedPdfInvoice
 * @author     BlueVisionTec UG (haftungsbeschränkt) <magedev@bluevisiontec.eu>
 */
class BlueVisionTec_EnhancedGoogleShopping_Model_Attribute_Link extends Mage_GoogleShopping_Model_Attribute_Link
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
		$newEntry = parent::convertAttribute($product, $entry);

		$links = $newEntry->getLink();
		
		$link = $links[0];
		$url = $link->getHref();

		// add Google Analytics source
		$url .= '&utm_source=GoogleShopping';
		$link->setHref($url);
		// update first link in array
		$links[0] = $link;
		$newEntry->setLink($links);
		
		return $newEntry;
    }
}
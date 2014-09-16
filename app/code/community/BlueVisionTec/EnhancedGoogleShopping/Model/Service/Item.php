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
 * Google Content Item Model
 *
 * @category   BlueVisionTec
 * @package    BlueVisionTec_EnhancedPdfInvoice
 * @author     BlueVisionTec UG (haftungsbeschränkt) <magedev@bluevisiontec.eu>
 */
class BlueVisionTec_EnhancedGoogleShopping_Model_Service_Item extends Mage_GoogleShopping_Model_Service_Item
{
  /**
   * Return item stats array based on Zend Gdata Entry object
   *
   * @param Varien_Gdata_Gshopping_Entry $entry
   * @return array
  */
  protected function _getEntryStats($entry)
  {
	  $result = parent::_getEntryStats($entry);
	  $date = new Zend_Date();
	  $date->add('30', Zend_Date::DAY);

	  $result['expires'] = $date->get(Zend_Date::TIMESTAMP);
	  
	  return $result;
  }
  
  /**
   * Update Item data in Google Content
   *
   * @param Mage_GoogleShopping_Model_Item $item
   * @return Mage_GoogleShopping_Model_Service_Item
  */
  public function update($item)
  {
	  $service = $this->getService();
	  $query = $this->_buildItemQuery($item);
	  $entry = $service->getItem($query);

	  $stats = $this->_getEntryStats($entry);
	  if (isset($stats['expires'])) {
		  $item->setExpires($stats['expires']);
	  }
	  $entry->removeContentAttribute("expiration_date");
	  $date = new Zend_Date($stats['expires'],Zend_Date::TIMESTAMP);
	  $entry->addContentAttribute("expiration_date",$date->toString(Zend_Date::ATOM));
	  $expDate = $entry->getContentAttributeByName("expiration_date");

	  $entry = $item->getType()->convertProductToEntry($item->getProduct(), $entry);
	  
	  $entry = $service->updateItem($entry);

	  return $this;
  }
}
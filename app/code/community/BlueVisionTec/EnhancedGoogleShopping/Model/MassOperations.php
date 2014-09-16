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
 * Controller for mass opertions with items
 *
 * @category   BlueVisionTec
 * @package    BlueVisionTec_EnhancedPdfInvoice
 * @author     BlueVisionTec UG (haftungsbeschränkt) <magedev@bluevisiontec.eu>
 */
class BlueVisionTec_EnhancedGoogleShopping_Model_MassOperations extends Mage_GoogleShopping_Model_MassOperations
{
  /**
	* Update Google Content items.
	*
	* @param array|Mage_GoogleShopping_Model_Resource_Item_Collection $items
	* @throws Zend_Gdata_App_CaptchaRequiredException
	* @throws Mage_Core_Exception
	* @return Mage_GoogleShopping_Model_MassOperations
	*/
  public function synchronizeItems($items)
  {
	$totalUpdated = 0;
	$totalDeleted = 0;
	$totalFailed = 0;
	$errors = array();

	$itemsCollection = $this->_getItemsCollection($items);
	
	$renewNotListed = Mage::getStoreConfig('bvt_enhancedgoogleshopping_config/settings/autorenew_notlisted');
	$removeInactive = Mage::getStoreConfig('bvt_enhancedgoogleshopping_config/settings/autoremove_disabled');

	if ($itemsCollection) {
	  if (count($itemsCollection) < 1) {
		return $this;
	  }
	  foreach ($itemsCollection as $item) {
		if ($this->_flag && $this->_flag->isExpired()) {
		  break;
		}
		try {
		  if($removeInactive && ($item->getProduct()->isDisabled() || !$item->getProduct()->getStockItem()->getIsInStock() )) {
			$item->deleteItem();
			$item->delete();
			$totalDeleted++;
			Mage::log("remove inactive: ".$item->getProduct()->getSku()." - ".$item->getProduct()->getName());
		  } else {
			$item->updateItem();
			$item->save();
			// The item was updated successfully
			$totalUpdated++;
		  }
		} catch (Varien_Gdata_Gshopping_HttpException $e) {
		  if (in_array('notfound', $e->getCodes())) {
			if($renewNotListed && !$item->getProduct()->isDisabled()) {
			  $productId = $item->getProductId();
			  $storeId = $item->getProduct()->getStoreId();
			  $item->delete(); 
			  $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
			  Mage::getModel('googleshopping/item')->insertItem($product)->save();
			  $totalUpdated++;
			  Mage::log("Readded Item to Google Shopping: ".$product->getName());
			} else {
			  $item->delete();
			  $totalDeleted++;
			}
		  } else {
			$this->_addGeneralError();
			$errors[] = Mage::helper('googleshopping')
				->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
			$totalFailed++;
		  }
		} catch (Zend_Gdata_App_CaptchaRequiredException $e) {
			throw $e;
		} catch (Zend_Gdata_App_Exception $e) {
		  $this->_addGeneralError();
		  $errors[] = Mage::helper('googleshopping')
			  ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
		  $totalFailed++;
		} catch (Mage_Core_Exception $e) {
		  $errors[] = Mage::helper('googleshopping')->__('The item "%s" cannot be updated at Google Content. %s', $item->getProduct()->getName(), $e->getMessage());
		  $totalFailed++;
		} catch (Exception $e) {
		  Mage::logException($e);
		  $errors[] = Mage::helper('googleshopping')->__('The item "%s" hasn\'t been updated.', $item->getProduct()->getName());
		  $totalFailed++;
		}
	  }
	} else {
	  return $this;
	}
		
	$this->_getNotifier()->addNotice(
		Mage::helper('googleshopping')->__('Product synchronization with Google Shopping completed'),
		Mage::helper('googleshopping')->__('Total of %d items(s) have been deleted; total of %d items(s) have been updated.', $totalDeleted, $totalUpdated)
	);
	if ($totalFailed > 0 || count($errors)) {
		array_unshift($errors, Mage::helper('googleshopping')->__("Cannot update %s items.", $totalFailed));
		/*$this->_getNotifier()->addMajor(
			Mage::helper('googleshopping')->__('Errors happened during synchronization with Google Shopping'),
			$errors
		);*/
	}

	return $this;
  }
  
  /**
     * Add product to Google Content.
     *
     * @param array $productIds
     * @param int $storeId
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @throws Mage_Core_Exception
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function addProducts($productIds, $storeId)
    {
        $totalAdded = 0;
        $errors = array();
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $product = Mage::getModel('catalog/product')
                        ->setStoreId($storeId)
                        ->load($productId);
                        
                    if($product->isDisabled() && $product->getStockItem()->getIsInStock()) {
					  Mage::log("skip inactive: ".$product->getSku()." ".$product->getName());
					  continue;
                    }

                    if ($product->getId()) {
                        Mage::getModel('googleshopping/item')
                            ->insertItem($product)
                            ->save();
                        // The product was added successfully
                        $totalAdded++;
                    }
                } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $errors[] = Mage::helper('googleshopping')->parseGdataExceptionMessage($e->getMessage(), $product);
                } catch (Zend_Db_Statement_Exception $e) {
                    $message = $e->getMessage();
                    if ($e->getCode() == self::ERROR_CODE_SQL_UNIQUE_INDEX) {
                        $message = Mage::helper('googleshopping')->__("The Google Content item for product '%s' (in '%s' store) has already exist.", $product->getName(), Mage::app()->getStore($product->getStoreId())->getName());
                    }
                    $errors[] = $message;
                } catch (Mage_Core_Exception $e) {
                    $errors[] = Mage::helper('googleshopping')->__('The product "%s" cannot be added to Google Content. %s', $product->getName(), $e->getMessage());
                } catch (Exception $e) {
                    Mage::logException($e);
                    $errors[] = Mage::helper('googleshopping')->__('The product "%s" hasn\'t been added to Google Content.', $product->getName());
                }
            }
            if (empty($productIds)) {
                return $this;
            }
        }

        if ($totalAdded > 0) {
            $this->_getNotifier()->addNotice(
                Mage::helper('googleshopping')->__('Products were added to Google Shopping account.'),
                Mage::helper('googleshopping')->__('Total of %d product(s) have been added to Google Content.', $totalAdded)
            );
        }

        if (count($errors)) {
        Mage::log($errors);
            $this->_getNotifier()->addMajor(
                Mage::helper('googleshopping')->__('Errors happened while adding products to Google Shopping.'),
                $errors
            );
        }

        if ($this->_flag->isExpired()) {
            $this->_getNotifier()->addMajor(
                Mage::helper('googleshopping')->__('Operation of adding products to Google Shopping expired.'),
                Mage::helper('googleshopping')->__('Some products may have not been added to Google Shopping bacause of expiration')
            );
        }

        return $this;
    }
}
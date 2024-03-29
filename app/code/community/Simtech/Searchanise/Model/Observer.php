<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

class Simtech_Searchanise_Model_Observer
{
    protected static $newAttributes = array();
    protected static $productIdsInCategory = array();
    
    public function __construct() 
    {
        // nothing for now
    }

    /**
     * Function for cron
     *
     */
    public function autoSync()
    {
        Mage::helper('searchanise/ApiSe')->log('start cron autoSync', 'information');
        
        // only run if set to
        $cronAsyncEnabled = Mage::helper('searchanise/ApiSe')->checkCronAsync();
        if ($cronAsyncEnabled) {
            Mage::helper('searchanise/ApiSe')->log('cron is enabled', 'information');
            $result = Mage::helper('searchanise/ApiSe')->async();
            Mage::helper('searchanise/ApiSe')->log($result, 'information');
                        
        } else {
            Mage::helper('searchanise/ApiSe')->log('cron is not enabled', 'information');
        }
        
        Mage::helper('searchanise/ApiSe')->log('end cron autoSync', 'information');
        
        return $this;
    }

    // FOR SYSTEM //
    /**
     * After image cache was cleaned
     *
     */
    public function cleanCatalogImagesCacheAfter()
    {
        Mage::helper('searchanise/ApiSe')->queueImport();

        return $this;
    }
    // END FOR SYSTEM //
    
    // FOR PRODUCTS //
    /**
     * Before save product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        Mage::getModel('searchanise/queue')->addActionDeleteProductFromOldStore($observer->getEvent()->getProduct());
        
        return $this;
    }
    
    /**
     * After save product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        // fixme in the future
        // Add a check-up for changes of the parameters which are related to other languages and storefronts.
        //~ Mage::getModel('searchanise/queue')->addActionUpdateProduct($observer->getEvent()->getProduct(), $observer->getEvent()->getProduct()->getStoreId());
        Mage::getModel('searchanise/queue')->addActionUpdateProduct($observer->getEvent()->getProduct());
        
        return $this;
    }
    
    /**
     * Before delete product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductDeleteBefore(Varien_Event_Observer $observer)
    {
        Mage::getModel('searchanise/queue')->addActionDeleteProduct($observer->getEvent()->getProduct());
        
        return $this;
    }
    
    /**
     * Product attribute update
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getData('product_ids');
        
        if (!empty($productIds)) {
            foreach ($productIds as $k => $productId) {
                $product = Mage::getModel('catalog/product')
                    ->load($productId);
                
                if (!empty($product)) {
                    $storeIds = $product->getStoreIds();
                    
                    if (!empty($storeIds)) {
                        foreach ($storeIds as $k => $storeId) {
                            Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE, $product->getId(), null, $storeId);
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Product website update
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogProductWebsiteUpdateBefore(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getData('product_ids');
        $websiteIds = $observer->getEvent()->getData('website_ids');
        $action = $observer->getEvent()->getData('action');
        $storeIds = Mage::helper('searchanise/ApiSe')->getStoreByWebsiteIds($websiteIds);
        
        if ((!empty($storeIds)) && (!empty($productIds))) {
            foreach ($productIds as $k => $productId) {
                if ($action == 'add') {
                    foreach ($storeIds as $k => $storeId) {
                        Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_UPDATE, $productId, null, $storeId);
                    }
                    
                } elseif ($action == 'remove') {
                    $productOld = Mage::getModel('catalog/product')
                        ->load($productId);
                    
                    if (!empty($productOld)) {
                        $storeIdsOld = $productOld->getStoreIds();
                        
                        if (!empty($storeIdsOld)) {
                            foreach ($storeIds as $k => $storeId) {
                                if (in_array($storeId, $storeIdsOld)) {
                                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_DELETE, $productId, null, $storeId);
                                }
                            }
                        }
                    }
                }
            } 
        }
        
        return $this;
    }
    
    // FOR CATEGORIES //
    /**
     * Save category before
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategorySaveBefore(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        
        if ($category && $category->getId()) {
            $products = $category->getProductCollection();
            Mage::getModel('searchanise/queue')->addActionProducts($products);

            // save current products ids
            // need for find new products in catalogCategorySaveAfter
            if (!empty($products)) {
                self::$productIdsInCategory = array();
                
                foreach ($products as $product) {
                    if ($product->getId()) {
                        self::$productIdsInCategory[] = $product->getId();
                    }
                }
            }
        }
        
        return $this;
    }

    /**
     * Save category after
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategorySaveAfter(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        
        if ($category && $category->getId()) {
            $products = $category->getProductCollection();

            if (!empty($products)) {
                if (empty(self::$productIdsInCategory)) {
                    Mage::getModel('searchanise/queue')->addActionProducts($products);
                } else {
                    $productIds = array();
                    foreach ($products as $product) {
                        $id = $product->getId();
                        if ((!empty($id)) && (!in_array($id, self::$productIdsInCategory))) {
                            $productIds[] = $id;
                        }
                    }

                    Mage::getModel('searchanise/queue')->addActionProductIds($productIds);
                }
            }
        }
        
        return $this;
    }

    /**
     * Move category after
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogCategoryTreeMoveAfter(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        
        if ($category && $category->getId()) {
            $products = $category->getProductCollection();

            if (!empty($products)) {
                if (empty(self::$productIdsInCategory)) {
                    Mage::getModel('searchanise/queue')->addActionProducts($products);
                } else {
                    $productIds = array();
                    foreach ($products as $product) {
                        $id = $product->getId();
                        if ((!empty($id)) && (!in_array($id, self::$productIdsInCategory))) {
                            $productIds[] = $id;
                        }
                    }

                    Mage::getModel('searchanise/queue')->addActionProductIds($productIds);
                }
            }
        }
        
        return $this;
    }

    // FOR SALES //
    /**
     * 
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function salesOrderSaveAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        
        if ($order && $order->getId()) {
            Mage::getModel('searchanise/queue')->addActionOrderItems($order->getItemsCollection());
        }
        
        return $this;
    }
    
    // FOR IMPORTEXPORT //
    /**
     * 
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseImportSaveProductEntityAfter(Varien_Event_Observer $observer)
    {
        $_newSku = $observer->getData('_newSku');
        
        if (!empty($_newSku)) {
            $productIds = array();
            
            foreach ($_newSku as $entity) {
                if ($entity['entity_id']) {
                    $productIds[] = $entity['entity_id'];
                }
            }
            
            if (!empty($productIds)) {
                Mage::getModel('searchanise/queue')->addActionProductIds($productIds , Simtech_Searchanise_Model_Queue::ACT_UPDATE);
            }
        }
        
        return $this;
    }
    
    /**
     * 
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseImportDeleteProductEntityAfter(Varien_Event_Observer $observer)
    {
        $idToDelete = $observer->getData('idToDelete');
        
        if (!empty($idToDelete)) {
            Mage::getModel('searchanise/queue')->addActionProductIds($idToDelete, Simtech_Searchanise_Model_Queue::ACT_DELETE);
        }
        
        return $this;
    }
    
    // FOR CORE //
    /**
     * Before save store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseCoreSaveStoreBefore(Varien_Event_Observer $observer)
    {
        $store = $observer->getData('store');
        
        if ($store && $store->getId()) {
            $isActive = $store->getIsActive();
            $isActiveOld = null;
            $storeOld = Mage::app()->getStore($store->getId());
            
            if ($storeOld) {
                $isActiveOld = $storeOld->getIsActive();
            }
                        
            if ($isActiveOld != $isActive) {
                if (Mage::helper('searchanise/ApiSe')->signup($store, false, false) == true) {
                    if ($isActive) {
                        Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('enabled', $store);
                        Mage::helper('searchanise/ApiSe')->queueImport($store, false);
                        Mage::helper('searchanise/ApiSe')->setNotification(
                            'N',
                            Mage::helper('searchanise')->__('Notice'),
                            str_replace('[language]', $store->getName(), Mage::helper('searchanise')->__('Searchanise: New search engine for [language] created. Catalog import started'))
                        );
                    } else {
                        Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('disabled', $store);
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Save store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseCoreSaveStoreAfter(Varien_Event_Observer $observer)
    {
        $store = $observer->getData('store');
        
        if ($store && $store->getId()) {
            $checkPrivateKey = Mage::helper('searchanise/ApiSe')->checkPrivateKey($store);
            
            if (Mage::helper('searchanise/ApiSe')->signup($store, false, false) == true) {
                if (!$checkPrivateKey) {
                    if ($store->getIsActive()) {
                        Mage::helper('searchanise/ApiSe')->queueImport($store, false);
                        Mage::helper('searchanise/ApiSe')->setNotification(
                            'N',
                            Mage::helper('searchanise')->__('notice'),
                            str_replace('[language]', $store->getName(), Mage::helper('searchanise')->__('Searchanise: New search engine for [language] created. Catalog import started'))
                        );
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Delete store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseCoreDeleteStoreAfter(Varien_Event_Observer $observer)
    {
        $store = $observer->getData('store');
        
        if ($store && $store->getId()) {
            Mage::helper('searchanise/ApiSe')->deleteKeys($store);
        }
        
        return $this;
    }
    
    // FOR ADMINHTML //
    /**
     * Before save adminhtml config data
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseAdminhtmlConfigDataSaveBefore(Varien_Event_Observer $observer)
    {
        $model = $observer->getData('object');
        $groups  = $model->getGroups();
        $section = $model->getSection();
        $storesIds = $model->getStore();
        $website = $model->getWebsite();
        
        if (empty($storesIds)) {
            if (!empty($website)) {
                $storesIds = Mage::helper('searchanise/ApiSe')->getStoreByWebsiteCodes($website);
            }
        }
        
        $stores = Mage::helper('searchanise/ApiSe')->getStores(null, $storesIds);
        
        if (!empty($stores)) {
            if ($section == 'catalog') {
                
            // Change status module
            } elseif ($section == 'advanced') {
                foreach ($groups as $group => $groupData) {
                    if (isset($groupData['fields']['Simtech_Searchanise']['value'])) {
                        $status = ($groupData['fields']['Simtech_Searchanise']['value']) ? 'D' : 'Y';
                        
                        foreach ($stores as $k => $store) {
                            if ($store->getIsActive()) {
                                $statusOld = Mage::helper('searchanise/ApiSe')->getStatusModule($store);
                                
                                if ($statusOld != $status) {
                                    if (Mage::helper('searchanise/ApiSe')->signup($store, false, false) == true) {
                                        if ($status == 'Y') {
                                            Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('enabled', $store);
                                            Mage::helper('searchanise/ApiSe')->queueImport($store, false);
                                            Mage::helper('searchanise/ApiSe')->setNotification(
                                                'N',
                                                Mage::helper('searchanise')->__('Notice'),
                                                str_replace('[language]', $store->getName(), Mage::helper('searchanise')->__('Searchanise: New search engine for [language] created. Catalog import started'))
                                            );
                                        } else {
                                            Mage::helper('searchanise/ApiSe')->sendAddonStatusRequest('disabled', $store);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $this;
    }
    /**
     * After save adminhtml config data
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseAdminhtmlConfigDataSaveAfter(Varien_Event_Observer $observer)
    {
        $model = $observer->getData('object');
        $section = $model->getSection();
        $storesIds = $model->getStore();
        $website = $model->getWebsite();
        
        if (empty($storesIds)) {
            if (!empty($website)) {
                $storesIds = Mage::helper('searchanise/ApiSe')->getStoreByWebsiteCodes($website);
            }
        }
        
        $stores = Mage::helper('searchanise/ApiSe')->getStores(null, $storesIds);
        
        if (!empty($stores)) {
            if ($section == 'catalog') {
                foreach ($stores as $k => $store) {
                    $filters = Mage::helper('searchanise/ApiSe')->getPriceFilters($store);
                    
                    if (!empty($filters)) {
                        foreach ($filters as $filter) {
                            Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE, $filter->getId(), $store);
                        }
                    }

                    // change facet-prices
                    {
                        $queueData = array(
                            'data'     => serialize(Simtech_Searchanise_Model_Queue::DATA_FACET_PRICES),
                            'action'   => Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE,
                            'store_id' => $store->getId(),
                        );
                        
                        Mage::getModel('searchanise/queue')->setData($queueData)->save();
                    }
                }
                
            // Change status module
            } elseif ($section == 'advanced') {
                
            }
        }
        
        return $this;
    }
    
    // FOR EAV //
    /**
     * Before save attribute
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogEntityAttributeSaveBefore(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        
        if ($attribute && $attribute->getId()) {
            $flFacet = Mage::helper('searchanise/ApiXML')->checkFacet($attribute);
            
            $flFacetOld = null;
            
            $attributeOld = Mage::getModel('catalog/entity_attribute')
                ->load($attribute->getId());
            
            if (!empty($attributeOld)) {
                $flFacetOld = Mage::helper('searchanise/ApiXML')->checkFacet($attributeOld);
            }
            
            if ($flFacet != $flFacetOld) {
                if ($flFacet) {
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE, $attribute->getId());
                } else {
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_FACET_DELETE, $attribute->getId());
                }
            }
        }

        if (($attribute) && (!$attribute->getId())) {
            self::$newAttributes[$attribute->getData('attribute_code')] = true;
        } else {
            // uncomment if need this checking
            // self::$newAttributes[$attribute->getData('attribute_code')] = false;
        }

        return $this;
    }
    
    /**
     * Save attribute
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogEntityAttributeSaveAfter(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        
        if ($attribute && $attribute->getId()) {
            $attributeCode = $attribute->getData('attribute_code');
            
            if ((!empty(self::$newAttributes)) && 
                (array_key_exists($attributeCode, self::$newAttributes)) && 
                (self::$newAttributes[$attributeCode])) {
                $flFacet = Mage::helper('searchanise/ApiXML')->checkFacet($attribute);

                
                if ($flFacet) {
                    Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_FACET_UPDATE, $attribute->getId());
                }
            }
        }

        return $this;
    }
    
    /**
     * Delete attribute
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function catalogEntityAttributeDeleteAfter(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        
        if ($attribute && $attribute->getId()) {
            $flFacet = Mage::helper('searchanise/ApiXML')->checkFacet($attribute);
            
            if ($flFacet) {
                Mage::getModel('searchanise/queue')->addAction(Simtech_Searchanise_Model_Queue::ACT_FACET_DELETE, $attribute->getId());
            }
        }
        
        return $this;
    }
    
    // FOR TAG //
    /**
     * After save tag
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function tagSaveAfter(Varien_Event_Observer $observer)
    {
        $tag = $observer->getEvent()->getData('object');
        
        if (!empty($tag)) {
            $productIds = $tag->getRelatedProductIds();
            
            Mage::getModel('searchanise/queue')->addActionProductIds($productIds, Simtech_Searchanise_Model_Queue::ACT_UPDATE);
        }
        
        return $this;
    }
    
    /**
     * Before delete tag
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function tagDeleteBefore(Varien_Event_Observer $observer)
    {
        $tag = $observer->getEvent()->getData('object');
        
        if (!empty($tag)) {
            $productIds = $tag->getRelatedProductIds();
            
            Mage::getModel('searchanise/queue')->addActionProductIds($productIds, Simtech_Searchanise_Model_Queue::ACT_UPDATE);
        }
        
        return $this;
    }
    
    /**
     * Add tag to product
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogIndex_Model_Observer
     */
    public function searchaniseTagRelationSaveAfter(Varien_Event_Observer $observer)
    {
        // fixme in the future
        // need add check approved tag
        $tagRelation = $observer->getEvent()->getData('object');
        
        if (!empty($tagRelation)) {
            $tag = Mage::getModel('tag/tag')
                ->setData('tag_id', $tagRelation->getTagId())
                ->load();
            
            if (!empty($tag)) {
                $productIds = $tag->getRelatedProductIds();
                
                Mage::getModel('searchanise/queue')->addActionProductIds($productIds, Simtech_Searchanise_Model_Queue::ACT_UPDATE);
            }
        }
        
        return $this;
    }
}
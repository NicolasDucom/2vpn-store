<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_AdvancedCompare
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

require_once 'Mage/Catalog/controllers/Product/CompareController.php';

class ET_AdvancedCompare_IndexController extends Mage_Catalog_Product_CompareController
{
    protected $_limitMessage=null;

    public function redrawsidebarAction()
    {
        $layout = Mage::getSingleton('core/layout');

        $layout->getUpdate();
        $renderer = $layout->createBlock('catalog/product_compare_sidebar');
        $renderer->setTemplate("catalog/product/compare/sidebar.phtml");

        Mage::getSingleton('catalog/session')->getMessages(true);

        $rendererhtml = $renderer->toHtml();
        if ($this->_limitMessage) {
            $rendererhtml .= "<script>alert('".str_replace("'", "\\'", $this->_limitMessage)."');</script>";
        }

        $this->getResponse()->setBody(Zend_Json::encode($rendererhtml));
    }

    public function silentaddAction()
    {
        return $this->addAction();
    }

    public function addAction()
    {
        if (Mage::getStoreConfig('advancedcompare/limits/enable_compare_limits')) {
            $itemCollection = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $itemCollection->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            } elseif ($this->_customerId) {
                $itemCollection->setCustomerId($this->_customerId);
            } else {
                $itemCollection->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }

            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSiteFilterToCollection($itemCollection);
            if (count($itemCollection) >= Mage::getStoreConfig('advancedcompare/limits/compare_limits')) {
                $this->_limitMessage = Mage::helper("advancedcompare")->__(
                    "You have reached a maximal amount (%s) of products to compare simultaneously",
                    Mage::getStoreConfig('advancedcompare/limits/compare_limits')
                );
                return $this->_redirectReferer();
            }
        }
        return parent::addAction();
    }


    public function silentremoveAction()
    {
        return $this->removeAction();
    }

    public function silentclearAction()
    {
        return $this->clearAction();
    }

    protected function _redirectReferer($defaultUrl=null)
    {
        if (
            ($this->getRequest()->getModuleName() == "advancedcompare")
            &(in_array($this->getRequest()->getActionName(), array("silentadd", "silentremove", "silentclear")))
        ) {
            $this->redrawsidebarAction();
        } else {
            parent::_redirectReferer($defaultUrl);
        }
    }
}

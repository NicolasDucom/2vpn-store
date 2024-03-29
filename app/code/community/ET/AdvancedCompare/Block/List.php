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

class ET_AdvancedCompare_Block_List extends Mage_Catalog_Block_Product_Compare_List
{
    public function setTemplate($template)
    {
        $replacetemplate = Mage::getStoreConfig('advancedcompare/popup/replacetemplate');

        if ($replacetemplate) {
            $version = substr(Mage::getVersion(), 0, 3);

            switch ($version) {
                case '1.3':
                    $template = 'et_advancedcompare/list_13x.phtml';
                    break;

                default:
                    $template = 'et_advancedcompare/list_15x.phtml';

            }
        }

        return parent::setTemplate($template);
    }
}

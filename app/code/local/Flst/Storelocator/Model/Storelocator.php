<?php
class Flst_Storelocator_Model_Storelocator extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('storelocator/storelocator');
    }
}
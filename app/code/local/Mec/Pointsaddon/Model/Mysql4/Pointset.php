<?php
class Mec_Pointsaddon_Model_Mysql4_Pointset extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("pointsaddon/pointset", "id");
    }
}
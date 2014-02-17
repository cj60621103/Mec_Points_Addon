<?php
class Mec_Pointsaddon_Block_Sales_Totals_Points extends Mage_Core_Block_Template {
	
	public function getOrder() {
        return $this->getParentBlock()->getOrder();
    }

    

	 public function initTotals() {
        if ($this->getOrder()->getMecpointTotal()) {
           

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                        'code' => 'mecpoint_total',
                        'strong' => false,
                        'label' => Mage::helper('pointsaddon') -> __('Points'),
                        'value' => $this->getOrder()->getMecpointTotal()
                    )), 'subtotal');
        }

        return $this;
    }
	


}
<?php
class Mec_Pointsaddon_Model_Quote_Address_Total_Mecpoint 
extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
     public function __construct()
    {
         $this->setCode('mecpoint_total');
         }
    /**
     * Collect totals information about mecpoint
     * 
     * @param Mage_Sales_Model_Quote_Address $address 
     * @return Mage_Sales_Model_Quote_Address_Total_Shipping 
     */
     public function collect(Mage_Sales_Model_Quote_Address $address)
    {
         parent::collect($address);
         $items = $this->_getAddressItems($address);
         if (!count($items)) {
            return $this;
         }
         $quote = $address->getQuote();

		 //amount definition
		 
         $mecpointAmount = 0;
		 foreach($quote->getAllItems() as $item){
			$mecpointAmount += $item->getPoints();
		 }
		
		
		// Mage::log($mecpointAmount);
		// Mage::log($address->getAddressType());
         //amount definition
		
         // $mecpointAmount = $quote->getStore()->roundPrice($mecpointAmount);
         // $this ->_setAmount($mecpointAmount)->_setBaseAmount($mecpointAmount);
         // $address->setData('mecpoint_total',$mecpointAmount);
		 $address->setMecpointTotal($mecpointAmount);
		

         return $this;
     }
    
    /**
     * Add mecpoint totals information to address object
     * 
     * @param Mage_Sales_Model_Quote_Address $address 
     * @return Mage_Sales_Model_Quote_Address_Total_Shipping 
     */
     public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
         parent :: fetch($address);
		 // Mage::log('fetch');
         $amount = $address->getMecpointTotal();
		 // Mage::log($amount);
         if ($amount != 0){
             $address->addTotal(array(
                     'code' => $this->getCode(),
                     'title' => $this->getLabel(),
                     'value' => $amount
                    ));
         }
        
         return $this;
     }
    
    /**
     * Get label
     * 
     * @return string 
     */
     public function getLabel()
    {
         return Mage::helper('pointsaddon') -> __('Points');
    }
}
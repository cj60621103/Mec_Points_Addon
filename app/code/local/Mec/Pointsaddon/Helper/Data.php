<?php
class Mec_Pointsaddon_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function canSave($values, $product_id)
	{
		$can_save = false;
		$size = Mage::getModel('pointsaddon/pointset')->getCollection()
					  ->addFieldToFilter('product_id', array('eq' => $product_id))
					  ->addFieldToFilter('customer_group_id', array('eq' => $values['cust_group']))
					  ->addFieldToFilter('base_price', array('eq' => $values['price']))
					  ->addFieldToFilter('points', array('eq' => $values['points_count']))
					  ->getSize();
					  
		if($size == 0){
			$can_save = true;
		}
		
		return $can_save;
	}
	
	
	public function canConvertPoints($product_id)
	{
		$can_convert = false;
		$size = Mage::getModel('pointsaddon/pointset')->getCollection()
				->addFieldToFilter('product_id', array('eq' => $product_id))
				->getSize();
				
		if($size > 0){
			$can_convert = true;
		}
		
		return $can_convert;
	}
	
	
	
	public function convertToStoreCurreny($base_price)
	{
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		return Mage::helper('directory')->currencyConvert($base_price, $baseCurrencyCode, $currentCurrencyCode);
	
	}
	
	
}
	 
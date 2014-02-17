<?php
class Mec_Pointsaddon_Helper_Addon extends Mage_Core_Helper_Abstract
{
	public function getPointsCollection($product_id, $customer_group_id)
	{
		$collection =  Mage::getModel('pointsaddon/pointset')->getCollection()
					   ->addFieldToFilter('customer_group_id', array('eq' => $customer_group_id))
					   ->addFieldToFilter('product_id', array('eq' => $product_id));
					   
		return $collection;
		
	}
	
	
	public function isPointsCategory($ids)
	{
		$flag = false;
		foreach($ids as $id){
			$category = Mage::getModel('catalog/category')->load($id);
			if((boolean)$category->getPointsCategory()){
				if(Mage::registry('current_category')->getId() == $category->getId()){
					$flag = true;
					break;
				}
			}
		}
		
		return $flag;
	}
	
	public function checkCanAddCart($qty, $product_id, $points_set_id)
	{
		$respone = array();
		$points_for_customer = Mage::getModel('points/summary')->loadByCustomerID(Mage::getSingleton('customer/session')->getCustomer()->getId())->getPoints();
		
		$quote_points = $this->calculateQuotePoints();
		
		$current_product_point = Mage::getModel('pointsaddon/pointset')->load($points_set_id)->getPoints() * $qty;
	
		if($quote_points + $current_product_point > $points_for_customer){
			$respone['error'] = Mage::helper('catalog')->__("Can't Add This Product, Points Not Enough");
			$respone['succeed'] = false;
		}else{
			$respone['succeed'] = true;
		}
		
		return $respone;
		
	}
	
	
	public function checkCanUpdateCart($qty, $product_id, $points_set_id, $quote_item)
	{
		$respone = array();
		$points_for_customer = Mage::getModel('points/summary')->loadByCustomerID(Mage::getSingleton('customer/session')->getCustomer()->getId())->getPoints();
		
		$quote_points = $this->calculateQuotePoints();
		$excule_points = $quote_item->getPoints();
		
		$current_product_point = Mage::getModel('pointsaddon/pointset')->load($points_set_id)->getPoints() * $qty;
		
		if($quote_points - $excule_points  + $current_product_point > $points_for_customer){
			$respone['error'] = Mage::helper('catalog')->__("Can't Update This Product, Points Not Enough");
			$respone['succeed'] = false;
		}else{
			$respone['succeed'] = true;
		}
		
		return $respone;
	}
	
	
	
	protected function calculateQuotePoints()
	{
		$points = 0;
		foreach(Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item){
			$points += $item->getPoints();
		}
		return $points;
	}
	
	
	public function getProductMaximum($product_id)
	{
		$maximum = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, 'mec_maximum', Mage::app()->getStore()->getId());
		$values_array = array();
		if($maximum != ""){
			$maximum = (int)$maximum;
			for($i = 1; $i<= $maximum; $i++){
				$values_array[] = $i;
			}
			return $values_array;
		}else{
			return null;
		}
	}
	
	
	

}
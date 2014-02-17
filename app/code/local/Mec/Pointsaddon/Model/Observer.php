<?php
class Mec_Pointsaddon_Model_Observer
{
	
	 static protected $_singletonFlag = false;
	
	public function orderPlaceAfter(Varien_Event_Observer $observer)
	{
		// Mage::log('addon orderPlaceAfter');
		$order = $observer->getEvent()->getOrder();
		$deduct_points = 0; 
		foreach($order->getAllItems() as $item){
			$deduct_points += $item->getPoints();
		}
		
		// Mage::log($deduct_points);
		if($deduct_points > 0){
			$deduct_points = -$deduct_points;
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            Mage::getModel('points/api')->addTransaction(
                    $deduct_points, 'spend_on_order', $customer, $order, array('order_increment_id' => $order->getIncrementId())
            );
		}
	}
	
	
	
	
	public function salesQuoteItemSetCustomAttribute(Varien_Event_Observer $observer)
	{	
		$request = $observer->getRequest()->getParams();
		$product = $observer->getProduct();
		// Mage::log('salesQuoteItemSetCustomAttribute');
		// Mage::log($product->getId());
		// Mage::log($request);
		$qty = $request['qty'];
		if(isset($request['points_set_id']) && $request['points_set_id'] > 0){
			$point_model = Mage::getModel('pointsaddon/pointset')->load($request['points_set_id']);
			// $product_points = $point_model->getPoints() * $qty;
			$product_points_price = $point_model->getBasePrice();
			
			switch($product->getTypeId()){
				case 'simple':
					foreach(Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item){
						if($item->getProductId() == $product->getId()){
							$item->setPointsSetId($request['points_set_id']);
							$item->setPoints($point_model->getPoints() * $item->getQty());
							$item->setCustomPrice($product_points_price);
							$item->setOriginalCustomPrice($product_points_price);
							$item->save();
						}
					}	
			}
		
		}
	}	
		
		
		
	public function updateSalesQuoteItemSetCustomAttribute(Varien_Event_Observer $observer)
	{
		$quote_item = $observer->getItem();
		$request = $observer->getRequest()->getParams();
		$qty = $request['qty'];
		
		if(isset($request['points_set_id']) && $request['points_set_id'] > 0){
			$product = Mage::getModel('catalog/product')->load($quote_item->getProductId());
			$point_model = Mage::getModel('pointsaddon/pointset')->load($request['points_set_id']);
			// $product_points = $point_model->getPoints() * $qty;
			$product_points_price = $point_model->getBasePrice();
			
			switch($product->getTypeId()){
				case 'simple':													
					$quote_item->setPointsSetId($request['points_set_id']);
					$quote_item->setPoints($point_model->getPoints() * $quote_item->getQty());
					$quote_item->setCustomPrice($product_points_price);
					$quote_item->setOriginalCustomPrice($product_points_price);
					$quote_item->save();
					break;	
					
			}
		
		}
		
	
	}
		
		
	
	
	
	
	public function saveProductTabData(Varien_Event_Observer $observer)
	{
		if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;
             
            $product = $observer->getEvent()->getProduct();
         
            try {
                /**
                 * Perform any actions you want here
                 *
                 */
                $pointsFieldValue =  $this->_getRequest()->getPost('points');
				if(count($pointsFieldValue) > 0){
					foreach($pointsFieldValue as $values){
						$save_flag = Mage::helper('pointsaddon')->canSave($values, $product->getId());
						if($save_flag && $values['setid'] == ''){
							$data_model = Mage::getModel('pointsaddon/pointset')->addData(
								array(
									'product_id' => $product->getId(),
									'base_price' => $values['price'],
									'points' => $values['points_count'],
									'customer_group_id' => $values['cust_group']
								)
							)->save();
							$data_obj = Mage::getModel('pointsaddon/pointset')->load($data_model->getId());
							$data_obj->setSetIncrementId($data_obj->getId())->save();
						}else{
							Mage::getModel('pointsaddon/pointset')->addData(
								array(
									'product_id' => $product->getId(),
									'base_price' => $values['price'],
									'points' => $values['points_count'],
									'customer_group_id' => $values['cust_group']
								)
							)->setId($values['setid'])->save();
						}
						
						if($values['delete'] == 1){
							Mage::getModel('pointsaddon/pointset')->setId($values['setid'])->delete();
						}	
					}
				}
                /**
                 * Uncomment the line below to save the product
                 *
                 */
                //$product->save();
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
	}
		
		
		
		
	public function getProduct()
    {
        return Mage::registry('product');
    }
     
    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
	
	
	
	
	public function saveMecpointTotal(Varien_Event_Observer $observer)
    {
         $order = $observer->getEvent()->getOrder();
         $quote = $observer->getEvent()->getQuote();
         $shippingAddress = $quote->getShippingAddress();
         if($shippingAddress && $shippingAddress->getData('mecpoint_total')){
             $order->setData('mecpoint_total', $shippingAddress->getData('mecpoint_total'));
             }
        else{
             $billingAddress = $quote->getBillingAddress();
             $order->setData('mecpoint_total', $billingAddress->getData('mecpoint_total'));
             }
         $order->save();
     }
    
     public function saveMecpointTotalForMultishipping(Varien_Event_Observer $observer)
    {
         $order = $observer->getEvent()->getOrder();
         $address = $observer->getEvent()->getAddress();
         $order->setData('mecpoint_total', $shippingAddress->getData('mecpoint_total'));
		 $order->save();
     }
	
}

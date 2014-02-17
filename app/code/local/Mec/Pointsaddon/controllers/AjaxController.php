<?php
class Mec_Pointsaddon_AjaxController extends Mage_Core_Controller_Front_Action{
	
	public function calculateParamAction()
	{
		$qty = $this->getRequest()->getParam('qty');
		$points_set_id = $this->getRequest()->getParam('points_set_id');
		
		$points_obj = Mage::getModel('pointsaddon/pointset')->load($points_set_id);
		
		$response['html'] = Mage::helper('catalog')->__('%s points + %s price', $points_obj->getPoints() * $qty, Mage::helper('core')->currency(Mage::helper('pointsaddon')->convertToStoreCurreny($points_obj->getBasePrice() * $qty), true, false));
		
		$this->getResponse()->setBody(Zend_Json::encode($response));
	}
	

}
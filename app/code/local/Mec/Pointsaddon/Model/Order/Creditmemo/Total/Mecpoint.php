<?php
class Mec_Pointsaddon_Model_Order_Creditmemo_Total_Mecpoint 
extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {

		return $this;

        $order = $creditmemo->getOrder();
        $orderMecpointTotal        = $order->getMecpointTotal();

        if ($orderMecpointTotal) {
			// $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$orderMecpointTotal);
			// $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$orderMecpointTotal);
        }

        return $this;
    }
}
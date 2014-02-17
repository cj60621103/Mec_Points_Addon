<?php
class Mec_Pointsaddon_Model_Order_Invoice_Total_Mecpoint
extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
		$order=$invoice->getOrder();
        $orderMecpointTotal = $order->getMecpointTotal();
        // if ($orderMecpointTotal&&count($order->getInvoiceCollection())==0) {
            // $invoice->setGrandTotal($invoice->getGrandTotal()+$orderMecpointTotal);
            // $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$orderMecpointTotal);
        // }
        return $this;
    }
}
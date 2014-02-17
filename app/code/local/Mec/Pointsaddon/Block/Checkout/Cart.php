<?php
class Mec_Pointsaddon_Block_Checkout_Cart extends Mage_Checkout_Block_Cart{

	public function chooseTemplate()
    {
        $itemsCount = $this->getItemsCount() ? $this->getItemsCount() : $this->getQuote()->getItemsCount();
        if ($itemsCount) {
            // $this->setTemplate($this->getCartTemplate());
			$this->setTemplate('mec/checkout/cart.phtml');
        } else {
            $this->setTemplate($this->getEmptyTemplate());
        }
    }

}
<?php

class WP_OnePageCheckout_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    public function getAddress()
    {
        if (is_null($this->_address))
        {
            if ($this->isCustomerLoggedIn())
            {
                $this->_address = $this->getQuote()->getShippingAddress();
            }
            else
            {
                $this->_address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
            }
        }
        return $this->_address;
    }

    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }
}

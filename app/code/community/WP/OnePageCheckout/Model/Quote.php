<?php

class WP_OnePageCheckout_Model_Quote extends Mage_Sales_Model_Quote
{
    public function isVirtual()
    {
        if (Mage::helper('onepagecheckout')->isDisabled()) return parent::isVirtual();
        if (Mage::helper('onepagecheckout')->isShippingDisabledCompletly()) return true;
        return parent::isVirtual();
    }
}

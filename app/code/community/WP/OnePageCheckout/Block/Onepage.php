<?php

class WP_OnePageCheckout_Block_Onepage extends Mage_Checkout_Block_Onepage
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setTemplate();
    }

    private function _setTemplate()
    {
        $layoutType = Mage::getStoreConfig('onepage_checkout/layout/type');
        switch ($layoutType) {
            case WP_OnePageCheckout_Model_System_Config_Source_Layouttype::TYPE_3COLUMNS:
                $this->setTemplate('webandpeople/onepagecheckout/onepage/layout/3columns.phtml');
                break;

            case WP_OnePageCheckout_Model_System_Config_Source_Layouttype::TYPE_CUSTOM:
                $this->setTemplate('webandpeople/onepagecheckout/onepage/layout/custom.phtml');
                break;

            default:
                $this->setTemplate('webandpeople/onepagecheckout/onepage/layout/standard.phtml');
        }
    }

    private function _initLayoutData()
    {
        $steps          = $this->getSteps();
        $billing        = $this->getLayout()->getBlock('checkout.onepage.billing');
        if ($billing->isUseBillingAddressForShipping()) {
            $cssClassShippingAsBilling  = ' shipping_as_billing';
            $styleShipping              = ' style="display:none"';
        } else {
            $cssClassShippingAsBilling  = '';
            $styleShipping              = '';
        }
        if ($billing->canShip()) {
            $canShip                    = true;
            $cssClassNoShipping         = '';
        } else {
            $canShip                    = false;
            $cssClassNoShipping         = ' no_shipping';
        }
        $this->assign('steps', $steps);
        $this->assign('canShip', $canShip);
        $this->assign('cssClassShippingAsBilling', $cssClassShippingAsBilling);
        $this->assign('cssClassNoShipping', $cssClassNoShipping);
        $this->assign('styleShipping', $styleShipping);
    }

    protected function _toHtml()
    {
        $this->_initLayoutData();
        return parent::_toHtml();
    }
}

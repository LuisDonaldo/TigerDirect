<?php

class WP_OnePageCheckout_Block_Onepage_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    public function isEnabled()
    {
        $enabled    = Mage::getStoreConfigFlag('onepage_checkout/newsletter_subscription/enabled');
        $visibility = Mage::getStoreConfig('onepage_checkout/newsletter_subscription/visibility');
        $isLogged   = $this->isCustomerLoggedIn();
        switch ($visibility) {
            case WP_OnePageCheckout_Model_System_Config_Source_Visibility::VISIBILITY_GUEST_ONLY:
                $enabled = $enabled && !$isLogged;
                break;

            case WP_OnePageCheckout_Model_System_Config_Source_Visibility::VISIBILITY_LOGGED_USER_ONLY:
                $enabled = $enabled && $isLogged;
                break;
        }
        return $enabled;
    }

    public function getCheckedStatus()
    {
        return Mage::getStoreConfigFlag('onepage_checkout/newsletter_subscription/checked') ? ' checked="checked"' : '';
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled()) return null;
        return parent::_toHtml();
    }
}

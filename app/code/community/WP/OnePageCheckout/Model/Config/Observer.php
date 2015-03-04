<?php

class WP_OnePageCheckout_Model_Config_Observer
{
    public function addCustomLayoutHandle(Varien_Event_Observer $observer)
    {
        $controllerAction   = $observer->getEvent()->getAction();
        $layout             = $observer->getEvent()->getLayout();
        if ($controllerAction && $layout && $controllerAction instanceof Mage_Adminhtml_System_ConfigController) {
            if ($controllerAction->getRequest()->getParam('section') == 'onepage_checkout') {
                $layout->getUpdate()->addHandle('wp_onepagecheckout_system_configuration');
            }
        }
        return $this;
    }

    public function afterSaveOnepagecheckoutSettings($observer)
    {
        $website = $observer->getWebsite();
        $store = $observer->getStore();
        $data = Mage::getModel('adminhtml/config_data')
            ->setSection('onepage_checkout')
            ->setWebsite($website)
            ->setStore($store)
            ->load();

        if (!$website) {
            if (!isset($data['onepage_checkout/general/guest_checkout']))
                $data['onepage_checkout/general/guest_checkout']            = Mage::getStoreConfig('onepage_checkout/general/guest_checkout');
            if (!isset($data['onepage_checkout/general/customer_must_be_logged']))
                $data['onepage_checkout/general/customer_must_be_logged']   = Mage::getStoreConfig('onepage_checkout/general/customer_must_be_logged');
            if (!isset($data['onepage_checkout/general/enable_agreements']))
                $data['onepage_checkout/general/enable_agreements']         = Mage::getStoreConfig('onepage_checkout/general/enable_agreements');
        }

        // --- Checkout ---
        $section = 'checkout'; $fields = array(); $groups = array();

        if (isset($data['onepage_checkout/general/guest_checkout']) || !$website)
            $fields['guest_checkout'] = array('value' => $data['onepage_checkout/general/guest_checkout']);
        else
            $fields['guest_checkout'] = array('inherit' => true);

        if (isset($data['onepage_checkout/general/customer_must_be_logged']) || !$website)
            $fields['customer_must_be_logged'] = array('value' => $data['onepage_checkout/general/customer_must_be_logged']);
        else
            $fields['customer_must_be_logged'] = array('inherit' => true);

        if (isset($data['onepage_checkout/general/enable_agreements']) || !$website)
            $fields['enable_agreements'] = array('value' => $data['onepage_checkout/general/enable_agreements']);
        else
            $fields['enable_agreements'] = array('inherit' => true);

        foreach ($fields as $field => $value) $groups['options']['fields'][$field] = $value;

        $this->_saveConfigData($groups, $section, $store, $website);
        // --- / Checkout ---

        // --- Sales Gift Options ---
        $section = 'sales'; $fields = array(); $groups = array();

        if (isset($data['onepage_checkout/gift_options/allow_order']) || !$website)
            $fields['allow_order'] = array('value' => $data['onepage_checkout/gift_options/allow_order']);
        else
            $fields['allow_order'] = array('inherit' => true);

        if (isset($data['onepage_checkout/gift_options/allow_items']) || !$website)
            $fields['allow_items'] = array('value' => $data['onepage_checkout/gift_options/allow_items']);
        else
            $fields['allow_items'] = array('inherit' => true);

        $keyAllowOrder = Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER;
        $xPath = explode('/', $keyAllowOrder);
        foreach ($fields as $field => $value) {
            $groups[$xPath[1]]['fields'][$field] = $value;
        }

        $this->_saveConfigData($groups, $section, $store, $website);
        // --- / Sales Gift Options ---

        $this->_reinitConfigData();
    }

    public function afterSaveOrignCheckoutSettings($observer)
    {
        #return;
        $website = $observer->getWebsite();
        $store = $observer->getStore();
        $this->copyOrignCheckoutSettings($website, $store);
        $this->_reinitConfigData();
    }

    public function afterSaveOrignSalesSettings($observer)
    {
        #return;
        $website = $observer->getWebsite();
        $store = $observer->getStore();
        $this->copyOrignSalesSettings($website, $store);
        $this->_reinitConfigData();
    }

    public function reinitConfigData()
    {
        $this->_reinitConfigData();
    }

    public function copyOrignSalesSettings($website, $store = '')
    {
        $data = Mage::getModel('adminhtml/config_data')
            ->setSection('sales')
            ->setWebsite($website)
            ->setStore($store)
            ->load();

        $keyAllowOrder = Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER;
        $keyAllowItems = Mage_GiftMessage_Helper_Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS;

        if (!count($data) || !$website) {
            if (!isset($data[$keyAllowOrder]))
                $data[$keyAllowOrder]  = Mage::getStoreConfig($keyAllowOrder);
            if (!isset($data[$keyAllowItems]))
                $data[$keyAllowItems]  = Mage::getStoreConfig($keyAllowItems);
        }

        // --- Gift Options ---
        $section = 'onepage_checkout'; $fields = array(); $groups = array();

        if (isset($data[$keyAllowOrder]) || !$website)
            $fields['allow_order'] = array('value' => $data[$keyAllowOrder]);
        else
            $fields['allow_order'] = array('inherit' => true);

        if (isset($data[$keyAllowItems]) || !$website)
            $fields['allow_items'] = array('value' => $data[$keyAllowItems]);
        else
            $fields['allow_items'] = array('inherit' => true);

        #Mage::log($fields);
        foreach ($fields as $field => $value) $groups['gift_options']['fields'][$field] = $value;

        $this->_saveConfigData($groups, $section, $store, $website);
        // --- / Gift Options ---
    }

    public function copyOrignCheckoutSettings($website, $store = '')
    {
        $data = Mage::getModel('adminhtml/config_data')
            ->setSection('checkout')
            ->setWebsite($website)
            ->setStore($store)
            ->load();

        if (!count($data) || !$website) {
            if (!isset($data['checkout/options/guest_checkout']))
                $data['checkout/options/guest_checkout']            = Mage::getStoreConfig('checkout/options/guest_checkout');
            if (!isset($data['checkout/options/customer_must_be_logged']))
                $data['checkout/options/customer_must_be_logged']   = Mage::getStoreConfig('checkout/options/customer_must_be_logged');
            if (!isset($data['checkout/options/enable_agreements']))
                $data['checkout/options/enable_agreements']         = Mage::getStoreConfig('checkout/options/enable_agreements');
        }

        // --- General ---
        $section = 'onepage_checkout'; $fields = array(); $groups = array();

        if (isset($data['checkout/options/guest_checkout']) || !$website)
            $fields['guest_checkout'] = array('value' => $data['checkout/options/guest_checkout']);
        else
            $fields['guest_checkout'] = array('inherit' => true);

        if (isset($data['checkout/options/customer_must_be_logged']) || !$website)
            $fields['customer_must_be_logged'] = array('value' => $data['checkout/options/customer_must_be_logged']);
        else
            $fields['customer_must_be_logged'] = array('inherit' => true);

        if (isset($data['checkout/options/enable_agreements']) || !$website)
            $fields['enable_agreements'] = array('value' => $data['checkout/options/enable_agreements']);
        else
            $fields['enable_agreements'] = array('inherit' => true);

        #Mage::log($fields);
        foreach ($fields as $field => $value) $groups['general']['fields'][$field] = $value;

        $this->_saveConfigData($groups, $section, $store, $website);
        // --- / General ---
    }

    private function _saveConfigData($groups, $section, $store, $website)
    {
        Mage::getModel('adminhtml/config_data')
            ->setSection($section)
            ->setWebsite($website)
            ->setStore($store)
            ->setGroups($groups)
            ->save();
    }

    private function _reinitConfigData()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }
}

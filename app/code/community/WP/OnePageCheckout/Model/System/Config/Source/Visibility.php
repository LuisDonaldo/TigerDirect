<?php

class WP_OnePageCheckout_Model_System_Config_Source_Visibility
{
    const VISIBILITY_ALL                = 'all';
    const VISIBILITY_GUEST_ONLY         = 'guest_only';
    const VISIBILITY_LOGGED_USER_ONLY   = 'logged_user_only';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::VISIBILITY_ALL,
                'label' => Mage::helper('onepagecheckout')->__('All')
            ),
            array(
                'value' => self::VISIBILITY_GUEST_ONLY,
                'label' => Mage::helper('onepagecheckout')->__('Guest only')
            ),
            array(
                'value' => self::VISIBILITY_LOGGED_USER_ONLY,
                'label' => Mage::helper('onepagecheckout')->__('Logged user only')
            ),
        );
    }
}

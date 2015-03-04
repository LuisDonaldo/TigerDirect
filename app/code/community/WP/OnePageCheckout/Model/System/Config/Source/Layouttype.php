<?php

class WP_OnePageCheckout_Model_System_Config_Source_Layouttype
{
    const TYPE_STANDARD     = 'standard';
    const TYPE_3COLUMNS     = '3columns';
    const TYPE_CUSTOM       = 'custom';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::TYPE_STANDARD,
                'label' => Mage::helper('onepagecheckout')->__('Standard')
            ),
            array(
                'value' => self::TYPE_3COLUMNS,
                'label' => Mage::helper('onepagecheckout')->__('Three Columns')
            ),
            array(
                'value' => self::TYPE_CUSTOM,
                'label' => Mage::helper('onepagecheckout')->__('Custom')
            ),
        );
    }
}

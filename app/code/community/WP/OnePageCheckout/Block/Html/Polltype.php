<?php

class WP_OnePageCheckout_Block_Html_Polltype
    extends WP_OnePageCheckout_Block_Html_Select
{
    public function getOptionArray()
    {
        return array(
            array('value' => 'radio', 'label' => Mage::helper('onepagecheckout')->__('Single choice')),
            array('value' => 'checkbox', 'label' => Mage::helper('onepagecheckout')->__('Multiple choice')),
        );
    }
}

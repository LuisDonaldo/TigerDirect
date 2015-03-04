<?php

class WP_OnePageCheckout_Block_Toggle extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        if (Mage::helper('onepagecheckout')->isDisabled()) return;
        $layout = $this->getLayout();
        $layout->getBlock('root')->setTemplate('page/1column.phtml');
        $head = $layout->getBlock('head');
        $head->addItem('skin_js', 'js/webandpeople/onepagecheckout/onepagecheckout.js');
        $head->addItem('skin_css', 'css/webandpeople/onepagecheckout/onepagecheckout.css');
    }
}

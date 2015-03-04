<?php

class WP_OnePageCheckout_Block_Onepage_Polls extends Mage_Checkout_Block_Onepage_Abstract
{
    public function isEnabled()
    {
        $enabled    = Mage::getStoreConfigFlag('onepage_checkout/polls/enabled');
        $visibility = Mage::getStoreConfig('onepage_checkout/polls/visibility');
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

    public function getPolls()
    {
        return Mage::helper('onepagecheckout')->getPolls();
    }

    public function getPollAnswers($pollId)
    {
        $pollAnswers = Mage::getResourceModel('poll/poll_answer_collection')->addPollFilter($pollId)->getItems();
        return $pollAnswers;
    }

    public function getPoll($pollId)
    {
        $poll = Mage::getModel('poll/poll')->load($pollId);
        return $poll;
    }

    protected function _toHtml()
    {
        if (!$this->isEnabled()) return null;
        return parent::_toHtml();
    }
}

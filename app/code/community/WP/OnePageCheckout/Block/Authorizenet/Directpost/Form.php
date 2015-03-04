<?php

class WP_OnePageCheckout_Block_Authorizenet_Directpost_Form extends Mage_Payment_Block_Form_Cc
{
    /**
     * Internal constructor
     * Set info template for payment step
     *
     */
    protected function _construct()
    {
        parent::_construct();
        if (!Mage::helper('onepagecheckout')->hasAuthorizenetDirectpost()) return;

        $this->setTemplate('authorizenet/directpost/info.phtml');
    }

    /**
     * Render block HTML
     * If method is not directpost - nothing to return
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('onepagecheckout')->hasAuthorizenetDirectpost()) return;

        $payment = Mage::getSingleton('checkout/type_onepage')
            ->getQuote()
            ->getPayment();

        try {
            $this->setMethod($payment->getMethodInstance());
        } catch (Exception $e) {
            // ---
        }

        $method = $this->getMethod();
        if (is_null($method) || $method->getCode() != Mage::getSingleton('authorizenet/directpost')->getCode()) {
            return null;
        }

        $this->getMethod()->getInfoInstance()->setData($this->getRequest()->getParam('payment'));

        return parent::_toHtml();
    }

    /**
     * Retrieve payment method model
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethod()
    {
        $method = $this->getData('method');
        if (!($method instanceof Mage_Payment_Model_Method_Abstract)) {
            return null;
        }
        return $method;
    }

    public function getMethodCode()
    {
        if (is_null($this->getMethod())) return '';
        return $this->getMethod()->getCode();
    }

    /**
     * Get type of request
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        return $this->getAction()
            ->getRequest()
            ->getParam('isAjax');
    }
}

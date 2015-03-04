<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';

class WP_OnePageCheckout_OnepageController extends Mage_Checkout_OnepageController
{
    private $_disabled = null;
    private $_storeId = null;

    private function _isDisabled()
    {
        #{licCode}#
        if (is_null($this->_disabled))
            $this->_disabled = Mage::helper('onepagecheckout')->isDisabled();
        return $this->_disabled;
    }

    public function preDispatch()
    {
        if ($this->_isDisabled()) return parent::preDispatch();

        parent::preDispatch();
        $this->_preDispatchValidateCustomer();

        $checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($checkoutSessionQuote->getIsMultiShipping()) {
            $checkoutSessionQuote->setIsMultiShipping(false);
            $checkoutSessionQuote->removeAllAddresses();
        }

        if (!$this->_isCustomerMustBeLogged()) {
            $notice = Mage::helper('onepagecheckout')->__('You need to be logged to proceed to checkout.');
            Mage::getSingleton('core/session')->addNotice($notice);
            Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());
            $this->_forward('login', 'account', 'customer');
            return;
        }

        return $this;
    }

    public function indexAction()
    {
        if ($this->_isDisabled()) return parent::indexAction();

        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getOnepage()->initCheckout();

        /* +++ Start */
        $shippingMethod = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
        #Mage::log('first method');
        #Mage::log($shippingMethod);
        $this->_initBillingAddress();
        $this->_initShippingAddress(); #{licCode}#
        $this->_setDefaultShippingMethod($shippingMethod);
        try {
            $payment['method'] = $this->getOnepage()->getQuote()->getPayment()->getMethod();
            if ($payment['method']) {
                $this->getOnepage()->savePayment($payment);
                $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            }
        } catch (Exception $e) {
            // ---
        }

        #{licCode}#
        /* +++ End */

        $this->loadLayout();

        /* +++ Start */
        $layoutType = Mage::getStoreConfig('onepage_checkout/layout/type');
        $this->getLayout()->getBlock('root')->addBodyClass('layout-' . $layoutType);
        /* +++ End */

        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    public function saveOrderAction()
    {
        if ($this->_isDisabled()) return parent::saveOrderAction();
        if ($this->_expireAjax()) return;
        if (!$this->getRequest()->isPost()) return;

        #Mage::log($this->getRequest()->getParams());
        // --- save submit data to session ---
        $this->getOnepage()->getCheckout()->setOrderSubmitData($this->getRequest()->getParams());

        #{licCode}#

        // --- save all data ---

        $this->_updatePayment();

        $data = $this->getRequest()->getPost('billing', array());
        if (isset($data['email'])) $data['email'] = trim($data['email']);
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $this->_updateBillingAddress($data, $customerAddressId);
        if (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
            $this->_ajaxUpdateShippingMethods('billing');
            $result['duplicateBillingInfo'] = 'true';
        } else {
            $this->_ajaxUpdateShippingMethods();
        }
        unset($data['use_for_shipping']);

        #Mage::log('1!');

        $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
        if (!empty($result['error'])) {
            #Mage::log($result);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        #Mage::log('2!');

        if ($this->canShip()) {

            if (Mage::getStoreConfig('onepage_checkout/layout/enable_different_shipping')) {
                $data = $this->getRequest()->getPost('shipping', array());
                $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            }

            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
            if (!empty($result['error'])) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }

            #Mage::log('3!');

            // ---

            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            // --- $result will have error data if shipping method is empty ---
            if ($result) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }

            #Mage::log('4!');

            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));

            #{licCode}#

            #Mage::log('5!');
        }
        #Mage::log('6!');

        $redirectUrl = '';
        try {
            // --- set payment to quote ---
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // --- get section and redirect data ---
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl) $result['redirect'] = $redirectUrl;
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields())
            {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

        #{licCode}#

        if (!empty($result['error']) || $redirectUrl) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        $this->_ajaxUpdateTotalView();

        // --- newsletter subscription ---
        $this->getOnepage()->getCheckout()->setIsSubscribed($this->getRequest()->getParam('is_subscribed', 0));

        // --- customer comments ---
        $this->getOnepage()->getCheckout()->setWpCustomerComments($this->getRequest()->getParam('customer_comments', false));

        // --- / save all data ---

        //Mage::log('7');

        $result = array();
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
            $this->getOnepage()->saveOrder();
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if( !empty($message) ) {
                $result['error_messages'] = $message;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();

        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Login post action
     */
    public function loginPostAction()
    {
        $result = '';
        $result['redirect'] = Mage::getUrl('*/*/');
        $session = Mage::getSingleton('customer/session');

        if (!$this->_validateFormKey()) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        if ($session->isLoggedIn()) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        #{licCode}#

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    $result['message'] = Mage::helper('customer')->__('You have successfully logged...');
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->setUsername($login['username']);
                    $result['error'] = true;
                    $result['message'] = $message;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $result['error'] = true;
                $result['message'] = Mage::helper('customer')->__('Login and password are required.');
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function ajaxUpdateAction()
    {
        $result = array();

        if ($this->_isDisabled()) {
            // --- reload ---
            $result['redirect'] = Mage::getUrl('*/*/');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return;
        }

        #{licCode}#

        if (!$this->_expireAjax() && $this->getRequest()->isPost()) {
            $type = $this->getRequest()->getParam('type');
            try {
                switch ($type) {

                    case 'all_methods':
                        $data = $this->getRequest()->getPost('billing', array());
                        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
                        $this->_updateBillingAddress($data, $customerAddressId);

                        if (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                            $this->_ajaxUpdateShippingMethods('billing');
                            $result['duplicateBillingInfo'] = 'true';
                        } else {
                            $this->_ajaxUpdateShippingMethods();
                        }

                        $this->_ajaxUpdateTotalView();

                        $result['update_section'][] = array(
                            'name' => 'checkout-payment-method-load',
                            'html' => $this->_getPaymentMethodsHtml()
                        );

                        if ($this->canShip()) {
                            $result['update_section'][] = array(
                                'name' => 'checkout-shipping-method-load',
                                'html' => $this->_getShippingMethodsHtml()
                            );
                        }

                        $result['update_section'][] = array(
                            'name' => 'checkout-review-load',
                            'html' => $this->_getReviewHtml()
                        );

                        $result['update_section'][] = array(
                            'name' => 'checkout-items-before-button',
                            'html' => $this->_getItemsBeforeButtonHtml()
                        );

                        $result['update_section'][] = array(
                            'name' => 'checkout-items-after',
                            'html' => $this->_getItemsAfterHtml()
                        );
                        break;

                    case 'discount_coupon':
                        $skip = false;

                        // --- No reason continue with empty shopping cart ---
                        if (!$this->getOnepage()->getQuote()->getItemsCount()) $skip = true;

                        $couponCode = (string)$this->getRequest()->getParam('coupon_code');
                        if ($this->getRequest()->getParam('remove') == 1) $couponCode = '';
                        $oldCouponCode = $this->getOnepage()->getQuote()->getCouponCode();

                        if (!strlen($couponCode) && !strlen($oldCouponCode)) $skip = true;

                        if (!$skip) {
                            $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                            $this->getOnepage()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                                ->collectTotals()
                                ->save();
                        }

                        if ($couponCode && $couponCode != $this->getOnepage()->getQuote()->getCouponCode()) {
                           $result['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                        }

                        $result['update_section'][] = array(
                            'name' => 'discount-coupon-form',
                            'html' => $this->_getDiscountCouponHtml()
                        );

                        $result['update_section'][] = array(
                            'name' => 'checkout-review-load',
                            'html' => $this->_getReviewHtml()
                        );
                }
            } catch (Exception $e) {
                $result['error'] = true;
                $result['message'] = $e->getMessage();
            }
        }

        #{licCode}#

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function canShip()
    {
        return !$this->getOnepage()->getQuote()->isVirtual();
    }

    private function _ajaxUpdateShippingMethods($dataGroup = "shipping")
    {
        $data = $this->getRequest()->getPost($dataGroup, array());
        if ($dataGroup == 'shipping') $data['same_as_billing'] = 0; else $data['same_as_billing'] = 1;
        $customerAddressId = $this->getRequest()->getPost($dataGroup . '_address_id', false);
        $this->_updateShippingAddress($data, $customerAddressId); #{licCode}#
    }

    private function _ajaxUpdateTotalView()
    {
        // --- Update Shipping Method ---
        $shippingMethod = $this->getRequest()->getPost('shipping_method', false);
        $this->_setDefaultShippingMethod($shippingMethod);
        // --- / Update Shipping Method ---
        #{licCode}#
        $this->_updatePayment();
        $this->getOnepage()->getQuote()->collectTotals()->save();
    }

    private function _setDefaultShippingMethod($shippingMethodDefault = '')
    {
        $shippingMethods = $this->_getAvailableShippingMethods();
        if (is_array($shippingMethods) && count($shippingMethods) == 1) {
            $shippingMethodDefault = array_shift($shippingMethods);
        }
        #Mage::log($shippingMethodDefault);
        #{licCode}#
        // --- Set Shipping Method by Default ---
        if ($shippingMethodDefault) {
            $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethodDefault);
            $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        }
        #Mage::log('default');
        #Mage::log($shippingMethodDefault);
    }

    private function _getAvailableShippingMethods()
    {
        $methods = array();
        $address = $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        $_shippingRateGroups = $address->getGroupedAllShippingRates();
        #{licCode}#
        foreach ($_shippingRateGroups as $code => $_rates) {
            foreach ($_rates as $_rate) {
                $method             = $_rate->getCarrier() . '_' . $_rate->getMethod();
                $methods[$method]   = $method;
            }
        }
        #Mage::log('all methods');
        #Mage::log($methods);
        return $methods;
    }


    private function _initShippingAddress()
    {
        $data = $this->getOnepage()->getQuote()->getShippingAddress()->getData();
        $data = $this->_setDafaultAddressValues($data);
        $data['same_as_billing'] = 1; #{licCode}#
        $this->_updateShippingAddress($data);
        return $data;
    }

    private function _initBillingAddress()
    {
        $data = $this->getOnepage()->getQuote()->getBillingAddress()->getData();
        $data = $this->_setDafaultAddressValues($data); #{licCode}#
        $this->_updateBillingAddress($data);
    }

    private function _setDafaultAddressValues($data)
    {
        #{licCode}#
        $dataEntered = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getData();
        $this->_setDefaultAddressItem($data, $dataEntered, 'country_id', 'country_id');
        $this->_setDefaultAddressItem($data, $dataEntered, 'region_id', 'region_id');
        $this->_setDefaultAddressItem($data, $dataEntered, 'region', 'region');
        $this->_setDefaultAddressItem($data, $dataEntered, 'city', 'city');
        $this->_setDefaultAddressItem($data, $dataEntered, 'postcode', 'postcode'); #{licCode}#
        $this->_setDefaultAddressItem($data, $dataEntered, 'telephone', 'telephone');
        $this->_setDefaultAddressItem($data, $dataEntered, 'street', 'street_line1');
        return $data;
    }

    private function _setDefaultAddressItem(&$data, &$dataEntered, $itemCode, $configItemCode)
    {
        if (array_key_exists($itemCode, $data)) {
            $_itemValue = '';
            if (isset($dataEntered[$itemCode])) $_itemValue = $dataEntered[$itemCode];
            if (!$_itemValue) $_itemValue = trim(Mage::getStoreConfig('onepage_checkout/origin_shipping_settings/' . $configItemCode, $this->_getStoreId()));
            $_itemDisabled = !Mage::getStoreConfig('onepage_checkout/fields_settings/' . $configItemCode, $this->_getStoreId());
            switch ($itemCode) {
                case 'street':
                    if (!isset($data[$itemCode][0]) || !trim($data[$itemCode][0]) || $_itemDisabled) {
                        $data[$itemCode][0] = $_itemValue;
                    }
                    break;

                default:
                    if (!isset($data[$itemCode]) || !trim($data[$itemCode]) || $_itemDisabled) {
                        $data[$itemCode] = $_itemValue;
                    }
            }
        }
        #{licCode}#
    }

    private function _updateShippingAddress($data, $customerAddressId = false)
    {
        $address = $this->getOnepage()->getQuote()->getShippingAddress();
        $methodShipping = $address->getShippingMethod();
        #Mage::log('method from address');
        #Mage::log($methodShipping);
        if ($customerAddressId) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId() && $customerAddress->getCustomerId() == $this->getOnepage()->getQuote()->getCustomerId()) {
                $address->importCustomerAddress($customerAddress);
                $address->implodeStreetAddress();
                $address->setCollectShippingRates(true);
                $address->setShippingMethod($methodShipping);
                return;
            }
        }
        #{licCode}#
        unset($data['address_id']); #{licCode}#
        unset($data['save_in_address_book']);
        $address->addData($data);
        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);
        $address->setShippingMethod($methodShipping);
    }

    private function _updateBillingAddress($data, $customerAddressId = false)
    {
        $address = $this->getOnepage()->getQuote()->getBillingAddress();
        if ($customerAddressId) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId() && $customerAddress->getCustomerId() == $this->getOnepage()->getQuote()->getCustomerId()) {
                $address->importCustomerAddress($customerAddress);
                $address->implodeStreetAddress();
                return;
            }
        }
        unset($data['address_id']);
        unset($data['save_in_address_book']); #{licCode}#
        $address->addData($data);
        $address->implodeStreetAddress();
    }

    private function _updatePayment()
    {
        $payment = $this->_getPaymentValue();
        #Mage::log($payment);
        if ($payment) {
            try {
                $this->getOnepage()->getQuote()->getPayment()->importData($payment);
            } catch (Exception $e) {
                // ---
            }
        }
    }

    private function _getPaymentValue()
    {
        // ---
        $payment = $this->getRequest()->getPost('payment', false);
        $current = '';
        if ($payment && is_array($payment) && isset($payment['method']) && $payment['method']) {
            $current = $payment['method'];
        }
        #Mage::log($current);
        // ---
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        #{licCode}#
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        $methods = $layout->getBlock('paymentmethod')->getMethods();
        $codes = array();
        foreach ($methods as $_method) {
            $codes[] = $_method->getCode();
        }
        #Mage::log($codes);
        if (in_array($current, $codes)) {
            return array('method' => $current);
        } elseif (count($codes) == 1) {
            return array('method' => $codes[0]);
        }
        return false;
    }

    private function _getStoreId()
    {
        #{licCode}#
        if (is_null($this->_storeId)) $this->_storeId = Mage::app()->getStore()->getId();
        return $this->_storeId;
    }

    protected function _getReviewHtml()
    {
        if ($this->_isDisabled()) return parent::_getReviewHtml();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        #{licCode}#
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getBlock('review')->toHtml();
        return $output;
    }

    protected function _getPaymentMethodsHtml()
    {
        if ($this->_isDisabled()) return parent::_getPaymentMethodsHtml();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        #{licCode}#
        $output = $layout->getBlock('paymentmethod')->toHtml();
        return $output;
    }

    protected function _getShippingMethodsHtml()
    {
        if ($this->_isDisabled()) return parent::_getShippingMethodsHtml();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        #{licCode}#
        $output = $layout->getBlock('shippingmethod')->toHtml();
        return $output;
    }

    protected function _getAdditionalHtml()
    {
        if ($this->_isDisabled()) return parent::_getAdditionalHtml();
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getBlock('additional')->toHtml();
        #{licCode}#
        return $output;
    }

    private function _getDiscountCouponHtml()
    {
        #{licCode}#
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getBlock('coupon')->toHtml();
        return $output;
    }

    private function _getItemsAfterHtml()
    {
        #{licCode}#
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getBlock('onepagecheckout.items.after')->toHtml();
        return $output;
    }

    private function _getItemsBeforeButtonHtml()
    {
        #{licCode}#
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('onepagecheckout_onepage_ajax');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getBlock('onepagecheckout.items.before.button')->toHtml();
        #Mage::log($output);
        #Mage::log('=====================');
        return $output;
    }

    protected function _isCustomerMustBeLogged()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
            || !Mage::helper('onepagecheckout')->isCustomerMustBeLogged();
    }
}

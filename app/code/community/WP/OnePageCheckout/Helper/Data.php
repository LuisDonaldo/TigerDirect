<?php

class WP_OnePageCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_polls = null;
    private $_store = null;

    private static $_regexMatchCache        = array();
    private static $_disabledByRegexpCache  = array();

    public function getConfigParam($key)
    {
        $key    = 'onepage_checkout/' . $key;
        $data   = Mage::getStoreConfig($key);
        if (!is_array($data)) return trim($data);
            else return array_map('trim', $data);
    }

    public function isIE6()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) return;
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        preg_match('/MSIE ([0-9]{1,}[\.0-9]{0,})/', $userAgent, $matches);
        if (!isset($matches[1])) return;
        $version = floatval($matches[1]); #Mage::log($version);
        $flag = false; if( $version <= 6.0 ) $flag = true;
        return $flag;
    }

    public function isDisabled()
    {
        if (!self::getConfigParam('general/enabled')) return true;
        if (self::getConfigParam('general/ie6_ignore') && self::isIE6()) return true;
        // --- to disable by regexp expression ---
        if ($this->isDisabledByUserAgentAgainstRegexps()) return true;
        return false;
    }

    public function getGiftmessageBlock(Varien_Object $entity)
    {
        $type = 'onepage_checkout';
        return Mage::getSingleton('core/layout')->createBlock('giftmessage/message_inline')
            ->setTemplate('webandpeople/onepagecheckout/onepage/shipping_method/giftmessage.phtml')
            ->setId('giftmessage_form_' . $this->_nextId++)
            ->setDontDisplayContainer(false)
            ->setEntity($entity)
            ->setType($type)->toHtml();
    }

    public function isShippingDisabledCompletly()
    {
        return self::getConfigParam('layout/shipping_disabled');
    }

    public function hasPaypalHss()
    {
        $file = Mage::getBaseDir() . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Paypal' . DS . 'Helper' . DS . 'Hss.php';
        return file_exists($file);
    }

    public function hasAuthorizenetDirectpost()
    {
        $file = Mage::getBaseDir() . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Authorizenet' . DS . 'Model' . DS . 'Directpost.php';
        return file_exists($file);
    }

    public function getPolls($compact = true)
    {
        if (is_null($this->_polls)) {
            try {
                $res = unserialize(Mage::getStoreConfig('onepage_checkout/polls/items'));
            } catch (Exception $e) {
                // ---
            }
            $polls = array();
            if (is_array($res) && count($res)) {
                foreach ($res as $item) {
                    $polls[$item['poll']] = $item['poll_type'];
                }
            }
            $this->_polls = $polls;
        }
        return $this->_polls;
    }

    public function isDisabledByUserAgentAgainstRegexps()
    {
        $regexpsConfigPath = 'onepage_checkout/general/disabled_by_regexp';

        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        if (!empty(self::$_disabledByRegexpCache[$regexpsConfigPath])) {
            return self::$_disabledByRegexpCache[$regexpsConfigPath];
        }

        $configValueSerialized = Mage::getStoreConfig($regexpsConfigPath, $this->getStore());

        if (!$configValueSerialized) {
            return false;
        }

        $regexps = @unserialize($configValueSerialized);

        if (empty($regexps)) {
            return false;
        }

        foreach ($regexps as $rule) {
            if (!empty(self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']])) {
                self::$_disabledByRegexpCache[$regexpsConfigPath] = true;
                return true;
            }

            $regexp = '/' . trim($rule['regexp'], '/') . '/';

            if (@preg_match($regexp, $_SERVER['HTTP_USER_AGENT'])) {
                self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']] = true;
                self::$_disabledByRegexpCache[$regexpsConfigPath] = true;
                return true;
            }
        }

        return false;
    }

    public function getStore()
    {
        if ($this->_store === null) {
            return Mage::app()->getStore();
        }
        return $this->_store;
    }

    public function isCustomerMustBeLogged()
    {
        return Mage::getStoreConfigFlag('onepage_checkout/general/customer_must_be_logged');
    }
}

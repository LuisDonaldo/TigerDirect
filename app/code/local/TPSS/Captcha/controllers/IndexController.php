<?php
# Controllers are not autoloaded so we will have to do it manually:
require_once 'Mage/Contacts/controllers/IndexController.php';
class TPSS_Captcha_IndexController extends Mage_Contacts_IndexController
{
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }
                if(Mage::getStoreConfig('contacts/general/enabled') == 1)
                {
					$_data = $this->getRequest()->getPost();
					$_captcha = Mage::getModel('customer/session')->getData('vcaptcha_word');
					if ($_captcha['data'] != $_data['captcha']['vcaptcha']) {
						$error = true;
						$_SESSION['contacts']['name'] = $_POST['name'];
						$_SESSION['contacts']['email'] = $_POST['email'];
						$_SESSION['contacts']['telephone'] = $_POST['telephone'];
						$_SESSION['contacts']['comment'] = $_POST['comment'];
						Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Incorrect Captcha. Please, try again'));
						$this->_redirect('*/*/');
						return;
					}
				}
				
                if ($error) {
                    throw new Exception();
                }
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);
                if(Mage::getStoreConfig('contacts/general/enabled') == 1)
                {
					$_SESSION['contacts']['name'] = '';
					$_SESSION['contacts']['email'] = '';
					$_SESSION['contacts']['telephone'] = '';
					$_SESSION['contacts']['comment'] = '';
				}
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }
}

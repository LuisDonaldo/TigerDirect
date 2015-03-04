<?php

class WP_OnePageCheckout_Model_Observer
{
    public function observeLayoutHandleInitialization(Varien_Event_Observer $observer)
    {
        if (Mage::helper('onepagecheckout')->isDisabled()) return;
        $controllerAction   = $observer->getEvent()->getAction();
        $fullActionName     = $controllerAction->getFullActionName();
        #Mage::log($fullActionName);
        if ($fullActionName == 'checkout_onepage_index') {
            $controllerAction->getLayout()->getUpdate()->addHandle('wp_onepagecheckout_default');
        }
        return $this;
    }

    public function checkNewsletterSubscription($observer)
    {
        if (Mage::getSingleton('checkout/session')->getIsSubscribed()) {
            $quote      = $observer->getEvent()->getQuote();
            $customer   = $quote->getCustomer();

            switch ($quote->getCheckoutMethod()) {

                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER:
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN:
                    $customer->setIsSubscribed(1);
                    break;

                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
                    $session = Mage::getSingleton('core/session');
                    try {
                        $email = $quote->getBillingAddress()->getEmail();
                        $subscriber = Mage::getModel('newsletter/subscriber');
                        $subscriber->loadByEmail($email);
                        if (!$subscriber->getCustomerId() && !$subscriber->isSubscribed()) {
                            $status = $subscriber->subscribe($email);
                            if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                                $session->addSuccess(Mage::helper('onepagecheckout')->__('Confirmation request has been sent regarding your newsletter subscription'));
                            }
                        }
                    } catch (Mage_Core_Exception $e) {
                        $session->addException($e, Mage::helper('onepagecheckout')->__('There was a problem with the newsletter subscription: %s', $e->getMessage()));
                    } catch (Exception $e) {
                        $session->addException($e, Mage::helper('onepagecheckout')->__('There was a problem with the newsletter subscription'));
                    }
                    break;
            }
            Mage::getSingleton('checkout/session')->setIsSubscribed(0);
        }
        return $this;
    }

    public function setCustomerOrderComments($observer)
    {
        $orderComments = array();
        // --- comments ---
        $comments = nl2br(strip_tags(trim(Mage::getSingleton('checkout/session')->getWpCustomerComments())));
        if ($comments) {
            $orderComments[] = Mage::helper('onepagecheckout')->__('Customer\'s comments:') . '<br /><br />' . $comments;
        }
        // --- polls ---
        $pollsResultFlatDetails = $this->getCustomerOrderPolls();
        if ($pollsResultFlatDetails) {
            $orderComments[] = Mage::helper('onepagecheckout')->__('Customer\'s poll result:') . '<br />' . $pollsResultFlatDetails;
        }
        // --- save ---
        if (count($orderComments)) {
            $orderCommentsText = implode('<br /><br />', $orderComments);
            $order = $observer->getEvent()->getOrder();
            $order->setCustomerNote($orderCommentsText);
            $order->setCustomerNoteNotify(true);
        }
        return $this;
    }

    public function filterArrayPollData($k)
    {
        return substr($k, 0, 7) == 'survey_';
    }

    public function getCustomerOrderPolls()
    {
        if (!Mage::getStoreConfig('onepage_checkout/polls/enabled')) return;
        $orderPostData = Mage::getSingleton('checkout/session')->getOrderSubmitData();
        #Mage::log($orderPostData);
        $filter = array_filter(array_keys($orderPostData), array($this, 'filterArrayPollData'));
        $filter = array_intersect_key($orderPostData, array_flip($filter));
        $pollsResult = array(); foreach ($filter as $item) $pollsResult = array_merge($pollsResult, $item);
        #Mage::log($pollsResult);
        $res = array(); $pollsResultFlatDetails = '';
        foreach ($pollsResult as $info) {
            list($pollId, $answerId) = explode('|', $info);
            $poll = Mage::getModel('poll/poll')->load($pollId);
            $vote = Mage::getModel('poll/poll_vote')
                ->setPollAnswerId($answerId)
                ->setIpAddress(Mage::helper('core/http')->getRemoteAddr(true))
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            $poll->addVote($vote);
            if (!isset($res[$pollId])) {
                $res[$pollId] = array();
                $res[$pollId]['title'] = nl2br(strip_tags(trim($poll->getPollTitle())));
                $res[$pollId]['answer'] = array();
                $pollAnswers[$pollId] = Mage::getResourceModel('poll/poll_answer_collection')->addPollFilter($pollId)->getItems();
            }
            if (isset($pollAnswers[$pollId][$answerId])) {
                $res[$pollId]['answer'][$answerId] = nl2br(strip_tags(trim($pollAnswers[$pollId][$answerId]->getAnswerTitle())));
            }
        }
        if (count($res)) {
            foreach ($res as $poll) {
                if (count($poll['answer'])) {
                    $text = '- ' . implode('<br />- ', $poll['answer']);
                } else {
                    $text = Mage::helper('onepagecheckout')->__('There was no answer.');
                }
                $pollsResultFlatDetails.= '<br />' . $poll['title'] . '<br />' . $text . '<br />';
            }
        }
        return $pollsResultFlatDetails;
    }
}

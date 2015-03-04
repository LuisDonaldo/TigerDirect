<?php

class WP_OnePageCheckout_Block_Html_Polls
    extends WP_OnePageCheckout_Block_Html_Select
{
    public function getOptionArray()
    {
        $options = array();
        $pollResourceModel = Mage::getResourceModel('poll/poll');
        if (!is_object($pollResourceModel)) return $options;
        $read = $pollResourceModel->getReadConnection();
        $select = $read->select()
            ->from(array('main_table'=>$pollResourceModel->getMainTable()), '*');
        $polls = $read->fetchAll($select);
        if (is_array($polls) && count($polls)) {
            foreach ($polls as $poll) {
                $options[] = array(
                    'value' => $poll['poll_id'],
                    'label' => addslashes($poll['poll_title'])
                );
            }
        }
        return $options;
    }
}

<?php

class WP_OnePageCheckout_Block_System_Config_Form_Field_Array_Polls
    extends WP_OnePageCheckout_Block_System_Config_Form_Field_Array_Abstract
{
    public function _prepareToRender()
    {
        $this->addColumn('poll', array(
            'label'     => Mage::helper('onepagecheckout')->__('Poll'),
            'style'     => 'width:120px',
            'class'     => 'select wp_init',
            'renderer'  => new WP_OnePageCheckout_Block_Html_Polls(),
        ));
        $this->addColumn('poll_type', array(
            'label'     => Mage::helper('onepagecheckout')->__('Poll Type'),
            'style'     => 'width:120px',
            'class'     => 'select wp_init',
            'renderer'  => new WP_OnePageCheckout_Block_Html_Polltype(),
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('onepagecheckout')->__('Add Poll');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::_getElementHtml($element);
        $html.=
        '<script type="text/javascript">
            var selectGroup = $$(\'select.wp_init\');
            selectGroup.each(function(item){
                var val = item.readAttribute(\'rel\');
                var opts = item.options;
                var len = item.options.length;
                k = 0;
                for (var i = 0; i < len; i++) {
                    if (opts[i].value == val) {
                        k = i;
                        break;
                    }
                }
                opts[k].selected = true;
                item.setAttribute(\'title\', opts[k].text);
            });
        </script>';
        return $html;
    }
}

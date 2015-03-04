<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.idealiagroup.com/magento-ext-license.html
 *
 * @category   IG
 * @package    IG_Lightbox2
 * @copyright  Copyright (c) 2011-2012 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://www.idealiagroup.com/magento-ext-license.html
 */
 
class IG_Lightbox2_Model_Adminhtml_System_Config_Source_Colorbox_Theme
{
	protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options)
		{
			$this->_options[] = array(
				'label' => Mage::helper('ig_lightbox2')->__('Theme 1'),
				'value' => 'theme1'
			);
			$this->_options[] = array(
				'label' => Mage::helper('ig_lightbox2')->__('Theme 2'),
				'value' => 'theme2'
			);
			$this->_options[] = array(
				'label' => Mage::helper('ig_lightbox2')->__('Theme 3'),
				'value' => 'theme3'
			);
			$this->_options[] = array(
				'label' => Mage::helper('ig_lightbox2')->__('Theme 4'),
				'value' => 'theme4'
			);
			$this->_options[] = array(
				'label' => Mage::helper('ig_lightbox2')->__('Theme 5'),
				'value' => 'theme5'
			);
        }
		
        return $this->_options;
    }
}
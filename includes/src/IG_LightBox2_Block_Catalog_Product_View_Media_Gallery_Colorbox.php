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
 * @package    IG_LightBox2
 * @copyright  Copyright (c) 2011-2012 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://www.idealiagroup.com/magento-ext-license.html
 */
 
class IG_LightBox2_Block_Catalog_Product_View_Media_Gallery_Colorbox extends IG_LightBox2_Block_Catalog_Product_View_Media_Gallery
{
	protected $_type = 'colorbox';
	protected $_jsList = array(
		'ig_lightbox2/colorbox/colorbox/jquery.colorbox-min.js',
	);
	protected $_cssList = array();
	
	protected function getParams()
	{
		$return = parent::getParams();
		unset($return['theme']);
		
		$return['rel'] = 'ig_lightbox2';
		if (!$return['left']) unset($return['left']);
		if (!$return['right']) unset($return['right']);
		if (!$return['bottom']) unset($return['bottom']);
		if (!$return['top']) unset($return['top']);
		
		return $return;
	}
	
	protected function _prepareLayout()
	{
		$this->_cssList[] = 'ig_lightbox2/colorbox/'.$this->getParam('theme').'/colorbox.css';
		
		return parent::_prepareLayout();
	}
}

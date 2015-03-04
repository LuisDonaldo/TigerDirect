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
 
abstract class IG_LightBox2_Block_Catalog_Product_View_Media_Gallery extends Mage_Catalog_Block_Product_View_Media
{
	const XML_PATH_LIGHTBOX2_GENERAL_TYPE = 'ig_lightbox2/general/type';
	const XML_PATH_LIGHTBOX2_GENERAL_MAXWIDTH = 'ig_lightbox2/general/max_width';
	const XML_PATH_LIGHTBOX2_GENERAL_MAXHEIGHT = 'ig_lightbox2/general/max_height';
	
	const XML_PATH_LIGHTBOX2_GENERAL_IMGWIDTH = 'ig_lightbox2/general/img_width';
	const XML_PATH_LIGHTBOX2_GENERAL_IMGHEIGHT = 'ig_lightbox2/general/img_height';
	const XML_PATH_LIGHTBOX2_GENERAL_IMGFRAME = 'ig_lightbox2/general/img_frame';
	const XML_PATH_LIGHTBOX2_GENERAL_THUWIDTH = 'ig_lightbox2/general/thu_width';
	const XML_PATH_LIGHTBOX2_GENERAL_THUHEIGHT = 'ig_lightbox2/general/thu_height';
	const XML_PATH_LIGHTBOX2_GENERAL_THUFRAME = 'ig_lightbox2/general/thu_frame';
	
	protected $_type = '';
	protected $_jsList = array();
	protected $_cssList = array();
	
	/**
	 * Get image max width
	 *
	 * @return int
	 */
	protected function getMaxWidth()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_MAXWIDTH));
	}
	
	/**
	 * Get image max height
	 *
	 * @return int
	 */
	protected function getMaxHeight()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_MAXHEIGHT));
	}
	
	/**
	 * Get image width
	 *
	 * @return int
	 */
	protected function getImageWidth()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_IMGWIDTH));
	}
	
	/**
	 * Get image height
	 *
	 * @return int
	 */
	protected function getImageHeight()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_IMGHEIGHT));
	}
	
	/**
	 * Display image frame
	 *
	 * @return int
	 */
	protected function getImageFrame()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_IMGFRAME) ? true : false;
	}
	
	/**
	 * Get image width
	 *
	 * @return int
	 */
	protected function getThumbnailWidth()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_THUWIDTH));
	}
	
	/**
	 * Get image height
	 *
	 * @return int
	 */
	protected function getThumbnailHeight()
	{
		return intval(Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_THUHEIGHT));
	}
	
	/**
	 * Display thumbnail frame
	 *
	 * @return int
	 */
	protected function getThumbnailFrame()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_THUFRAME) ? true : false;
	}
	
	/**
	 * Get lightbox type to be used
	 *
	 * @return string
	 */
	protected function _getLightboxType()
	{
		return Mage::getStoreConfig(self::XML_PATH_LIGHTBOX2_GENERAL_TYPE);
	}
	
	/**
	 * Get a configuration parameter
	 *
	 * @param string $key
	 * @return string
	 */
	protected function getParam($key)
	{
		return Mage::getStoreConfig('ig_lightbox2/type-'.$this->_type.'/'.$key);
	}
	
	/**
	 * Get a configuration parameters as array
	 *
	 * @return array
	 */
	protected function getParams()
	{
		$return = array();
		$res = Mage::getStoreConfig('ig_lightbox2/type-'.$this->_type);
		
		foreach ($res as $k => $v)
		{
			if (is_numeric($v))
				$return[$k] = floatval($v);
			else
				$return[$k] = $v;
		}
		
		return $return;
	}
	
	protected function _prepareLayout()
	{
		if ($this->_getLightboxType() != $this->_type)
			return parent::_prepareLayout();
		
		foreach ($this->_jsList as $js)
			$this->getLayout()->getBlock('head')->addItem('skin_js', $js);
		
		foreach ($this->_cssList as $css)
			$this->getLayout()->getBlock('head')->addCss($css);
		
		return parent::_prepareLayout();
	}
}

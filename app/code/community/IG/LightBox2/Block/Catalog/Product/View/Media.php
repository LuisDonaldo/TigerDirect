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
 
class IG_LightBox2_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
	const XML_PATH_LIGHTBOX2_GENERAL_TYPE = 'ig_lightbox2/general/type';
	
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
	 * Return gallery block name
	 *
	 * @return string
	 */
	public function getGalleryBlockName()
	{
		return 'gallery-'.$this->_getLightboxType();
	}
}

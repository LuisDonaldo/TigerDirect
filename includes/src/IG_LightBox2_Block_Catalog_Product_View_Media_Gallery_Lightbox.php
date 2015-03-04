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
 
class IG_LightBox2_Block_Catalog_Product_View_Media_Gallery_Lightbox extends IG_LightBox2_Block_Catalog_Product_View_Media_Gallery
{
	protected $_type = 'lightbox';
	protected $_jsList = array(
		'ig_lightbox2/lightbox/js/jquery.lightbox-0.5.pack.js',
	);
	protected $_cssList = array(
		'ig_lightbox2/lightbox/css/jquery.lightbox-0.5.css'
	);
	
	protected function getParams()
	{
		$return = parent::getParams();
		$return['imageLoading'] = $this->getSkinUrl('ig_lightbox2/lightbox/images/lightbox-ico-loading.gif');
		$return['imageBtnClose'] = $this->getSkinUrl('ig_lightbox2/lightbox/images/lightbox-btn-close.gif');
		$return['imageBtnPrev'] = $this->getSkinUrl('ig_lightbox2/lightbox/images/lightbox-btn-prev.gif');
		$return['imageBtnNext'] = $this->getSkinUrl('ig_lightbox2/lightbox/images/lightbox-btn-next.gif');
		$return['imageBlank'] = $this->getSkinUrl('ig_lightbox2/lightbox/images/lightbox-blank.gif');
		
		return $return;
	}
}
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
 
class IG_LightBox2_Block_Catalog_Product_View_Media_Gallery_Prettyphoto extends IG_LightBox2_Block_Catalog_Product_View_Media_Gallery
{
	protected $_type = 'prettyphoto';
	protected $_jsList = array(
		'ig_lightbox2/prettyphoto/js/jquery.prettyPhoto.js',
	);
	protected $_cssList = array(
		'ig_lightbox2/prettyphoto/css/prettyPhoto.css'
	);
	
	protected function getParams()
	{
		$return = parent::getParams();
		$return['social_tools'] = '';
		
		return $return;
	}
}

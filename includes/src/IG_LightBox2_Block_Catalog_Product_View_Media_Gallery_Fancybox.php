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
 
class IG_LightBox2_Block_Catalog_Product_View_Media_Gallery_Fancybox extends IG_LightBox2_Block_Catalog_Product_View_Media_Gallery
{
	protected $_type = 'fancybox';
	protected $_jsList = array(
		'ig_lightbox2/fancybox/jquery.fancybox-1.3.4.pack.js',
		'ig_lightbox2/fancybox/jquery.mousewheel-3.0.4.pack.js',
		'ig_lightbox2/fancybox/jquery.easing-1.3.pack.js',
	);
	protected $_cssList = array(
		'ig_lightbox2/fancybox/jquery.fancybox-1.3.4.css'
	);
}

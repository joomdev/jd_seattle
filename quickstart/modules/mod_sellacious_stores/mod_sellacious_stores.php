<?php
/**
 * @version     1.6.1
 * @package     Sellacious Seller Stores Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

JLoader::register('ModSellaciousStores', __DIR__ . '/helper.php');

$helper = SellaciousHelper::getInstance();

/** @var  Joomla\Registry\Registry $params */
$class_sfx             = $params->get('class_sfx', '');
$limit                 = $params->get('total_records', '50');
$category_id           = $params->get('category_id', '0');
$display_ratings       = $params->get('display_ratings', '1');
$display_product_count = $params->get('display_product_count', '1');
$layout                = $params->get('layout', 'grid');
$autoplayopt           = $params->get('autoplay', '0');
$autoplayspeed         = $params->get('autoplayspeed', '3000');
$gutter                = $params->get('gutter', '8');
$responsive0to500      = $params->get('responsive0to500', '1');
$responsive500         = $params->get('responsive500', '2');
$responsive992         = $params->get('responsive992', '3');
$responsive1200        = $params->get('responsive1200', '4');
$responsive1400        = $params->get('responsive1400', '4');
$ordering              = $params->get('ordering', '3');
$orderBy               = $params->get('orderby', 'DESC');

$stores = $helper->seller->getModStores($params, 'stores');

if (empty($stores))
{
	return;
}

require JModuleHelper::getLayoutPath('mod_sellacious_stores', $layout);

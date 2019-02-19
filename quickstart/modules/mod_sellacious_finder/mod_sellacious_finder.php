<?php
/**
 * @version     1.6.1
 * @package     Sellacious Finder Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

/** @var  Joomla\Registry\Registry  $params */
$helper = SellaciousHelper::getInstance();

$integration             = $params->get('integration', 'sellacious');
$search_layout           = $params->get('search_layout', 'default');
$full_width              = $params->get('full_width', '1');
$input_width             = $params->get('input_width', '200');
$display_label           = $params->get('display_label', '1');
$label_value             = $params->get('label_value', 'Search');
$finder_placeholder      = $params->get('finder_placeholder', 'Search..');
$button_type             = $params->get('button_type', 'icon');
$button_text             = $params->get('button_text', 'Search');
$button_position         = $params->get('button_position', 'right');
$show_product_image      = $params->get('show_product_image', '1');
$show_product_category   = $params->get('show_product_category', '1');
$show_product_price      = $params->get('show_product_price', '1');
$category_redirect       = $params->get('category_result_redirect', '1');
$show_category_image     = $params->get('show_category_image', '1');
$show_category_results   = $params->get('show_category_results', '1');
$seller_redirect         = $params->get('seller_result_redirect', '1');
$show_seller_image       = $params->get('show_seller_image', '1');
$show_seller_results     = $params->get('show_seller_results', '1');
$categories_redirect     = $params->get('categories_result_redirect', '1');
$show_categories_image   = $params->get('show_categories_image', '1');
$show_categories_results = $params->get('show_categories_results', '1');
$sellers_redirect        = $params->get('sellers_result_redirect', '1');
$show_sellers_image      = $params->get('show_sellers_image', '1');
$show_sellers_results    = $params->get('show_sellers_results', '1');
$search_order            = (array) $params->get('ordering', array('product' => 1, 'category' => 2, 'seller' => 3, 'categories' => 4, 'sellers' => 5));

asort($search_order);

if (!$show_category_results)
{
	unset($search_order['category']);
}

if (!$show_seller_results)
{
	unset($search_order['seller']);
}

if (!$show_categories_results)
{
	unset($search_order['categories']);
}


if (!$show_sellers_results)
{
	unset($search_order['sellers']);
}

if ($integration == 'finder')
{
	$ajaxUrl = JRoute::_('index.php?option=com_sellacious&task=search.suggest&format=json&tmpl=component');
}
else
{
	$ajaxUrl = JRoute::_('index.php?option=com_sellacious&task=search.query&format=json&tmpl=component');
}

require JModuleHelper::getLayoutPath('mod_sellacious_finder', $search_layout);

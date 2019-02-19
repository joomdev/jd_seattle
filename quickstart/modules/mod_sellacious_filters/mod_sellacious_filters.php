<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

jimport('sellacious.loader');

if (class_exists('SellaciousHelper'))
{
	require_once __DIR__ . '/helper.php';

	$isValid = ModSellaciousFiltersHelper::validate();

	if ($isValid)
	{
		/** @var  Joomla\Registry\Registry  $params */
		$helper      = SellaciousHelper::getInstance();
		$app         = JFactory::getApplication();
		$class_sfx   = $params->get('class_sfx', '');
		$state       = ModSellaciousFiltersHelper::getModel()->getState();
		$ordering    = $helper->config->get('filter_options_ordering', '');
		$ordering    = explode(", ", $ordering);
		$categories  = ModSellaciousFiltersHelper::getCategories();
		$filters     = ModSellaciousFiltersHelper::getFilters();
		$offers      = ModSellaciousFiltersHelper::getOffers();
		$shopList    = ModSellaciousFiltersHelper::getShopList();
		$showAllFor  = $helper->config->get('show_all_for');
		$showMoreFor = $helper->config->get('show_more_for');

		require JModuleHelper::getLayoutPath('mod_sellacious_filters', 'filter');
	}
}

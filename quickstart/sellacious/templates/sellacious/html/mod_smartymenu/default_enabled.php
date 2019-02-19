<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/** @var Registry $params */
$active_uri = JUri::getInstance()->toString(array('path', 'query', 'fragment'));

foreach ($parent_menus as $parent_menu)
{
	$menu_params = new Registry($parent_menu->params);
	$class       = $menu_params->get('menu-anchor_css', 'gear');

	$link = '#';

	if ($parent_menu->link)
	{
		$link = $parent_menu->link;
	}

	$menu->addChild(new JSmartyMenuNode($parent_menu->title, $link, 'class:' . $class), true);
	$childMenus = $helper->getMenus($params, $parent_menu->id);

	foreach ($childMenus as $childMenu)
	{
		$menu_params = new Registry($childMenu->params);
		$class       = $menu_params->get('menu-anchor_css', '');

		$link   = '#';
		$active = false;

		if ($childMenu->link)
		{
			$link   = $childMenu->link;
			$active = $active_uri == JUri::base(true) . '/' . $link;
		}

		$menu->addChild(new JSmartyMenuNode($childMenu->title, $link, 'class:' . $class, $active));
	}

	$menu->getParent();
}

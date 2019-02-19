<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Helper for mod_breadcrumbs
 */
class ModBreadCrumbsHelper
{
	/**
	 * Retrieve breadcrumb items
	 *
	 * @param  \Joomla\Registry\Registry  &$params module parameters
	 *
	 * @return  array
	 */
	public static function getList(&$params)
	{
		$items      = array();
		$active_uri = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
		$base_uri   = rtrim(JUri::base(true), '/') . '/';
		$url        = str_replace($base_uri, '', $active_uri);

		/** @var stdClass $menu_item */
		$app       = JFactory::getApplication();
		$menu      = $app->getMenu();
		$menu_item = $menu->getItems(array('link', 'access'), array($url, 0), true);

		try
		{
			// We assume that only one such link exists, duplicate links in this table may break this logic
			if (isset($menu_item->id))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('a.id, a.title, a.title AS name, a.lft, a.rgt, a.parent_id')
					  ->from('#__menu a')
					  ->where('a.published = 1')
					  ->where('a.id IN (' . implode(', ', $db->q($menu_item->tree)) . ')')
					  ->where('a.parent_id > 0')
					  ->order('a.lft');

				$db->setQuery($query);

				$items = $db->loadObjectList();
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $items;
	}
}

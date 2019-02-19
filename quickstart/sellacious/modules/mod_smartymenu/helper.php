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

/**
 * Helper for mod_menu
 *
 * @since  1.2.1
 */
class ModSmartyMenuHelper
{
	/**
	 * Get a list of the available menus items.
	 *
	 * @param   Registry  $params     Menu params
	 * @param   int       $parent_id  If provided then only the menu items under the said parent will be returned.
	 *
	 * @return  array  An array of the available menus (from the menu types table).
	 *
	 * @since   1.2.1
	 */
	public static function getMenus($params, $parent_id = null)
	{
		$helper = SellaciousHelper::getInstance();
		$db     = JFactory::getDbo();
		$type   = $helper->config->get('main_menutype');
		$query  = $db->getQuery(true);
		$items  = array();

		if ($type && $type !== '*')
		{
			$query->select('a.*')
				->from('#__menu AS a')
				->where('a.menutype = ' . $db->q($type))
				->where('a.published = 1')
				->order('a.lft');

			if ($parent_id)
			{
				$query->where('parent_id = ' . (int) $parent_id);
			}
			else
			{
				$query->where('a.level = 1');
			}

			$db->setQuery($query);

			$items = $db->loadObjectList();
		}

		if (!$items)
		{
			$items     = static::loadPreset();
			$parent_id = $parent_id ?: 1;

			foreach ($items as &$item)
			{
				if ($item->parent_id != $parent_id)
				{
					$item = null;
				}
			}

			$items = array_filter($items);
		}

		return static::filterMenus($items);
	}

	/**
	 * Load menu items list from the xml
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 */
	public static function loadPreset()
	{
		static $items = null;

		if (!isset($items))
		{
			// Create menu items for back-office
			$items = array();
			$file  = JPATH_SELLACIOUS . '/menu.xml';

			if (file_exists($file))
			{
				$menu = simplexml_load_file($file);

				if ($menu instanceof SimpleXMLElement)
				{
					static::createMenu($menu, array(), $items);
				}
			}
		}

		return $items;
	}

	/**
	 * Create the menu for sellacious Backoffice
	 *
	 * @param   SimpleXMLElement  $menu       The menu node to process
	 * @param   array             $inherited  The inherited attributes
	 * @param   array             $items      List to be populated
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public static function createMenu(SimpleXMLElement $menu, array $inherited = array(), array &$items)
	{
		static $id = 1;

		if ($menu instanceof SimpleXMLElement)
		{
			// Merge given attributes from parent menu item into the current
			$menuAttr   = $menu->attributes();
			$properties = $inherited;

			// Merge doesn't work with attributes iterator
			foreach ($menuAttr as $key => $property)
			{
				$properties[$key] = (string) $property;
			}

			$attributes = $menu->xpath('attributes');
			$attributes = array_merge($properties, array_map('strval', (array) $attributes[0]));

			// If we have an id use it, else create the menu with given properties
			if (!empty($attributes['id']))
			{
				$id = $attributes['id'];
			}
			else
			{
				$attributes['id'] = ++$id;

				$items[$id] = (object) $attributes;
			}

			$properties['parent_id'] = $attributes['id'];
			$properties['level']     = (isset($attributes['level']) ? $attributes['level'] : '0') + 1;

			// Now create the child menu items for this menu item
			foreach ($menu->xpath('menu') as $child)
			{
				static::createMenu($child, $properties, $items);
			}
		}
	}

	/**
	 * Filter out the menu items which are not allowed to current user
	 *
	 * @param   stdClass  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.1
	 */
	protected static function filterMenus($items)
	{
		$menus  = array();
		$helper = SellaciousHelper::getInstance();

		foreach ($items as $item)
		{
			$item->title = JText::_($item->title);

			$params  = new Registry($item->params);
			$actions = (array) $params->get('menu-access');

			if (empty($actions))
			{
				$menus[] = $item;
			}
			else
			{
				foreach ($actions as $action)
				{
					if ($helper->access->check($action))
					{
						$menus[] = $item;

						break;
					}
				}
			}
		}

		return $menus;
	}
}

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Element;

/**
 * Special Category entity import class
 *
 * @since   1.4.7
 */
class SplCategory
{
	/**
	 * @var   array
	 *
	 * @since   1.4.7
	 */
	protected static $cache = array();

	/**
	 * Method to get category id from title. The title can be a '/' separated hierarchy of individual titles
	 *
	 * @param   string  $catName  The title (or hierarchy of titles) to find
	 * @param   bool    $create   Whether to create new if it does not exist
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function getId($catName, $create = false)
	{
		// No leading or trailing slashes
		$catName = trim($catName, '/ ');

		if (!isset(static::$cache[$catName]))
		{
			if (false === strpos($catName, '/'))
			{
				// This is first level category
				$title    = $catName;
				$parentId = 1;
			}
			else
			{
				// Find out which category to find/create under which parent id
				$parts    = preg_split('#(?<!\\\)/#', $catName, -1, PREG_SPLIT_NO_EMPTY);
				$parts    = $parts ? array_map('stripslashes', $parts) : array();
				$title    = array_pop($parts);
				$parent   = implode('/', $parts);
				$parentId = static::getId($parent, $create);
			}

			// Since it's not cached yet, we'd lookup in the db
			$helper = \SellaciousHelper::getInstance();
			$filter = array('list.select' => 'a.id', 'parent_id' => $parentId, 'title' => $title);
			$catId  = $helper->splCategory->loadResult($filter);

			// Not found. Can we attempt to create?
			if (!$catId && $create)
			{
				$category = new \stdClass;

				$category->title     = $title;
				$category->parent_id = $parentId;

				$catId = static::create($category);
			}

			// Store in cache for later
			static::$cache[$catName] = $catId;
		}

		return static::$cache[$catName];
	}

	/**
	 * Internal method to create a category from title.
	 *
	 * @param   \stdClass  $category
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected static function create($category)
	{
		static $increment = 0;

		$me     = \JFactory::getUser();
		$db     = \JFactory::getDbo();
		$isoNow = \JFactory::getDate()->toSql();
		$alias  = \JApplicationHelper::stringURLSafe($category->title);
		$alias  = $alias ?: \JApplicationHelper::stringURLSafe($isoNow) . '-' . ++$increment;

		$category->alias      = $alias;
		$category->state      = 1;
		$category->created    = $isoNow;
		$category->created_by = $me->id;

		$db->insertObject('#__sellacious_splcategories', $category, 'id');

		return $category->id;
	}
}

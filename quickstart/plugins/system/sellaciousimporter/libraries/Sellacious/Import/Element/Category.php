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
 * Category entity import class
 *
 * @since   1.4.7
 */
class Category
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
	 * @param   string  $type     The category type
	 * @param   bool    $create   Whether to create new if it does not exist
	 * @param   int[]   $fields   Specification fields
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function getId($catName, $type, $create = false, $fields = array())
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
				$parts    = $parts ?: array();
				$title    = stripslashes(array_pop($parts));
				$parent   = implode('/', $parts);
				$parentId = static::getId($parent, $type, $create);
			}

			// Since it's not cached yet, we'd lookup in the db
			$helper = \SellaciousHelper::getInstance();
			$filter = array('list.select' => 'a.id', 'parent_id' => $parentId, 'title' => $title);
			$catId  = $helper->category->loadResult($filter);

			// Not found. Can we attempt to create?
			if (!$catId && $create)
			{
				$category = new \stdClass;

				$category->title      = $title;
				$category->parent_id  = $parentId;
				$category->type       = $type;

				$catId = static::create($category, $fields);
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
	 * @param   int[]      $fields
	 *
	 * @return  int
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected static function create($category, $fields = array())
	{
		static $increment = 0;

		$me     = \JFactory::getUser();
		$db     = \JFactory::getDbo();
		$isoNow = \JFactory::getDate()->toSql();
		$alias  = \JApplicationHelper::stringURLSafe($category->title);
		$alias  = $alias ?: \JApplicationHelper::stringURLSafe($isoNow) . '-' . ++$increment;

		$helper  = \SellaciousHelper::getInstance();
		$pFields = $helper->category->getFields($category->parent_id, array('core', 'variant'), true);
		$nFields = array_diff($fields, $pFields);

		$category->alias       = $alias;
		$category->state       = 1;
		$category->created     = $isoNow;
		$category->created_by  = $me->id;
		$category->core_fields = json_encode($nFields);

		$db->insertObject('#__sellacious_categories', $category, 'id');

		return $category->id;
	}

	/**
	 * Method to add specification fields to a category.
	 * If the field already exists it will not be added, else will be added as core field
	 *
	 * @param   int    $catId
	 * @param   int[]  $fields
	 *
	 * @return  bool
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function addFields($catId, $fields)
	{
		$db      = \JFactory::getDbo();
		$helper  = \SellaciousHelper::getInstance();
		$eFields = $helper->category->getFields($catId, array('core', 'variant'), true);
		$nFields = array_diff($fields, $eFields);

		// If we do not have any fields to add, skip updating
		if ($nFields)
		{
			$cFields = $helper->category->loadResult(array('list.select' => 'a.core_fields', 'id' => $catId));
			$cFields = (array) json_decode($cFields, true);
			$cFields = array_unique(array_merge($cFields, $nFields));

			$category = new \stdClass;

			$category->id          = $catId;
			$category->core_fields = json_encode($cFields);

			if ($db->updateObject('#__sellacious_categories', $category, array('id')))
			{
				throw new \Exception(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_CATEGORY_ADD_SPECIFICATION_FIELDS_FAILED', $catId, implode(', ', $nFields)));
			}
		}

		return true;
	}
}

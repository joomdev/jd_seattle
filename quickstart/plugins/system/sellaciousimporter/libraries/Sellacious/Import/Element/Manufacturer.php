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
 * Manufacturer entity class
 *
 * @since   1.4.7
 */
class Manufacturer
{
	/**
	 * @var   array
	 *
	 * @since   1.4.7
	 */
	protected static $cache = array();

	/**
	 * Method to get manufacturer userid from business name.
	 *
	 * @param   \stdClass  $manufacturer  The manufacturer object
	 * @param   string     $key           The field to be used to find matching user
	 *
	 * @return  int
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function getUid($manufacturer, $key)
	{
		$value = $manufacturer->$key;

		if (!isset(static::$cache[$value]))
		{
			try
			{
				$result  = 0;

				if ($key == 'code')
				{
					$filters = array('list.select' => 'a.user_id', 'code' => $value);
				}
				elseif ($key == 'company')
				{
					$filters = array('list.select' => 'a.user_id', 'title' => $value);
				}
				elseif ($key == 'name' || $key == 'username' || $key == 'email')
				{
					$filters = array('list.select' => 'a.id', 'list.from' => '#__users', $key => $value);
				}
				else
				{
					$filters = null;
				}

				if ($filters)
				{
					$helper = \SellaciousHelper::getInstance();
					$result = $helper->manufacturer->loadResult($filters);
				}

				static::$cache[$value] = $result;
			}
			catch (\Exception $e)
			{
				throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_MISSING_MANUFACTURERS', $value, $e->getMessage()));
			}
		}

		return static::$cache[$value];
	}

	/**
	 * Internal method to create a manufacturer record
	 *
	 * @param   \stdClass  $record  The manufacturer object
	 * @param   bool       $update  Whether to update
	 *
	 * @return  int  The manufacturer user id
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function create($record, $update = false)
	{
		static $cache = array();

		if (empty($record->user_id))
		{
			try
			{
				$record->user_id = User::create($record->name, $record->username, $record->email);
			}
			catch (\Exception $e)
			{
				throw new \Exception(\JText::sprintf('COM_IMPORTER_IMPORT_ERROR_MISSING_MANUFACTURER_ACCOUNT', $e->getMessage()));
			}
		}

		// Once we process a seller uid, we won't do it again in subsequent iterations
		if (isset($cache[$record->user_id]))
		{
			return $record->user_id;
		}

		$helper = \SellaciousHelper::getInstance();
		$db     = \JFactory::getDbo();

		// Create the manufacturer record
		$recordId = $helper->manufacturer->loadResult(array('list.select' => 'a.id', 'user_id' => $record->user_id));

		if (!$recordId || $update)
		{
			$now = \JFactory::getDate();
			$me  = \JFactory::getUser();
			$obj = new \stdClass;

			$obj->user_id     = $record->user_id;
			$obj->title       = $record->company;
			$obj->code        = $record->code;

			if ($recordId)
			{
				$obj->id          = $recordId;
				$obj->modified    = $now->toSql();
				$obj->modified_by = $me->id;

				$saved = $db->updateObject('#__sellacious_manufacturers', $obj, array('id'));
			}
			else
			{
				$category = static::getCategory();

				$obj->id          = null;
				$obj->category_id = $category->id;
				$obj->state       = 1;
				$obj->created     = $now->toSql();
				$obj->created_by  = $me->id;

				$saved = $db->insertObject('#__sellacious_manufacturers', $obj, 'id');

				// Add to appropriate user groups as per category
				foreach ($category->usergroups as $usergroup)
				{
					\JUserHelper::addUserToGroup($record->user_id, $usergroup);
				}
			}

			if (!$saved)
			{
				throw new \Exception(\JText::_('COM_IMPORTER_IMPORT_ERROR_MISSING_MANUFACTURER'));
			}
		}

		$cache[$record->user_id] = true;

		return $record->user_id;
	}

	/**
	 * Get a default manufacturer category
	 *
	 * @return  \stdClass
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected static function getCategory()
	{
		static $cache;

		if (!isset($cache))
		{
			$helper = \SellaciousHelper::getInstance();

			$filter   = array('list.select' => 'a.id, a.usergroups', 'type' => 'manufacturer', 'is_default' => 1);
			$category = $helper->category->loadObject($filter);

			if (!$category)
			{
				$filter   = array('list.select' => 'a.id, a.usergroups', 'type' => 'manufacturer');
				$category = $helper->category->loadObject($filter);
			}

			if (!$category)
			{
				throw new \Exception(\JText::_('COM_SELLACIOUS_IMPORT_ERROR_MISSING_MANUFACTURER_CATEGORY'));
			}

			$category->usergroups = (array) json_decode($category->usergroups, true);

			$cache = $category;
		}

		return $cache;
	}
}

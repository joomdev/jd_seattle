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
 * Seller entity class
 *
 * @since   1.4.7
 */
class Seller
{
	/**
	 * @var   array
	 *
	 * @since   1.4.7
	 */
	protected static $cache = array();

	/**
	 * Method to get seller userid from business name.
	 *
	 * @param   \stdClass  $seller  The seller object
	 * @param   string     $key     The field to be used to find matching user
	 *
	 * @return  int
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public static function getUid($seller, $key)
	{
		$value = $seller->$key;

		if (!isset(static::$cache[$value]))
		{
			try
			{
				$result  = 0;

				if ($key == 'code')
				{
					$filters = array('list.select' => 'a.user_id', 'code' => $value);
				}
				elseif ($key == 'business_name')
				{
					$filters = array('list.select' => 'a.user_id', 'title' => $value);
				}
				elseif ($key == 'store_name')
				{
					$filters = array('list.select' => 'a.user_id', 'store_name' => $value);
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
					$result = $helper->seller->loadResult($filters);
				}

				static::$cache[$value] = $result;
			}
			catch (\Exception $e)
			{
				throw new \Exception(\JText::sprintf('COM_IMPORTER_IMPORT_ERROR_MISSING_SELLERS', $value, $e->getMessage()));
			}
		}

		return static::$cache[$value];
	}

	/**
	 * Internal method to create a seller record
	 *
	 * @param   \stdClass  $record  The seller object
	 * @param   bool       $update  Whether to update
	 *
	 * @return  int  The seller user id
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
				throw new \Exception(\JText::sprintf('COM_IMPORTER_IMPORT_ERROR_MISSING_SELLER_ACCOUNT', $e->getMessage()));
			}
		}

		// Once we process a seller uid, we won't do it again in subsequent iterations
		if (isset($cache[$record->user_id]))
		{
			return $record->user_id;
		}

		$helper = \SellaciousHelper::getInstance();
		$db     = \JFactory::getDbo();

		// Bother about profile record only if needed
		if ($record->mobile || $record->website)
		{
			$profileId = $helper->profile->loadResult(array('list.select' => 'a.id', 'user_id' => $record->user_id));

			if (!$profileId || $update)
			{
				$p = new \stdClass;

				$p->id      = $profileId;
				$p->user_id = $record->user_id;
				$p->mobile  = $record->mobile;
				$p->website = $record->website;

				if ($profileId)
				{
					$db->updateObject('#__sellacious_profiles', $p, array('id'));
				}
				else
				{
					$db->insertObject('#__sellacious_profiles', $p, 'id');
				}
			}
		}

		// Create the seller record
		$recordId = $helper->seller->loadResult(array('list.select' => 'a.id', 'user_id' => $record->user_id));

		if (!$recordId || $update)
		{
			$now = \JFactory::getDate();
			$me  = \JFactory::getUser();
			$obj = new \stdClass;

			$obj->user_id       = $record->user_id;
			$obj->title         = $record->business_name;
			$obj->code          = $record->code;
			$obj->store_name    = $record->store_name;
			$obj->store_address = $record->store_address;
			$obj->currency      = $record->currency;

			if ($recordId)
			{
				$obj->id          = $recordId;
				$obj->modified    = $now->toSql();
				$obj->modified_by = $me->id;

				$saved = $db->updateObject('#__sellacious_sellers', $obj, array('id'));
			}
			else
			{
				$category = static::getCategory();

				$obj->id          = null;
				$obj->category_id = $category->id;
				$obj->state       = 1;
				$obj->created     = $now->toSql();
				$obj->created_by  = $me->id;

				$saved = $db->insertObject('#__sellacious_sellers', $obj, 'id');

				// Add to appropriate user groups as per category
				foreach ($category->usergroups as $usergroup)
				{
					\JUserHelper::addUserToGroup($record->user_id, $usergroup);
				}
			}

			if (!$saved)
			{
				throw new \Exception(\JText::_('COM_IMPORTER_IMPORT_ERROR_MISSING_SELLER'));
			}
		}

		$cache[$record->user_id] = true;

		return $record->user_id;
	}

	/**
	 * Get a default seller category
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

			$filter   = array('list.select' => 'a.id, a.usergroups', 'type' => 'seller', 'is_default' => 1);
			$category = $helper->category->loadObject($filter);

			if (!$category)
			{
				$filter   = array('list.select' => 'a.id, a.usergroups', 'type' => 'seller');
				$category = $helper->category->loadObject($filter);
			}

			if (!$category)
			{
				throw new \Exception(\JText::_('COM_IMPORTER_IMPORT_ERROR_MISSING_SELLER_CATEGORY'));
			}

			$category->usergroups = (array) json_decode($category->usergroups, true);

			$cache = $category;
		}

		return $cache;
	}
}

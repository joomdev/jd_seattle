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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious helper.
 *
 * @since  3.0
 */
class SellaciousHelperClient extends SellaciousHelperBase
{
	/**
	 * Get the client category for the given user
	 *
	 * @param   int   $userId
	 * @param   bool  $useDefault
	 * @param   bool  $full
	 *
	 * @return  int|stdClass
	 *
	 * @since   1.5.1
	 */
	public function getCategory($userId, $useDefault = false, $full = false)
	{
		$filter   = array(
			'list.select' => 'c.*',
			'list.join'   => array(array('inner', '#__sellacious_categories AS c ON c.id = a.category_id')),
			'user_id'     => $userId,
		);
		$category = $this->loadObject($filter);

		if (!$category && $useDefault)
		{
			$category = $this->helper->category->getDefault('client');
		}

		return $category ? ($full ? $category : $category->id) : null;
	}

	/**
	 * Get supported client types list
	 *
	 * @param   bool  $associative
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getTypes($associative = false)
	{
		$key = __METHOD__;

		if (empty($this->cache[$key]))
		{
			$db    = $this->db;
			$query = $db->getQuery(true);

			$query->select($db->qn(array('a.client_type', 'a.title'), array('value', 'text')))
				  ->from($db->qn('#__sellacious_client_types', 'a'))
				  ->where($db->qn('state') . ' = 1');

			$db->setQuery($query);

			try
			{
				$types = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				$types = array();

				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}

			$this->cache[$key] = $types;
		}

		if (!$associative)
		{
			return $this->cache[$key];
		}

		if (empty($this->cache[$key . '.assoc']))
		{
			$this->cache[$key . '.assoc'] = ArrayHelper::getColumn($this->cache[$key], 'text', 'value');
		}

		return $this->cache[$key . '.assoc'];
	}

	/**
	 * Create a default client record for the given user id
	 *
	 * @param   int  $userId
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function create($userId)
	{
		$cat   = $this->helper->category->getDefault('client', 'a.id');
		$table = $this->getTable();
		$keys  = array('user_id' => $userId);
		$table->load($keys);

		if (!$table->get('id'))
		{
			$table->set('state', 1);
		}

		$table->set('user_id', $userId);

		if ($cat)
		{
			$table->set('category_id', $cat->id);
		}

		$table->check();
		$table->store();

		$this->helper->user->setUserGroups($userId);
	}

	/**
	 * Fetch the list of authorised user accounts for selected client account
	 *
	 * @param   int   $clientUid  User id for the client account for which to fetch the information
	 * @param   bool  $idOnly     Whether to load only the authorised user ids or full user record
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 */
	public function getAuthorised($clientUid, $idOnly = true)
	{
		$filter = array(
			'list.select' => $idOnly ? 'u.id' : 'u.id, u.name, u.email, a.credit_limit',
			'list.from'   => '#__sellacious_client_authorised',
			'list.join'   => array(
				array('inner', '#__users u ON u.id = a.user_id'),
			),
			'list.where'  => 'a.client_uid = ' . (int) $clientUid,
		);

		$value = $idOnly ? $this->loadColumn($filter) : $this->loadAssocList($filter);

		return (array) $value;
	}

	/**
	 * Set the list of authorised user accounts for selected client account
	 *
	 * @param   int    $client_uid  User id for the client account for which to fetch the information
	 * @param   array  $auth_users  List of user ids to be added
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function setAuthorised($client_uid, array $auth_users)
	{
		$current = $this->getAuthorised($client_uid, false);

		$current = ArrayHelper::getColumn($current, 'credit_limit', 'id');
		$allUid  = ArrayHelper::getColumn($auth_users, 'id');

		$remove  = array_diff(array_keys($current), $allUid);

		$dispatcher = $this->helper->core->loadPlugins('sellacious');

		if (count($remove))
		{
			$query = $this->db->getQuery(true);
			$query->delete('#__sellacious_client_authorised')
				->where('client_uid = ' . (int) $client_uid)
				->where('user_id IN (' . implode(', ', array_map('intval', $remove)) . ')');

			if ($this->db->setQuery($query)->execute())
			{
				// Todo: Trigger plugin event
			}
		}

		if (count($auth_users))
		{
			foreach ($auth_users as $auth_user)
			{
				$uid   = $auth_user['id'];
				$limit = $auth_user['credit_limit'];
				$query = $this->db->getQuery(true);

				$auth_user['credit_limit_old'] = isset($current[$uid]) ? $current[$uid] : 0;
				$auth_user['client_uid']       = $client_uid;
				$auth_user['user_id']          = $uid;

				// Is an insert needed?
				if (!array_key_exists($uid, $current))
				{
					$query->insert('#__sellacious_client_authorised')
						->columns(array('client_uid', 'user_id', 'credit_limit'))
						->values(implode(', ', array((int) $client_uid, (int) $uid, (float) $limit)));

					if ($this->db->setQuery($query)->execute())
					{
						$dispatcher->trigger('onContentAfterSave', array('com_sellacious.client.authorised', (object) $auth_user, true));
					}
				}
				// Is an update needed? Only applicable when credit limit is changed.
				elseif (abs($current[$uid] - $limit) >= 0.01)
				{
					$query->update('#__sellacious_client_authorised')
						->where('client_uid = ' . (int) $client_uid)
						->where('user_id = ' . (int) $uid)
						->set('credit_limit = ' . (float) $limit);

					if ($this->db->setQuery($query)->execute())
					{
						$dispatcher->trigger('onContentAfterSave', array('com_sellacious.client.authorised', (object) $auth_user, false));
					}
				}
			}
		}
	}

	/**
	 * Get the markup price markup for client category
	 *
	 * @param   int  $categoryId
	 *
	 * @return  array  An array containing markup value and a boolean to specify whether its a percentage
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.1
	 */
	public function getCategoryMarkup($categoryId)
	{
		$filter = array('list.select' => 'a.params', 'id' => $categoryId);
		$params = $this->helper->category->loadResult($filter);
		$params = new Registry($params);
		$markup = $params->get('category_price_markup', '');

		return substr($markup, -1) == '%' ? array((float) substr($markup, 0, -1), true) : array((float) $markup, false);
	}
}

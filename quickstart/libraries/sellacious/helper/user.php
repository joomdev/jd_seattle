<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious user helper.
 *
 * @since   1.0.0
 */
class SellaciousHelperUser extends SellaciousHelperBase
{
	/**
	 * Return a list of sellacious user groups filtered by type
	 *
	 * @param   string  $type  The type of user group to retrieve, null for all types
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 *
	 * @since   1.0
	 */
	public function getGroups($type = null)
	{
		if (is_null($type))
		{
			$k   = array();
			$k[] = $this->helper->config->get('usergroups_client', array());
			$k[] = $this->helper->config->get('usergroups_seller', array());
			$k[] = $this->helper->config->get('usergroups_staff', array());
			$k[] = $this->helper->config->get('usergroups_manufacturer', array());
			$k[] = $this->helper->config->get('usergroups_company', array());

			$pks = array_reduce($k, 'array_merge', array());
		}
		else
		{
			$pks = $this->helper->config->get('usergroups_' . $type, array());
		}

		$db    = $this->db;
		$query = $db->getQuery(true);
		$pks   = ArrayHelper::toInteger($pks);

		$query->select('a.*')
			->select('a.id AS value, a.title AS text')
			->from($db->qn('#__usergroups', 'a'));

		if (count($pks))
		{
			$where   = array();
			$where[] = 'a.id IN (' . implode(', ', $pks) . ')';

			$filter = array(
				'list.select' => 'a.lft, a.rgt',
				'list.from'   => '#__usergroups',
				'list.where'  => $where,
			);
			$items  = (array) $this->loadObjectList($filter);

			foreach ($items as $item)
			{
				$where[] = '(a.lft < ' . (int) $item->lft . ' AND a.rgt > ' . (int) $item->rgt . ')';
			}

			$query->select('a.id NOT IN (' . implode(', ', $pks) . ') AS disabled');
			$query->where(implode("\nOR ", $where));
		}
		else
		{
			$query->select('0 AS disabled');

			// If no group is selected we don't want any
			$query->where('0');
		}

		$query->select('COUNT(DISTINCT b.id) AS level')
			->join('LEFT', $db->qn('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id, a.title, a.lft, a.rgt, a.parent_id')
			->order('a.lft ASC');

		try
		{
			$groups = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			$groups = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $groups;
	}

	/**
	 * Proxy method to create/update sellacious profile for a given joomla user
	 *
	 * @param   array  $data     Profile data to add
	 * @param   int    $user_id  User Id
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function saveProfile($data, $user_id)
	{
		$profile = $this->helper->profile->getItem(array('user_id' => $user_id));

		$data['user_id'] = $user_id;

		$data  = array_merge((array) $profile, $data);

		$table = $this->getTable('Profile');

		$table->bind($data);
		$table->check();
		$table->store();

		return true;
	}

	/**
	 * Method to load all linked accounts like manufacturer, seller, staff etc for a given user
	 *
	 * @param   int   $user_id  User id of the Joomla user for whom profile data to load
	 * @param   bool  $active   Whether to return only active accounts
	 *
	 * @return  stdClass[]  List of account and their attributes
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getLinkedAccounts($user_id, $active = false)
	{
		$accounts = array(
			'manufacturer' => $this->getAccount($user_id, 'manufacturer', $active),
			'client'       => $this->getAccount($user_id, 'client', $active),
			'seller'       => $this->getAccount($user_id, 'seller', $active),
			'staff'        => $this->getAccount($user_id, 'staff', $active),
		);

		return $accounts;
	}

	/**
	 * Get the single account for the given user of the given type (like - manufacturer, client, seller, staff)
	 *
	 * @param   int     $user_id  User Id
	 * @param   string  $type     Literals 'manufacturer', 'client', 'seller' or 'staff'
	 * @param   bool    $active   Whether to return only active accounts
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getAccount($user_id, $type, $active = false)
	{
		$roles = $this->getRoles();

		if (!array_key_exists($type, $roles))
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PROFILE_UNSUPPORTED_ACCOUNT_TYPE', $type));
		}

		$keys = $active ? array('user_id' => $user_id, 'state' => 1) : array('user_id' => $user_id);

		return $this->helper->$type->getItem($keys);
	}

	/**
	 * Get the available roles for the users in sellacious
	 *
	 * @return  string[]
	 *
	 * @since   1.5.2
	 *
	 * @note   This is a temporary method and will be moved to UserHelper::getRoles()
	 */
	public function getRoles()
	{
		$roles = array(
			'client'       => \JText::_('LIB_SELLACIOUS_USER_ROLE_CLIENT_LABEL'),
			'seller'       => \JText::_('LIB_SELLACIOUS_USER_ROLE_SELLER_LABEL'),
			'manufacturer' => \JText::_('LIB_SELLACIOUS_USER_ROLE_MANUFACTURER_LABEL'),
			'staff'        => \JText::_('LIB_SELLACIOUS_USER_ROLE_STAFF_LABEL'),
		);

		return $roles;
	}

	/**
	 * Method to add/update given multiple linked accounts like manufacturer, seller, staff etc for a user
	 *
	 * @param   array  $array    Properties array for the accounts to link
	 * @param   int    $user_id  User id of the concerned user
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.0.0
	 */
	public function addLinkedAccounts($array, $user_id)
	{
		foreach ($array as $type => $account)
		{
			if (empty($account) || empty($account['category_id']))
			{
				continue;
			}

			$account['user_id'] = $user_id;

			$this->addAccount($account, $type);
		}

		$this->setUserGroups($user_id);
	}

	/**
	 * Method to add a single account of given type (like - manufacturer, client, seller, staff) for the given user.
	 *
	 * @param   array   $data  Properties for the accounts
	 * @param   string  $type  Type of the target account
	 *
	 * @return  int  The record id for the user type specific table
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  However no alternates are yet available. Todo: Split into respective helpers
	 */
	public function addAccount($data, $type)
	{
		$roles = $this->getRoles();

		if (!array_key_exists($type, $roles))
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PROFILE_UNSUPPORTED_ACCOUNT_TYPE', $type));
		}

		$user_id = $data['user_id'];

		$user  = JFactory::getUser($user_id);
		$table = $this->getTable($type);

		$keys = array('user_id' => $user_id);
		$table->load($keys);

		if (empty($data['title']) && property_exists($table, 'title'))
		{
			$data['title'] = $user->name;
		}

		if (empty($data['code']) && property_exists($table, 'code'))
		{
			$data['code'] = strtoupper($user->username);
		}

		$data['state'] = 1;

		// Remove id so that no new row is forced
		unset($data['id']);

		foreach ($data as $k => $v)
		{
			// If republishing a row, don't overwrite with blank value. Only write non-blank values.
			if ($table->get('state') || strlen($v) != 0)
			{
				$table->set($k, $v);
			}
		}

		$table->store();

		$this->setUserGroups($user_id);

		return $table->get('id');
	}

	/**
	 * Remove a linked account from the user profile
	 *
	 * @param   int     $user_id  User id for the target user
	 * @param   string  $type     Type of account to unlink
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function removeAccount($user_id, $type)
	{
		if (!in_array($type, array('manufacturer', 'client', 'seller', 'staff')))
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PROFILE_UNSUPPORTED_ACCOUNT_TYPE', $type));
		}

		$table = $this->getTable($type);

		// Deleting is not appropriate, so we just unpublish
		if ($table->load(array('user_id' => $user_id)) && $table->get('state'))
		{
			$table->set('state', 0);
			$table->set('category_id', 0);

			if ($table->store())
			{
				$this->setUserGroups($user_id);
			}
			else
			{
				JLog::add($table->getError());
			}
		}
	}

	/**
	 * Method to add/update Joomla user groups assignment for all users related to a given category
	 *
	 * @param   int  $category_id  Category id for which to process
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function updateUsersGroupsByCategory($category_id)
	{
		$accounts = $this->getAccountsByCategory($category_id);

		foreach ($accounts as $account)
		{
			if (!$this->setUserGroups($account->user_id))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to assign joomla user group for a given user effective as a result of all of his membership types (like:
	 * seller, manufacturer etc) together
	 *
	 * @param   int  $user_id  Table object containing data about the desired account of target user
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setUserGroups($user_id)
	{
		$types  = array('manufacturer', 'client', 'seller', 'staff');
		$user   = JFactory::getUser($user_id);
		$groups = array();

		foreach ($types as $type)
		{
			$table = $this->getTable($type);
			$table->load(array('user_id' => $user_id, 'state' => 1));

			$category = $this->helper->category->getItem($table->get('category_id'));

			if (!empty($category->usergroups))
			{
				$groups = array_merge($groups, (array) $category->usergroups);
			}
		}

		$app_groups   = array();
		$app_groups[] = (array) $this->helper->config->get('usergroups_client', array());
		$app_groups[] = (array) $this->helper->config->get('usergroups_seller', array());
		$app_groups[] = (array) $this->helper->config->get('usergroups_staff', array());
		$app_groups[] = (array) $this->helper->config->get('usergroups_manufacturer', array());
		$app_groups   = array_reduce($app_groups, 'array_merge', array());

		$current  = JAccess::getGroupsByUser($user->id, false);
		$c_groups = array_intersect($app_groups, $current);

		$remove = array_diff($c_groups, $groups);
		$add    = array_diff($groups, $c_groups);

		try
		{
			foreach ($remove as $ug)
			{
				JUserHelper::removeUserFromGroup($user->id, $ug);
			}

			foreach ($add as $ug)
			{
				JUserHelper::addUserToGroup($user->id, $ug);
			}
		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('COM_SELLACIOUS_USER_USERGROUP_NO_CONFIGURED', $e->getMessage()), JLog::WARNING, 'jerror');
		}

		return true;
	}

	/**
	 * Method to load all accounts like: seller, manufacturer etc for all user(s) falling under given category
	 *
	 * @param   int  $category_id
	 *
	 * @return  array  List of accounts loaded
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getAccountsByCategory($category_id)
	{
		// todo: may be we need to load supported types dynamically!
		$supported = array('manufacturer', 'client', 'seller', 'staff');

		$items    = array();
		$category = $this->helper->category->getItem($category_id);

		// Simply skip for unsupported types, that's not an error
		if (in_array($category->type, $supported))
		{
			$table = $this->getTable($category->type);

			$db    = $this->db;
			$query = $db->getQuery(true);

			$query->select('a.id, a.user_id')
				->from($db->qn($table->getTableName(), 'a'))
				->where('a.category_id = ' . $db->q($category->id));

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList();
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}

		return (array) $items;
	}

	/**
	 * Get a list of all stored addresses for the given user id. We fetch full address fields always, if you don't display then layout will autohide
	 * empty fields.
	 *
	 * @param   int   $user_id  User id to query for
	 * @param   null  $state    Whether to return filtered by published state (int), or all irrespective (null)
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	public function getAddresses($user_id, $state = null)
	{
		$filter = array(
			'list.select' => 'a.*',
			'list.from'   => '#__sellacious_addresses',
			'list.where'  => 'user_id = ' . (int) $user_id,
		);

		if (isset($state))
		{
			$filter['state'] = $state;
		}

		return $this->loadObjectList($filter);
	}

	/**
	 * Get a stored address for the given user id preferably the primary address otherwise the first address
	 *
	 * @param   int  $user_id  User id to query for
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.2
	 */
	public function getPrimaryAddress($user_id)
	{
		$filter = array(
			'list.from'   => '#__sellacious_addresses',
			'user_id'     => (int) $user_id,
			'is_primary'  => 1,
		);

		$item = $this->loadObject($filter);

		if (!$item)
		{
			unset($filter['is_primary']);

			$filter['list.order'] = 'a.id';

			$item = $this->loadObject($filter);
		}

		return $item;
	}

	/**
	 * Get a list of all stored addresses for the given user id. We fetch full address fields always, if you don't display then layout will autohide
	 * empty fields.
	 *
	 * @param   int  $address_id  Address id to query for
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getAddressById($address_id)
	{
		$filter = array(
			'list.select' => 'a.*',
			'list.from'   => '#__sellacious_addresses',
			'id'          => (int) $address_id,
		);

		return $this->loadObject($filter);
	}

	/**
	 * Save a given address record into the database
	 *
	 * @param   array  $data     Record id of item to remove
	 * @param   int    $user_id  NULL = Current user, any other 'int' value = said user id
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function saveAddress($data, $user_id = null)
	{
		$data  = (object) $data;
		$table = $this->getTable('Address');

		// Load existing record
		if (!empty($data->id))
		{
			$table->load($data->id);
		}

		// Never overwrite user id
		if ($table->get('user_id'))
		{
			unset($data->user_id);
		}
		// Use $data->user_id OR $user_id OR current user coalescing in that order
		elseif (empty($data->user_id))
		{
			$data->user_id = JFactory::getUser($user_id)->id;
		}

		// Always enable new record
		if (!$table->get('id'))
		{
			$data->state = 1;
		}

		$table->bind($data);
		$table->check();
		$table->store();

		return (object) $table->getProperties();
	}

	/**
	 * Remove a selected address record from the database
	 *
	 * @param   int  $cid      Record id of item to remove
	 * @param   int  $user_id  False = ignore user check, null = current user, any other 'int' value = said user id
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function removeAddress($cid, $user_id = null)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->delete('#__sellacious_addresses')->where('id = ' . $cid);

		if ($user_id !== false)
		{
			$user = JFactory::getUser($user_id);
			$query->where('user_id = ' . $user->id);
		}

		try
		{
			$db->setQuery($query);

			$db->execute();
		}
		catch (Exception $e)
		{
			throw $e;
		}

		return $db->getAffectedRows() > 0;
	}

	/**
	 * Get a record from base table for this helper
	 *
	 * @param   mixed  $keys  Record primary key or set of keys
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getItem($keys)
	{
		if (is_array($keys))
		{
			$db    = $this->db;
			$query = $this->getListQuery($keys);

			$db->setQuery($query);

			$record = $db->loadObject();
			$keys   = $record ? $record->id : 0;
		}

		return parent::getItem($keys);
	}

	/**
	 * Auto create a new user account with the given registry data
	 *
	 * @param   Registry  $info      User details to store
	 * @param   bool      $activate  Whether to automatically activate the new account
	 *
	 * @return  JUser
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   This method will be moved to Sellacious\User\UserHelper::register()
	 */
	public function autoRegister(Registry $info, $activate = false)
	{
		$password = JUserHelper::genRandomPassword(8);
		$password = $info->get('password', $password);

		$data = (object) array(
			'name'      => $info->get('name', 'Sellacious User'),
			'username'  => $info->get('username'),
			'email1'    => $info->get('email'),
			'email2'    => $info->get('email'),
			'groups'    => $info->get('groups', array()),
			'password1' => $password,
			'password2' => $password,
			'block'     => 0,
			'params'    => $info->get('params'),
		);

		// Let passed argument overriden by 'block' property
		if ($info->get('block') !== null)
		{
			$activate = !$info->get('block');
		}

		if (empty($data->groups))
		{
			$uParams    = JComponentHelper::getParams('com_users');
			$regGroup   = $uParams->get('new_usertype', 2);
			$guestGroup = $uParams->get('guest_usergroup', 1);

			$c_def  = array('is_default' => 1, 'type' => 'client');
			$groups = $this->helper->category->getFieldValue($c_def, 'usergroups') ?: array();
			$groups = array_diff($groups, array($guestGroup)) ?: array($regGroup);

			$data->groups = array_merge($data->groups, $groups);
		}

		// Get the dispatcher and load the users plugins.
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('user');
		$results    = $dispatcher->trigger('onContentPrepareData', array('com_users.registration', $data));

		// Check for errors encountered while preparing the data.
		if (count($results) && in_array(false, $results, true))
		{
			throw new Exception($dispatcher->getError());
		}

		// Now proceed towards saving user info
		$user = new JUser;
		$data = ArrayHelper::fromObject($data);

		// Prepare the data for the user object.
		$data['email']    = JStringPunycode::emailToPunycode($data['email1']);
		$data['password'] = $data['password1'];

		$uParams         = JComponentHelper::getParams('com_users');
		$user_activation = $uParams->get('useractivation');

		// Check if the user needs to activate their account.
		if (!$activate && ($user_activation == 1 || $user_activation == 2))
		{
			$data['activation'] = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
			$data['block']      = 1;
		}

		// Bind the data.
		if (!$user->bind($data))
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_REGISTRATION_BIND_FAILED', $user->getError()));
		}

		// Store the data.
		if (!$user->save())
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_REGISTRATION_SAVE_FAILED', $user->getError()));
		}

		$user->set('password_plain', $password);

		try
		{
			$dispatcher = $this->helper->core->loadPlugins('sellacious');
			$dispatcher->trigger('onAfterSaveUser', array('com_sellacious.user', $user, true));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage());
		}

		return $user;
	}

	/**
	 * Get the address form for front-end
	 *
	 * @param   array  $options  JForm instance options
	 * @param   array  $data     Data to be bound to the form
	 *
	 * @return  JForm
	 *
	 * @since   1.0.0
	 */
	public function getAddressForm($options, $data = array())
	{
		$name = array_key_exists('name', $options) ? $options['name'] : 'com_sellacious.address.form';
		$form = JForm::getInstance($name, 'user_address', $options, true);

		$fields = array('company', 'address', 'po_box', 'landmark', 'country', 'state_loc', 'district', 'zip', 'mobile', 'residential');

		foreach ($fields as $fieldName)
		{
			$show = $this->helper->config->get('geolocation_levels.' . $fieldName, 1);

			if ($show == 0)
			{
				$form->removeField($fieldName);
			}
			else
			{
				$form->setFieldAttribute($fieldName, 'required', $show == 2 ? 'true' : 'false');
			}
		}

		$showM = $this->helper->config->get('geolocation_levels.mobile', 1);
		$showZ = $this->helper->config->get('geolocation_levels.zip', 1);

		if ($showM && ($regexM = $this->helper->config->get('address_mobile_regex', '')))
		{
			$form->setFieldAttribute('mobile', 'validate', 'regex');
			$form->setFieldAttribute('mobile', 'regex', $regexM);
		}

		if ($showZ && ($regexZ = $this->helper->config->get('address_zip_regex', '')))
		{
			$form->setFieldAttribute('zip', 'validate', 'regex');
			$form->setFieldAttribute('zip', 'regex', $regexZ);
		}

		if (empty($data['id']))
		{
			$me      = JFactory::getUser();
			$filters = array('list.select' => 'a.business_name', 'id' => $me->id);
			$form->setFieldAttribute('company', 'default', $this->helper->client->loadResult($filters));
		}

		$form->bind($data);

		return $form;
	}

	/**
	 * Save the custom profile attributes of a user
	 *
	 * @param   int    $userId  Product id in concern
	 * @param   array  $values  Associative array of spec field id and field value
	 * @param   bool   $reset   Remove current values before inserting
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function saveCustomProfile($userId, array $values, $reset = true)
	{
		if ($reset)
		{
			$this->helper->field->clearValue('profile', $userId, array_keys($values), true);
		}

		foreach ($values as $fieldId => $value)
		{
			$this->helper->field->setValue('profile', $userId, $fieldId, $value);
		}
	}
}

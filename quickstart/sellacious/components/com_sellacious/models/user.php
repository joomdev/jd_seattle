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
 * Sellacious model.
 *
 * @since   1.0.0
 */
class SellaciousModelUser extends SellaciousModelAdmin
{
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = $this->app->getUserStateFromRequest("$this->option.edit.$this->name.data", 'jform', array(), 'array');

		if (empty($data))
		{
			// Load user info
			$data = $this->getItem();

			// Remove password
			unset($data->password);

			// Add profile info
			$profile = $this->helper->profile->getItem(array('user_id' => $data->get('id')));

			$data->set('profile', $profile);

			$data->custom_profile = $this->helper->field->getValue('profile', $data->get('id'));

			// This is the form data so we only load active records
			$accounts = $this->helper->user->getLinkedAccounts($data->get('id'), true);

			foreach ($accounts as $type => $account)
			{
				if (!empty($account))
				{
					if ($type == 'seller')
					{
						// Load seller shippable locations
						$account->shipping_geo = $this->helper->seller->getShipLocations($data->get('id'), true);

						$account->commission = $this->helper->seller->getCommissions($data->get('id'));
					}
					elseif ($type == 'client')
					{
						// Load client's authorised users list
						$account->authorised = $this->helper->client->getAuthorised($data->get('id'), false);
					}
				}

				$data->set($type, $account);
			}
		}
		else
		{
			if (isset($data['client']['authorised']['email']) && is_array($data['client']['authorised']['email']))
			{
				$data['client']['authorised'] = $this->helper->core->skew2dArray($data['client']['authorised']);
			}
		}

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		$me  = JFactory::getUser();

		if (in_array($me->id, $pks))
		{
			$pks = array_diff($pks, array($me->id));

			$this->app->enqueueMessage(JText::plural('COM_SELLACIOUS_USERS_OWN_PROFILE_DELETE_WARNING', $me->name), 'warning');
		}

		if ($delete = parent::delete($pks))
		{
			$this->helper->field->deleteValue('profile', $pks);

			$tables = array(
				array('#__sellacious_addresses', 'user_id'),
				array('#__sellacious_cart', 'user_id'),
				array('#__sellacious_cart', 'seller_uid'),
				array('#__sellacious_cart_info', 'user_id'),
				array('#__sellacious_clients', 'user_id'),
				array('#__sellacious_client_authorised', 'user_id'),
				array('#__sellacious_client_authorised', 'client_uid'),
				array('#__sellacious_coupons', 'seller_uid'),
				array('#__sellacious_eproduct_media', 'seller_uid'),
				array('#__sellacious_manufacturers', 'user_id'),
				array('#__sellacious_prices_cache', 'seller_uid'),
				array('#__sellacious_product_prices', 'seller_uid'),
				array('#__sellacious_profiles', 'user_id'),
				array('#__sellacious_product_queries', 'seller_uid'),
				array('#__sellacious_product_sellers', 'seller_uid'),
				array('#__sellacious_ratings', 'seller_uid'),
				array('#__sellacious_sellers', 'user_id'),
				array('#__sellacious_seller_listing', 'seller_uid'),
				array('#__sellacious_seller_shippable', 'seller_uid'),
				array('#__sellacious_shoprules', 'seller_uid', array('id > 1', 'seller_uid > 0')),
				array('#__sellacious_staffs', 'user_id'),
				array('#__sellacious_variant_sellers', 'seller_uid'),
				// Following are NOT duplicates one is for customer and the other for seller,
				array('#__sellacious_wishlist', 'user_id'),
				array('#__sellacious_wishlist', 'seller_uid'),
				array('#__sellacious_seller_commissions', 'seller_uid', array('seller_uid > 0')),
			);

			$queries = array();

			$uid   = $this->_db->getQuery(true)->select('id')->from('#__users');
			$query = $this->_db->getQuery(true);

			foreach ($tables as $table)
			{
				$query->clear()->delete($table[0])->where($table[1] . ' NOT IN (' . $uid . ')');

				if (isset($table[2]))
				{
					$query->where($table[2]);
				}

				$queries[] = (string) $query;
			}

			// Seller references for products
			$psx = $this->_db->getQuery(true)->select('id')->from('#__sellacious_product_sellers');

			$queries[] = (string) $query->clear()->delete('#__sellacious_physical_sellers')->where('psx_id NOT IN (' . $psx . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_eproduct_sellers')->where('psx_id NOT IN (' . $psx . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_package_sellers')->where('psx_id NOT IN (' . $psx . ')');

			// Execute all queries
			foreach ($queries as $query)
			{
				try
				{
					$this->_db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					// Ignore as of now
				}
			}
		}

		return $delete;
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Plugin group to load
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj     = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$user_id = isset($obj->id) ? $obj->id : 0;
		$isNew   = ($user_id == 0);

		if ($isNew ? $this->helper->access->check('user.add') : $this->helper->access->check('user.edit'))
		{
			$categories = array();

			if (!empty($obj->client->category_id))
			{
				$categories[] = $obj->client->category_id;
			}
			else
			{
				$dCat         = $this->helper->category->getDefault('client', 'a.id');
				$categories[] = $dCat ? $dCat->id : 0;
			}

			if ($user_id)
			{
				$form->setFieldAttribute('avatar', 'recordId', $user_id);
			}

			$form->loadFile('user/client');

			$client_id = isset($obj->client->id) ? $obj->client->id : 0;
			$form->setFieldAttribute('org_certificate', 'recordId', $client_id, 'client');

			if (!$this->helper->access->isSubscribed())
			{
				$form->setFieldAttribute('authorised', 'readonly', 'true', 'client');
			}
			elseif (!$this->helper->config->get('allow_client_authorised_users'))
			{
				$form->removeField('authorised', 'client');
			}

			if (!$this->helper->access->isSubscribed())
			{
				$form->setFieldAttribute('credit_limit', 'readonly', 'true', 'client');
			}
			elseif (!$this->helper->config->get('allow_credit_limit'))
			{
				$form->removeField('credit_limit', 'client');
			}

			if (!empty($obj->staff->category_id))
			{
				$categories[] = $obj->staff->category_id;

				$form->loadFile('user/staff');
			}

			if (!empty($obj->seller->category_id))
			{
				$categories[] = $obj->seller->category_id;

				$form->loadFile('user/seller');

				$seller_id = isset($obj->seller->id) ? $obj->seller->id : 0;
				$form->setFieldAttribute('logo', 'recordId', $seller_id, 'seller');

				if ($this->helper->config->get('shipped_by') != 'seller')
				{
					$form->removeField('ship_origin_group', 'seller');
					$form->removeField('ship_origin_country', 'seller');
					$form->removeField('ship_origin_state', 'seller');
					$form->removeField('ship_origin_district', 'seller');
					$form->removeField('ship_origin_zip', 'seller');
					// $form->removeGroup('seller.shipping_geo');
				}

				if (!$this->helper->config->get('listing_currency'))
				{
					$form->removeField('currency', 'seller');
				}

				if (!$this->helper->config->get('shippable_location_by_seller'))
				{
					$form->removeGroup('seller.shipping_geo');
				}

				if (!$this->helper->config->get('multi_seller'))
				{
					$form->removeField('commission', 'seller');
				}

				if (!empty($data->seller->store_location))
				{
					$coordinates = explode(',', $data->seller->store_location);
					$form->setFieldAttribute('store_location_address', 'lat', $coordinates[0], 'seller');
					$form->setFieldAttribute('store_location_address', 'lng', $coordinates[1], 'seller');
				}
			}

			if (!empty($obj->manufacturer->category_id))
			{
				$categories[] = $obj->manufacturer->category_id;

				$form->loadFile('user/manufacturer', true);

				$mfr_id = isset($obj->manufacturer->id) ? $obj->manufacturer->id : 0;
				$form->setFieldAttribute('logo', 'recordId', $mfr_id, 'manufacturer');
			}

			if (!$this->helper->config->get('user_currency'))
			{
				$form->removeField('currency', 'profile');
			}

			if (!$isNew)
			{
				$form->loadFile('user/address');
			}

			$fieldIds    = $this->helper->category->getFields($categories, array('core'), true);
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile');

			foreach ($xmlElements as $xmlElement)
			{
				$form->load($xmlElement);
			}
		}

		$me = JFactory::getUser();

		if ($me->id == $user_id)
		{
			$form->setFieldAttribute('block', 'type', 'hidden');
			$form->setFieldAttribute('block', 'filter', 'unset');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to return a single record. Joomla model doesn't use caching, we use.
	 *
	 * @param   JObject  $item  The record item.
	 *
	 * @return  JObject
	 *
	 * @since   1.2.0
	 */
	public function processItem($item)
	{
		if ($user_id = $item->get('id'))
		{
			$item->set('addresses', $this->helper->user->getAddresses($user_id));
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  int
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		// Extract variables
		$custom       = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile      = ArrayHelper::getValue($data, 'profile', null);
		$manufacturer = ArrayHelper::getValue($data, 'manufacturer', null);
		$seller       = ArrayHelper::getValue($data, 'seller', null);
		$staff        = ArrayHelper::getValue($data, 'staff', null);
		$client       = ArrayHelper::getValue($data, 'client', null);

		unset($data['custom_profile'], $data['profile'], $data['manufacturer'], $data['seller'], $data['staff'], $data['client']);

		$isNew = empty($data['id']);
		$user  = $this->saveUser($data);

		if (!($user instanceof JUser))
		{
			return false;
		}

		// Set up profile and all for the user just saved
		$profile['state'] = $user->block ? 0 : 1;

		$this->helper->user->saveProfile($profile, $user->id);
		$this->helper->user->saveCustomProfile($user->id, (array) $custom);

		try
		{
			$_control    = 'jform.avatar';
			$_tableName  = 'user';
			$_context    = 'avatar';
			$_recordId   = $user->id;
			$_extensions = array('jpg', 'png', 'jpeg', 'gif');
			$_options    = ArrayHelper::getValue($data, 'avatar', array(), 'array');

			$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		// Save manufacturer
		if (!empty($manufacturer['category_id']))
		{
			$manufacturer['user_id'] = $user->id;

			$mId = $this->helper->user->addAccount($manufacturer, 'manufacturer');

			try
			{
				$_control    = 'jform.manufacturer.logo';
				$_tableName  = 'manufacturers';
				$_context    = 'logo';
				$_recordId   = $mId;
				$_extensions = array('jpg', 'png', 'jpeg', 'gif');
				$_options    = ArrayHelper::getValue($manufacturer, 'logo', array(), 'array');

				$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'manufacturer');
		}

		// Save seller
		if (!empty($seller['category_id']))
		{
			$seller['user_id'] = $user->id;

			$locations = ArrayHelper::getValue($seller, 'shipping_geo', array(), 'array');

			foreach ($locations as &$location)
			{
				$location = strlen($location) ? explode(',', $location) : array();
			}

			$locations = array_reduce($locations, 'array_merge', array());

			if (isset($seller['commission']))
			{
				$commissions = $seller['commission'];

				unset($seller['commission']);
			}

			$sId = $this->helper->user->addAccount($seller, 'seller');

			try
			{
				$_control    = 'jform.seller.logo';
				$_tableName  = 'sellers';
				$_context    = 'logo';
				$_recordId   = $sId;
				$_extensions = array('jpg', 'png', 'jpeg', 'gif');
				$_options    = ArrayHelper::getValue($seller, 'logo', array(), 'array');

				$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}

			$this->helper->seller->setShipLocations($user->id, $locations);

			if (!empty($commissions))
			{
				$this->helper->seller->setCommissions($user->id, $commissions);
			}
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'seller');
			$this->helper->seller->setShipLocations($user->id, array());
		}

		// Save staff
		if (!empty($staff['category_id']))
		{
			$staff['user_id'] = $user->id;

			$this->helper->user->addAccount($staff, 'staff');
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'staff');
		}

		// Save client
		if (!empty($client['category_id']))
		{
			$client['user_id'] = $user->id;

			$authorised = ArrayHelper::getValue($client, 'authorised', array(), 'array');
			$authorised = $this->helper->core->skew2dArray($authorised);

			unset($client['authorised']);

			$cId = $this->helper->user->addAccount($client, 'client');

			try
			{
				$_control    = 'jform.client.org_certificate';
				$_tableName  = 'clients';
				$_context    = 'org_certificate';
				$_recordId   = $cId;
				$_extensions = array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'doc', 'docx');
				$_options    = ArrayHelper::getValue($client, 'org_certificate', array(), 'array');

				$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}

			$cAuth = array();

			foreach ($authorised as $auth_one)
			{
				$auth_one['isNew'] = false;

				if (empty($auth_one['id']))
				{
					$temp = $auth_one;

					$temp['username'] = $temp['email'];

					try
					{
						$xUid = $this->helper->user->loadResult(array('email' => $temp['email']));

						if ($xUid)
						{
							$auth_one['id'] = $xUid;
						}
						else
						{
							unset($temp['id']);

							$cAuthU = $this->helper->user->autoRegister(new Registry($temp));

							$auth_one['isNew'] = true;
							$auth_one['id']    = $cAuthU->id;
						}
					}
					catch (Exception $e)
					{
						JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
					}
				}

				if ($auth_one['id'])
				{
					$cAuth[] = $auth_one;
				}
			}

			$this->helper->client->setAuthorised($user->id, $cAuth);
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'client');
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.user', $user, $isNew));

		return $user->id;
	}

	/**
	 * @param   array  $data  The data to save for related Joomla user account.
	 *
	 * @return  JUser|bool  The user id of the user account on success, false otherwise
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function saveUser($data)
	{
		$pk = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->name . '.id');

		if ($pk == 0)
		{
			$registry = new Registry($data);
			$user     = $this->helper->user->autoRegister($registry);

			// Set global edit id in case rest of the process fails, page should load with new user id
			// Joomla bug in Registry, array key does not update. Fixed in later version of J! 3.4.x
			$state       = $this->app->getUserState("com_sellacious.edit.$this->name.data");
			$state['id'] = $user->id;

			$this->setState("$this->name.id", $user->id);
			$this->app->setUserState("com_sellacious.edit.$this->name.data", $state);
			$this->app->setUserState("com_sellacious.edit.$this->name.id", (int) $user->id);
		}
		else
		{
			$user = JUser::getInstance($data['id']);

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('sellacious');

			// Bind the data.
			if (!$user->bind($data))
			{
				$this->setError($user->getError());

				return false;
			}

			// Trigger the onAfterSave event.
			$dispatcher->trigger('onBeforeSaveUser', array($this->option . '.' . $this->name, &$user, false));

			// Store the data.
			if (!$user->save())
			{
				$this->setError($user->getError());

				return false;
			}

			// Trigger the onAfterSave event.
			$dispatcher->trigger('onAfterSaveUser', array($this->option . '.' . $this->name, &$user, false));
		}

		return $user;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('user.delete');
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  boolean|\JException  Boolean true on success, false on failure, or \JException if no items are selected
	 *
	 * @since   1.6
	 */
	public function saveorder($pks = array(), $order = null)
	{
		$table          = $this->getTable('Profile');
		$tableClassName = get_class($table);
		$contentType    = new \JUcmType;
		$type           = $contentType->getTypeByTable($tableClassName);
		$tagsObserver   = $table->getObserverOfClass('\JTableObserverTags');
		$conditions     = array();

		if (empty($pks))
		{
			return \JError::raiseWarning(500, \JText::_($this->text_prefix . '_ERROR_NO_ITEMS_SELECTED'));
		}

		$orderingField = $table->getColumnAlias('ordering');

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load(array('user_id' => (int) $pk));

			// Access checks.
			if (!$this->canEditState($table))
			{
				// Prune items that you can't change.
				unset($pks[$i]);
				\JLog::add(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), \JLog::WARNING, 'jerror');
			}
			elseif ($table->$orderingField != $order[$i])
			{
				$table->$orderingField = $order[$i];

				if ($type)
				{
					$this->createTagsHelper($tagsObserver, $type, $pk, $type->type_alias, $table);
				}

				if (!$table->store())
				{
					$this->setError($table->getError());

					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found     = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key          = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		// Execute reorder for each category.
		if ($conditions)
		{
			foreach ($conditions as $cond)
			{
				$table->load($cond[0]);
				$table->reorder($cond[1]);
			}
		}
		else
		{
			$table->reorder();
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function publish(&$pks, $value = 1)
	{
		$me  = JFactory::getUser();

		if (in_array($me->id, $pks))
		{
			$pks = array_diff($pks, array($me->id));

			$this->app->enqueueMessage(JText::plural('COM_SELLACIOUS_USERS_OWN_PROFILE_UNPUBLISH_WARNING', $me->name), 'warning');
		}

		$published = parent::publish($pks, $value);

		if ($published && count($pks))
		{
			$query = $this->_db->getQuery(true);
			$query->update('#__sellacious_profiles')
				->set('state = ' . ($value == 1 ? 1 : 0))
				->where('user_id IN (' . implode(', ', $pks) . ')');

			try
			{
				$this->_db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}

		return $published;
	}

	/**
	 * This method sends a reminder email for given non-activated users.
	 *
	 * @param   array  $pks  A list of the primary keys.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function resendActivationMail($pks)
	{
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => 'user_activation.self'));

		if ($table->get('state'))
		{
			$users = $this->getInactiveUsersInfo($pks);

			foreach ($users as $user)
			{
				$this->addUserMail($table, $user);
			}
		}
	}

	/**
	 * Send the email for the given user object using given email template object
	 *
	 * @param   JTable  $template  The template table object
	 * @param   object  $user      The user object
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function addUserMail($template, $user)
	{
		$base = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$link = JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $user->activation);

		$link = str_replace(JUri::base(true), JUri::root(true), $link);

		$emailParams = $this->helper->config->getParams('com_sellacious', 'emailtemplate_options');

		$replacements = array(
			'sitename'          => JFactory::getConfig()->get('sitename'),
			'site_url'          => rtrim(JUri::root(), '/'),
			'email_header'      => $emailParams->get('header', ''),
			'email_footer'      => $emailParams->get('footer', ''),
			'activation_link'   => rtrim($base, '/') . $link,
			'full_name'         => $user->name,
			'email_address'     => $user->email,
			'registration_date' => JHtml::_('date', $user->registerDate, 'F d, Y h:i A T'),
			'days_passed'       => $user->days,
		);

		$recipients = explode(',', $template->get('recipients'));

		if ($template->get('send_actual_recipient'))
		{
			array_unshift($recipients, $user->email);
		}

		$this->queue($template, $replacements, $recipients);
	}

	/**
	 * Get a list of users that have not yet activated their account
	 *
	 * @param   array    $pks   A list of the primary keys.
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function getInactiveUsersInfo($pks)
	{
		$users = array();
		$today = JHtml::_('date', 'now', 'Y-m-d');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.name, a.username, a.email, a.block, a.registerDate, a.activation')
			->select('datediff(' .$db->q($today) . ', a.registerDate) as days')
			->from($db->qn('#__users', 'a'))
			->where('a.block = 1')
			->where('id IN (' . implode(', ', $pks) . ')')
			->where('a.activation != ' . $db->q(''));

		$db->setQuery($query);

		if ($results = $db->loadObjectList())
		{
			foreach ($results as $result)
			{
				$users[$result->id] = $result;
			}
		}

		return array_values($users);
	}

	/**
	 * This method sends a password reset email for given users.
	 *
	 * @param   array    $pks   A list of the primary keys.
	 *
	 * @return  mixed  JException | boolean
	 *
	 * @since   1.6.0
	 */
	public function processResetPasswordRequest($pks)
	{
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => 'password_reset.self'));

		if ($table->get('state'))
		{
			foreach ($pks as $pk)
			{
				// Get the user object.
				$user = JUser::getInstance($pk);

				// Set the confirmation token.
				$token = JApplicationHelper::getHash(JUserHelper::genRandomPassword());
				$hashedToken = JUserHelper::hashPassword($token);

				$user->activation = $hashedToken;

				// Save the user to the database.
				if (!$user->save(true))
				{
					return new JException(JText::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
				}

				$this->addPasswordResetUserMail($table, $user, $token);
			}
		}
		return true;
	}

	/**
	 * Send the email for the given user object using given email template object
	 *
	 * @param   JTable  $template  The template table object
	 * @param   object  $user      The user object
	 * @param   string  $token     New Generated token
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function addPasswordResetUserMail($template, $user, $token)
	{
		// Assemble the password reset confirmation link.
		$base = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$link = JRoute::_('index.php?option=com_users&view=reset&layout=confirm&token=' . $token);
		$link = str_replace(JUri::base(true), JUri::root(true), $link);

		$emailParams = $this->helper->config->getParams('com_sellacious', 'emailtemplate_options');

		$replacements = array(
			'sitename'          => JFactory::getConfig()->get('sitename'),
			'site_url'          => rtrim(JUri::root(), '/'),
			'email_header'      => $emailParams->get('header', ''),
			'email_footer'      => $emailParams->get('footer', ''),
			'verification_code' => $token,
			'verification_url'  => rtrim($base, '/') . $link,
			'full_name'         => $user->name,
			'email_address'     => $user->email,
			'registration_date' => JHtml::_('date', $user->registerDate, 'F d, Y h:i A T'),
		);

		$recipients = explode(',', $template->get('recipients'));

		if ($template->get('send_actual_recipient'))
		{
			array_unshift($recipients, $user->email);
		}

		$this->queue($template, $replacements, $recipients);
	}
	/**
	 * Queue the email in the database using given template and data for the given recipients
	 *
	 * @param   JTable  $template      The template table object
	 * @param   array   $replacements  The short code replacements for the email text
	 * @param   array   $recipients    The recipient emails
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function queue($template, $replacements, $recipients)
	{
		$recipients = array_filter($recipients);
		$subject    = trim($template->get('subject'));
		$body       = trim($template->get('body'));

		// Check Recipients, subject and body should not empty before adding to Email Queue
		if (empty($recipients) || $subject == '' || $body == '')
		{
			return;
		}

		// Pre instantiate for constant access.
		$table = JTable::getInstance('MailQueue', 'SellaciousTable');

		// All codes are in upper case
		$replacements = array_change_key_case($replacements, CASE_UPPER);

		$data             = new stdClass;
		$data->context    = $template->get('context');
		$data->subject    = $subject;
		$data->body       = $body;
		$data->is_html    = true;
		$data->state      = SellaciousTableMailQueue::STATE_QUEUED;
		$data->recipients = $recipients;
		$data->sender     = $template->get('sender');
		$data->cc         = !empty($template->cc) ? explode(',', $template->cc) : array();
		$data->bcc        = !empty($template->bcc) ? explode(',', $template->bcc) : array();
		$data->replyto    = !empty($template->replyto) ? explode(',', $template->replyto) : array();

		foreach ($replacements as $code => $replacement)
		{
			$data->subject = str_replace('%' . $code . '%', $replacement, $data->subject);
			$data->body    = str_replace('%' . $code . '%', $replacement, $data->body);
		}

		try
		{
			$table->save($data);
		}
		catch (Exception $e)
		{
			// Todo: Handle this
		}
	}
}

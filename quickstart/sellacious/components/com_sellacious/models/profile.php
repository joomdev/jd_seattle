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
class SellaciousModelProfile extends SellaciousModelAdmin
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$uid = JFactory::getUser()->id;

		// This form view is for user's own profile only with restricted access.
		$this->app->setUserState('com_sellacious.edit.profile.id', $uid);
		$this->state->set('profile.id', $uid);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @throws  Exception
	 *
	 * @return  JTable
	 *
	 * @since   1.1.0
	 */
	public function getTable($type = 'User', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

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
				// Load seller shippable locations also
				if ($type == 'seller' && !empty($account))
				{
					$account->shipping_geo = $this->helper->seller->getShipLocations($data->get('id'), true);
				}

				$data->set($type, $account);
			}
		}

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
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
		$me      = JFactory::getUser();

		if ($this->helper->access->check('user.edit.own') && $user_id == $me->id)
		{
			$categories = array();

			if (!empty($obj->client->category_id))
			{
				$categories[] = $obj->client->category_id;

				$form->loadFile('profile/client');

				$client_id = isset($obj->client->id) ? $obj->client->id : 0;
				$form->setFieldAttribute('org_certificate', 'recordId', $client_id, 'client');

				if (!$this->helper->access->isSubscribed() || !$this->helper->config->get('allow_client_authorised_users'))
				{
					$form->removeField('authorised', 'client');
				}

				$filter  = array('list.select' => 'a.params', 'id' => $obj->client->category_id);
				$cParams = $this->helper->category->loadResult($filter);
				$cParams = new Registry($cParams);
			}
			else
			{
				$dCat         = $this->helper->category->getDefault('client', 'a.id');
				$categories[] = $dCat ? $dCat->id : 0;
				$cParams = new Registry;
			}

			if ($user_id)
			{
				$form->setFieldAttribute('avatar', 'recordId', $user_id);
			}

			if (!empty($obj->staff->category_id))
			{
				$categories[] = $obj->staff->category_id;

				$form->loadFile('profile/staff');
			}

			if (!empty($obj->seller->category_id))
			{
				$categories[] = $obj->seller->category_id;

				$form->loadFile('profile/seller');

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

				$filter  = array('list.select' => 'a.params', 'id' => $obj->seller->category_id);
				$sParams = $this->helper->category->loadResult($filter);
				$sParams = new Registry($sParams);
			}
			else
			{
				$sParams = $cParams;
			}

			if (!empty($obj->manufacturer->category_id))
			{
				$categories[] = $obj->manufacturer->category_id;

				$form->loadFile('profile/manufacturer', true);

				$mfr_id = isset($obj->manufacturer->id) ? $obj->manufacturer->id : 0;
				$form->setFieldAttribute('logo', 'recordId', $mfr_id, 'manufacturer');
			}

			if (!$this->helper->config->get('user_currency'))
			{
				$form->removeField('currency', 'profile');
			}

			$form->loadFile('profile/address');

			$fieldIds    = $this->helper->category->getFields($categories, array('core'), true);
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile');

			foreach ($xmlElements as $xmlElement)
			{
				$form->load($xmlElement);
			}

			if (!$this->helper->access->check('user.edit'))
			{
				if (!($sParams->get('name', 1) == 1 || $sParams->get('name', 1) == 3))
				{
					$form->removeField('name');
				}

				if (!($sParams->get('params.timezone', 1) == 1 || $sParams->get('params.timezone', 1) == 3))
				{
					$form->removeField('timezone', 'params');
				}

				if (!($sParams->get('seller.title', 1) == 1 || $sParams->get('seller.title', 1) == 3))
				{
					$form->removeField('title', 'seller');
				}

				if (!($sParams->get('seller.store_name', 1) == 1 || $sParams->get('seller.store_name', 1) == 3))
				{
					$form->removeField('store_name', 'seller');
				}

				if (!($sParams->get('seller.store_address', 1) == 1 || $sParams->get('seller.store_address', 1) == 3))
				{
					$form->removeField('store_address', 'seller');
				}

				if (!($sParams->get('seller.currency', 1) == 1 || $sParams->get('seller.currency', 1) == 3))
				{
					$form->removeField('currency', 'seller');
				}

				if (!($sParams->get('seller.store_location', 1) == 1 || $sParams->get('seller.store_location', 1) == 3))
				{
					$form->removeField('store_location', 'seller');
				}

				if ($sParams->get('profile.avatar', 1) == 0 && $cParams->get('profile.avatar', 1) == 0)
				{
					$form->removeField('avatar');
				}

				if (!($cParams->get('client.client_type', 1) == 1 || $cParams->get('client.client_type', 1) == 3))
				{
					$form->removeField('client_type', 'client');
				}

				if (!($cParams->get('client.business_name', 1) == 1 || $cParams->get('client.business_name', 1) == 3))
				{
					$form->removeField('business_name', 'client');
				}

				if (!($cParams->get('client.org_reg_no', 1) == 1 || $cParams->get('client.org_reg_no', 1) == 3))
				{
					$form->removeField('org_reg_no', 'client');
				}

				if (!($cParams->get('client.org_certificate', 1) == 1 || $cParams->get('client.org_certificate', 1) == 3))
				{
					$form->removeField('org_certificate', 'client');
				}

				if (!($sParams->get('profile.mobile', 1) == 1 || $sParams->get('profile.mobile', 1) == 3))
				{
					$form->removeField('mobile', 'profile');
				}

				if (!($sParams->get('profile.website', 1) == 1 || $sParams->get('profile.website', 1) == 3))
				{
					$form->removeField('website', 'profile');
				}

				if (!($sParams->get('profile.currency', 1) == 1 || $sParams->get('profile.currency', 1) == 3))
				{
					$form->removeField('currency', 'profile');
				}

				if (!($sParams->get('profile.bankinfo.name', 1) == 1 || $sParams->get('profile.bankinfo.name', 1) == 3))
				{
					$form->removeField('name', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.country', 1) == 1 || $sParams->get('profile.bankinfo.country', 1) == 3))
				{
					$form->removeField('country', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.branch', 1) == 1 || $sParams->get('profile.bankinfo.branch', 1) == 3))
				{
					$form->removeField('branch', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.beneficiary', 1) == 1 || $sParams->get('profile.bankinfo.beneficiary', 1) == 3))
				{
					$form->removeField('beneficiary', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.accountnumber', 1) == 1 || $sParams->get('profile.bankinfo.accountnumber', 1) == 3))
				{
					$form->removeField('accountnumber', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.code', 1) == 1 || $sParams->get('profile.bankinfo.code', 1) == 3))
				{
					$form->removeField('code', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.micr', 1) == 1 || $sParams->get('profile.bankinfo.micr', 1) == 3))
				{
					$form->removeField('micr', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.ifsc', 1) == 1 || $sParams->get('profile.bankinfo.ifsc', 1) == 3))
				{
					$form->removeField('ifsc', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.bankinfo.swift', 1) == 1 || $sParams->get('profile.bankinfo.swift', 1) == 3))
				{
					$form->removeField('swift', 'profile.bankinfo');
				}

				if (!($sParams->get('profile.taxinfo.salestax', 1) == 1 || $sParams->get('profile.taxinfo.salestax', 1) == 3))
				{
					$form->removeField('salestax', 'profile.taxinfo');
				}

				if (!($sParams->get('profile.taxinfo.servicetax', 1) == 1 || $sParams->get('profile.taxinfo.servicetax', 1) == 3))
				{
					$form->removeField('servicetax', 'profile.taxinfo');
				}

				if (!($sParams->get('profile.taxinfo.incometax', 1) == 1 || $sParams->get('profile.taxinfo.incometax', 1) == 3))
				{
					$form->removeField('incometax', 'profile.taxinfo');
				}

				if (!($sParams->get('profile.taxinfo.tax', 1) == 1 || $sParams->get('profile.taxinfo.tax', 1) == 3))
				{
					$form->removeField('tax', 'profile.taxinfo');
				}
			}
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

		$user = $this->saveUser($data);

		if (!($user instanceof JUser))
		{
			return false;
		}

		// Set up profile and all for the user just saved
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
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'client');
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.user', $user, false));

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
}

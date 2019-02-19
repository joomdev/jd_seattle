<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious model.
 *
 * @since   1.2.0
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

		$me  = JFactory::getUser();

		// This form view is for user's own profile only with restricted access.
		$this->app->setUserState('com_sellacious.edit.profile.id', $me->id);
		$this->state->set('profile.id', $me->id);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
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
	 * @throws  Exception
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj     = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$user_id = $this->getState('profile.id');
		$me      = JFactory::getUser();

		$canEdit = $this->helper->access->check('user.edit.own') && $user_id == $me->id && !$me->guest;

		if ($canEdit)
		{
			if (empty($obj->client->category_id))
			{
				$category = $this->helper->category->getDefault('client', 'a.id');

				$obj->client->category_id = $category ? $category->id : 0;
			}

			$categories = array();

			if ($obj->client->category_id)
			{
				$categories[] = $obj->client->category_id;
			}

			$form->loadFile('profile/client');

			$client_id = isset($obj->client->id) ? $obj->client->id : 0;
			$form->setFieldAttribute('org_certificate', 'recordId', $client_id, 'client');

			$form->setFieldAttribute('avatar', 'recordId', $user_id);

			if (!empty($obj->seller->category_id))
			{
				$categories[] = $obj->seller->category_id;

				$form->loadFile('profile/seller');
			}

			// Remove Profile Currency if not allowed in Global Configuration for Client
			if (!$this->helper->config->get('user_currency'))
			{
				$form->removeField('currency', 'profile');
			}

			$fieldIds    = $this->helper->category->getFields($categories, array('core'), true);
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile');

			foreach ($xmlElements as $xmlElement)
			{
				$form->load($xmlElement);
			}

			// Remove disabled fields from form
			if (empty($obj->client->category_id))
			{
				$cParams = new Registry;
			}
			else
			{
				$filter  = array('list.select' => 'a.params', 'id' => $obj->client->category_id);
				$cParams = $this->helper->category->loadResult($filter);
				$cParams = new Registry($cParams);
			}

			if (empty($obj->seller->category_id))
			{
				$sParams = $cParams;
			}
			else
			{
				$filter  = array('list.select' => 'a.params', 'id' => $obj->seller->category_id);
				$sParams = $this->helper->category->loadResult($filter);
				$sParams = new Registry($sParams);
			}

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

			if (($sParams->get('address', 1) == 1 || $sParams->get('address', 1) == 3))
			{
				$form->loadFile('profile/address');

				$fields = array(
					'address',
					'landmark',
					'country',
					'state_loc',
					'district',
					'zip',
					'mobile',

					'company',
					'po_box',
					'residential',
				);

				foreach ($fields as $fieldName)
				{
					$show = $this->helper->config->get('geolocation_levels.' . $fieldName, 1);

					if ($show == 0)
					{
						$form->removeField($fieldName, 'address');
					}
					else
					{
						$form->setFieldAttribute($fieldName, 'required', $show == 2 ? 'true' : 'false', 'address');
					}
				}

				$showM = $this->helper->config->get('geolocation_levels.mobile', 1);
				$showZ = $this->helper->config->get('geolocation_levels.zip', 1);

				if ($showM && ($regexM = $this->helper->config->get('address_mobile_regex', '')))
				{
					$form->setFieldAttribute('mobile', 'validate', 'regex', 'address');
					$form->setFieldAttribute('mobile', 'regex', $regexM, 'address');
				}

				if ($showZ && ($regexZ = $this->helper->config->get('address_zip_regex', '')))
				{
					$form->setFieldAttribute('zip', 'validate', 'regex', 'address');
					$form->setFieldAttribute('zip', 'regex', $regexZ, 'address');
				}
			}

			// Remove Address Field Group for Seller
			if (!empty($obj->seller->category_id))
			{
				$form->removeGroup('address');
			}
		}

		// No T&C in edit profile
		$form->removeField('agree_tnc');

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		// Extract variables
		$custom  = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile = ArrayHelper::getValue($data, 'profile', null);
		$seller  = ArrayHelper::getValue($data, 'seller', null);
		$client  = ArrayHelper::getValue($data, 'client', null);
		$address = ArrayHelper::getValue($data, 'address', null);

		unset($data['custom_profile'], $data['profile'], $data['seller'], $data['client'], $data['address']);

		$isNew = empty($data['id']);

		$data['id'] = $this->getState('profile.id');

		$user = $this->saveUser($data);

		if (!($user instanceof JUser))
		{
			return false;
		}

		// Set up profile and all for the user just saved
		$this->helper->user->saveProfile($profile, $user->id);

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

		if ($custom)
		{
			$this->helper->user->saveCustomProfile($user->id, (array) $custom);
		}

		// save seller info
		if (!empty($seller))
		{
			$this->helper->user->addLinkedAccounts(array('seller' => $seller), $user->id);
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

		if ($address)
		{
			$address['is_primary'] = 1;

			$this->helper->user->saveAddress($address, $user->id);
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.profile', $user, $isNew));

		return $user->id;
	}

	/**
	 * Save the user record
	 *
	 * @param   array  $data  The data to save for related Joomla user account.
	 *
	 * @return  JUser|bool  The user id of the user account on success, false otherwise
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function saveUser($data)
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

		return $user;
	}

	/**
	 * Pre-process loaded item before returning if needed
	 *
	 * @param   JObject  $item
	 *
	 * @return  JObject
	 *
	 * @since   1.6.0
	 */
	protected function processItem($item)
	{
		$item = parent::processItem($item);
		$pk   = $item->get('id');

		$profile = $this->helper->profile->getItem(array('user_id' => $pk));
		$seller  = $this->helper->seller->getItem(array('user_id' => $pk));
		$client  = $this->helper->client->getItem(array('user_id' => $pk));
		$address = $this->helper->user->getPrimaryAddress($pk);
		$custom  = $this->helper->field->getValue('profile', $pk);

		$item->set('profile', $profile);
		$item->set('seller', $seller);
		$item->set('client', $client);
		$item->set('address', $address);
		$item->set('custom_profile', $custom);

		return $item;
	}
}

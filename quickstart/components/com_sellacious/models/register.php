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
 */
class SellaciousModelRegister extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return false;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return false;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$catid = $this->app->getUserStateFromRequest('com_sellacious.edit.register.catid', 'catid', 0, 'int');

		if ($catid)
		{
			$filters  = array('list.select' => 'a.id', 'id' => $catid, 'type' => 'client');
			$category = $this->helper->category->loadObject($filters);
		}
		else
		{
			$category = $this->helper->category->getDefault('client', 'a.id');
		}

		if ($category)
		{
			$this->state->set('register.catid', $category->id);
		}
		else
		{
			$this->setError(JText::_('COM_SELLACIOUS_REGISTER_NO_CATEGORY_SELECTED'));
		}
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
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
	 *
	 * @return  JForm|bool  A JForm object on success, false on failure
	 *
	 * @since   1.2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$name = strtolower($this->option . '.' . $this->name);

		$form = $this->loadForm($name, 'profile', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data  = $this->app->getUserStateFromRequest("$this->option.edit.$this->name.data", 'jform', array(), 'array');
		$catid = $this->getState('register.catid');

		$data['client']['category_id'] = $catid;

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
	 * @since   1.2.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj   = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$catid = $this->getState('register.catid');
		$me    = JFactory::getUser();

		$obj->client = new stdClass;

		$obj->client->category_id = $catid;

		if (!empty($obj->client->category_id))
		{
			$form->loadFile('profile/client');
			$form->removeField('org_certificate', 'client');

			$fieldIds    = $this->helper->category->getFields(array($obj->client->category_id), array('core'), true);
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile');

			foreach ($xmlElements as $xmlElement)
			{
				$form->load($xmlElement);
			}
		}

		$form->setFieldAttribute('password', 'required', 'true');
		$form->setFieldAttribute('password2', 'required', 'true');

		if (!$this->helper->config->get('user_currency'))
		{
			$form->removeField('currency', 'profile');
		}

		// Remove disabled fields from form
		$category = $this->helper->category->getItem($catid);
		$params   = new Registry($category->params);

		if (!($params->get('name', 1) == 1 || $params->get('name', 1) == 2))
		{
			$form->removeField('name');
		}

		if (!($params->get('params.timezone', 1) == 1 || $params->get('params.timezone', 1) == 2))
		{
			$form->removeField('timezone', 'params');
		}

		if (!$params->get('profile.avatar', 1) || !isset($obj->id))
		{
			$form->removeField('avatar');
		}

		if (!($params->get('client.client_type', 1) == 1 || $params->get('client.client_type', 1) == 2))
		{
			$form->removeField('client_type', 'client');
		}

		if (!($params->get('client.business_name', 1) == 1 || $params->get('client.business_name', 1) == 2))
		{
			$form->removeField('business_name', 'client');
		}

		if (!($params->get('client.org_reg_no', 1) == 1 || $params->get('client.org_reg_no', 1) == 2))
		{
			$form->removeField('org_reg_no', 'client');
		}

		if (!($params->get('client.org_certificate', 1) == 1 || $params->get('client.org_certificate', 1) == 2))
		{
			$form->removeField('org_certificate', 'client');
		}

		if (!($params->get('profile.mobile', 1) == 1 || $params->get('profile.mobile', 1) == 2))
		{
			$form->removeField('mobile', 'profile');
		}

		if (!($params->get('profile.website', 1) == 1 || $params->get('profile.website', 1) == 2))
		{
			$form->removeField('website', 'profile');
		}

		if (!($params->get('profile.currency', 1) == 1 || $params->get('profile.currency', 1) == 2))
		{
			$form->removeField('currency', 'profile');
		}

		if (!($params->get('profile.bankinfo.name', 1) == 1 || $params->get('profile.bankinfo.name', 1) == 2))
		{
			$form->removeField('name', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.country', 1) == 1 || $params->get('profile.bankinfo.country', 1) == 2))
		{
			$form->removeField('country', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.branch', 1) == 1 || $params->get('profile.bankinfo.branch', 1) == 2))
		{
			$form->removeField('branch', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.beneficiary', 1) == 1 || $params->get('profile.bankinfo.beneficiary', 1) == 2))
		{
			$form->removeField('beneficiary', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.accountnumber', 1) == 1 || $params->get('profile.bankinfo.accountnumber', 1) == 2))
		{
			$form->removeField('accountnumber', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.code', 1) == 1 || $params->get('profile.bankinfo.code', 1) == 2))
		{
			$form->removeField('code', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.micr', 1) == 1 || $params->get('profile.bankinfo.micr', 1) == 2))
		{
			$form->removeField('micr', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.ifsc', 1) == 1 || $params->get('profile.bankinfo.ifsc', 1) == 2))
		{
			$form->removeField('ifsc', 'profile.bankinfo');
		}

		if (!($params->get('profile.bankinfo.swift', 1) == 1 || $params->get('profile.bankinfo.swift', 1) == 2))
		{
			$form->removeField('swift', 'profile.bankinfo');
		}

		if (!($params->get('profile.taxinfo.salestax', 1) == 1 || $params->get('profile.taxinfo.salestax', 1) == 2))
		{
			$form->removeField('salestax', 'profile.taxinfo');
		}

		if (!($params->get('profile.taxinfo.servicetax', 1) == 1 || $params->get('profile.taxinfo.servicetax', 1) == 2))
		{
			$form->removeField('servicetax', 'profile.taxinfo');
		}

		if (!($params->get('profile.taxinfo.incometax', 1) == 1 || $params->get('profile.taxinfo.incometax', 1) == 2))
		{
			$form->removeField('incometax', 'profile.taxinfo');
		}

		if (!($params->get('profile.taxinfo.tax', 1) == 1 || $params->get('profile.taxinfo.tax', 1) == 2))
		{
			$form->removeField('tax', 'profile.taxinfo');
		}

		if (($params->get('address', 1) == 1 || $params->get('address', 1) == 2))
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

		if (!$me->guest || $params->get('tnc', 1) == 0)
		{
			$form->removeField('agree_tnc');
		}
		else
		{
			$form->setFieldAttribute('agree_tnc', 'catid', $catid);
		}

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
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$postData = $data;

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.register', $data, true));

		$custom   = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile  = ArrayHelper::getValue($postData, 'profile', array(), 'array');
		$client   = ArrayHelper::getValue($postData, 'client', array(), 'array');
		$address  = ArrayHelper::getValue($postData, 'address', array(), 'array');

		unset($postData['custom_profile'], $postData['profile'], $postData['client'], $postData['address']);

		$postData['id']       = $this->getState($this->name . '.id');
		$postData['username'] = isset($postData['username']) ? $postData['username'] : $postData['email'];

		try
		{
			$user = $this->helper->user->autoRegister(new Registry($postData));

			if (!($user instanceof JUser))
			{
				return false;
			}

			$this->setState($this->name . '.id', $user->id);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Set up profile and all for the user just saved
		if ($profile)
		{
			$this->helper->user->saveProfile($profile, $user->id);
		}
		else
		{
			$this->helper->profile->create($user->id);
		}

		if ($custom)
		{
			$this->helper->user->saveCustomProfile($user->id, (array) $custom);
		}

		$client['category_id'] = $this->getState('register.catid');
		$this->helper->user->addLinkedAccounts(array('client' => $client), $user->id);

		if ($address)
		{
			$address['is_primary'] = 1;

			$this->helper->user->saveAddress($address, $user->id);
		}

		$data['id'] = $user->id;

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.register', (object) $data, true));

		return $user->id;
	}
}

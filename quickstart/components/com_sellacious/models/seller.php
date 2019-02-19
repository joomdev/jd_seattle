<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious seller model.
 */
class SellaciousModelSeller extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
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

		$catid = $this->app->getUserStateFromRequest('com_sellacious.edit.seller.catid', 'catid', 0, 'int');

		if ($catid)
		{
			$filters  = array('list.select' => 'a.id', 'id' => $catid, 'type' => 'seller');
			$category = $this->helper->category->loadObject($filters);
		}
		else
		{
			$category = $this->helper->category->getDefault('seller', 'a.id');
		}

		if ($category)
		{
			$this->state->set('seller.catid', $category->id);
		}
		else
		{
			$this->setError(JText::_('COM_SELLACIOUS_SELLER_REGISTER_NO_CATEGORY_SELECTED'));
		}

		$me = JFactory::getUser();

		if (!$me->guest)
		{
			$this->state->set('seller.id', $me->id);
		}
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

		$data['seller']['category_id'] = $catid;

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$me = JFactory::getUser();

		if (!$me->guest)
		{
			if (is_object($data))
			{
				$data->name  = $me->name;
				$data->email = $me->email;
			}
			elseif (is_array($data))
			{
				$data['name']  = $me->name;
				$data['email'] = $me->email;
			}
		}

		parent::preprocessData($context, $data, $group);
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
		$me = JFactory::getUser();

		if (!$me->guest)
		{
			$form->setFieldAttribute('name', 'disabled', 'true');
			$form->setFieldAttribute('email', 'disabled', 'true');
			$form->setFieldAttribute('name', 'required', 'false');
			$form->setFieldAttribute('email', 'required', 'false');
			$form->setFieldAttribute('avatar', 'recordId', $me->id);
		}

		$obj   = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$catid = $this->getState('seller.catid');

		$obj->seller = new stdClass;

		$obj->seller->category_id = $catid;

		if (empty($obj->seller->category_id))
		{
			$form->removeGroup('seller');
		}
		else
		{
			$fieldIds    = $this->helper->category->getFields(array($obj->seller->category_id), array('core'), true);
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile');

			foreach ($xmlElements as $xmlElement)
			{
				$form->load($xmlElement);
			}
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

		if (!($params->get('seller.title', 1) == 1 || $params->get('seller.title', 1) == 2))
		{
			$form->removeField('title', 'seller');
		}

		if (!$params->get('seller.avatar', 1) == 1 || !$me->id)
		{
			$form->removeField('avatar');
		}

		if (!($params->get('seller.store_name', 1) == 1 || $params->get('seller.store_name', 1) == 2))
		{
			$form->removeField('store_name', 'seller');
		}

		if (!($params->get('seller.store_address', 1) == 1 || $params->get('seller.store_address', 1) == 2))
		{
			$form->removeField('store_address', 'seller');
		}

		if (!($params->get('seller.currency', 1) == 1 || $params->get('seller.currency', 1) == 2))
		{
			$form->removeField('currency', 'seller');
		}

		if (!($params->get('seller.store_location', 1) == 1 || $params->get('seller.store_location', 1) == 2))
		{
			$form->removeField('store_location', 'seller');
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

		// We also show for registered users as seller registration is only shown for non-sellers
		if ($params->get('tnc', 1) == 0)
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
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$postData = $data;

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.seller', $data, true));

		$custom  = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile = ArrayHelper::getValue($postData, 'profile', array(), 'array');
		$seller  = ArrayHelper::getValue($postData, 'seller', array(), 'array');

		unset($postData['custom_profile'], $postData['profile'], $postData['seller']);

		$postData['id']       = $this->getState($this->name . '.id');
		$postData['username'] = isset($postData['username']) ? $postData['username'] : $postData['email'];

		if (JFactory::getUser()->guest)
		{
			try
			{
				$user = $this->helper->user->autoRegister(new Registry($postData));

				if (!($user instanceof JUser))
				{
					return false;
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			$user = JUser::getInstance($postData['id']);
		}

		$this->setState($this->name . '.id', $user->id);

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

		if ($profile)
		{
			// Set up profile and all for the user just saved
			$oProfile = (array) $this->helper->profile->getItem(array('user_id' => $user->id));
			$profile  = array_merge($oProfile, $profile);
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

		$seller['category_id'] = $this->getState('seller.catid');
		$this->helper->user->addLinkedAccounts(array('seller' => $seller), $user->id);

		$data['id'] = $user->id;

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.seller', (object) $data, true));

		return $user->id;
	}
}

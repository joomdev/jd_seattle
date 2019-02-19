<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious model.
 */
class SellaciousModelCoupon extends SellaciousModelAdmin
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
		return $this->helper->access->check('coupon.delete') ||
			($this->helper->access->check('coupon.delete.own') && $record->seller_uid == JFactory::getUser()->id);
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
		return $this->helper->access->check('coupon.edit.state');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		// Initialise variables
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('sellacious');

		$table = $this->getTable();
		$pk    = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		try
		{
			$table->bind($data);
			$table->check();

			// Trigger the onBeforeSave event.
			$dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

			// Store the data.
			$table->store();

			// Trigger the onAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		$this->setState($this->getName() . '.id', $table->get('id'));

		return true;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$data     = is_array($data) ? ArrayHelper::toObject($data, 'stdClass', false) : $data;
		$me       = JFactory::getUser();
		$isSeller = $this->helper->seller->is();

		// No default selection for Admin, but a must for sellers
		if ($this->helper->access->check('coupon.edit'))
		{
			if (!isset($data->seller_uid))
			{
				$data->seller_uid = $this->app->getUserState('com_sellacious.edit.coupon.seller_uid', null);
			}
		}
		else
		{
			$data->seller_uid = $isSeller ? $me->id : 0;
		}

		$this->app->setUserState('com_sellacious.edit.coupon.seller_uid', $data->seller_uid);

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception  If there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		if (!$this->helper->config->get('multi_seller', 0))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');
		}

		// If allowed to change all then only provide sellers list.
		if (!$this->helper->access->check('coupon.edit'))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');

			$form->setFieldAttribute('state', 'type', 'hidden');
			$form->setFieldAttribute('state', 'hidden', 'true');
			$form->setFieldAttribute('state', 'readonly', 'true');
		}

		parent::preprocessForm($form, $data, $group);
	}
}

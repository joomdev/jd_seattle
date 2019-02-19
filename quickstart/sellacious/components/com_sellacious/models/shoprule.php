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
 * Sellacious Shop rule model
 */
class SellaciousModelShoprule extends SellaciousModelAdmin
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
		return $this->helper->access->check('shoprule.delete') ||
			($this->helper->access->check('shoprule.delete.own') && $record->seller_uid == JFactory::getUser()->id);
	}

	/**
	 * Method to test whether a record can be edited.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('shoprule.edit.state');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @since   1.6
	 */
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();

		/** @var SellaciousTableShopRule $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);

			$isNew = false;
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		// Bind the data.
		$table->bind($data);

		// Check the data.
		$table->check();

		// Trigger the onBeforeSave event.
		$context = $this->option . '.' . $this->name;
		$dispatcher->trigger($this->event_before_save, array($context, &$table, $isNew));

		// Store the data.
		$table->store();

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($context, &$table, $isNew));

		// Rebuild the path for the shoprule:
		$table->rebuildPath($table->get('id'));

		// Rebuild the paths of the shop-rule's children
		$table->rebuild($table->get('id'), $table->lft, $table->level, $table->get('path'));

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
		if ($this->helper->access->check('shoprule.edit'))
		{
			if (!isset($data->seller_uid))
			{
				$data->seller_uid = $this->app->getUserState('com_sellacious.edit.shoprule.seller_uid', null);
			}
		}
		else
		{
			$data->seller_uid = $isSeller ? $me->id : 0;
		}

		$this->app->setUserState('com_sellacious.edit.shoprule.seller_uid', $data->seller_uid);

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param  JForm $form   A form object.
	 * @param  mixed $data   The data expected for the form.
	 * @param string $group  Plugin group to load
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$this->helper->core->loadPlugins('sellaciousrules');

		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if ($obj->type != 'discount')
		{
			$form->removeField('filterable');
		}

		if (isset($obj->parent_id) && $obj->parent_id == 0 && $obj->id > 0)
		{
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
		}

		if (!empty($obj->id))
		{
			$form->setFieldAttribute('type', 'readonly', 'true');
		}

		if (!$this->helper->config->get('multi_seller', 0))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');
		}

		// If allowed to change all then only provide sellers list.
		if (!$this->helper->access->check('shoprule.edit'))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');

			$form->setFieldAttribute('state', 'type', 'hidden');
			$form->setFieldAttribute('state', 'hidden', 'true');
			$form->setFieldAttribute('state', 'readonly', 'true');
		}

		if ($obj->sum_method == 1)
		{
			$form->removeField('apply_rule_on_price_display');
		}

		parent::preprocessForm($form, $data, $group);
	}
}

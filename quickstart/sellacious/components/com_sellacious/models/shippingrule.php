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

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Shop rule model
 *
 * @since   1.2.0
 */
class SellaciousModelShippingRule extends SellaciousModelAdmin
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
		$me      = JFactory::getUser();
		$allowed = $this->helper->access->check('shippingrule.delete') ||
			($me->id == $record->owned_by && $this->helper->access->check('shippingrule.delete.own'));

		return $allowed;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since  12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('shippingrule.edit.state');
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
		if (is_object($data))
		{
			$method = $data->get('method_name');

			if ($method == 'slabs.weight')
			{
				$key = 'weight_slabs';
			}
			elseif ($method == 'slabs.quantity')
			{
				$key = 'quantity_slabs';
			}
			elseif ($method == 'slabs.price')
			{
				$key = 'price_slabs';
			}
			else
			{
				$key = null;
			}

			if ($key)
			{
				try
				{
					$data->slabs[$key] = $this->helper->shippingRule->getSlabs($data->id);
				}
				catch (Exception $e)
				{
					$data->slabs[$key] = array();
				}
			}

		}

		parent::preprocessData($context, $data, $group);
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
	 * @since   1.6
	 */
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();

		/** @var SellaciousTableShippingRule $table */
		$table = $this->getTable();
		$pk    = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		$slabs = !empty($data['slabs']) ? $data['slabs'] : array();


		// Include the plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);

			$isNew = false;
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title)   = $this->generateNewTitle(null, null, $data['title']);
			$data['title'] = $title;
		}

		$table->bind($data);
		$table->check();

		// Trigger the onBeforeSave event.
		$context = $this->option . '.' . $this->name;
		$dispatcher->trigger($this->event_before_save, array($context, &$table, $isNew));

		if (!$this->helper->access->check('shippingrule.edit'))
		{
			$table->set('owned_by', JFactory::getUser()->id);
		}

		// Store the data.
		$table->store();

		$ruleId = $table->get('id');
		$method = $table->get('method_name');

		$this->setState($this->getName() . '.id', $ruleId);

		if ($method == 'slabs.weight')
		{
			$slabs = ArrayHelper::getValue($slabs, 'weight_slabs', '[]');
		}
		elseif ($method == 'slabs.quantity')
		{
			$slabs = ArrayHelper::getValue($slabs, 'quantity_slabs', '[]');
		}
		elseif ($method == 'slabs.price')
		{
			$slabs = ArrayHelper::getValue($slabs, 'price_slabs', '[]');
		}
		else
		{
			$slabs = '[]';
		}

		$slabs = json_decode($slabs);

		$this->helper->shippingRule->clearSlabs($ruleId);

		foreach ($slabs as $slab)
		{
			$this->helper->shippingRule->addSlab($ruleId, $slab);
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($context, &$table, $isNew));

		$this->setState($this->getName() . '.id', $ruleId);

		return true;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the category or parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   12.2
	 */
	protected function generateNewTitle($parent_id, $alias, $title)
	{
		$table = $this->getTable();

		$keys = array('title' => $title);

		while ($table->load($keys))
		{
			$title = StringHelper::increment($title);

			$keys['title'] = $title;
		}

		return array($title, $alias);
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
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		$methods = (array) $this->helper->config->get('seller_shippable_methods', array());

		$form->setFieldAttribute('method_name', 'choices', implode('|', $methods));

		$methodName = empty($obj->method_name) ? '*' : $obj->method_name;

		switch ($methodName)
		{
			case '*':
				$form->setFieldAttribute('packaging_weight', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.weight':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.quantity':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('packaging_weight', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.price':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('packaging_weight', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				break;

			default:
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;
		}

		parent::preprocessForm($form, $data, $group);
	}
}

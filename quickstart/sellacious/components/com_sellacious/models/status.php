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
class SellaciousModelStatus extends SellaciousModelAdmin
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
		if ($record->is_core)
		{
			$this->setError(JText::_($this->text_prefix . '_CORE_DELETE_DENIED'));

			return false;
		}

		return $this->helper->access->check('status.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param  object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		if ($record->is_core)
		{
			$this->setError(JText::_($this->text_prefix . '_CORE_EDIT_STATE_DENIED'));

			return false;
		}

		return $this->helper->access->check('status.edit.state');
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm  $form  A form object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error loading the form.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if (!empty($obj->context))
		{
			$form->setFieldAttribute('allow_change_to', 'context', $obj->context);
		}

		if (empty($obj->context) || ($obj->context != 'order.physical' && $obj->context != 'order.electronic'))
		{
			$form->removeField('stock');
		}

		if (!empty($obj->is_core))
		{
			$form->setFieldAttribute('type', 'readonly', 'true');
			$form->setFieldAttribute('state', 'readonly', 'true');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Set stock handling for the status
	 *
	 * @param  int    $pk    Status Id
	 * @param  string $value New Value for handling - A, R, O, ''
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function setStockHandling($pk, $value)
	{
		$table = $this->getTable();

		$table->load($pk);

		if ($table->get('id'))
		{
			$table->set('stock', $value);

			$table->store();
		}
		else
		{
			throw new Exception($this->text_prefix . '_INVALID_ITEM');
		}

		return true;
	}
}

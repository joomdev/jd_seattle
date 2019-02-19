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
class SellaciousModelCurrency extends SellaciousModelAdmin
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
		return $this->helper->access->check('currency.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('currency.edit.state');
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
	 * @since   1.2.0
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JEventDispatcher::getInstance();

		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
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
			list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		$this->setState($this->getName() . '.id', $table->get('id'));

		return true;
	}

	/**
	 * Save the forex rate submitted
	 *
	 * @param  $data
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function saveForex($data)
	{
		if (empty($data['x_from']) || empty($data['x_to']))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CURRENCIES_FOREX_BASE_TARGET_ARGUMENTS_REQUIRED'));
		}

		$saved = false;

		// Skip if source and target are same
		if ($data['x_from'] != $data['x_to'])
		{
			$x_factor     = ArrayHelper::getValue($data, 'x_factor');
			$x_factor_inv = ArrayHelper::getValue($data, 'x_factor_inv');

			if ($factor = $x_factor ? $x_factor : ($x_factor_inv ? 1.0 / $x_factor_inv : null))
			{
				$this->helper->currency->setRate($data['x_from'], $data['x_to'], round($factor, 5));

				$saved = true;
			}

			if ($factor = $x_factor_inv ? $x_factor_inv : ($x_factor ? 1.0 / $x_factor : null))
			{
				$this->helper->currency->setRate($data['x_to'], $data['x_from'], round($factor, 5));

				$saved = true;
			}
		}

		return $saved;
	}
}

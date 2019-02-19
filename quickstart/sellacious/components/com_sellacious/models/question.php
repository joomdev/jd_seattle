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

/**
 * Sellacious questions model.
 *
 * @since   1.6.0
 */
class SellaciousModelQuestion extends SellaciousModelAdmin
{

	/**
	 * Method to save the form data.
	 *
	 * @param   array  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6.0
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

		$me = JFactory::getUser();

		$data['state']      = 1;
		$data['replied_by'] = $me->id;
		$data['replied']    = JFactory::getDate()->toSql();

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
}

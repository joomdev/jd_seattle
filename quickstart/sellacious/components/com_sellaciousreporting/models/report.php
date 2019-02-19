<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access.
defined('_JEXEC') or die;

use Sellacious\Report\ReportHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;

/**
 * Report model.
 *
 * @since  1.6.0
 */
class SellaciousreportingModelReport extends SellaciousModelAdmin
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function populateState()
	{
		parent::populateState();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6.0
	 */

	public function getTable($type = 'Report', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	* Method to get the record form.
	*
	* @param   array    $data      Data for the form.
	* @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	*
	* @return  mixed    A JForm object on success, false on failure
	*
	* @since   1.6.0
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_sellaciousreporting.report',
			'report',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
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
	 * @throws  Exception if there is an error in the form event.
	 * @since   1.6.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellaciousreporting')
	{
		$registry = new Registry($data);

		$handlerName = $registry->get('handler', null);

		$form->setFieldAttribute('columns', 'handler', $handlerName);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_sellaciousreporting.edit.report.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Pre-process loaded item before returning if needed
	 *
	 * @param   object  $item
	 *
	 * @return  object
	 *
	 * @since   1.6.0
	 */
	protected function processItem($item)
	{
		$item = parent::processItem($item);

		$query = $this->_db->getQuery(true);
		$query->select('a.user_cat_id')
			->from($this->_db->qn('#__sellacious_reports_permissions', 'a'))
			->where('a.permission_type = ' . $this->_db->quote('view'))
			->where('a.report_id = ' . (int) $item->id);

		$viewPermissions = (array) $this->_db->setQuery($query)->loadColumn();

		$item->permissions["reports_permissions_view"] = $viewPermissions;

		$query = $this->_db->getQuery(true);
		$query->select('a.user_cat_id')
			->from($this->_db->qn('#__sellacious_reports_permissions', 'a'))
			->where('a.permission_type = ' . $this->_db->quote('edit'))
			->where('a.report_id = ' . (int) $item->id);

		$editPermissions = (array) $this->_db->setQuery($query)->loadColumn();

		$item->permissions["reports_permissions_edit"] = $editPermissions;

		return $item;
	}

	/**
	 * Method to save the record
	 *
	 * @param   array  $data  Submitted data to save
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function save($data)
	{
		// Initialise variables
		$app        = JFactory::getApplication();
		$dispatcher = JEventDispatcher::getInstance();
		$date       = JFactory::getDate();
		$user       = JFactory::getUser();

		/** @var SellaciousreportingTableReport $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('sellaciousreporting');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		$permissions = ArrayHelper::getValue($data, 'permissions', array(), 'array');
		$viewPermissions = array();
		$editPermissions = array();

		if (!empty($permissions))
		{
			$viewPermissions = $permissions["reports_permissions_view"];
			$editPermissions = $permissions["reports_permissions_edit"];
		}

		if (!empty($data["filter"]))
		{
			$filter = new Registry($data["filter"]);
			$data["filter"] = $filter->toString();
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

		if (!empty($viewPermissions))
		{
			// Add allowed user groups
			$reportId = $table->id;

			$query = $this->_db->getQuery(true);
			$query->select('a.user_cat_id')
				->from($this->_db->qn('#__sellacious_reports_permissions', 'a'))
				->where('a.report_id = ' . (int) $reportId)
				->where('a.permission_type = ' . $this->_db->quote('view'));

			$current = (array) $this->_db->setQuery($query)->loadColumn();

			$add    = array_diff($viewPermissions, $current);
			$remove = array_diff($current, $viewPermissions);

			if ($remove)
			{
				$query->clear()
					->delete($this->_db->qn('#__sellacious_reports_permissions'))
					->where('report_id = ' . (int) $reportId)
					->where('permission_type = ' . $this->_db->quote('view'))
					->where('user_cat_id IN (' . implode(',', array_map('intval', $remove)) . ')');

				$this->_db->setQuery($query)->execute();
			}

			if ($add)
			{
				$o = new stdClass;

				$o->report_id = $reportId;

				foreach ($add as $permission)
				{
					$o->user_cat_id = $permission;
					$o->permission_type = 'view';

					$this->_db->insertObject('#__sellacious_reports_permissions', $o, null);
				}
			}
		}
		else if ($table->id)
		{
			$query = $this->_db->getQuery(true);
			$reportId = $table->id;

			$query->clear()
				->delete($this->_db->qn('#__sellacious_reports_permissions'))
				->where('report_id = ' . (int) $reportId)
				->where('permission_type = ' . $this->_db->quote('view'));

			$this->_db->setQuery($query)->execute();
		}

		if (!empty($editPermissions))
		{
			// Add allowed user groups
			$reportId = $table->id;

			$query = $this->_db->getQuery(true);
			$query->select('a.user_cat_id')
				->from($this->_db->qn('#__sellacious_reports_permissions', 'a'))
				->where('a.report_id = ' . (int) $reportId)
				->where('a.permission_type = ' . $this->_db->quote('edit'));

			$current = (array) $this->_db->setQuery($query)->loadColumn();

			$add    = array_diff($editPermissions, $current);
			$remove = array_diff($current, $editPermissions);

			if ($remove)
			{
				$query->clear()
					->delete($this->_db->qn('#__sellacious_reports_permissions'))
					->where('report_id = ' . (int) $reportId)
					->where('permission_type = ' . $this->_db->quote('edit'))
					->where('user_cat_id IN (' . implode(',', array_map('intval', $remove)) . ')');

				$this->_db->setQuery($query)->execute();
			}

			if ($add)
			{
				$o = new stdClass;

				$o->report_id = $reportId;

				foreach ($add as $permission)
				{
					$o->user_cat_id = $permission;
					$o->permission_type = 'edit';

					$this->_db->insertObject('#__sellacious_reports_permissions', $o, null);
				}
			}
		}
		else if ($table->id)
		{
			$query = $this->_db->getQuery(true);
			$reportId = $table->id;

			$query->clear()
				->delete($this->_db->qn('#__sellacious_reports_permissions'))
				->where('report_id = ' . (int) $reportId)
				->where('permission_type = ' . $this->_db->quote('edit'));

			$this->_db->setQuery($query)->execute();
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		$this->setState($this->getName() . '.id', $table->get('id'));

		return true;
	}
}

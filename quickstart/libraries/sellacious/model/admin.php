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
 * Sellacious admin model.
 *
 * @package  Sellacious
 *
 * @since    3.0
 */
abstract class SellaciousModelAdmin extends JModelAdmin
{
	/**
	 * @var   SellaciousHelper
	 *
	 * @since  1.0.0
	 */
	protected $helper;

	/**
	 * @var  \JApplicationCms
	 *
	 * @since   1.6.0
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @see     JModelAdmin
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app         = JFactory::getApplication();
		$this->helper      = SellaciousHelper::getInstance();
		$this->text_prefix = strtoupper($this->option . '_' . $this->name);
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
	 * @since   1.0.0
	 */
	public function getTable($type = '', $prefix = 'SellaciousTable', $config = array())
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
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$name = strtolower($this->option . '.' . $this->name);

		$form = $this->loadForm($name, strtolower($this->name), array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a form object.
	 * Sellacious Notes: We need to override coz of the parent behaviour is not as expected in terms of 'loadData' config
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   mixed    $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 * @since   11.1
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = ArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		try
		{
			/** @var  $form  JForm */
			$form = JForm::getInstance($name, $source, $options, false, $xpath);

			// Get the data for the form.
			$data = $this->loadFormData();

			// Allow for additional modification of the form, and events to be triggered on original context.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			if (!isset($options['load_data']) || !$options['load_data'])
			{
				$data = array();
			}

			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
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
		$data = $this->app->getUserStateFromRequest($this->option . '.edit.' . $this->name . '.data', 'jform', array(), 'array');

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->helper->core->loadPlugins();

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
		// Get the dispatcher and load the users plugins.
		$dispatcher = $this->helper->core->loadPlugins('sellacious');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));

		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}

		if (is_object($data))
		{
			/**
			 * VERIFY: Why this conversion to array was needed @2016-02-02@
			 * RESULT: Due to a Joomla bug in J3.4.x JObject private attribute was included in array conversion
			 * with **prefix causing issues. @2016-02-02@
			 */
			if (version_compare(JVERSION, '3.4.8', '<='))
			{
				$data = ArrayHelper::fromObject($data);
			}
		}
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
	 * @throws  Exception if there is an error in the form event.
	 *
	 * @see     JFormField
	 *
	 * @since   12.2
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$this->helper->core->loadPlugins();

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to return a single record. Joomla model doesn't use caching, we use.
	 *
	 * @param   int  $pk  (optional) The record id of desired item.
	 *
	 * @return  JObject
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getItem($pk = null)
	{
		static $cache = null;

		$pk = !empty($pk) ? $pk : (int) $this->getState($this->getName() . '.id');

		if (empty($cache[$pk]))
		{
			$item = parent::getItem($pk);

			$item = $this->processItem($item);

			$cache[$pk] = $item;
		}

		return $cache[$pk];
	}

	/**
	 * Pre-process loaded item before returning if needed
	 *
	 * @param   JObject  $item
	 *
	 * @return  JObject
	 *
	 * @since   1.0.0
	 */
	protected function processItem($item)
	{
		return $item;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		// Get an instance of the table object.
		$table = $this->getTable();

		if (!$table instanceOf JTableNested)
		{
			$this->setError(JText::_('COM_SELLACIOUS_ITEM_NOT_SUPPORTED_REBUILD'));

			return false;
		}

		if (!$table->rebuild())
		{
			$this->setError($table->getError());

			return false;
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onAfterTableRebuild', array('com_sellacious.' . $this->getName()));

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @throws  Exception
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		if (!parent::save($data))
		{
			if ($error = $this->getError())
			{
				throw new Exception($error);
			}
		}

		return true;
	}

	/**
	 * Method to save the reordered table nested set tree using JTableNested and normal using parent class implementation.
	 *
	 * @param   array  $idArray    An array of primary key ids.
	 * @param   array  $lft_array  The lft value
	 *
	 * @return  bool  False on failure or error, True otherwise
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		$table = $this->getTable();

		if (!$table instanceOf JTableNested)
		{
			// @fixme: Ordering needs to be verified for non-nested tables
			return parent::saveorder($idArray, $lft_array);
		}

		if (!$table->saveorder($idArray, $lft_array))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the cache
		$this->cleanCache();

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
	 * @throws  Exception
	 *
	 * @since    12.2
	 */
	protected function generateNewTitle($parent_id, $alias, $title)
	{
		$table = $this->getTable();

		if (property_exists($table, 'parent_id'))
		{
			$keys = array('alias' => $alias, 'parent_id' => $parent_id);
		}
		elseif (property_exists($table, 'catid'))
		{
			$keys = array('alias' => $alias, 'catid' => $parent_id);
		}
		else
		{
			$keys = array('alias' => $alias);
		}

		while ($table->load($keys))
		{
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');

			$keys['alias'] = $alias;
		}

		return array($title, $alias);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.

	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	public function delete(&$pks)
	{
		$dispatcher = \JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();

		// Include the plugins for the delete events.
		\JPluginHelper::importPlugin($this->events_map['delete']);

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canDelete($table))
				{
					$context = $this->option . '.' . $this->name;

					// Trigger the before delete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

					if (in_array(false, $result, true))
					{
						$this->setError($table->getError());

						return false;
					}

					// Multilanguage: if associated, delete the item in the _associations table
					if ($this->associationsContext && \JLanguageAssociations::isEnabled())
					{
						$db = $this->getDbo();
						$query = $db->getQuery(true)
							->select('COUNT(*) as count, ' . $db->quoteName('as1.key'))
							->from($db->quoteName('#__associations') . ' AS as1')
							->join('LEFT', $db->quoteName('#__associations') . ' AS as2 ON ' . $db->quoteName('as1.key') . ' =  ' . $db->quoteName('as2.key'))
							->where($db->quoteName('as1.context') . ' = ' . $db->quote($this->associationsContext))
							->where($db->quoteName('as1.id') . ' = ' . (int) $pk)
							->group($db->quoteName('as1.key'));

						$db->setQuery($query);
						$row = $db->loadAssoc();

						if (!empty($row['count']))
						{
							$query = $db->getQuery(true)
								->delete($db->quoteName('#__associations'))
								->where($db->quoteName('context') . ' = ' . $db->quote($this->associationsContext))
								->where($db->quoteName('key') . ' = ' . $db->quote($row['key']));

							if ($row['count'] > 2)
							{
								$query->where($db->quoteName('id') . ' = ' . (int) $pk);
							}

							$db->setQuery($query);
							$db->execute();
						}
					}

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}

					// Trigger the after event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);

					if (!$this->getError())
					{
						$this->setError(\JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					}

					return false;
				}
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}

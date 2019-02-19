<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import;

// no direct access
defined('_JEXEC') or die;

use Sellacious\Utilities\Timer;

/**
 * Import utility class
 *
 * @since   1.5.0
 */
class UsersImporter
{
	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $name = 'Users';

	/**
	 * @var    array
	 *
	 * @since   1.5.0
	 */
	protected $options = array();

	/**
	 * @var    \JDatabaseDriver
	 *
	 * @since   1.5.0
	 */
	protected $db;

	/**
	 * @var    \SellaciousHelper
	 *
	 * @since   1.5.0
	 */
	protected $helper;

	/**
	 * @var    \JEventDispatcher
	 *
	 * @since   1.5.0
	 */
	protected $dispatcher;

	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $filename;

	/**
	 * @var    resource
	 *
	 * @since   1.5.0
	 */
	protected $fp;

	/**
	 * @var    Timer
	 *
	 * @since   1.5.0
	 */
	protected $timer;

	/**
	 * The actual CSV headers found in the uploaded file (always processed in the same character case as provided in the CSV)
	 *
	 * @var    string[]
	 *
	 * @since   1.5.0
	 */
	protected $headers = array();

	/**
	 * The internal key names for the CSV columns (always processed in lowercase)
	 *
	 * @var    string[]
	 *
	 * @since   1.5.0
	 */
	protected $fields;

	/**
	 * Constructor
	 *
	 * @since   1.5.0
	 */
	public function __construct()
	{
		$this->db         = \JFactory::getDbo();
		$this->helper     = \SellaciousHelper::getInstance();
		$this->dispatcher = $this->helper->core->loadPlugins();
		$this->timer      = Timer::getInstance('Import.' . $this->name);
	}

	/**
	 * Set the import configuration options
	 *
	 * @param   string  $key    The name of the parameter to set
	 * @param   mixed   $value  The new value
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;
	}

	/**
	 * Get the import configuration options
	 *
	 * @param   string  $key  The name of the parameter to set
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * Get the columns for the users import CSV template for the given categories if any, or a basic one without any specifications
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function getColumns()
	{
		$columns = array();

		// Let the plugins add custom columns
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchImportColumns', array('com_importer.import.users', &$columns));

		return $columns;
	}

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $filename  The absolute file path for the CSV
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function load($filename)
	{
		// Try to read from the file
		ignore_user_abort(true);
		ini_set('auto_detect_line_endings', true);

		if (substr($filename, -4) != '.csv')
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		$fp = @fopen($filename, 'r');

		if (!$fp)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		// First row contains column header
		$headers = fgetcsv($fp);

		if (!$headers)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_NO_HEADER', basename($filename)));
		}

		$this->fp       = $fp;
		$this->filename = $filename;
		$this->headers  = $headers;
		$this->fields   = array_map('strtolower', $headers);
	}

	/**
	 * Get the fields from the active CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.5.0
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Method to apply column alias for the uploaded CSV. This is useful if the CSV column headers do not match the prescribed names
	 *
	 * @param   array  $aliases  The column alias array. [column => alias]
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function setColumnsAlias($aliases)
	{
		// If there are no aliases we skip mapping and use original headers
		if (!$aliases)
		{
			$fields = array_map('strtolower', $this->headers);
		}
		else
		{
			$fields = array();

			foreach ($this->headers as $index => $alias)
			{
				$field          = array_search($alias, $aliases) ?: '__IGNORE_' . (int) $index;
				$fields[$index] = strtolower($field);
			}
		}

		$this->check($fields);

		$this->fields = $fields;
	}

	/**
	 * Import the users from CSV that was earlier loaded
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 *
	 * @see    load()
	 */
	public function import()
	{
		$offset  = 0;
		$skipped = array();

		try
		{
			// Check file pointer
			if (!$this->fp)
			{
				throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_FILE_NOT_LOADED'));
			}

			// Check headers, if translated one is not available try using actual CSV header
			if (!$this->fields)
			{
				$this->fields = array_map('strtolower', $this->headers);
			}

			$this->check($this->fields);

			// Mark the start of process
			$this->timer->start(\JText::sprintf('COM_SELLACIOUS_IMPORT_START_FILENAME', basename($this->filename)));

			// Iterate over
			while($row = fgetcsv($this->fp))
			{
				$offset++;

				set_time_limit(30);

				// Convert the array into an associative array
				$record  = array_combine($this->fields, $row);

				// Let the plugins pre-process the record and perform any relevant task
				$returns = $this->dispatcher->trigger('onBeforeImportRecord', array('com_importer.import.users', &$record));

				if (in_array(false, $returns, true))
				{
					$skipped[] = $offset;

					continue;
				}

				// Cleanup the CSV values
				array_walk($record, 'trim');

				// Perform the native import operation
				$imported = $this->processRecord($record);

				if (!$imported)
				{
					$skipped[] = $offset;

					continue;
				}

				// Let the plugins post-process the record and perform any relevant task
				$returns = $this->dispatcher->trigger('onAfterImportRecord', array('com_importer.import.users', &$record));

				if (in_array(false, $returns, true))
				{
					$skipped[] = $offset;

					continue;
				}

				// Mark the progress
				$this->timer->hit($offset, 50, \JText::_('COM_SELLACIOUS_IMPORT_PROGRESS'));
			}

			// Rebuild any nested set tree involved
			$this->timer->stop(\JText::sprintf('COM_SELLACIOUS_IMPORT_REBUILD_NESTED_TABLE', $offset));

			/** @var  \JTableNested  $table */
			$table = $this->helper->category->getTable();
			$table->rebuild();

			$table = $this->helper->splCategory->getTable();
			$table->rebuild();

			// Mark the end of process
			$this->timer->stop(\JText::sprintf('COM_SELLACIOUS_IMPORT_FINISHED', $offset));

			if ($skipped)
			{
				$this->timer->log(\JText::sprintf('COM_SELLACIOUS_IMPORT_ITEMS_SKIPPED', count($skipped), implode(', ', $skipped)));
			}

			return true;
		}
		catch (\Exception $e)
		{
			// Mark the unexpected termination of process
			$this->timer->interrupt(\JText::sprintf('COM_SELLACIOUS_IMPORT_INTERRUPTED_AT_OFFSET', $offset, $e->getMessage()));

			if ($skipped)
			{
				$this->timer->log(\JText::sprintf('COM_SELLACIOUS_IMPORT_ITEMS_SKIPPED', count($skipped), implode(', ', $skipped)));
			}

			return false;
		}
	}

	/**
	 * Method to check whether the CSV columns are importable.
	 *
	 * @param   array  $fields  The alias processed column list
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function check($fields)
	{
		// Compare the columns list with minimal required fields only
		$columns = $this->getColumns();

		// Make sure all the column names are all in lowercase for consistency
		$fields  = array_map('strtolower', $fields);
		$columns = array_map('strtolower', $columns);

		$missing = array_diff($columns, $fields);

		if (count($missing))
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_IMPORT_ERROR_MISSING_COLUMNS_LIST', implode(',', $missing)));
		}
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   array  $record  The record as an associative array to be imported into sellacious
	 *
	 * @return  bool  Whether the record was imported successfully
	 *
	 * @since   1.5.0
	 */
	protected function processRecord(&$record)
	{
		try
		{
			// Order of saving following items is important, do not randomly move up-down unless very sure
			// $userId = $this->saveUser($record);

			// $this->saveUserAddress($record, $userId);
		}
		catch (\Exception $e)
		{
			$this->timer->log($e->getMessage());

			return false;
		}

		return true;
	}
}

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

use Joomla\Registry\Registry;
use Sellacious\Batch\Batch;
use Sellacious\Utilities\Timer;

/**
 * Import utility class
 *
 * @since   1.5.2
 */
abstract class AbstractImporter
{
	/**
	 * The temporary table name that would hold the staging data from CSV for import processing
	 *
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	public $importTable;

	/**
	 * @var    Timer
	 *
	 * @since   1.4.7
	 */
	public $timer;

	/**
	 * @var    Batch
	 *
	 * @since   1.6.1
	 */
	public $batch;

	/**
	 * @var    string
	 *
	 * @since   1.4.7
	 */
	protected $name;

	/**
	 * @var    Registry
	 *
	 * @since   1.4.7
	 */
	protected $options;

	/**
	 * @var    \JDatabaseDriver
	 *
	 * @since   1.4.7
	 */
	protected $db;

	/**
	 * @var    \SellaciousHelper
	 *
	 * @since   1.4.7
	 */
	protected $helper;

	/**
	 * @var    \JEventDispatcher
	 *
	 * @since   1.4.7
	 */
	protected $dispatcher;

	/**
	 * @var    string
	 *
	 * @since   1.4.7
	 */
	protected $filename;

	/**
	 * @var    resource
	 *
	 * @since   1.4.7
	 */
	protected $fp;

	/**
	 * The actual CSV headers found in the uploaded file (always processed in the same character case as provided in the CSV)
	 *
	 * @var    string[]
	 *
	 * @since   1.4.7
	 */
	protected $headers = array();

	/**
	 * The internal key names for the CSV columns (always processed in lowercase)
	 *
	 * @var    string[]
	 *
	 * @since   1.4.7
	 */
	protected $fields;

	/**
	 * Constructor
	 *
	 * @throws \Exception
	 *
	 * @since   1.4.7
	 */
	public function __construct()
	{
		preg_match('/([^\\\\]+)Importer$/', get_class($this), $r);

		$this->name       = strtolower($r[1]);
		$this->db         = \JFactory::getDbo();
		$this->helper     = \SellaciousHelper::getInstance();
		$this->dispatcher = $this->helper->core->loadPlugins();
		$this->timer      = Timer::getInstance('Import.' . $this->name);
		$this->options    = new Registry;
	}

	/**
	 * Set the import configuration options
	 *
	 * @param   string  $key    The name of the parameter to set
	 * @param   mixed   $value  The new value
	 *
	 * @return  static
	 *
	 * @since   1.4.7
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}

	/**
	 * Initialize the importer instance
	 *
	 * @param   ImportRecord  $import
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function setup(ImportRecord $import)
	{
		$this->setOption('import.id', $import->id);
		$this->setOption('import.path', $import->path);
		$this->setOption('import.log_path', $import->log_path);
		$this->setOption('import.output_path', $import->output_path);
		$this->setOption('session.user', $import->created_by);

		foreach ($import->options->toArray() as $oKey => $oValue)
		{
			$this->setOption($oKey, $oValue);
		}

		$batch = new Batch;
		$batch->setProgress($import->progress);

		$this->batch = $batch;
	}

	/**
	 * Method to be called by import processors to notify the importer about their progress
	 *
	 * @param   string  $name     The step name
	 * @param   int     $total    The total number of records to process
	 * @param   int     $ahead    The total number of records already processed
	 * @param   string  $message  Custom log message, default message will be used if not given
	 *
	 * @since   1.6.1
	 */
	public function setTicker($name, $total, $ahead, $message = null)
	{
		$step = $this->batch->getStep($name);

		if ($total !== null)
		{
			$step->setSize($total);
		}

		if ($message)
		{
			$this->timer->log($message);
		}
		else
		{
			$this->timer->hit($ahead, 1, sprintf('Total: %d %s', $step->getSize(), $name));
		}

		$step->setTick($ahead, $message);

		$iid = $this->getOption('import.id');

		if ($iid)
		{
			$o = (object) array('id' => $iid, 'progress' => (string) $this->batch);

			$this->db->updateObject('#__importer_imports', $o, array('id'));
		}
	}

	/**
	 * Method to be called by import processors to notify the importer about their progress
	 *
	 * @param   string  $name     The step name
	 * @param   string  $message  Custom log message, default message will be used if not given
	 *
	 * @since   1.6.1
	 */
	public function setComplete($name, $message = null)
	{
		$step = $this->batch->getStep($name);

		if ($message)
		{
			$this->timer->log($message);
		}

		$step->setComplete(true);

		$iid = $this->getOption('import.id');

		if ($iid)
		{
			$o = (object) array('id' => $iid, 'progress' => (string) $this->batch);

			$this->db->updateObject('#__importer_imports', $o, array('id'));
		}
	}

	/**
	 * Get the import configuration options
	 *
	 * @param   string  $key      The name of the parameter to set
	 * @param   mixed   $default  The default value to return if value not set
	 *
	 * @return  mixed
	 *
	 * @since   1.4.7
	 */
	public function getOption($key, $default = null)
	{
		return $this->options->get($key, $default);
	}

	/**
	 * Return the database driver instance linked to this object
	 *
	 * @return  \JDatabaseDriver
	 *
	 * @since   1.6.1
	 */
	public function getDb()
	{
		return $this->db;
	}

	/**
	 * Get the columns for the import CSV template for the given categories if any, or a basic one without any specifications
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	abstract public function getColumns();

	/**
	 * Get the additional columns for the records which are required for the import utility system
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	abstract public function getSysColumns();

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $filename  The absolute file path for the CSV
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public function load($filename)
	{
		// Try to read from the file
		ignore_user_abort(true);
		ini_set('auto_detect_line_endings', true);

		if (substr($filename, -4) != '.csv')
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_IMPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		$fp = @fopen($filename, 'r');

		if (!$fp)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_IMPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		// First row contains column header
		$headers = fgetcsv($fp);

		if (!$headers)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_IMPORT_ERROR_FILE_NO_HEADER', basename($filename)));
		}

		$this->fp       = $fp;
		$this->filename = $filename;

		// Remove BOM, if found. Only first column can have this as BOM occurs at the beginning of files.
		$BOM = pack("CCC", 0xef, 0xbb, 0xbf);

		if (isset($headers[0]) && substr($headers[0], 0,3) == $BOM)
		{
			$headers[0] = substr($headers[0], 3);
		}

		$this->headers = array_map('trim', $headers);
		$this->fields  = array_map('strtolower', $this->headers);
	}

	/**
	 * Get the fields from the active CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.4.7
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
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public function setColumnsAlias($aliases)
	{
		// If there are no aliases we skip mapping and use original headers
		$columns = $this->getColumns();

		if (!$aliases)
		{
			$fields = array_map('strtolower', $this->headers);
		}
		else
		{
			$fields = array();

			foreach ($this->headers as $index => $alias)
			{
				// If the alias is set and it is a valid column use it, else ignore
				if (($field = array_search($alias, $aliases)) && in_array($field, $columns))
				{
					$fields[$index] = strtolower($field);
				}
				else
				{
					$fields[$index] = '__IGNORE_' . (int) $index;
				}
			}
		}

		$this->check($fields);

		$this->fields = $fields;
	}

	/**
	 * Import the records from CSV that was earlier loaded
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 *
	 * @see     load()
	 */
	abstract public function import();

	/**
	 * Method to check whether the CSV columns are importable.
	 *
	 * @param   array  $fields  The alias processed column list
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	abstract protected function check($fields);

	/**
	 * Create a temporary mapping table in the database for the CSV records.
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function createTemporaryTable()
	{
		// Mapping column-alias may narrow down the fields list, so use the final list of fields
		if (!count($this->fields))
		{
			throw new \Exception('No fields');
		}

		// Create table structure with all columns, but we'll insert only [fields => row]
		$offset  = 0;
		$cols    = array();
		$columns = $this->getColumns();
		$fields  = array_merge($this->getSysColumns(), $columns);
		$fields  = array_map('strtolower', $fields);

		$cols[] = $this->db->qn('x__id') . ' INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY';
		$cols[] = $this->db->qn('x__state') . ' INT(11) DEFAULT 0';

		foreach ($fields as $field)
		{
			// Skip ignored columns
			if ($field[0] != '_')
			{
				$cols[] = $this->db->qn($field) . ' TEXT';
			}
		}

		$this->db->dropTable($this->importTable, true);

		$queryC = 'CREATE TEMPORARY' . ' TABLE ' . $this->db->qn($this->importTable) . " (\n  " . implode(",\n  ", $cols) . "\n);";
		$this->db->setQuery($queryC)->execute();

		$this->dispatcher->trigger('onBeforeImportTable', array('com_importer.import.' . $this->name, $this));

		$skip    = (int) $this->getOption('skip_rows', 0);
		$process = (int) $this->getOption('import_rows', 0);

		// Import CSV records into the temporary table
		while ($row = fgetcsv($this->fp))
		{
			// Skip given number of rows
			if ($skip-- > 0)
			{
				continue;
			}

			if ($process > 0 && $offset >= $process)
			{
				break;
			}

			$offset++;

			// Convert the array into an associative array
			$record = array_combine($this->fields, $row);

			// Cleanup the CSV values, DO NOT 'filter'
			$record = array_map('trim', $record);

			$object = (object) $record;

			// NULL'ify empty column values
			foreach ($fields as $column)
			{
				if (!isset($object->$column) || strlen($object->$column) === 0)
				{
					$object->$column = null;
				}
			}

			/** @deprecated: This method will not be called */
			$this->translate($object);

			$this->preprocessCsvRow($row, $object, $offset);

			$this->db->insertObject($this->importTable, $object, 'x__id');

			// Mark the progress
			$this->timer->hit($offset, 100, \JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS_PREPARE'));
		}

		$this->timer->hit($offset, 1, \JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS_PREPARE'));

		$this->dispatcher->trigger('onAfterImportTable', array('com_importer.import.' . $this->name, $this));

		$this->processTemporaryTable();

		$this->timer->log(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS_PREPARE_FINISHED', $offset));

		return true;
	}

	/**
	 * Perform the initial processing of the temporary table before actual import begins.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function processTemporaryTable()
	{

	}

	/**
	 * Process the batch import process
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function processBatch()
	{
		// Iterate over the rows except for ignored (-1) and imported (1) externally
		$query = $this->db->getQuery(true);
		$query->select('x__id')->from($this->importTable)->where('x__state = 0');

		$iterator = $this->db->setQuery($query)->getIterator();
		$index    = -1;

		foreach($iterator as $index => $item)
		{
			// Defer loading as one iteration may update more rows which can be reused subsequently
			$query->clear()->select('*')->from($this->importTable)->where('x__id = ' . (int) $item->x__id);

			$obj           = $this->db->setQuery($query)->loadObject();
			$imported      = $this->processRecord($obj);
			$obj->x__state = (int) $imported;

			$this->db->updateObject($this->importTable, $obj, array('x__id'));

			// Mark the progress
			$this->timer->hit($index + 1, 100, \JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS'));
		}

		$this->timer->hit($index + 1, 1, \JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS'));
	}

	/**
	 * Convert the human readable text values from the import CSV to database friendly values to be saved.
	 *
	 * @param   \stdClass  $obj  The record from the CSV import table
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.1
	 *
	 * @deprecated   Use preprocessCsvRow() instead
	 */
	protected function translate($obj)
	{

	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $obj  The record to be imported into sellacious
	 *
	 * @return  bool  Whether the record was imported successfully
	 *
	 * @since   1.4.7
	 */
	abstract protected function processRecord($obj);

	/**
	 * Prepare the final import schema and write into a CSV file so that it can be reviewed.
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	protected function outputCsv()
	{
		try
		{
			$fields = $this->db->getTableColumns($this->importTable);
			$fields = array_keys($fields);

			$filename = $this->getOption('import.output_path');

			if (!$filename)
			{
				// No output requested, skip it.
				return false;
			}

			$fp = fopen($filename, 'w');

			if (!$fp)
			{
				throw new \Exception(\JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_ERROR_OUTPUT_FILE_COULD_NOT_OPEN'));
			}

			fputcsv($fp, $fields);

			$query = $this->db->getQuery(true);
			$query->select('*')->from($this->importTable);

			$iterator = $this->db->setQuery($query)->getIterator();

			foreach ($iterator as $item)
			{
				fputcsv($fp, (array) $item);
			}

			fclose($fp);

			return true;
		}
		catch (\Exception $e)
		{
			$this->timer->log($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to pre-process a CSV row before inserting into the importTable
	 *
	 * @param   array      $row     The row as loaded from the CSV file
	 * @param   \stdClass  $record  The record that will be inserted into the importTable
	 * @param   int        $offset  The row offset starting from 1 for first data row
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function preprocessCsvRow($row, $record, $offset)
	{
	}

	/**
	 * Get the current user, its important not to use depend on session as CLI has not session
	 *
	 * @return  \JUser
	 *
	 * @since   1.6.1
	 */
	public function getUser()
	{
		$uid = $this->getOption('session.user', 0);

		return \JUser::getInstance($uid);
	}
}

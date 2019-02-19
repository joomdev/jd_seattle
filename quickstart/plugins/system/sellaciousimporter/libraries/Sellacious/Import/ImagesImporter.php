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

use Sellacious\Media\MediaHelper;

/**
 * Import utility class
 *
 * @since   1.4.7
 */
class ImagesImporter extends AbstractImporter
{
	/**
	 * The temporary table name that would hold the staging data for import processing
	 *
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	public $importTable = '#__sellacious_import_temp_images';

	/**
	 * @var    string
	 *
	 * @since   1.4.7
	 */
	protected $baseDir;

	/**
	 * @var    \DirectoryIterator
	 *
	 * @since   1.4.7
	 */
	protected $iterator;

	/**
	 * Constructor
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public function __construct()
	{
		parent::__construct();

		$this->baseDir = trim($this->helper->media->getBaseDir(), '/ ');
	}

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $rootDirectory  The absolute file path for the CSV
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	public function load($rootDirectory)
	{
		if (!is_dir($rootDirectory))
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_IMPORT_ERROR_FOLDER_COULD_NOT_OPEN', basename($rootDirectory)));
		}

		$iterator = new \DirectoryIterator($rootDirectory);

		if (!$iterator)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_IMPORTER_IMPORT_ERROR_FOLDER_COULD_NOT_OPEN', basename($rootDirectory)));
		}

		$this->filename = $rootDirectory;
		$this->iterator = $iterator;
	}

	/**
	 * Import the Products from CSV
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function import()
	{
		try
		{
			// Mark the start of process
			$this->timer->start(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_START_FILENAME', basename($this->filename)));

			// Build a temporary table from CSV
			$this->createTemporaryTable();

			// Let the plugins pre-process the table and perform any preparation task
			$this->dispatcher->trigger('onBeforeImport', array('com_importer.import.images', $this));

			// Process the batch
			$this->processBatch();

			// Let the plugins post-process the record and perform any relevant task
			$this->dispatcher->trigger('onAfterImport', array('com_importer.import.images', $this));

			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Remove the temporary table
			$this->db->dropTable($this->importTable, true);

			// Mark the end of process
			$this->timer->stop(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_FINISHED'));

			return true;
		}
		catch (\Exception $e)
		{
			// Mark the unexpected termination of process
			$this->timer->interrupt(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_INTERRUPTED', $e->getMessage()));

			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Remove the temporary table
			$this->db->dropTable($this->importTable, true);

			return false;
		}
	}

	/**
	 * Create a temporary mapping table in the database for the records.
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function createTemporaryTable()
	{
		if (!$this->iterator)
		{
			return false;
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

		$queryC = 'CREATE TEMPORARY TABLE ' . $this->db->qn($this->importTable) . " (\n  " . implode(",\n  ", $cols) . "\n);";
		$this->db->setQuery($queryC)->execute();

		$this->dispatcher->trigger('onBeforeImportTable', array('com_importer.import.' . $this->name, $this));

		// Populate list of items
		$context    = $this->getOption('context');
		$extensions = $this->getOption('extensions') ?: array('jpg', 'png');

		list($tablename, $context) = explode('.', $context, 2);

		foreach ($this->iterator as $file)
		{
			$extension = $file->isFile() ? $file->getExtension() : null;

			if ($file->isDot() || $file->isLink() || (!$file->isDir() && !in_array($extension, $extensions)))
			{
				continue;
			}

			$filename = $file->getFilename();
			$char     = substr($filename, 0, 1);

			if ($char == '.' || $char == '_' || $char == '~')
			{
				continue;
			}

			$row = new \stdClass;

			$row->filename  = $filename;
			$row->basename  = $file->getBasename('.' . $extension);
			$row->extension = $extension;
			$row->pathname  = $file->getPathname();
			$row->directory = $file->isDir();
			$row->tablename = $tablename;
			$row->context   = $context;

			$offset++;

			set_time_limit(30);

			foreach ($fields as $column)
			{
				if (!isset($row->$column))
				{
					$row->$column = null;
				}
			}

			$this->db->insertObject($this->importTable, $row, 'x__id');

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
	 * Method to import a single record obtained from the source
	 *
	 * @param   \stdClass  $record  The record to be imported into sellacious
	 *
	 * @return  bool  Whether the record was imported successfully
	 *
	 * @since   1.4.7
	 */
	protected function processRecord($record)
	{
		try
		{
			// Allow translation from plugin which can already set the product id
			$key = $this->getOption('key');

			if (!empty($record->x__product_id))
			{
				$productId = $record->x__product_id;
			}
			elseif ($key == 'product_id')
			{
				$productId = $record->basename;
			}
			elseif ($key == 'sku')
			{
				$productId = Element\Product::findBySKU($record->basename);
			}
			elseif ($key == 'alias')
			{
				$productId = Element\Product::findByAlias($record->basename);
			}

			if (empty($productId))
			{
				throw new \Exception(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_MISSING_FILE_FOLDER', $record->basename, $key, $record->basename));
			}

			$record->x__product_id = $productId;

			$base = $this->baseDir . '/' . $record->tablename . '/' . $record->context . '/' . $productId;

			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			if (!is_dir(JPATH_SITE . '/' . $base))
			{
				\JFolder::create(JPATH_SITE . '/' . $base);
			}

			if (!is_dir(JPATH_SITE . '/' . $base))
			{
				throw new \Exception(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_MISSING_DIRECTORY', $base));
			}

			$files = array();

			if ($record->directory)
			{
				$files0 = \JFolder::files($record->pathname);

				foreach ($files0 as $index => $file)
				{
					$original = $record->pathname . '/'.  $file;
					$new      = $record->filename . '-'.  $file;

					$files[$original] = $new;
				}
			}
			else
			{
				$original = $record->pathname;
				$new      = $record->filename;

				$files[$original] = $new;
			}

			$refs = array();

			foreach ($files as $original => $new)
			{
				\JFile::move($original, JPATH_SITE . '/' . $base . '/' . $new);

				$ref = $this->addRecord($record, $productId, $base . '/' . $new);

				if ($ref)
				{
					$refs[$ref] = $base . '/' . $new;
				}
			}

			$record->x__media_id   = json_encode(array_keys($refs));
			$record->x__media_path = json_encode(array_values($refs));
		}
		catch (\Exception $e)
		{
			$this->timer->log($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $record  The object
	 * @param   int        $refId   The record id for reference
	 * @param   string     $path    The image path relative to site root
	 *
	 * @return  bool  Whether the item was imported successfully
	 *
	 * @since   1.4.7
	 */
	protected function addRecord($record, $refId, $path)
	{
		$row = array(
			'table_name' => $record->tablename,
			'context'    => $record->context,
			'record_id'  => $refId,
			'path'       => $path,
		);

		$table = $this->helper->media->getTable();
		$table->load($row);

		if (!$table->get('id'))
		{
			$mime = MediaHelper::getMimeType(JPATH_SITE . '/' . $path);
			$size = filesize(JPATH_SITE . '/' . $path);

			$row  = array(
				'table_name'    => $record->tablename,
				'context'       => $record->context,
				'record_id'     => $refId,
				'path'          => $path,
				'original_name' => basename($path),
				'type'          => $mime,
				'size'          => $size,
				'doc_type'      => null,
				'doc_reference' => null,
				'protected'     => 0,
				'state'         => 1,
				'ordering'      => 1,
			);

			$table->save($row);
		}

		return $table->get('id');
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
	public function getColumns()
	{
		return array(
			'filename',
			'basename',
			'extension',
			'pathname',
			'directory',
			'tablename',
			'context',
		);
	}

	/**
	 * Get the additional columns for the records which are required for the import utility system
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function getSysColumns()
	{
		return array(
			'x__product_id',
			'x__variant_id',
			'x__media_id',
			'x__media_path',
		);
	}

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
	protected function check($fields)
	{
	}
}

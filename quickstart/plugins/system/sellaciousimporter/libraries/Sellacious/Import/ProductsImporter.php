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

use Sellacious\Import\Processor\AbstractProcessor;

/**
 * Import utility class for products
 *
 * @since   1.4.7
 */
class ProductsImporter extends AbstractImporter
{
	/**
	 * The temporary table name that would hold the staging data from CSV for import processing
	 *
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	public $importTable = '#__sellacious_import_temp_products';

	/**
	 * The individual processors for the import routine
	 *
	 * @var    AbstractProcessor[]
	 *
	 * @since   1.6.1
	 */
	protected $processors;

	/**
	 * The individual processors dependency fields that are now fulfilled by any processor
	 *
	 * @var    string[]
	 *
	 * @since   1.6.1
	 */
	protected $fulfilled = array();

	/**
	 * Constructor.
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function __construct()
	{
		parent::__construct();

		$this->loadProcessors();
	}

	/**
	 * Destructor.
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function __destruct()
	{
		$this->db->dropTable($this->importTable, true);
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
		parent::setup($import);

		$this->load($import->path);
		$this->setColumnsAlias($import->mapping->toArray());
	}

	/**
	 * Method to add an import processor.
	 * The class name given will be loaded and a new instance will be created.
	 *
	 * @param   AbstractProcessor|string  $processor  The fully qualified class name or object instance of the import processor.
	 *
	 * @since   1.6.1
	 */
	public function addProcessor($processor)
	{
		if (is_object($processor) && is_subclass_of($processor, 'Sellacious\Import\Processor\AbstractProcessor'))
		{
			$oid = spl_object_hash($processor);

			$this->processors[$oid] = $processor;
		}
		elseif (is_string($processor) && class_exists($processor) && is_subclass_of($processor, 'Sellacious\Import\Processor\AbstractProcessor'))
		{
			$processor = new $processor($this);
			$oid       = spl_object_hash($processor);

			$this->processors[$oid] = $processor;
		}
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
	public function import()
	{
		try
		{
			// Check file pointer
			if (!$this->fp)
			{
				throw new \RuntimeException(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_FILE_NOT_LOADED'));
			}

			// Check headers, if translated one is not available try using actual CSV header
			if (!$this->fields)
			{
				$this->fields = array_map('strtolower', $this->headers);
			}

			// Sort processors dependency wise
			$this->timer->start(\JText::sprintf('Preparing import processors for products import.'));

			$this->sortProcessors();

			$this->check($this->fields);

			// Mark the start of process
			$this->timer->start(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_START_FILENAME', basename($this->filename)));

			// Build a temporary table from CSV
			$this->createTemporaryTable();

			// Process the batch
			$this->processBatch();

			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Mark the end of process
			$this->timer->stop(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_FINISHED'));

			return true;
		}
		catch (\Exception $e)
		{
			// Mark the unexpected termination of process
			$this->timer->interrupt(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_INTERRUPTED', $e->getMessage()));

			$this->timer->log(\JText::sprintf('%s:%s', $e->getFile(), $e->getLine()));

			$traces = preg_split('/[\r\n\t]+#\d+/', $e->getTraceAsString()) ?: array();

			foreach (array_reverse($traces) as $line)
			{
				$this->timer->log($line);
			}

			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			$this->timer->stop(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_FINISHED'));

			return false;
		}
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
		$columns = array();

		foreach ($this->processors as $processor)
		{
			$cols = $processor->getColumns();

			foreach ($cols as $col)
			{
				$columns[] = $col;
			}
		}

		$columns = array_unique(array_map('strtoupper', $columns));

		/**
		 * Let the plugins add custom columns
		 *
		 * @deprecated  This plugin call will be removed, use ImportProcessor instead
		 */
		$this->dispatcher->trigger('onFetchImportColumns', array('com_importer.import.products', &$columns, $this));

		return array_values($columns);
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
		$columns = array();

		foreach ($this->processors as $processor)
		{
			$cols = $processor->getDependables();

			foreach ($cols as $col)
			{
				$columns[] = $col;
			}
		}

		$columns = array_unique($columns);

		return $columns;
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
		$this->setOption('categories', array());

		parent::setColumnsAlias($aliases);
	}

	/**
	 * Method to load all the available processors for this import
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	protected function loadProcessors()
	{
		$this->addProcessor('Sellacious\Import\Processor\Products\ProductProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\VariantProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\CategoryProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\ManufacturerProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\SellerProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\PsxProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\VsxProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\ListingProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\PhysicalProductProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\PackageProductProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\PhysicalPsxProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\ElectronicPsxProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\PackagePsxProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\PriceProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\ImageProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\RelatedGroupProcessor');
		$this->addProcessor('Sellacious\Import\Processor\Products\SpecificationsProcessor');

		// Let the plugins add custom processors
		try
		{
			$this->dispatcher->trigger('onLoadImportProcessors', array('com_importer.import.products', $this));
		}
		catch (\Exception $e)
		{
			$this->timer->log('Error: ' . $e->getMessage());
			$this->timer->log('Some import handlers may not have loaded. Import will continue with loaded handlers.');
		}
	}

	/**
	 * Sort the list of processors in chain based on whether their dependency is fulfilled
	 *
	 * @return   void
	 *
	 * @since    1.6.1
	 */
	protected function sortProcessors()
	{
		$processors = $this->processors;
		$handlers   = array();

		while (true)
		{
			$found = array();

			foreach ($processors as $id => $processor)
			{
				$dep = $processor->getDependencies();

				$unfulfilled = array_diff($dep, array_keys($this->fulfilled));

				// If all dependency fulfilled
				if (!$unfulfilled)
				{
					$handlers[$id] = $processor;
					$dependables   = $processor->getDependables();

					$this->setFulfilled($dependables);

					$found[] = $id;
				}
			}

			// Deadlock encountered, quit time!
			if (!$found)
			{
				break;
			}

			// Un-list found ones so that we can find next candidate
			foreach ($found as $key)
			{
				unset($processors[$key]);
			}
		}

		// We cannot execute any remaining processors as their dependencies could not be fulfilled
		foreach ($processors as $processor)
		{
			$this->timer->log('Skipping import processor: ' . get_class($processor));
		}

		$this->processors = $handlers;
	}

	/**
	 * Mark a set of dependency columns as fulfilled
	 *
	 * @param    string[] $keys  $processors  Processors to attempt for running
	 *
	 * @return   void
	 *
	 * @since    1.6.1
	 */
	protected function setFulfilled($keys)
	{
		$keys = (array) $keys;

		foreach ($keys as $key)
		{
			$this->fulfilled[$key] = true;
		}
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
		foreach ($this->processors as $processor)
		{
			$processor->validate($fields);
		}
	}

	/**
	 * Method to pre-process a CSV row before inserting into the importTable
	 *
	 * @param   array      $row     The row as loaded from the CSV file
	 * @param   \stdClass  $obj     The record that will be inserted into the importTable
	 * @param   int        $offset  The row offset starting from 1 for first data row
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	protected function preprocessCsvRow($row, $obj, $offset)
	{
		parent::preprocessCsvRow($row, $obj, $offset);

		foreach ($this->processors as $processor)
		{
			$processor->preProcessRecord($obj);
		}
	}

	/**
	 * Perform the initial processing of the temporary table AFTER the records from CSV have been copied there, BUT
	 * BEFORE the actual import routine begins.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function processTemporaryTable()
	{
		/**
		 * Let the plugins pre-process the table and perform any preparation task, such as
		 * - Add table indexes
		 * - Build a lookup for existing records
		 */
		$this->dispatcher->trigger('onBeforeImport', array('com_importer.import.products', $this));

		foreach ($this->processors as $processor)
		{
			$processor->preProcessBatch();
		}
	}

	/**
	 * Process the batch import process
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	protected function processBatch()
	{
		$query = $this->db->getQuery(true);
		$query->select('*')->from($this->importTable);

		$iterator = $this->db->setQuery($query)->getIterator();

		if ($count = $iterator->count())
		{
			$index = -1;

			$this->setTicker('products import', $count, 0);

			foreach($iterator as $index => $obj)
			{
				$this->processRecord($obj);

				$obj->x__state = 1;

				$this->writeOutput($obj);

				if (($index + 1) % 100 === 0)
				{
					$this->setTicker('products import', null, $index + 1);
				}
			}

			$this->setTicker('products import', null, $index + 1);
		}

		foreach($this->processors as $index => $processor)
		{
			$processor->postProcessBatch();
		}

		$this->setComplete('products import');

		// Let the plugins post-process the record and perform any relevant task
		$this->dispatcher->trigger('onAfterImport', array('com_importer.import.products', $this));
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $obj  The record to be imported into sellacious
	 *
	 * @return  void
	 *
	 * @since   1.4.7
	 */
	protected function processRecord($obj)
	{
		foreach ($this->processors as $id => $processor)
		{
			$processor->processRecord($obj);
		}
	}

	/**
	 * Method to write the generated record after the import has been processed into a CSV
	 *
	 * @param   \stdClass  $obj  The record that was imported into sellacious
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	protected function writeOutput($obj)
	{
		$this->db->updateObject($this->importTable, $obj, array('x__id'));
	}
}

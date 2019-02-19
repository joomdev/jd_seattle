<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Import\AbstractImporter;

defined('_JEXEC') or die;

/**
 * Abstract base class for Import Processor classes
 *
 * @package  Sellacious\Import\Processor
 *
 * @since    1.6.1
 */
abstract class AbstractProcessor
{
	/**
	 * The parent importer instance reference
	 *
	 * @var    AbstractImporter
	 *
	 * @since   1.6.1
	 */
	protected $importer;

	/**
	 * Whether this processor should be involved in the import process
	 *
	 * @var    bool
	 *
	 * @since   1.6.1
	 */
	protected $enabled;

	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName;

	/**
	 * The temporary table name to store the temporary working data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tmpTableName;

	/**
	 * The lookup dictionary to lookup already existing records' primary keys so that they can be updated
	 *
	 * @var    array
	 *
	 * @since   1.6.1
	 */
	protected $index = array();

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @var    string[]
	 *
	 * @since   1.6.1
	 */
	protected $columns;

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @var    string[]
	 *
	 * @since   1.6.1
	 */
	protected $dependencies;

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @var    string[]
	 *
	 * @since   1.6.1
	 */
	protected $dependables;

	/**
	 * Constructor
	 *
	 * @param   AbstractImporter  $importer  The parent importer instance object
	 *
	 * @since   1.6.1
	 */
	public function __construct(AbstractImporter $importer)
	{
		$this->importer = $importer;
	}

	/**
	 * Method to check whether the CSV columns are valid for import
	 *
	 * @param   array  $fields  The alias processed column list
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function validate($fields)
	{
	}

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @since   1.6.1
	 */
	public function getColumns()
	{
		if ($this->columns === null)
		{
			$this->columns = $this->getCsvColumns();
		}

		return $this->columns;
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @since   1.6.1
	 */
	public function getDependencies()
	{
		if ($this->dependencies === null)
		{
			$this->dependencies = $this->getRequiredColumns();
		}

		return $this->dependencies;
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @since   1.6.1
	 */
	public function getDependables()
	{
		if ($this->dependables === null)
		{
			$this->dependables = $this->getGeneratedColumns();
		}

		return $this->dependables;
	}

	/**
	 * Method to preprocess the import record that include filtering, typecasting, etc.
	 * No write actions should be carried out at this stage. This is meant for only preparing a CSV record for import.
	 *
	 * @param   \stdClass  $obj  The record from the import CSV
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessRecord($obj)
	{
	}

	/**
	 * Method to perform the actual import tasks for individual record.
	 * Any write actions can be performed at this stage relevant to the passed record.
	 * If this is called then all dependency must've been already fulfilled by some other processors.
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
	}

	/**
	 * Method to preprocess the import records.
	 * This can be creating an index of existing records, or any other prerequisites fulfilment before import begins.
	 * No write actions should be carried out at this stage.
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessBatch()
	{
	}

	/**
	 * Method to perform the actual import tasks. Any write actions can be performed at this stage.
	 * Any type of snapshot taking or iteration or whatever is required to perform the import of the columns relevant
	 * to this processor should be handled internally here.
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processBatch()
	{
	}

	/**
	 * Method to perform the post processing of the records, allowing to complete any finalization of import routine.
	 * Any pending write actions must be finished off at this stage only.
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function postProcessBatch()
	{
	}

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @see     getcolumns()
	 *
	 * @since   1.6.1
	 */
	abstract protected function getCsvColumns();

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @see     getDependencies()
	 *
	 * @since   1.6.1
	 */
	abstract protected function getRequiredColumns();

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @see     getDependables()
	 *
	 * @since   1.6.1
	 */
	abstract protected function getGeneratedColumns();

	protected function addIndex($key, $value)
	{
		$this->index[$key] = $value;
	}

	protected function getIndex($key)
	{
		$value = ArrayHelper::getValue($this->index, $key);

		return $value;
	}
}

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor\Products;

defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;
use Sellacious\Import\AbstractImporter;
use Sellacious\Import\Processor\AbstractProcessor;

class PhysicalProductProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_product_physical';

	/**
	 * Constructor
	 *
	 * @param   AbstractImporter  $importer  The parent importer instance object
	 *
	 * @since   1.6.1
	 */
	public function __construct(AbstractImporter $importer)
	{
		parent::__construct($importer);

		try
		{
			$config  = ConfigHelper::getInstance('com_sellacious');
			$allowed = $config->get('allowed_product_type', 'both');

			$this->enabled = $allowed === 'physical' || $allowed === 'both';
		}
		catch (\Exception $e)
		{
		}
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
	protected function getCsvColumns()
	{
		return $this->enabled ? array('length', 'width', 'height', 'weight') : array();
	}

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
	protected function getRequiredColumns()
	{
		return $this->enabled ? array('x__product_id') : array();
	}

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
	protected function getGeneratedColumns()
	{
		return array();
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
		if ($this->enabled && $obj->product_type === 'physical')
		{
			$obj->length = !empty($obj->length) ? json_encode(array('m' => (float) $obj->length)) : null;
			$obj->width  = !empty($obj->width) ? json_encode(array('m' => (float) $obj->width)) : null;
			$obj->height = !empty($obj->height) ? json_encode(array('m' => (float) $obj->height)) : null;
			$obj->weight = !empty($obj->weight) ? json_encode(array('m' => (float) $obj->weight)) : null;
		}
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
		if ($this->enabled)
		{
			$db    = $this->importer->getDb();
			$query = $db->getQuery(true);

			$query->select('id, product_id')->from($this->tableName);
			$iterator = $db->setQuery($query)->getIterator();

			foreach ($iterator as $item)
			{
				$this->addIndex((int) $item->product_id, (int) $item->id);
			}
		}
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
		if (!$this->enabled || !$obj->x__product_id || $obj->product_type !== 'physical')
		{
			return;
		}

		$record = new \stdClass;

		$record->id         = $this->getIndex((int) $obj->x__product_id);
		$record->product_id = $obj->x__product_id;
		$record->length     = $obj->length;
		$record->width      = $obj->width;
		$record->height     = $obj->height;
		$record->weight     = $obj->weight;

		$db = $this->importer->getDb();

		if ($record->id)
		{
			$db->updateObject($this->tableName, $record, array('id'));
		}
		else
		{
			$db->insertObject($this->tableName, $record, 'id');

			$this->addIndex((int) $record->product_id, (int) $record->id);
		}
	}
}

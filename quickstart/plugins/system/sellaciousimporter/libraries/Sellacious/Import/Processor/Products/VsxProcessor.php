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

class VsxProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_variant_sellers';

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
			$config        = ConfigHelper::getInstance('com_sellacious');
			$this->enabled = $config->get('multi_variant');
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
		$cols = array();

		if ($this->enabled)
		{
			$cols = array(
				'variant_current_stock',
				'variant_over_stock_sale_limit',
				'variant_reserved_stock',
				'variant_stock_sold',
				'variant_price_add',
				'variant_price_is_percent',
			);
		}

		return $cols;
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
		$cols = array();

		if ($this->enabled)
		{
			$cols = array(
				'x__variant_id',
				'x__seller_uid',
			);
		}

		return $cols;
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
		return $this->enabled ? array('x__vsx_id') : array();
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
		if ($this->enabled)
		{
			$booleans = array('TRUE', 'YES', '1', 'PUBLISHED', 'ACTIVE', 'ENABLED', 'T' , 'Y');

			$obj->variant_price_is_percent = in_array(strtoupper($obj->variant_price_is_percent), $booleans) ? 1 : 0;
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
		if (!$this->enabled)
		{
			return;
		}

		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		$query->select('id, variant_id, seller_uid')->from($this->tableName);
		$iterator = $db->setQuery($query)->getIterator();

		foreach ($iterator as $item)
		{
			$this->addIndex(sprintf('%d:%d', $item->variant_id, $item->seller_uid), (int) $item->id);
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
		if (!$this->enabled || !$obj->x__variant_id || !$obj->x__seller_uid)
		{
			return;
		}

		$key = sprintf('%d:%d', $obj->x__variant_id, $obj->x__seller_uid);
		$id  = $this->getIndex($key);

		$record = new \stdClass;

		$record->id             = $id;
		$record->variant_id     = $obj->x__variant_id;
		$record->seller_uid     = $obj->x__seller_uid;
		$record->price_mod      = $obj->variant_price_add;
		$record->price_mod_perc = $obj->variant_price_is_percent;
		$record->stock          = $obj->variant_current_stock;
		$record->over_stock     = $obj->variant_over_stock_sale_limit;
		$record->stock_reserved = $obj->variant_reserved_stock;
		$record->stock_sold     = $obj->variant_stock_sold;
		$record->state          = 1;

		$db = $this->importer->getDb();

		if ($record->id)
		{
			$db->updateObject($this->tableName, $record, array('id'));
		}
		else
		{
			$db->insertObject($this->tableName, $record, 'id');

			$this->addIndex($key, (int) $record->id);
		}

		$obj->x__vsx_id = $record->id;
	}
}

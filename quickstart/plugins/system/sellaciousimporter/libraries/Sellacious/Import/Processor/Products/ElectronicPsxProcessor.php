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

class ElectronicPsxProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_eproduct_sellers';

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

			$this->enabled = $allowed === 'electronic' || $allowed === 'both';
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
		if ($this->enabled)
		{
			return array(
				'eproduct_delivery_mode',
				'eproduct_download_limit',
				'eproduct_download_period',
				'eproduct_preview_url',
			);
		}

		return array();
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
		return $this->enabled ? array('x__psx_id') : array();
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
		if (!$this->enabled || !$obj->x__psx_id || $obj->product_type <> 'electronic')
		{
			return;
		}

		if ($this->getIndex($obj->x__psx_id))
		{
			return;
		}

		$id = $this->lookup($obj->x__psx_id);

		$record = new \stdClass;

		$record->id              = $id;
		$record->psx_id          = $obj->x__psx_id;
		$record->delivery_mode   = $obj->eproduct_delivery_mode;
		$record->download_limit  = $obj->eproduct_download_limit;
		$record->download_period = $obj->eproduct_download_period;
		$record->preview_url     = $obj->eproduct_preview_url;

		$db = $this->importer->getDb();

		if ($id)
		{
			$db->updateObject($this->tableName, $record, array('id'));
		}
		else
		{
			$db->insertObject($this->tableName, $record, 'id');

			$this->addIndex($record->psx_id, (int) $record->id);
		}
	}

	protected function lookup($key)
	{
		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		$query->select('id')->from($this->tableName)->where('psx_id = ' . (int) $key);

		$value = $db->setQuery($query)->loadResult();

		return (int) $value;
	}
}

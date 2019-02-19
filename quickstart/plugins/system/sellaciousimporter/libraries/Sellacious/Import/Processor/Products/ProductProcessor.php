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

use Sellacious\Import\AbstractImporter;
use Sellacious\Import\Processor\AbstractProcessor;

class ProductProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_products';

	/**
	 * The temporary table name to store the temporary working data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tmpTableName = '#__temp_import_product_processor';

	protected $helper;

	protected $typeIndex;

	protected $idIndex;

	protected $keyName;

	protected $keyCol;

	protected $keyFieldId;

	protected $updateFor;

	protected $createFor;

	protected $allowCreate;

	protected $allowUpdate;

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
			$this->helper = \SellaciousHelper::getInstance();
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
		return array(
			'product_unique_alias',
		//	'product_parent_key',
			'product_title',
			'product_type',
			'product_sku',
			'mfg_assigned_sku',
			'product_summary',
			'product_description',
			'product_current_stock',
			'product_over_stock_sale_limit',
			'product_reserved_stock',
			'product_stock_sold',
			'product_feature_1',
			'product_feature_2',
			'product_feature_3',
			'product_feature_4',
			'product_feature_5',
			'product_state',
			'product_ordering',
			'product_meta_key',
			'product_meta_description',
		);
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
		// We require x__category_ids here just for simplicity, may be we need a separate processor
		return array('x__manufacturer_uid', 'x__category_ids');
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
		return array(
			'x__product_id',
			'x__parent_id',
			'x__features',
		);
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
		$booleans = array('TRUE', 'YES', '1', 'PUBLISHED', 'ACTIVE', 'ENABLED', 'T' , 'Y');

		$obj->product_state    = in_array(strtoupper($obj->product_state), $booleans) ? 1 : 0;
		$obj->product_ordering = (int) $obj->product_ordering;

		$features = array(
			$obj->product_feature_1,
			$obj->product_feature_2,
			$obj->product_feature_3,
			$obj->product_feature_4,
			$obj->product_feature_5,
		);
		$features = array_filter($features, 'strlen');

		$obj->x__features = $features ? json_encode($features) : null;
	}

	/**
	 * Update empty product type if product already exists in db or set default to physical
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function preProcessBatch()
	{
		$db      = $this->importer->getDb();
		$columns = $db->getTableColumns($this->importer->importTable);
		$pKey    = $this->importer->getOption('unique_key.product');
		$pKey    = strtolower($pKey);

		$this->enabled = array_key_exists($pKey, $columns);

		if ($this->enabled)
		{
			$productKeys = array(
				'alias'            => 'product_unique_alias',
				'title'            => 'product_title',
				'local_sku'        => 'product_sku',
				'manufacturer_sku' => 'mfg_assigned_sku',
			);

			$key = array_search($pKey, $productKeys);

			if ($key)
			{
				$this->keyName = $pKey;
				$this->keyCol  = $key;
			}
			elseif (preg_match('/^spec_(\d+)(?:_.*)?$/', $pKey, $ukm))
			{
				$this->keyFieldId = $ukm[1];
			}
			else
			{
				$this->enabled = false;
			}
		}

		if (!$this->enabled)
		{
			return;
		}

		$this->buildIndex();

		$me = $this->importer->getUser();

		$this->createFor   = $this->importer->getOption('create.products');
		$this->updateFor   = $this->importer->getOption('update.products');
		$this->allowCreate = $this->createFor == 'all' || ($this->createFor == 'own' && $me->id > 0);
		$this->allowUpdate = $this->updateFor == 'all' || ($this->updateFor == 'own' && $me->id > 0);
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
		if (!$this->enabled)
		{
			return;
		}

		$keyName = $this->keyName;
		$mKey    = strtolower($obj->$keyName);

		if (!$mKey)
		{
			return;
		}

		// If product id already assigned, this must've been processed externally
		if ($obj->x__product_id)
		{
			return;
		}

		// See if we already processed this
		list($x, $y) = $this->getIndex($mKey);

		if ($x)
		{
			// Product type in this csv should be consistent for same product
			$obj->x__product_id = $x;
			$obj->product_type  = $y;

			return;
		}

		// See if it already exists in the database
		list($x, $y) = $this->lookup($mKey);

		if ($x)
		{
			// Product type in this csv can be changed for existing product
			$obj->x__product_id = $x;
			$obj->product_type  = $obj->product_type ?: $y;
		}

		$db  = $this->importer->getDb();
		$me  = $this->importer->getUser();
		$now = \JFactory::getDate()->toSql();

		try
		{
			$product = new \stdClass;

			$product->id               = $obj->x__product_id;
			$product->parent_id        = $obj->x__parent_id;
			$product->title            = $obj->product_title;
			$product->type             = $obj->product_type ?: 'physical';
			$product->local_sku        = $obj->product_sku;
			$product->manufacturer_sku = $obj->mfg_assigned_sku;
			$product->manufacturer_id  = $obj->x__manufacturer_uid;
			$product->introtext        = $obj->product_summary;
			$product->description      = $obj->product_description;
			$product->metakey          = $obj->product_meta_key;
			$product->metadesc         = $obj->product_meta_description;
			$product->features         = $obj->x__features;
			$product->state            = $obj->product_state === null ? 1 : $obj->product_state;
			$product->ordering         = $obj->product_ordering;

			// Make extra sure that the guest user (id = 0) does not accidentally create/update a global product
			if ($obj->x__product_id)
			{
				if ($this->allowUpdate)
				{
					$product->alias = $obj->product_unique_alias ?:
						($obj->product_title ? \JApplicationHelper::stringURLSafe($obj->product_title) : null);

					$product->modified    = $now;
					$product->modified_by = $me->id;

					$keys = array('id');

					if ($this->updateFor == 'own')
					{
						$product->owned_by = $me->id;

						$keys[] = 'owned_by';
					}

					$db->updateObject('#__sellacious_products', $product, $keys);
				}
			}
			else
			{
				if ($this->allowCreate)
				{
					$product->alias = $obj->product_unique_alias ?:
						($obj->product_title ? \JApplicationHelper::stringURLSafe($obj->product_title) : uniqid('alias_'));

					$product->created    = $now;
					$product->created_by = $me->id;
					$product->owned_by   = $this->createFor == 'own' ? $me->id : 0;

					$db->insertObject('#__sellacious_products', $product, 'id');

					$obj->x__product_id = $product->id;
				}
			}

			$categories = json_decode($obj->x__category_ids, true);

			if ($obj->x__product_id && $categories)
			{
				$this->helper->product->setCategories($obj->x__product_id, $categories);
			}

			// Mark this as done
			$this->addIndex($mKey, array($obj->x__product_id, $obj->product_type));
		}
		catch (\JDatabaseExceptionExecuting $e)
		{
			$this->importer->timer->log($e->getMessage() . ' @ ' . $e->getQuery());
		}
		catch (\Exception $e)
		{
			$this->importer->timer->log($e->getMessage());
		}
	}

	/**
	 * Build a search index for products based on selected unique key
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	protected function buildIndex()
	{
		$db = $this->importer->getDb();

		$db->dropTable($this->tmpTableName, true);

		$create = 'CREATE TABLE IF NOT EXISTS ' . $db->qn($this->tmpTableName) . ' (' .
		          '  id INT NOT NULL PRIMARY KEY,' .
		          '  type VARCHAR(20),' .
		          '  keyCol VARCHAR(1000),' .
		          '  INDEX USING BTREE (keyCol)' .
		          ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci';

		$db->setQuery($create)->execute();

		if ($this->keyCol)
		{
			$query = $db->getQuery(true);

			$query->select('id, type')->select($this->keyCol)->from($this->tableName);

			$db->setQuery('INSERT INTO ' . $db->qn($this->tmpTableName) . ' ' . $query)->execute();
		}
		elseif ($this->keyFieldId)
		{
			$query = $db->getQuery(true);

			$query->select('p.id, p.type, f.field_value')
			      ->from($db->qn('#__sellacious_field_values', 'f'))
			      ->where('f.table_name = ' . $db->q('products'))
			      ->where('f.field_id = ' . (int) $this->keyFieldId)
			      ->where('f.is_json = 0');

			$query->join('inner', $db->qn('#__sellacious_products', 'p') . ' p.id = f.record_id');

			$db->setQuery('INSERT INTO ' . $db->qn($this->tmpTableName) . ' ' . $query)->execute();
		}
	}

	/**
	 * Find the record if it exists in the database already
	 *
	 * @param   string  $key  The search value to match for
	 *
	 * @return  array
	 *
	 * @since   1.6.1
	 */
	protected function lookup($key)
	{
		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		$query->select('id, type')->from($this->tmpTableName)->where('keyCol = ' . $db->q($key));

		$record = $db->setQuery($query)->loadObject();

		return $record ? array($record->id, $record->type) : array(null, null);
	}

	/**
	 * Find the record if it was previously processed in this batch already
	 *
	 * @param   string  $key  The search value to match for
	 *
	 * @return  array
	 *
	 * @since   1.6.1
	 */
	protected function getIndex($key)
	{
		$values = parent::getIndex($key);

		return $values ?: array(null, null);
	}
}

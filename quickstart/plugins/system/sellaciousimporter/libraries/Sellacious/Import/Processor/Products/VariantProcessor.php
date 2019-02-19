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

class VariantProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_variants';

	protected $tmpTableName = '#__temp_import_variant_processor';

	protected $keyName;

	protected $keyCol;

	protected $updateFor;

	protected $createFor;

	protected $allowCreate;

	protected $allowUpdate;

	protected $independent;

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
				'variant_unique_alias',
				'variant_title',
				'variant_sku',
				'variant_feature_1',
				'variant_feature_2',
				'variant_feature_3',
				'variant_feature_4',
				'variant_feature_5',
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
		return  array('x__variant_id', 'x__variant_features');
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
			$features = array(
				$obj->variant_feature_1,
				$obj->variant_feature_2,
				$obj->variant_feature_3,
				$obj->variant_feature_4,
				$obj->variant_feature_5,
			);

			$features = array_filter($features, 'strlen');

			$obj->x__variant_features = $features ? json_encode($features) : null;
		}
		else
		{
			$obj->x__variant_id = 0;
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
		$db      = $this->importer->getDb();
		$columns = $db->getTableColumns($this->importer->importTable);
		$vKey    = $this->importer->getOption('unique_key.variant');
		$vKey    = strtolower($vKey);

		$this->enabled     = array_key_exists($vKey, $columns);
		$this->independent = $this->importer->getOption('variant_independent');

		if ($this->enabled)
		{
			$productKeys = array(
				'alias'     => 'variant_unique_alias',
				'title'     => 'variant_title',
				'local_sku' => 'variant_sku',
			);

			$key = array_search($vKey, $productKeys);

			if ($key)
			{
				$this->keyName = $vKey;
				$this->keyCol  = $key;
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

		$this->createFor   = $this->importer->getOption('create.variants');
		$this->updateFor   = $this->importer->getOption('update.variants');
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
	 * @throws  \Exception
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
		if (!$this->enabled || !$obj->x__product_id)
		{
			return;
		}

		$keyName = $this->keyName;
		$mKey    = strtolower($obj->$keyName);

		if (!$mKey)
		{
			return;
		}

		// If variant id already assigned, this must've been processed externally
		if ($obj->x__variant_id)
		{
			return;
		}

		if ($this->independent)
		{
			// Independent
			list($vid, $pid) = $this->getIndex($mKey);

			if ($vid)
			{
				// Its another variant of another product
				if ($pid != $obj->x__product_id)
				{
					return;
				}

				// Processed
				$obj->x__variant_id = $vid;

				return;
			}
		}
		else
		{
			// Dependent
			$key       = sprintf('%d:%s', $obj->x__product_id, strtolower($mKey));
			list($vid) = $this->getIndex($key);

			// Processed
			$obj->x__variant_id = $vid;

			return;
		}

		if ($this->independent)
		{
			list($vid, $pid) = $this->lookup($mKey);

			if ($vid)
			{
				// Its another variant of another product
				if ($pid != $obj->x__product_id)
				{
					return;
				}

				$obj->x__variant_id = $vid;
			}
		}
		else
		{
			list($vid) = $this->lookup($mKey, $obj->x__product_id);

			$obj->x__variant_id = $vid;
		}

		$db  = $this->importer->getDb();
		$me  = $this->importer->getUser();
		$now = \JFactory::getDate()->toSql();

		$variant = new \stdClass;

		$variant->id          = $obj->x__variant_id;
		$variant->product_id  = $obj->x__product_id;
		$variant->title       = $obj->variant_title;
		$variant->alias       = $obj->variant_title ? \JApplicationHelper::stringURLSafe($obj->variant_title) : null;
		$variant->local_sku   = $obj->variant_sku;
		$variant->features    = $obj->x__variant_features;
		$variant->state       = 1;

		if ($obj->x__variant_id)
		{
			if ($this->allowUpdate)
			{
				$variant->alias       = $obj->variant_unique_alias ?: ($obj->product_title ?
					\JApplicationHelper::stringURLSafe($obj->product_title) : null);
				$variant->modified    = $now;
				$variant->modified_by = $me->id;

				$keys = array('id');

				if ($this->updateFor == 'own')
				{
					$variant->owned_by = $me->id;

					$keys[] = 'owned_by';
				}

				$db->updateObject($this->tableName, $variant, $keys);
			}
		}
		else
		{
			if ($this->allowCreate)
			{
				$variant->alias      = $obj->variant_unique_alias ?: ($obj->product_title ?
					\JApplicationHelper::stringURLSafe($obj->product_title) : uniqid('alias_'));
				$variant->created    = $now;
				$variant->created_by = $me->id;
				$variant->owned_by   = $this->createFor == 'own' ? $me->id : 0;

				if ($db->insertObject($this->tableName, $variant, 'id'))
				{
					$obj->x__variant_id = $variant->id;
				}
			}
		}

		// Update index
		if ($this->independent)
		{
			$this->addIndex($mKey, array((int) $obj->x__variant_id, (int) $obj->x__product_id));
		}
		else
		{
			$k = sprintf('%d:%s', $obj->x__product_id, $mKey);

			$this->addIndex($k, array((int) $obj->x__variant_id, (int) $obj->x__product_id));
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
		$db  = $this->importer->getDb();

		$db->dropTable($this->tmpTableName, true);

		$create = 'CREATE TABLE IF NOT EXISTS ' . $db->qn($this->tmpTableName) . ' (' .
		          '  id INT NOT NULL PRIMARY KEY,' .
		          '  pid INT,' .
		          '  keyCol VARCHAR(1000),' .
		          '  INDEX USING BTREE (keyCol)' .
		          ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci';

		$db->setQuery($create)->execute();

		$query = $db->getQuery(true);

		$query->select('id, product_id')->select($this->keyCol)->from($this->tableName);

		$db->setQuery('INSERT INTO ' . $db->qn($this->tmpTableName) . ' ' . $query)->execute();
	}

	/**
	 * Search for existing variant in the database
	 *
	 * @param   string  $key  The search key
	 * @param   int     $pid  Product id to match if not independent
	 *
	 * @return  array
	 *
	 * @since   1.6.1
	 */
	protected function lookup($key, $pid = null)
	{
		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		$query->select('id, pid')->from($this->tmpTableName)->where('keyCol = ' . $db->q($key));

		if ($pid)
		{
			$query->where('pid = ' . (int) $pid);
		}

		$record = $db->setQuery($query)->loadObject();

		return $record ? array($record->id, $record->pid) : array(null, null);
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

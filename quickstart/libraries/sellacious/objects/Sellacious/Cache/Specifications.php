<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Cache;

defined('_JEXEC') or die;

use Sellacious\Cache;

/**
 * Sellacious Products Specifications Cache Object.
 *
 * @since  1.5.0
 */
class Specifications extends Cache
{
	/**
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $cacheTable = '#__sellacious_cache_specifications';

	/**
	 * @var    string[]
	 *
	 * @since  1.5.0
	 */
	protected $fields = array();

	/**
	 * @var    int[]
	 *
	 * @since  1.5.0
	 */
	protected $stats = array();

	/**
	 * Build the cache.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function build()
	{
		$created = $this->createTable();

		if ($created)
		{
			$this->productsCache();
			$this->variantsCache();
		}
	}

	/**
	 * Build the cache for products specifications
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function productsCache()
	{
		// Order by record id so that we get all fields for a single product at once.
		$filter   = array(
			'list.from'  => '#__sellacious_field_values',
			'list.order' => 'a.record_id',
			'table_name' => 'products',
		);
		$iterator = $this->helper->field->getIterator($filter);

		foreach ($iterator as $obj)
		{
			if (isset($record) && $record->x__product_id != $obj->record_id)
			{
				// If this is a different record than the one we are dealing with, insert current one and switch to new record.
				$this->db->insertObject($this->cacheTable, $record, 'x__id');

				$record = null;
			}

			if (array_key_exists($obj->field_id, $this->fields))
			{
				$this->stats[$obj->field_id] = isset($this->stats[$obj->field_id]) ? $this->stats[$obj->field_id] + 1 : 1;

				if (!isset($record))
				{
					$record = new \stdClass;

					$record->id            = null;
					$record->x__product_id = $obj->record_id;
					$record->x__variant_id = 0;
					$record->x__state      = 1;
				}

				$key = 'spec_' . (int) $obj->field_id;

				$record->$key = $obj->is_json ? 'JSON:' . $obj->field_value : $obj->field_value;
			}
		}

		// If we haven't inserted last record do it now.
		if (isset($record))
		{
			$this->db->insertObject($this->cacheTable, $record, 'x__id');
		}
	}

	/**
	 * Build the cache for variants specifications
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function variantsCache()
	{
		// Order by record id so that we get all fields for a single product at once.
		$filter   = array(
			'list.select' => array('a.*', 'v.product_id'),
			'list.from'   => '#__sellacious_field_values',
			'list.order'  => 'a.record_id',
			'list.join'   => array(array('left', $this->db->qn('#__sellacious_variants', 'v') . ' ON v.id = a.record_id')),
			'table_name'  => 'variants',
		);
		$iterator = $this->helper->field->getIterator($filter);

		foreach ($iterator as $obj)
		{
			if (isset($record) && $record->x__variant_id != $obj->record_id)
			{
				// If this is a different record than the one we are dealing with, insert current one and switch to new record.
				$this->db->insertObject($this->cacheTable, $record, 'x__id');

				$record = null;
			}

			// Fixme: Variant specs should include attributes from its product
			if (array_key_exists($obj->field_id, $this->fields))
			{
				$this->stats[$obj->field_id] = isset($this->stats[$obj->field_id]) ? $this->stats[$obj->field_id] + 1 : 1;

				if (!isset($record))
				{
					$record = new \stdClass;

					$record->id            = null;
					$record->x__product_id = $obj->product_id;
					$record->x__variant_id = $obj->record_id;
					$record->x__state      = 1;
				}

				$key = 'spec_' . (int) $obj->field_id;

				$record->$key = $obj->is_json ? 'JSON:' . $obj->field_value : $obj->field_value;
			}
		}

		// If we haven't inserted last record do it now.
		if (isset($record))
		{
			$this->db->insertObject($this->cacheTable, $record, 'x__id');
		}
	}

	/**
	 * Create the specifications table in the database for the CSV records.
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function createTable()
	{
		$filter = array(
			'list.select' => 'a.id, a.title',
			'context'     => 'product',
			'list.where'  => "a.type != 'fieldgroup'",
		);
		$fields = $this->helper->field->loadObjectList($filter);

		if (is_array($fields))
		{
			// Create table structure
			$cols[] = $this->db->qn('x__id') . ' INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY';
			$cols[] = $this->db->qn('x__product_id') . ' INT(11) DEFAULT 0';
			$cols[] = $this->db->qn('x__variant_id') . ' INT(11) DEFAULT 0';
			$cols[] = $this->db->qn('x__state') . ' INT(11) DEFAULT 0';

			$lenSql = 'SELECT MAX(LENGTH(field_value)) FROM #__sellacious_field_values WHERE field_id = %d';

			foreach ($fields as $field)
			{
				$len = $this->db->setQuery(sprintf($lenSql, $field->id))->loadResult();
				$col = 'spec_' . $field->id;
				$cmt = $field->title;

				$cols[] = sprintf('%s VARCHAR(%d) NOT NULL DEFAULT %s COMMENT %s', $this->db->qn($col), $len + 1, "''", $this->db->q($cmt));

				$this->fields[$field->id] = $field->title;
			}

			$this->db->dropTable($this->cacheTable);

			$queryC = "CREATE TABLE %s (\n  %s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
			$queryC = sprintf($queryC, $this->db->qn($this->cacheTable), implode(",\n  ", $cols));

			$this->db->setQuery($queryC)->execute();

			return true;
		}

		return false;
	}
}

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

class SpecificationsProcessor extends AbstractProcessor
{
	protected $helper;

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
		$columns    = array();
		$categories = $this->importer->getOption('categories', array());

		// Add specification fields if requested
		if (is_array($categories))
		{
			// If no category selected we use all product categories
			if (count($categories) == 0)
			{
				$db         = $this->importer->getDb();
				$filter     = array(
					'list.select' => 'a.id',
					'list.where'  => 'a.type LIKE ' . $db->q($db->escape('product/', true) . '%', false),
					'state'       => 1,
				);
				$categories = $this->helper->category->loadColumn($filter);
			}

			$fieldsIds   = $this->helper->category->getFields($categories, array('core', 'variant'), true);
			$specsFields = $this->helper->field->loadObjectList(array('list.select' => 'a.id, a.title', 'id' => $fieldsIds, 'state' => 1));

			foreach ($specsFields as $specsField)
			{
				$columns[] = 'SPEC_' .  $specsField->id . '_' . strtoupper(preg_replace('/[^0-9a-z]+/i', '_', $specsField->title));
			}
		}

		return $columns;
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
		return array(
			'x__product_id',
			'x__variant_id',
		);
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
			'x__specifications',
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
		if (!$obj->x__specifications)
		{
			$values = $this->extractSpecifications($obj);

			$obj->x__specifications = $values ? json_encode($values) : null;
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
		if (($obj->x__product_id || $obj->x__variant_id) && $obj->x__specifications)
		{
			try
			{
				$this->saveSpecifications($obj);
			}
			catch (\Exception $e)
			{
				$this->importer->timer->log($e->getMessage());
			}
		}
	}

	/**
	 * Extract the specs columns from the record and clear them from the row
	 *
	 * @param   \stdClass  $obj  The entire row from import table
	 *
	 * @return  array
	 *
	 * @since   1.4.7
	 */
	protected function extractSpecifications($obj)
	{
		static $multiple = array();
		static $props    = null;

		// Do this only once
		if ($props === null)
		{
			$props = array();

			foreach ($obj as $key => $value)
			{
				if (preg_match('/^spec_(\d+)(?:_.*)?$/', $key, $matches))
				{
					$pk = (int) $matches[1];

					$props[$pk]    = $key;
					$multiple[$pk] = false;
				}
			}

			if (count($props))
			{
				$filter = array('list.select' => 'a.id, a.params', 'id' => array_keys($props));
				$fields = $this->helper->field->getIterator($filter);

				foreach ($fields as $field)
				{
					$params = json_decode($field->params, true) ?: array();

					$multiple[$field->id] = isset($params['multiple']) && $params['multiple'] === 'true';
				}
			}
		}

		$values = array();

		foreach ($props as $pk => $key)
		{
			if (!empty($obj->$key))
			{
				// Split if multiple, any custom field using JSON should do this already
				$values[$pk] = $multiple[$pk] ? preg_split('#(?<!\\\);#', $obj->$key, -1, PREG_SPLIT_NO_EMPTY) : $obj->$key;
			}
		}

		return $values;
	}

	/**
	 * Save the specification fields
	 *
	 * @param   \stdClass  $obj  The importable record
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function saveSpecifications($obj)
	{
		$db       = $this->importer->getDb();
		$fields   = json_decode($obj->x__specifications, true);
		$fieldIds = array_keys($fields);

		if ($obj->x__variant_id)
		{
			$recordId  = $obj->x__variant_id;
			$tableName = 'variants';
		}
		elseif ($obj->x__product_id)
		{
			$recordId  = $obj->x__product_id;
			$tableName = 'products';
		}
		else
		{
			return true;
		}

		$this->helper->field->clearValue($tableName, $recordId, $fieldIds);

		foreach ($fields as $id => $value)
		{
			$fObj = (object) array(
				'table_name'  => $tableName,
				'record_id'   => $recordId,
				'field_id'    => $id,
				'field_value' => is_scalar($value) ? $value : json_encode($value),
				'is_json'     => is_scalar($value) ? 0 : 1,
			);

			$db->insertObject('#__sellacious_field_values', $fObj, 'id');
		}

		return true;
	}
}

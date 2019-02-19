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
use Sellacious\Import\Element\Product;
use Sellacious\Import\Element\Variant;
use Sellacious\Import\Processor\AbstractProcessor;

class PackageProductProcessor extends AbstractProcessor
{
	protected $helper;

	protected $keyName;

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
			$config       = ConfigHelper::getInstance('com_sellacious');
			$allowed      = $config->get('allowed_product_package');

			$this->enabled = $allowed;
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
		return $this->enabled ? array('package_items') : array();
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
			$key = $this->importer->getOption('unique_key.package');

			if (!$key)
			{
				$this->enabled = false;
			}

			$this->keyName = $key;
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
		if (!$this->enabled || !$obj->x__product_id || !$obj->package_items || $obj->product_type <> 'package')
		{
			return;
		}

		$identifiers = preg_split('#(?<!\\\);#', $obj->package_items, -1, PREG_SPLIT_NO_EMPTY);
		$items       = array();

		foreach ($identifiers as $identifier)
		{
			try
			{
				$p = null;
				$v = null;

				if ($this->keyName == 'product_code')
				{
					$this->helper->product->parseCode($identifier, $p, $v, $s);
				}
				else
				{
					$p = Product::findByKey($identifier, $this->keyName);
					$v = null;

					if (!$p)
					{
						$v = Variant::findByKey($identifier, $this->keyName, null);
						$p = $this->helper->variant->loadResult(array('list.select' => 'a.product_id', 'id' => $v));
					}
				}

				if ($p)
				{
					$items[sprintf('%d:%d', $p, $v)] = (object) array('package_id' => $obj->x__product_id, 'product_id' => $p, 'variant_id' => $v);
				}
			}
			catch (\Exception $e)
			{
				$this->importer->timer->log(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_PACKAGE_ITEM_LOOKUP', $identifier, $obj->product_title, $e->getMessage()));
			}
		}

		if ($items)
		{
			try
			{
				$db    = $this->importer->getDb();
				$query = $db->getQuery(true);

				$query->delete('#__sellacious_package_items')
				      ->where('package_id = ' . (int) $obj->x__product_id);

				$db->setQuery($query)->execute();

				foreach ($items as $map)
				{
					$db->insertObject('#__sellacious_package_items', $map, 'id');
				}
			}
			catch (\Exception $e)
			{
				$this->importer->timer->log(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_PACKAGE_ITEMS_ADD', $obj->product_title, $e->getMessage()));
			}
		}
	}
}

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
use Sellacious\Import\Element\SplCategory;
use Sellacious\Import\Processor\AbstractProcessor;

class ListingProcessor extends AbstractProcessor
{
	protected $tableName = '#__sellacious_seller_listing';

	protected $free;

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
			$config     = ConfigHelper::getInstance('com_sellacious');
			$this->free = (bool) $config->get('free_listing');
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
		$cols = array(
			'listing_start_date',
			'listing_end_date',
		);

		$repeat = $this->importer->getOption('category_rows', 2);

		$cols[] = 'special_categories';

		for ($n = 1; $n <= $repeat; $n++)
		{
			$cols[] = 'splcategory_' . $n;
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
		return array(
			'x__product_id',
			'x__seller_uid',
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
			'x__listing_days',
			'x__spl_category_ids',
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
		$sd = @strtotime($obj->listing_start_date);
		$ed = @strtotime($obj->listing_end_date);

		if ($sd && $ed && $sd < $ed)
		{
			$obj->listing_start_date = date('Y-m-d H:i:s', $sd);
			$obj->listing_end_date   = date('Y-m-d H:i:s', $ed);
			$obj->x__listing_days    = round(($ed - $sd) / 86400);

			if (!$obj->x__spl_category_ids)
			{
				try
				{
					$categories = $this->extractSplCategories($obj);

					$obj->x__spl_category_ids = json_encode($categories);
				}
				catch (\Exception $e)
				{
					$this->importer->timer->log('Error: ' . $e->getMessage());
				}
			}
		}
		else
		{
			$obj->listing_start_date = null;
			$obj->listing_end_date   = null;
			$obj->x__listing_days    = 0;
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
		$db    = $this->importer->getDb();
		$query = $db->getQuery(true);

		try
		{
			// Remove automatic subscriptions - basic + special
			$query->delete($this->tableName)
			      ->where('product_id = ' . (int) $obj->x__product_id)
			      ->where('seller_uid = ' . (int) $obj->x__seller_uid)
			      ->where('(subscription_date IS NULL OR subscription_date = 0)');

			if ($this->free)
			{
				// Do not remove free listing
				$query->where('category_id > 0');
			}

			$db->setQuery($query)->execute();

			// Disable (remaining) purchased subscriptions - basic + special
			$query->update($this->tableName)
			      ->set('state = 0')
			      ->where('product_id = ' . (int) $obj->x__product_id)
			      ->where('seller_uid = ' . (int) $obj->x__seller_uid)
			      ->where('state = 1');

			if ($this->free)
			{
				// Do not disable free listing
				$query->where('category_id > 0');
			}

			$db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			$this->importer->timer->log($e->getMessage());
		}

		// Add listings
		$listing = new \stdClass;

		$listing->id                = 0;
		$listing->product_id        = $obj->x__product_id;
		$listing->seller_uid        = $obj->x__seller_uid;
		$listing->category_id       = 0;
		$listing->days              = $obj->x__listing_days;
		$listing->publish_up        = $obj->listing_start_date;
		$listing->publish_down      = $obj->listing_end_date;
		$listing->subscription_date = null;
		$listing->carried_from      = null;
		$listing->state             = 1;

		// If we don't have free listing we create a basic listing too for special listing to work
		if (!$this->free)
		{
			if (!$db->insertObject($this->tableName, $listing, array('id')))
			{
				$this->importer->timer->log($db->getErrorMsg());

				return;
			}
		}

		// Handle special categories
		$categories = json_decode($obj->x__spl_category_ids, true);

		if (is_array($categories))
		{
			foreach ($categories as $catid)
			{
				$listing->id          = 0;
				$listing->category_id = $catid;

				if (!$db->insertObject($this->tableName, $listing, array('id')))
				{
					continue;
				}
			}
		}
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
		/** @var  \SellaciousTableCategory  $table */
		$table = \SellaciousTable::getInstance('SplCategory');

		$table->rebuild();
	}

	/**
	 * Extract the special categories from the record
	 *
	 * @param   \stdClass  $obj  The entire row from import table
	 *
	 * @return  int[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected function extractSplCategories($obj)
	{
		static $props = null;

		if ($props === null)
		{
			$props = array();

			foreach ($obj as $key => $value)
			{
				if (preg_match('/^splcategory_(\d+)$/', $key))
				{
					$props[] = $key;
				}
			}
		}

		// Extract the categories from split columns
		$catPaths = array();

		foreach ($props as $property)
		{
			$catPaths[] = $obj->$property;
		}

		if (!empty($obj->special_categories))
		{
			$catParts = preg_split('#(?<!\\\);#', $obj->special_categories, -1, PREG_SPLIT_NO_EMPTY);
			$catPaths = array_merge($catPaths, $catParts);
		}

		$canCreate  = $this->importer->getOption('create.special_categories', 0);
		$catNames   = array_unique(array_filter($catPaths, 'trim'));
		$categories = array();

		foreach ($catNames as $catName)
		{
			try
			{
				$categories[] = SplCategory::getId($catName, $canCreate);
			}
			catch (\Exception $e)
			{
				$message = \JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_MISSING_SPECIAL_CATEGORY', $catName, $e->getMessage());

				$this->importer->timer->log($message);
			}
		}

		return $categories;
	}
}

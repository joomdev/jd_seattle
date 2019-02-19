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
use Sellacious\Import\Element\Category;
use Sellacious\Import\Processor\AbstractProcessor;

class CategoryProcessor extends AbstractProcessor
{
	/**
	 * The destination table name to which to write the imported data
	 *
	 * @var    string
	 *
	 * @since   1.6.1
	 */
	protected $tableName = '#__sellacious_categories';

	/**
	 * The sellacious helper object instance
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since   1.6.1
	 */
	protected $helper;

	/**
	 * Flag to indicate whether we should sync menu with the categories
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since   1.6.1
	 */
	protected $syncMenu;

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
			$config         = ConfigHelper::getInstance('plg_system_sellaciousimporter');
			$this->syncMenu = $config->get('category_menu_sync', null);
			$this->helper   = \SellaciousHelper::getInstance();
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
		$cols    = array();
		$catRows = $this->importer->getOption('category_rows', 2);

		$cols[] = 'product_categories';

		for ($n = 1; $n <= $catRows; $n++)
		{
			$cols[] = 'category_' . $n;
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
		return array();
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
			'x__category_titles',
			'x__category_ids',
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
		$this->extractCategoryNames($obj);
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
		static $called;

		if (!$called)
		{
			if ($this->importer->getOption('clear.categories'))
			{
				// If this is a resumed import, it's not okay to do so
				// $this->clearCategories();
			}

			$called = true;
		}

		if (!$obj->x__category_titles)
		{
			return;
		}

		try
		{
			$categories = $this->extractCategories($obj);

			$obj->x__category_ids = json_encode($categories);
		}
		catch (\Exception $e)
		{
			$this->importer->timer->log('Error: ' . $e->getMessage());
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
		$table = \SellaciousTable::getInstance('Category');

		$table->rebuild();

		if ($this->syncMenu)
		{
			try
			{
				$this->helper->category->syncMenu();
			}
			catch (\Exception $e)
			{
				$this->importer->timer->log('Failed to sync menu with the categories: ' . $e->getMessage());
			}
		}
	}

	/**
	 * Method to delete all existing product categories, usually called before an import to remove older items
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function clearCategories()
	{
		try
		{
			$db    = $this->importer->getDb();
			$query = $db->getQuery(true);

			$query->delete($this->tableName)
				->where('type LIKE ' . $db->q('product/%', false))
				->where('id > 1');

			$db->setQuery($query)->execute();

			$this->importer->timer->log('Removed all existing product categories from database.', true);
		}
		catch (\JDatabaseExceptionExecuting $e)
		{
			$this->importer->timer->log('Error: ' . $e->getMessage() . ' @ ' . $e->getQuery(), true);
		}
	}

	/**
	 * Method to find the categories paths from the CSV record that needs to be created or loaded
	 *
	 * @param   \stdClass  $obj  The record from CSV import
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	protected function extractCategoryNames($obj)
	{
		$props    = $this->getColumns();
		$catPaths = array();

		if ($obj->product_categories)
		{
			$catPaths = preg_split('#(?<!\\\);#', $obj->product_categories, -1, PREG_SPLIT_NO_EMPTY);
		}

		foreach ($props as $property)
		{
			if ($property !== 'product_categories')
			{
				$catPaths[] = $obj->$property;
			}
		}

		$categories = array_unique(array_filter(array_map('trim', $catPaths), 'strlen'));

		$obj->x__category_titles = $categories ? json_encode($categories) : null;
	}

	/**
	 * Extract the categories from the record
	 *
	 * @param   \stdClass  $obj     The entire row from import table
	 * @param   array      $fields  The specification form fields
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.7
	 */
	protected function extractCategories($obj, $fields = array())
	{
		$categories = array();

		if ($obj->x__category_titles)
		{
			$catNames  = json_decode($obj->x__category_titles, true);
			$canCreate = $this->importer->getOption('create.categories', 0);

			foreach ($catNames as $catName)
			{
				try
				{
					$type  = 'product/' . strtolower($obj->product_type ?: 'physical');
					$catId = Category::getId($catName, $type, $canCreate, array_keys($fields));
				}
				catch (\Exception $e)
				{
					throw new \Exception(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_MISSING_CATEGORY', $catName, $e->getMessage()));
				}

				$categories[] = $catId;
			}
		}

		return $categories;
	}
}

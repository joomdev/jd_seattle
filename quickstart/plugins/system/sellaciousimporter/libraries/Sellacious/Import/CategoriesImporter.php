<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import;

// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Import utility class for categories
 *
 * @since   1.5.2
 */
class CategoriesImporter extends AbstractImporter
{
	/**
	 * The temporary table name that would hold the staging data from CSV for import processing
	 *
	 * @var    string
	 *
	 * @since   1.5.2
	 */
	public $importTable = '#__sellacious_import_temp_categories';

	/**
	 * The list of categories to be created/processed. This contains each items from CSV and their parent items extracted from CSV
	 *
	 * @var    \stdClass[]
	 *
	 * @since   1.5.2
	 */
	protected $categories = array();

	/**
	 * The list of specification fields created from CSV headers
	 *
	 * @var    \stdClass[]
	 *
	 * @since   1.5.2
	 */
	protected $specifications = array();

	/**
	 * The list of existing category map
	 *
	 * @var    \stdClass[]
	 *
	 * @since   1.6.0
	 */
	protected $map = array();

	/**
	 * The list of installed language
	 *
	 * @var    string[]
	 *
	 * @since   1.6.0
	 */
	protected $languages = array();

	/**
	 * Constructor
	 *
	 * @throws \Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct()
	{
		$languages = \JLanguageHelper::getInstalledLanguages(0);
		$default   = \JFactory::getLanguage()->getDefault();

		foreach ($languages as $element => $language)
		{
			$key = strtolower(str_replace('-', '_', $element));

			if ($default != $element)
			{
				$this->languages[$key] = $element;
			}
		}

		parent::__construct();
	}

	/**
	 * Get the columns for the import CSV template for the given categories if any, or a basic one without any specifications
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public function getColumns()
	{
		$columns = array(
			'CATEGORY_TITLE',
			'CATEGORY_TYPE',
			'CATEGORY_SUMMARY',
			'CATEGORY_DESCRIPTION',
			'ALLOW_PRODUCT_COMPARE',
			'IMAGE_URL',
		);

		foreach ($this->languages as $key => $language)
		{
			$columns[] = 'CATEGORY_TITLE_' . strtoupper($key);
			$columns[] = 'CATEGORY_DESCRIPTION_' . strtoupper($key);
		}

		// Let the plugins add custom columns
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchImportColumns', array('com_importer.import.categories', &$columns, $this));

		return array_values($columns);
	}

	/**
	 * Get the additional columns for the records which are required for the import utility system
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public function getSysColumns()
	{
		$columns = array(
			'x__name',
			'x__level',
			'x__path',
			'x__parent',
			'x__segments',
			'x__category_id',
			'x__parent_id',
			'x__category_type',
			'x__core_fields',
			'x__variant_fields',
			'x__auto',
		);

		foreach ($this->languages as $key => $language)
		{
			$columns[] = 'x__title_' . $key;
			$columns[] = 'x__segments_' . $key;
		}

		return $columns;
	}

	/**
	 * Import the records from CSV that was earlier loaded
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 *
	 * @see     load()
	 */
	public function import()
	{
		try
		{
			// Check file pointer
			if (!$this->fp)
			{
				throw new \RuntimeException(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_ERROR_FILE_NOT_LOADED'));
			}

			// Check headers, if translated one is not available try using actual CSV header
			if (!$this->fields)
			{
				$this->fields = array_map('strtolower', $this->headers);
			}

			$this->check($this->fields);

			// Mark the start of process
			$this->timer->start(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_START_FILENAME', basename($this->filename)));

			// We need to identify specifications fields before temporary table, we need that info during temporary table creation.
			$this->specifications = $this->extractFields();

			// Build a temporary table from CSV
			$this->createTemporaryTable();

			// Let the plugins pre-process the table and perform any preparation task
			$this->dispatcher->trigger('onBeforeImport', array('com_importer.import.categories', $this));

			if ($this->getOption('reset.categories'))
			{
				$this->clearCategories();
			}

			// Process the batch
			$this->processBatch();

			// Let the plugins post-process the record and perform any relevant task
			$this->dispatcher->trigger('onAfterImport', array('com_importer.import.categories', $this));

			// Rebuild any nested set tree involved
			$this->timer->stop(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_REBUILD_NESTED_TABLE'));

			/** @var  \JTableNested  $table */
			$table = $this->helper->category->getTable();
			$table->rebuild();

			// Re-sync category menu
			if ($this->helper->config->get('category_menu_sync', null, 'plg_system_sellaciousimporter'))
			{
				$this->helper->category->syncMenu();
			}

			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Remove the temporary table
			$this->db->dropTable($this->importTable, true);

			// Mark the end of process
			$this->timer->stop(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_FINISHED'));

			return true;
		}
		catch (\Exception $e)
		{
			// Mark the unexpected termination of process
			$this->timer->interrupt(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_INTERRUPTED', $e->getMessage()));

			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_IMPORT_GENERATING_OUTPUT_CSV'));

			$this->outputCsv();

			// Remove the temporary table
			$this->db->dropTable($this->importTable, true);

			return false;
		}
	}

	/**
	 * Method to check whether the CSV columns are importable.
	 *
	 * @param   array  $fields  The alias processed column list
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	protected function check($fields)
	{
	}

	/**
	 * Convert the human readable text values from the import CSV to database friendly values to be saved.
	 *
	 * @param   \stdClass  $obj  The record from the CSV import table
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 *
	 * @deprecated   This should be pre-merged into preprocessCsvRow() method itself
	 */
	protected function translate($obj)
	{
		$cTypes   = array(
			'physical'   => 'product/physical',
			'electronic' => 'product/electronic',
			'package'    => 'product/package',
		);
		$booleans = array('TRUE', 'YES', '1', 'PUBLISHED', 'ACTIVE', 'ENABLED', 'T' , 'Y');

		if (isset($obj->allow_product_compare))
		{
			$obj->allow_product_compare = in_array(strtoupper($obj->allow_product_compare), $booleans) ? 1 : 0;
		}

		$parts = preg_split('#(?<!\\\)/#', $obj->category_title, -1, PREG_SPLIT_NO_EMPTY);

		if ($parts)
		{
			$parts = array_map('trim', $parts);
			$parts = array_map('stripslashes', $parts);

			// Following lines shouldn't be moved up/down. There's magic in array_pop, fullName/parentName are not same
			$segments   = array_values($parts);
			$fullName   = implode(':::', $parts);
			$cName      = array_pop($parts);
			$parentName = implode(':::', $parts);

			foreach ($this->languages as $key => $language)
			{
				$tCol  = 'category_title_' . $key;
				$tColX = 'x__title_' . $key;
				$tSegX = 'x__segments_' . $key;

				$tSegments = array();
				$xSegments = array();

				// Title will be for all segments of hierarchical path
				if (isset($obj->$tCol))
				{
					$tSegments = preg_split('#(?<!\\\)/#', $obj->$tCol, - 1, PREG_SPLIT_NO_EMPTY) ?: array();
				}

				// If we have multiple segments, we may be given translation for all or just the last one
				if (count($tSegments) === count($segments))
				{
					$xSegments = $tSegments;
				}
				elseif (count($tSegments) == 1)
				{
					$xSegments   = array_fill(0, count($segments) - 2, null);
					$xSegments[] = reset($tSegments);
				}

				$obj->$tSegX = json_encode($xSegments);
				$obj->$tColX = end($xSegments);
			}

			$obj->x__name      = $cName;
			$obj->x__path      = $fullName;
			$obj->x__parent    = $parentName;
			$obj->x__level     = count($segments);
			$obj->x__segments  = json_encode($segments);
			$obj->x__parent_id = count($segments) == 1 ? 1 : null;

			// Child categories would inherit from parent and this value will be ignored
			$obj->x__category_type = ArrayHelper::getValue($cTypes, strtolower($obj->category_type), 'product/physical');
		}
		else
		{
			$obj->x__state = -1;
		}
	}

	/**
	 * Method to pre-process a CSV row before inserting into the importTable
	 *
	 * @param   array      $row     The row as loaded from the CSV file
	 * @param   \stdClass  $record  The record that will be inserted into the importTable
	 * @param   int        $offset  The row offset starting from 1 for first data row
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function preprocessCsvRow($row, $record, $offset)
	{
		parent::preprocessCsvRow($row, $record, $offset);

		// First row may contain list options for list type fields, for this the category title (first column) must be empty
		if ($offset === 1 && trim($record->category_title) === '')
		{
			foreach ($this->specifications as $i => $field)
			{
				if ($field->type == 'list')
				{
					$options = preg_split('#(?<!\\\)/#', $row[$i], -1, PREG_SPLIT_NO_EMPTY) ?: array();

					if (count($options))
					{
						$options = array_map('trim', $options);
						$options = array_map('stripslashes', $options);

						$object  = new \stdClass;

						$object->id     = $field->id;
						$object->params = json_encode(array('listoptions' => $options));

						$this->db->updateObject('#__sellacious_fields', $object, array('id'));
					}
				}
			}
		}
		else
		{
			$core     = array();
			$variants = array();

			foreach ($this->specifications as $i => $field)
			{
				if (isset($row[$i]))
				{
					if (strtolower($row[$i]) == 'c')
					{
						$core[] = $field->id;
					}
					elseif (strtolower($row[$i]) == 'v')
					{
						$variants[] = $field->id;
					}
				}
			}

			$record->x__core_fields    = $core ? json_encode($core) : null;
			$record->x__variant_fields = $variants ? json_encode($variants) : null;
		}
	}

	/**
	 * Perform the initial processing of the temporary table before actual import begins.
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function processTemporaryTable()
	{
		// We ensure that the top level are processed first and so on…
		$query = $this->db->getQuery(true);
		$query->select('x__id, x__name, x__level, x__path, x__parent, x__category_type, x__segments')
			->from($this->importTable)
			->where('x__state = 0')
			->order('x__level');

		foreach ($this->languages as $key => $language)
		{
			$tSegX = 'x__segments_' . $key;

			$query->select($tSegX);
		}

		$iterator = $this->db->setQuery($query)->getIterator();

		$cats = array();

		foreach ($iterator as $index => $obj)
		{
			$obj->x__segments = json_decode($obj->x__segments, true);

			foreach ($this->languages as $key => $language)
			{
				$tSegX = 'x__segments_' . $key;

				$obj->$tSegX = json_decode($obj->$tSegX, true);
			}

			$cats[$obj->x__path] = $obj;
		}

		// We need to modify the array in loop so we make a copy
		$categories = array_values($cats);

		foreach ($categories as $obj)
		{
			$segments = array();

			foreach ($obj->x__segments as $segment)
			{
				$cName      = $segment;
				$parentName = implode(':::', $segments);
				$segments[] = $segment;
				$fullName   = implode(':::', $segments);

				if (!isset($cats[$fullName]))
				{
					$category = (object) array(
						'x__id'                 => null,
						'x__name'               => $cName,
						'x__level'              => count($segments),
						'x__path'               => $fullName,
						'x__parent'             => $parentName,
						'x__segments'           => $segments,
						'x__auto'               => 1,
						'x__category_id'        => null,
						'x__parent_id'          => count($segments) == 1 ? 1 : null,
						'x__category_type'      => $obj->x__category_type,
						'x__core_fields'        => null,
						'x__variant_fields'     => null,
						'category_summary'      => null,
						'category_description'  => null,
						'allow_product_compare' => null,
					);

					// Translation fields
					foreach ($this->languages as $key => $language)
					{
						$tColX = 'x__title_' . $key;
						$tSegX = 'x__segments_' . $key;

						$xSegments = $obj->$tSegX;
						$xIndex    = $category->x__level - 1;

						$category->$tColX = isset($xSegments[$xIndex]) ? $xSegments[$xIndex] : '';
					}

					$this->db->insertObject($this->importTable, $category, 'x__id');

					$cats[$fullName] = $category;
				}
			}
		}

		// Re-query with the same query. New data has been inserted since.
		$iterator = $this->db->setQuery($query)->getIterator();

		$this->categories = $iterator;

		return true;
	}

	/**
	 * Process the batch import process
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	protected function processBatch()
	{
		$query = $this->db->getQuery(true);
		$index = -1;

		$this->timer->log(sprintf('Total %d categories to process.', count($this->categories)));

		// Now we can create each category, and be sure that the parent items are created or loaded first.
		foreach ($this->categories as $index => $category)
		{
			// Defer loading as one iteration may update more rows which can be reused subsequently
			if ($category->x__id)
			{
				$query->clear()->select('*')->from($this->importTable)->where('x__id = ' . (int) $category->x__id);

				$obj = $this->db->setQuery($query)->loadObject();

				// Find any existing record before creating new (may be override "type" only if not specified explicitly)
				$filter = array(
					'list.select' => 'a.id, a.title, a.type',
					'parent_id'   => $obj->x__parent_id,
					'title'       => $obj->x__name,
				);
				$catX   = $this->helper->category->loadObject($filter);

				if ($catX)
				{
					$obj->x__category_id   = $catX->id;
					$obj->x__category_type = $catX->type;
				}

				$imported = $this->processRecord($obj);

				$obj->x__state    = (int) $imported;
				$obj->x__segments = is_string($obj->x__segments) ? $obj->x__segments : json_encode($obj->x__segments);

				$this->db->updateObject($this->importTable, $obj, array('x__id'));
			}

			// Mark the progress
			$this->timer->hit($index + 1, 100, \JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS'));
		}

		$this->timer->hit($index + 1, 1, \JText::_('LIB_SELLACIOUS_IMPORTER_IMPORT_PROGRESS'));
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $category  The record to be imported into sellacious
	 *
	 * @return  bool  Whether the record was imported successfully
	 *
	 * @since   1.5.2
	 */
	protected function processRecord($category)
	{
		static $increment = 0;

		// Parent can be looked up in the $items array using fullName key matching, and the parent_id and type would be inherited!
		if (!$category->x__parent_id || !$category->x__category_type)
		{
			return false;
		}

		$me     = \JFactory::getUser();
		$isoNow = \JFactory::getDate()->toSql();
		$alias  = \JApplicationHelper::stringURLSafe($category->x__name);
		$alias  = $alias ?: \JApplicationHelper::stringURLSafe($isoNow) . '-' . ++$increment;

		$obj = new \stdClass;

		$obj->id             = $category->x__category_id ?: null;
		$obj->title          = $category->x__name;
		$obj->alias          = $alias;
		$obj->introtext      = $category->category_summary;
		$obj->description    = $category->category_description;
		$obj->compare        = $category->allow_product_compare;
		$obj->parent_id      = $category->x__parent_id;
		$obj->state          = 1;
		$obj->type           = $category->x__category_type;
		$obj->core_fields    = $category->x__core_fields;
		$obj->variant_fields = $category->x__variant_fields;
		$obj->created        = $isoNow;
		$obj->created_by     = $me->id;

		$saved = false;

		if ($obj->id)
		{
			if ($this->getOption('update.categories', 0))
			{
				$saved = $this->db->updateObject('#__sellacious_categories', $obj, array('id'));
			}
		}
		else
		{
			if ($this->getOption('create.categories', 0))
			{
				$saved = $this->db->insertObject('#__sellacious_categories', $obj, 'id');
			}
		}

		/*
		 * WARNING:
		 * This if and the above cannot be merged. Think + understand the code before you change!
		 * The value <var>$obj->id</var> is meant to be modified in each of the first if-else conditional branches.
		 */
		if ($obj->id)
		{
			$category->x__category_id = $obj->id;

			if ($saved)
			{
				$this->saveTranslations($category);
			}

			$oUpd = (object) array(
				'x__parent'        => $category->x__path,
				'x__parent_id'     => $category->x__category_id,
				'x__category_type' => $category->x__category_type,
			);

			$this->db->updateObject($this->importTable, $oUpd, array('x__parent'));
		}

		return (bool) $obj->id;
	}

	/**
	 * Load the image as specified in the record
	 *
	 * @param   \stdClass  $obj  The entire row from import
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	protected function saveImage($obj)
	{
		static $pattern;

		if (!$obj->x__category_id)
		{
			return true;
		}

		if (strpos($obj->image_url, '%') !== false)
		{
			// Optimize! Build pattern only once (We use the headers as short-code)
			if (!$pattern)
			{
				$headers = array();

				foreach ($this->headers as $header)
				{
					$headers[] = '%' . preg_quote($header, '/') . '%';
				}

				$pattern = '/(' . implode('|', $headers) . ')/i';
			}

			$matches = array();

			preg_match_all($pattern, $obj->image_url, $matches, PREG_SET_ORDER);

			foreach ($matches as $match)
			{
				$key = strtolower($match[1]);

				$obj->image_url = str_replace($match[0], isset($obj->$key) ? $obj->$key : '', $obj->image_url);
			}
		}

		// If there is no image, do not proceed here
		if (strlen($obj->image_url) == 0)
		{
			return true;
		}

		// Do not allow a query string
		if (strpos($obj->image_url, '?') !== false)
		{
			return false;
		}

		// Check for an allowed image file type
		$ext = substr($obj->image_url, -4);

		if ($ext != '.jpg' && $ext != '.png')
		{
			return false;
		}

		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// People often forget http(s), we can at most detect a 'www.'
		if (substr($obj->image_url, 0, 4) == 'www.')
		{
			$obj->image_url = 'http://' . $obj->image_url;
		}

		// Use this image: id, table_name, record_id, context, original_name, type, path, size, state
		$directory  = $this->helper->media->getBaseDir(sprintf('categories/images/%d/', $obj->x__category_id));
		$properties = array(
			'table_name' => 'categories',
			'record_id'  => $obj->x__category_id,
		);

		$filename = basename($obj->image_url);
		$path     = JPATH_SITE . $directory . $filename;

		if ((substr($obj->image_url, 0, 7) == 'http://' || substr($obj->image_url, 0, 7) == 'https://'))
		{
			// We'll download this image later in a separate batch. Put a placeholder for now
			$placeholder = \JHtml::_('image', 'com_importer/coming-soon-placeholder.png', '', null, true, 1);

			if ($placeholder)
			{
				$placeholder = JPATH_SITE . substr($placeholder, strlen(rtrim(\JUri::root(true), '\\/')));

				if (\JFolder::create(dirname($path)) && \JFile::copy($placeholder, $path))
				{
					$params = array(
						'remote_download' => true,
						'download_url'    => $obj->image_url,
					);

					$properties['state']  = -1;
					$properties['path']   = $directory . $filename;
					$properties['params'] = json_encode($params);
				}
			}
		}
		elseif (is_file(\JPath::clean(JPATH_SITE . '/' . $obj->image_url)))
		{
			if (\JFolder::create(dirname($path)) && \JFile::copy(JPATH_SITE . '/' . $obj->image_url, $path))
			{
				$properties['state'] = 1;
				$properties['path']  = $directory . $filename;
			}
		}

		if (isset($properties['path']))
		{
			$properties['context']       = 'images';
			$properties['type']          = 'image/' . ($ext == '.jpg' ? 'jpeg' : 'png');
			$properties['size']          = filesize(JPATH_SITE . '/' . $properties['path']);
			$properties['original_name'] = $filename;

			$record = (object) $properties;

			return $this->db->insertObject('#__sellacious_media', $record, 'id');
		}

		return false;
	}

	/**
	 * Extract the specification fields that needs to be created and associated to the category
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.5.2
	 */
	protected function extractFields()
	{
		$fields   = array();
		$selected = (array) $this->getOption('specification_fields');

		foreach ($this->headers as $i => $header)
		{
			if (in_array($header, $selected) && preg_match('#^(?:(F)\/)?(text|textarea|list|number)\/(.*)$#i', $header, $m))
			{
				list(, $filterable, $type, $name) = $m;

				$field = $this->createField($name, strtolower($type), strtolower($filterable) == 'f');

				if ($field)
				{
					$fields[$i] = $field;
				}
			}
		}

		/** @var  \SellaciousTableField  $table */
		$table = $this->helper->field->getTable();
		$table->rebuild();

		return $fields;
	}

	/**
	 * Create field
	 *
	 * @param   string  $name
	 * @param   string  $type
	 * @param   bool    $filterable
	 *
	 * @return  \stdClass
	 *
	 * @since   1.5.2
	 */
	protected function createField($name, $type, $filterable = false)
	{
		$me    = \JFactory::getUser();
		$now   = \JFactory::getDate();
		$parts = preg_split('#(?<!\\\)/#', $name, -1, PREG_SPLIT_NO_EMPTY);
		$parts = $parts ? array_map('stripslashes', $parts) : array();
		$name  = array_pop($parts);

		if ($type == 'fieldgroup')
		{
			$parent = (object) array('id' => 1, 'title' => 'Root');
		}
		else
		{
			$group  = array_pop($parts);
			$parent = $this->createField($group ?: 'General', 'fieldgroup', false);
		}

		if (!$parent)
		{
			return null;
		}

		$classes = array('text' => 'inputbox', 'number' => 'inputbox', 'textarea' => 'textarea', 'list' => 'w100p');
		$class   = ArrayHelper::getValue($classes, $type);

		$filter = array(
			'title'     => $name,
			'parent_id' => $parent->id,
		);
		$field  = $this->helper->field->loadObject($filter);

		if ($field)
		{
			// We cannot change context at all
			if ($field->context != 'product')
			{
				$this->timer->log(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_ERROR_CATEGORIES_SPECIFICATIONS_FIELD_CONTEXT_MISMATCH_WARNING', $name));

				return null;
			}
			// We cannot change type for a field group (however, this is an unreachable condition)
			elseif ($field->type == 'fieldgroup' && $type != 'fieldgroup')
			{
				$this->timer->log(\JText::sprintf('PLG_SYSTEM_SELLACIOUSIMPORTER_ERROR_CATEGORIES_SPECIFICATIONS_FIELD_GROUP_TYPE_CHANGE_WARNING'));

				return null;
			}

			$field->title       = $name;
			$field->type        = $type;
			$field->class       = $class;
			$field->filterable  = $filterable;
			$field->state       = 1;
			$field->modified    = $now->toSql();
			$field->modified_by = $me->id;

			$saved = $this->db->updateObject('#__sellacious_fields', $field, array('id'));
		}
		else
		{
			$alias = \JApplicationHelper::stringURLSafe($name) ?: $now->format('Y-m-d-h-i-s-') . rand(1, 1000);
			$field = new \stdClass;

			$field->id          = null;
			$field->title       = $name;
			$field->alias       = $alias;
			$field->parent_id   = $parent->id;
			$field->context     = 'product';
			$field->message     = '';
			$field->description = '';
			$field->validate    = '';
			$field->required    = 'false';
			$field->title       = $name;
			$field->type        = $type;
			$field->class       = $class;
			$field->filterable  = $filterable;
			$field->state       = 1;
			$field->created     = $now->toSql();
			$field->created_by  = $me->id;

			$saved = $this->db->insertObject('#__sellacious_fields', $field, 'id');
		}

		if (!$saved)
		{
			$this->timer->log(\JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_ERROR_CATEGORIES_SPECIFICATIONS_FIELD_SAVE_FAILED'));

			return null;
		}

		return $field;
	}

	/**
	 * Method to delete all existing product categories, usually called before an import to remove older items
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	protected function clearCategories()
	{
		$query = $this->db->getQuery(true);
		$query->delete('#__sellacious_categories')->where('id > 1')->where('type LIKE ' . $this->db->q('product/%', false));

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Method to save the translation for category title and description
	 *
	 * @param   \stdClass  $category  The record being imported into sellacious
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function saveTranslations($category)
	{
		// Translation fields
		foreach ($this->languages as $key => $language)
		{
			$cols = array(
				'title'       => 'x__title_' . $key,
				'description' => 'category_description_' . $key,
			);

			foreach ($cols as $field => $col)
			{
				if (!empty($category->$col))
				{
					$obj = new \stdClass;

					$obj->language_code   = $language;
					$obj->reference_table = 'sellacious_categories';
					$obj->reference_field = $field;
					$obj->reference_id    = $category->x__category_id;

					$filter = array(
						'list.select' => 'a.id',
						'list.from'   => '#__sellacious_translations',
					);
					$trId   = $this->helper->category->loadResult(array_merge($filter, (array) $obj));

					$obj->value = $category->$col;

					if ($trId)
					{
						$obj->id = $trId;

						$this->db->updateObject('#__sellacious_translations', $obj, array('id'));
					}
					else
					{
						$obj->state = 1;

						$this->db->insertObject('#__sellacious_translations', $obj, 'id');
					}
				}
			}
		}
	}
}

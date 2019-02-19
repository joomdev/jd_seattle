<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Report;

// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * This base object will be immutable, however this can be extended
 * and the child classes may allow property write if needed.
 *
 * @package  Sellacious\Report
 *
 * @property-read  $name
 * @property-read  $title
 * @property-read  $active
 *
 * @since   1.6.0
 */
abstract class ReportHandler
{
	/**
	 * @var    \JDatabaseDriver
	 *
	 * @since   1.6.0
	 */
	protected $db;

	/**
	 * The path to the report manifest file
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $manifestPath = null;

	/**
	 * The report type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $type;

	/**
	 * The report label
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $label;

	/**
	 * The report description
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $description;

	/**
	 * The list of columns to restrict the report, all other columns will be skipped if given
	 *
	 * @var   string[]
	 *
	 * @since   1.6.0
	 */
	protected $limitCols;

	/**
	 * @var    array
	 *
	 * @since   1.6.0
	 */
	protected $columns;

	/**
	 * @var    array
	 *
	 * @since   1.6.0
	 */
	protected $filters;

	/**
	 * @var    array
	 *
	 * @since   1.6.0
	 */
	protected $userFilters;

	/**
	 * Default ordering column
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $order;

	/**
	 * Default ordering direction
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $orderDir = 'ASC';

	/**
	 * Total records found in report
	 *
	 * @var    int
	 *
	 * @since  1.6.0
	 */
	protected $total;

	/**
	 * Report Summary
	 *
	 * @var    array
	 *
	 * @since  1.6.0
	 */
	protected $summary;

	/**
	 * Sellacious Helper
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since  1.6.0
	 */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @since   1.6.0
	 */
	public function __construct()
	{
		if (!isset($this->columns))
		{
			$this->columns = $this->loadColumns();
		}

		$this->db = \JFactory::getDbo();

		$this->helper = \SellaciousHelper::getInstance();
	}

	/**
	 * Get the report columns
	 *
	 * @param   bool  $all  Whether to return all available columns ignoring the limitCols setting
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getColumns($all = false)
	{
		if (!isset($this->columns))
		{
			$this->columns = $this->loadColumns();
		}

		if ($all || !$this->limitCols)
		{
			return array_values($this->columns);
		}

		$cols = array();

		foreach ($this->limitCols as $colName)
		{
			if (array_key_exists($colName, $this->columns))
			{
				$cols[] = $this->columns[$colName];
			}
		}

		return $cols;
	}

	/**
	 * Get the report filters
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getFilter()
	{
		return $this->filters;
	}

	/**
	 * Get the report filters set by user
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getUserFilter()
	{
		return $this->userFilters;
	}

	/**
	 * Get the report column
	 *
	 * @param   string  $name  Get the report column
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getColumn($name)
	{
		if (!isset($this->columns))
		{
			$this->columns = $this->loadColumns();
		}

		$column = array_filter($this->columns, function($col) use($name) {
			return ($col->name == $name);
		});

		return array_values($column);
	}

	/**
	 * Get the report columns which are sortable
	 *
	 * @param   bool  $all  Whether to return all available columns ignoring the limitCols setting
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getSortableColumns($all = false)
	{
		$columns = $this->getColumns($all);

		$cols = array_filter($columns, function($value) {
			return $value->sortable;
		});

		return $cols;
	}

	/**
	 * Get the report columns which are filterable
	 *
	 * @param   bool  $all  Whether to return all available columns ignoring the limitCols setting
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getFilterableColumns($all = false)
	{
		$columns = $this->getColumns($all);

		$cols = array_filter($columns, function($value) {
			return $value->filterable;
		});

		return $cols;
	}

	/**
	 * Get handler name
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getName()
	{
		return $this->type;
	}

	/**
	 * Get handler label
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getLabel()
	{
		return \JText::_($this->label);
	}

	/**
	 * Get handler description
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getDescription()
	{
		return \JText::_($this->description);
	}

	/**
	 * Get default ordering and direction
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getOrdering()
	{
		return $this->order . ' ' . $this->orderDir;
	}

	/**
	 * Get Report Summary
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getSummary()
	{
		if (!isset($this->summary))
		{
			$this->summary = array();
		}

		return $this->summary;
	}

	/**
	 * Get Rendered Report Summary
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function getRenderedSummary()
	{
		ob_start();
		$layoutPath = \JPluginHelper::getLayoutPath('system', 'sellaciousreportscart', 'summary_' . $this->type);

		if (is_file($layoutPath))
		{
			$displayData = $this->getSummary();

			unset($namespace, $layout);

			/**
			 * Variables available to the layout
			 *
			 * @var  $this
			 * @var  $layoutPath
			 * @var  $displayData
			 */
			include $layoutPath;
		}

		return ob_get_clean();
	}

	/**
	 * Get total records
	 *
	 * @return  int
	 *
	 * @since   1.6.0
	 */
	abstract public function getTotal();

	/**
	 * Set the report columns
	 *
	 * @param   array  $columns
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setColumns($columns = array())
	{
		$this->limitCols = $columns;
	}

	/**
	 * Set the report filters
	 *
	 * @param   array  $filters
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setFilter($filters = array())
	{
		$this->filters = $filters;
		$this->total   = 0;
	}

	/**
	 * Set total records
	 *
	 * @param   int  $total
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setTotal($total = 0)
	{
		$this->total = $total;
	}

	/**
	 * Set the user filters for report
	 *
	 * @param   array  $userFilters
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setUserFilter($userFilters = array())
	{
		$this->userFilters = $userFilters;
		$this->total       = 0;
	}

	/**
	 * Set the ordering and direction
	 *
	 * @param   string  $col
	 * @param   string  $dir
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setOrdering($col, $dir)
	{
		$this->order    = $col;
		$this->orderDir = $dir;
	}

	/**
	 * Set report summary
	 *
	 * @param   array  $summary
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setSummary($summary = array())
	{
		$this->summary = $summary;
	}


	/**
	 * Get Report List Items
	 *
	 * @param   int   $start   Starting index of the items
	 * @param   int   $limit   Number of records to show
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	abstract public function getList($start = 0, $limit = 0);

	/**
	 * Render Report Summary
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function renderSummary()
	{

	}

	/**
	 * Load Report columns from the handler's manifest xml file
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function loadColumns()
	{
		$parsedData         = $this->parseManifest($this->manifestPath);
		$this->type         = $parsedData['type'];
		$this->label        = $parsedData['label'];
		$this->description  = $parsedData['description'];
		$this->order        = $parsedData['ordering'];
		$this->orderDir     = $parsedData['ordering_dir'];

		return $parsedData["items"];
	}

	/**
	 * Parse the Report Xml file
	 *
	 * @param   string  $path  Path to the xml file
	 *
	 * @return  array   parsed data
	 *
	 * @since   1.6.0
	 */
	public function parseManifest($path)
	{
		$parsedData = array('type' => '', 'label' => '', 'description' => '', 'ordering' => '');
		$items = array();

		if (($xml = simplexml_load_file($path, null, LIBXML_NOCDATA)) && $xml instanceof \SimpleXMLElement)
		{
			$orderDir = $this->orderDir;

			foreach ($xml->columns->column as $element)
			{
				$item = new \stdClass;

				$item->name       = (string) $element['name'];
				$item->title      = \JText::_((string) $element['title']);
				$item->sortable   = $element['sortable'] == 'true';
				$item->filterable = $element['filterable'] == 'true';
				$item->sortorder  = (string) $element['sortorder'];

				$items[$item->name] = $item;

				if ($item->name == (string) $xml->columns['ordering'])
				{
					$orderDir = $element['sortorder'];
				}
			}

			$parsedData['type']         = $xml->type;
			$parsedData['label']        = $xml->label;
			$parsedData['description']  = $xml->desc;
			$parsedData['ordering']     = $xml->columns['ordering'] ? (string) $xml->columns['ordering'] : $this->order;
			$parsedData['ordering_dir'] = $orderDir;
		}

		$parsedData['items'] = $items;

		return $parsedData;
	}
}

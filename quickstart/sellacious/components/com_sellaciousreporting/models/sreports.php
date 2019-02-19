<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Report\ReportHandler;
use Sellacious\Report\ReportHelper;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of sreports records.
 *
 * @since  1.6.0
 */
class SellaciousreportingModelSreports extends SellaciousModelList
{
	// Report Id
	protected $reportId;

	/**
	 * @var  ReportHandler
	 *
	 * @since 1.6.0
	 */
	protected $reportHandler;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;

		$this->reportId         = $input->get('id', 0, 'uint');

		$this->setupHandler();

		$filterable = $this->reportHandler->getFilterableColumns();
		$sortable = $this->reportHandler->getSortableColumns();

		$filterable = ArrayHelper::arrayUnique(array_merge($filterable, $sortable));

		$this->filter_fields = array();

		foreach ($filterable as $column)
		{
			array_push($this->filter_fields, $column->name);
		}
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// List state information.
		parent::populateState($ordering, $direction);

		// Setting User Filters
		$filterForm = $this->getFilterForm();
		$reportFilters = array_keys($filterForm->getGroup('filter'));
		$reportFilters = array_map(function($val){
			$val = explode('_', $val);
			return $val[1];
		}, $reportFilters);

		$userFilters = $this->reportHandler->getUserFilter();

		foreach ($reportFilters as $reportFilter)
		{
			$userFilters[$reportFilter] = $this->state->get("filter." . $reportFilter);
		}

		$this->reportHandler->setUserFilter($userFilters);

		// Setting Report Filters
		$filters = $this->reportHandler->getFilter();
		if (!empty($filters))
		{
			foreach ($filters as $filter => $value)
			{
				$this->setState('filter.' . $filter, $value);
			}
		}

		// Setting Selected columns
		$columns = $this->reportHandler->getColumns();
		$columns = $app->getUserStateFromRequest("filter.columns", 'filter_columns', $columns, 'ARRAY');

		// Set Columns to state
		$this->setState('filter.columns', $columns);

		// Setting columns to the handler from state
		$colNames = ArrayHelper::getColumn($columns, 'name');
		$this->reportHandler->setColumns($colNames);
	}

	/**
	 * Get the report handler.
	 *
	 * @return  ReportHandler[]
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getHandler()
	{
		return $this->reportHandler;
	}

		/**
	 * Setup the report handler.
	 *
	 * @return  null
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function setupHandler()
	{
		$reportId = $this->reportId;

		$report = $this->getTable('Report', 'SellaciousTable');
		$report->load($reportId);

		$handlerName = $report->handler;
		$handler = ReportHelper::getHandler($handlerName);

		//Selected columns
		$reportCols = is_array($report->columns) ? $report->columns : (json_decode($report->columns, true) ?: array());
		$handler->setColumns($reportCols);

		//Selected filters
		$filters = is_array($report->filter) ? $report->filter : (json_decode($report->filter, true) ?: array());
		$handler->setFilter($filters);

		$this->reportHandler = $handler;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $start  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  \stdClass[]  An array of results.
	 *
	 * @since   1.6.0
	 * @throws  Exception
	 */
	public function _getList($query, $start = 0, $limit = 0)
	{
		$fullOrdering = $this->getState('list.fullordering');
		$defaultOrdering = array();

		if (empty(trim($fullOrdering)))
		{
			// If default ordering column no longer is selected, set the first filterable column as the default ordering
			$filterForm = $this->getFilterForm();

			$sortableColumns = $this->reportHandler->getSortableColumns();
			$ordering = explode(' ', $this->reportHandler->getOrdering());

			$defaultOrdering = array_filter($sortableColumns, function ($col) use ($ordering) {
				return (isset($ordering[0]) && $col->name == $ordering[0]);
			});

			if (empty($defaultOrdering) && isset($sortableColumns[0]))
			{
				$fullOrdering = $sortableColumns[0]->name . ' ' . $sortableColumns[0]->sortorder;

				$filterForm->setValue('fullordering', 'list', $fullOrdering);
			}
		}

		if (!empty(trim($fullOrdering)))
		{
			$fullOrdering = explode(' ', trim($fullOrdering));

			if (isset($fullOrdering[1]))
			{
				$ordering = $fullOrdering[0];
				$direction = $fullOrdering[1];
				$this->reportHandler->setOrdering($ordering, $direction);
			}
		}
		else if (empty($defaultOrdering))
		{
			// no ordering or direction
			$this->reportHandler->setOrdering('', '');
		}

		$list = $this->reportHandler->getList($start, $limit);

		$this->reportHandler->setSummary();

		return $list;
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   1.6.0
	 */
	public function getTotal()
	{
		$fullOrdering = $this->getState('list.fullordering');
		$defaultOrdering = array();

		if (empty(trim($fullOrdering)))
		{
			// If default ordering column no longer is selected, set the first filterable column as the default ordering
			$filterForm = $this->getFilterForm();

			$sortableColumns = $this->reportHandler->getSortableColumns();
			$ordering = explode(' ', $this->reportHandler->getOrdering());

			$defaultOrdering = array_filter($sortableColumns, function ($col) use ($ordering) {
				return (isset($ordering[0]) && $col->name == $ordering[0]);
			});

			if (empty($defaultOrdering) && isset($sortableColumns[0]))
			{
				$fullOrdering = $sortableColumns[0]->name . ' ' . $sortableColumns[0]->sortorder;

				$filterForm->setValue('fullordering', 'list', $fullOrdering);
			}
		}

		if (!empty(trim($fullOrdering)))
		{
			$fullOrdering = explode(' ', trim($fullOrdering));

			if (isset($fullOrdering[1]))
			{
				$ordering = $fullOrdering[0];
				$direction = $fullOrdering[1];
				$this->reportHandler->setOrdering($ordering, $direction);
			}
		}
		else if (empty($defaultOrdering))
		{
			// no ordering or direction
			$this->reportHandler->setOrdering('', '');
		}

		$total = $this->reportHandler->getTotal();

		return $total;
	}
}

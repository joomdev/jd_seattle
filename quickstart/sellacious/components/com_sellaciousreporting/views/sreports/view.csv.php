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

/**
 * View class for a list of reports.
 *
 * @since  1.6.0
 */
class SellaciousreportingViewSreports extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'sreport';

	/** @var  string */
	protected $view_item = 'sreport';

	/** @var  string */
	protected $view_list = 'sreports';

	/** @var  string */
	protected $handler;

	/** @var  int */
	protected $reportId;

	public function display($tpl = null)
	{
		/** @var  SellaciousreportingViewSreports $this*/
		$model = $this->getModel();

		/** @var  \SellaciousHelper $this*/
		$helper = SellaciousHelper::getInstance();

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->handler = $model->getHandler();

		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;

		$this->reportId  = $input->get('id', 0, 'uint');
		$exportAll = $input->get('export_all', 1, 'int');

		$filename = strtolower($this->handler->getName()) . "_report_" . date("Y-m-d_H-i", time());

		if ($exportAll)
		{
			$this->handler->setUserFilter(array());

			$list = $this->handler->getList();
		}
		else
		{
			$activeFilters = $model->getActiveFilters();
			$userFilters = $this->handler->getUserFilter();
			$userFilters = array_merge($userFilters, $activeFilters);

			$this->handler->setUserFilter($userFilters);

			$list = $this->handler->getList();
		}

		$csvData     = array();
		$colTitleArray = array();

		$columns = $this->handler->getColumns();

		foreach ($columns as $column)
		{
			$colTitleArray[] = $column->title;
		}

		$csvData[] = $colTitleArray;

		foreach ((array) $list as $item)
		{
			$itemCSV = array();

			foreach ($item as $value)
			{
				$itemCSV[] = (string) $value;
			}

			$csvData[] = $itemCSV;
		}

		// Set CSV headers
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=" . htmlspecialchars($filename) . ".csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $helper->core->array2csv($csvData);
		jexit();
	}
}

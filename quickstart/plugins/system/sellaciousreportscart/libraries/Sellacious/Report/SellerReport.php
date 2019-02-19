<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Report;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// no direct access
defined('_JEXEC') or die;

/**
 * Report handler for seller report
 *
 * @package   Sellacious\Report
 *
 * @since   1.6.0
 */
class SellerReport extends ReportHandler
{
	/**
	 * Constructor
	 *
	 * @since   1.6.0
	 */
	public function __construct()
	{
		$this->manifestPath = JPATH_PLUGINS . '/system/sellaciousreportscart/manifests/seller-report.xml';

		parent::__construct();
	}

	/**
	 * Get Report List Items
	 *
	 * @param   int $start Starting index of the items
	 * @param   int $limit Number of records to show
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function getList($start = 0, $limit = 0)
	{
		// Create query
		$query = $this->getListQuery();

		// Get List
		$this->db->setQuery($query, $start, $limit);

		$list = $this->db->loadObjectList();

		$this->processData($list);

		return $list;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6.0
	 */
	public function getListQuery()
	{
		$db = $this->db;
		$query = $db->getQuery(true);
		$date  = \JFactory::getDate();

		//Report Filters
		$reportFilters = $this->getFilter();

		//User Filters
		$userFilters = $this->getUserFilter();

		//Selected columns
		$columns = $this->getColumns();

		$exchangedStatuses = !empty($reportFilters['exchanged_statuses']) ? $reportFilters['exchanged_statuses'] : array('exchanged', 'exchanged_placed');
		$completedStatuses = !empty($reportFilters['completed_statuses']) ? $reportFilters['completed_statuses'] : array('approved', 'completed', 'delivered', 'shipped');
		$cancelledStatuses = !empty($reportFilters['cancelled_statuses']) ? $reportFilters['cancelled_statuses'] : array('cancellation');
		$lifetimeStatuses = !empty($reportFilters['lifetime_statuses']) ? $reportFilters['lifetime_statuses'] : array('approved', 'completed', 'delivered', 'shipped');

		//Subquery for commission
		$commSubQuery = $db->getQuery(true);
		$commSubQuery->select('SUM(g.amount) ');
		$commSubQuery->from($db->quoteName('#__sellacious_transactions', 'g'));
		$commSubQuery->where('g.reason = ' . $db->quote('order.item.sales_commission'));
		$commSubQuery->where('g.crdr = ' . $db->q('dr'));
		$commSubQuery->where('FIND_IN_SET(g.order_id, GROUP_CONCAT(DISTINCT a.id))');
		$commSubQuery->where('g.user_id = c.user_id');

		//subquery for completed orders
		$completedSubQuery = $db->getQuery(true);
		$completedSubQuery->select('COUNT(DISTINCT h.id)');
		$completedSubQuery->from($db->quoteName('#__sellacious_orders', 'h'));
		$completedSubQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'i') . ' ON (' . $db->quoteName('i.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$completedSubQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'j') . ' ON (' . $db->quoteName('j.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$completedSubQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'k') . ' ON (' . $db->quoteName('k.id') . ' = ' . $db->quoteName('j.status') . ')');
		$completedSubQuery->where('i.seller_uid = b.seller_uid');
		$completedSubQuery->where('j.state = 1');
		$completedSubQuery->where('k.type IN (' . implode(',' , $db->quote($completedStatuses)) . ')');

		//subquery for completed orders value
		$cSubQuery = $db->getQuery(true);
		$cSubQuery->select('DISTINCT h.id, (i.basic_price * i.quantity) as basic_price, i.seller_uid');
		$cSubQuery->from($db->quoteName('#__sellacious_orders', 'h'));
		$cSubQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'i') . ' ON (' . $db->quoteName('i.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$cSubQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'j') . ' ON (' . $db->quoteName('j.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$cSubQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'k') . ' ON (' . $db->quoteName('k.id') . ' = ' . $db->quoteName('j.status') . ')');
		$cSubQuery->where('j.state = 1');
		$cSubQuery->where('k.type IN (' . implode(',' , $db->quote($completedStatuses)) . ')');

		$completedValSubQuery = $db->getQuery(true);
		$completedValSubQuery->select('ROUND(COALESCE (SUM(a.basic_price), 0))');
		$completedValSubQuery->from('(' . $cSubQuery->__tostring() . ') a');
		$completedValSubQuery->where('a.seller_uid = b.seller_uid');

		//subquery for cancelled orders
		$cancelledSubQuery = $db->getQuery(true);
		$cancelledSubQuery->select('COUNT(DISTINCT h.id)');
		$cancelledSubQuery->from($db->quoteName('#__sellacious_orders', 'h'));
		$cancelledSubQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'i') . ' ON (' . $db->quoteName('i.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$cancelledSubQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'j') . ' ON (' . $db->quoteName('j.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$cancelledSubQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'k') . ' ON (' . $db->quoteName('k.id') . ' = ' . $db->quoteName('j.status') . ')');
		$cancelledSubQuery->where('i.seller_uid = b.seller_uid');
		$cancelledSubQuery->where('j.state = 1');
		$cancelledSubQuery->where('k.type IN (' . implode(',' , $db->quote($cancelledStatuses)) . ')');

		//subquery for cancelled orders value
		$cnclSubQuery = $db->getQuery(true);
		$cnclSubQuery->select('DISTINCT h.id, (i.basic_price * i.quantity) as basic_price, i.seller_uid');
		$cnclSubQuery->from($db->quoteName('#__sellacious_orders', 'h'));
		$cnclSubQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'i') . ' ON (' . $db->quoteName('i.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$cnclSubQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'j') . ' ON (' . $db->quoteName('j.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$cnclSubQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'k') . ' ON (' . $db->quoteName('k.id') . ' = ' . $db->quoteName('j.status') . ')');
		$cnclSubQuery->where('j.state = 1');
		$cnclSubQuery->where('k.type IN (' . implode(',' , $db->quote($cancelledStatuses)) . ')');

		$cancelledValSubQuery = $db->getQuery(true);
		$cancelledValSubQuery->select('ROUND(COALESCE (SUM(a.basic_price), 0))');
		$cancelledValSubQuery->from('(' . $cnclSubQuery->__tostring() . ') a');
		$cancelledValSubQuery->where('a.seller_uid = b.seller_uid');

		//subquery for total exchanged orders
		$exchangedSubQuery = $db->getQuery(true);
		$exchangedSubQuery->select('COUNT(DISTINCT h.id)');
		$exchangedSubQuery->from($db->quoteName('#__sellacious_orders', 'h'));
		$exchangedSubQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'i') . ' ON (' . $db->quoteName('i.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$exchangedSubQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'j') . ' ON (' . $db->quoteName('j.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$exchangedSubQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'k') . ' ON (' . $db->quoteName('k.id') . ' = ' . $db->quoteName('j.status') . ')');
		$exchangedSubQuery->where('i.seller_uid = b.seller_uid');
		$exchangedSubQuery->where('j.state = 1');
		$exchangedSubQuery->where('k.type IN (' . implode(',' , $db->quote($exchangedStatuses)) . ')');

		//subquery for exchanged orders value
		$eSubQuery = $db->getQuery(true);
		$eSubQuery->select('DISTINCT h.id, (i.basic_price * i.quantity) as basic_price, i.seller_uid');
		$eSubQuery->from($db->quoteName('#__sellacious_orders', 'h'));
		$eSubQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'i') . ' ON (' . $db->quoteName('i.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$eSubQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'j') . ' ON (' . $db->quoteName('j.order_id') . ' = ' . $db->quoteName('h.id') . ')');
		$eSubQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'k') . ' ON (' . $db->quoteName('k.id') . ' = ' . $db->quoteName('j.status') . ')');
		$eSubQuery->where('j.state = 1');
		$eSubQuery->where('k.type IN (' . implode(',' , $db->quote($exchangedStatuses)) . ')');

		$exchangedValSubQuery = $db->getQuery(true);
		$exchangedValSubQuery->select('ROUND(COALESCE (SUM(a.basic_price), 0))');
		$exchangedValSubQuery->from('(' . $eSubQuery->__tostring() . ') a');
		$exchangedValSubQuery->where('a.seller_uid = b.seller_uid');

		//Exact values to select for each column
		$queryColumns = array(
			"seller_name"               => 'c.store_name as seller_name',
			"company_name"              => 'c.title as company_name',
			"total_products"            => 'COUNT(DISTINCT b.item_uid) as total_products',
			"total_sales"               => 'COUNT(DISTINCT a.id) as total_sales',
			"total_sales_value"         => 'ROUND(SUM(b.basic_price * b.quantity)) as total_sales_value',
			"total_commission"          => '(' . $commSubQuery->__toString() . ') as total_commission',
			"total_completed_orders"    => '(' . $completedSubQuery->__tostring() . ') as total_completed_orders',
			"total_completed_value"     => '(' . $completedValSubQuery->__tostring() . ') as total_completed_value',
			"total_cancelled_orders"    => '(' . $cancelledSubQuery->__toString() . ') as total_cancelled_orders',
			"total_cancelled_value"     => '(' . $cancelledValSubQuery->__toString() . ') as total_cancelled_value',
			"total_exchanged_orders"    => '(' . $exchangedSubQuery->__toString() . ') as total_exchanged_orders',
			"total_exchanged_value"     => '(' . $exchangedValSubQuery->__tostring() . ') as total_exchanged_value',
		);

		$processedColumns = $this->processColumns($columns, $queryColumns);
		$selectedColumns = array_values($processedColumns);

		$query->from($db->quoteName('#__sellacious_orders', 'a'));
		$query->join('INNER', $db->quoteName('#__sellacious_order_items', 'b') . ' ON (' . $db->quoteName('b.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_sellers', 'c') . ' ON (' . $db->quoteName('c.user_id') . ' = ' . $db->quoteName('b.seller_uid') . ')');
		$query->join('INNER', $db->quoteName('#__users', 'd') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('c.user_id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_order_status', 'e') . ' ON (' . $db->quoteName('e.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_statuses', 'f') . ' ON (' . $db->quoteName('f.id') . ' = ' . $db->quoteName('e.status') . ')');

		$query->where('e.state = 1');

		//Applying report filters
		if (isset($reportFilters['seller_categories']) && !empty($reportFilters['seller_categories']))
		{
			$query->where('c.category_id IN (' . implode(',', $reportFilters['seller_categories']) .')');
		}

		if (isset($reportFilters['seller_registered']) && !empty($reportFilters['seller_registered']))
		{
			$registered = (int) $reportFilters['seller_registered'];
			$query->where('NOW() < DATE_ADD(d.registerDate, INTERVAL ' . $registered . ' DAY)');
		}

		if (isset($reportFilters['last_sales_made']) && !empty($reportFilters['last_sales_made']))
		{
			$query->where('HOUR(TIMEDIFF(NOW(), a.created)) / 24 <= ' . (int) $reportFilters['last_sales_made']);
		}

		if (!empty($lifetimeStatuses))
		{
			$query->where('f.type IN (' . implode(',' , $db->quote($lifetimeStatuses)) . ')');
		}

		$query->select($selectedColumns);

		$query->group('b.seller_uid');

		$subQuery = $db->getQuery(true);
		$subQuery->select($db->quoteName(array_keys(($processedColumns))));
		$subQuery->from('(' . $query->__toString() . ') a');

		//Applying user search filters
		if (isset($userFilters['search']) && !empty($userFilters['search']))
		{
			$search = $db->q('%' . $db->escape($userFilters['search'], true) . '%', false);
			$where = array();

			foreach (array_keys($processedColumns) as $processedColumn)
			{
				$where[] = 'a.' . $processedColumn . ' LIKE ' . $search;
			}

			$subQuery->where('(' . implode(' OR ', $where) . ')');
		}

		$ordering = $this->getOrdering();

		if (!empty(trim($ordering)))
		{
			$subQuery->order($ordering);
		}

		return $subQuery;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6.0
	 */
	public function getSummaryQuery()
	{
		$db = $this->db;
		$query = $db->getQuery(true);
		$date  = \JFactory::getDate();

		//Report Filters
		$reportFilters = $this->getFilter();

		$lifetimeStatuses = !empty($reportFilters['lifetime_statuses']) ? $reportFilters['lifetime_statuses'] : array('approved', 'completed', 'delivered', 'shipped');

		$query->select('COUNT(DISTINCT a.id) as total_order_no, ROUND(SUM(b.basic_price * b.quantity)) as total_sale_value');
		$query->from($db->quoteName('#__sellacious_orders', 'a'));
		$query->join('INNER', $db->quoteName('#__sellacious_order_items', 'b') . ' ON (' . $db->quoteName('b.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_sellers', 'c') . ' ON (' . $db->quoteName('c.user_id') . ' = ' . $db->quoteName('b.seller_uid') . ')');
		$query->join('INNER', $db->quoteName('#__users', 'd') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('c.user_id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_order_status', 'e') . ' ON (' . $db->quoteName('e.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_statuses', 'f') . ' ON (' . $db->quoteName('f.id') . ' = ' . $db->quoteName('e.status') . ')');

		$query->where('e.state = 1');

		//Applying report filters
		if (isset($reportFilters['seller_categories']) && !empty($reportFilters['seller_categories']))
		{
			$query->where('c.category_id IN (' . implode(',', $reportFilters['seller_categories']) .')');
		}

		if (isset($reportFilters['seller_registered']) && !empty($reportFilters['seller_registered']))
		{
			$registered = (int) $reportFilters['seller_registered'];
			$query->where('NOW() < DATE_ADD(d.registerDate, INTERVAL ' . $registered . ' DAY)');
		}

		if (isset($reportFilters['last_sales_made']) && !empty($reportFilters['last_sales_made']))
		{
			$query->where('HOUR(TIMEDIFF(NOW(), a.created)) / 24 <= ' . (int) $reportFilters['last_sales_made']);
		}

		if (!empty($lifetimeStatuses))
		{
			$query->where('f.type IN (' . implode(',' , $db->quote($lifetimeStatuses)) . ')');
		}

		return $query;
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
		$db = $this->db;

		// Create query
		$query = $this->getSummaryQuery();
		$db->setQuery($query);
		$summary = $db->loadAssoc();

		parent::setSummary($summary);
	}

	/**
	 * Method to process report summary.
	 *
	 * @param   array  $items Report data
	 *
	 * @return  null
	 *
	 * @since   1.6.0
	 */
	public function processSummary(&$items)
	{
		// nothing to do here for now
	}

	/**
	 * Method to get report data.
	 *
	 * @param   \stdClass[]     $selectedColumns    Selected Report Columns
	 * @param   array           $queryColumns       Columns for Query
	 *
	 * @return  mixed   Report data.
	 *
	 * @since   1.6.0
	 */
	public function processColumns($selectedColumns, $queryColumns)
	{
		$columns = array();

		foreach ($selectedColumns as $column)
		{
			$queryColNames = array_keys($queryColumns);

			if (in_array($column->name, $queryColNames))
			{
				$columns[$column->name] = $queryColumns[$column->name];
			}
		}

		return $columns;
	}

	/**
	 * Method to process report data.
	 *
	 * @param   array  $items Report data
	 *
	 * @return  null
	 *
	 * @since   1.6.0
	 */
	public function processData(&$items)
	{
		// Nothing to do here for now
	}

	/**
	 * Get total records
	 *
	 * @return  int
	 *
	 * @since   1.6.0
	 */
	public function getTotal()
	{
		if (!$this->total)
		{
			$query = $this->getListQuery();
			$this->db->setQuery($query);
			$list = $this->db->loadObjectList();

			$this->processData($list);

			$this->total = count($list);
		}

		return $this->total;
	}
}

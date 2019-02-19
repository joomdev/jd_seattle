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
 * Report handler for cart reports
 *
 * @package   Sellacious\Report
 *
 * @since     1.6.0
 */
class CartReport extends ReportHandler
{
	/**
	 * Constructor
	 *
	 * @since   1.6.0
	 */
	public function __construct()
	{
		$this->manifestPath = JPATH_PLUGINS . '/system/sellaciousreportscart/manifests/cart-report.xml';

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
		$this->db->setQuery($query);

		$list = $this->db->loadObjectList();

		$this->processData($list);

		$list = array_slice($list, $start, $limit);

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
		$db    = $this->db;
		$query = $db->getQuery(true);
		$date  = \JFactory::getDate();

		//Report Filters
		$reportFilters = $this->getFilter();

		//User Filters
		$userFilters = $this->getUserFilter();

		//Selected columns
		$columns = $this->getColumns();

		$abandoned = isset($reportFilters['days_after_cart_abandoned']) ? $reportFilters['days_after_cart_abandoned'] : 30;

		//Exact values to select for each column
		$queryColumns = array(
			"user_email"        => 'c.email as user_email',
			"user_id"           => '(CASE WHEN a.user_id > 0 THEN a.user_id ELSE ' . $db->quote(\JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_GUEST_USER")) . ' END) as user_id',
			"total_products"    => 'COUNT(DISTINCT b.uid) as total_products',
			"billing"           => '(CASE WHEN a.billing > 0 THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) as billing',
			"shipping"          => '(CASE WHEN a.shipping > 0 THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) as shipping',
			"checkout_answered" => 'null as checkout_answered',
			"shipment_selected" => 'null as shipment_selected',
			"payment_status"    => 'null as payment_status',
			"cart_total"        => '0.00 as cart_total',
			"last_modified"     => 'a.modified as last_modified',
			"time_since"        => 'TIMESTAMPDIFF(MICROSECOND, a.created, NOW()) as time_since',
			"abandoned_cart"    => '(CASE WHEN a.modified = \'0000-00-00 00:00:00\' THEN \'No\' WHEN FLOOR(HOUR(TIMEDIFF(NOW(), a.modified)) / 24) > ' . $abandoned . ' THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) AS abandoned_cart',
		);

		$selectedColumns   = array_values($this->processColumns($columns, $queryColumns));
		$selectedColumns[] = 'a.id';
		$selectedColumns[] = 'a.cart_token';
		$selectedColumns[] = 'a.params as cart_info_params';
		$selectedColumns[] = 'CONCAT(FLOOR(HOUR(TIMEDIFF(NOW(), a.created)) / 24), \' days, \',MOD(HOUR(TIMEDIFF(NOW(),a.created)), 24), \' hours\') AS TimeDiff';
		$selectedColumns[] = 'a.user_id as cart_user_id';
		$selectedColumns[] = 'null as order_number';

		$query->from($db->quoteName('#__sellacious_cart_info', 'a'));
		$query->join('INNER', $db->quoteName('#__sellacious_cart', 'b') . ' ON (' . $db->quoteName('b.token') . ' = ' . $db->quoteName('a.cart_token') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.user_id') . ')');

		$query->where('b.state = 1');
		$query->where($db->quote($date->toSql()) . ' > DATE_ADD(' . $db->quoteName('a.created') . ', INTERVAL 1 DAY)'); // Condition: If it has been more than one day for the cart

		//Applying report filters
		if (isset($reportFilters['total_product']) && !empty($reportFilters['total_product']))
		{
			$query->having('COUNT(b.uid) >= ' . $reportFilters['total_product']);
		}

		if ((isset($reportFilters['product_categories']) && !empty($reportFilters['product_categories'])) || (isset($reportFilters['product_types']) && !empty($reportFilters['product_types'])))
		{
			array_push($selectedColumns, "GROUP_CONCAT(b.uid) as product_ids");
		}

		if (isset($reportFilters['last_modified_in_days']) && !empty($reportFilters['last_modified_in_days']))
		{
			$modified = (int) $reportFilters['last_modified_in_days'];
			$query->where('NOW() < DATE_ADD(a.modified, INTERVAL ' . $modified . ' DAY)');
		}

		if (isset($reportFilters['abandoned_only']) && !empty($reportFilters['abandoned_only']))
		{
			$query->having('abandoned_cart = ' . $db->quote(\JText::_("JYES")));
		}

		//Applying user filters
		if (isset($userFilters['search']) && !empty($userFilters['search']))
		{
			$query->where('c.email LIKE ' . $db->quote('%' . $userFilters['search'] . '%'));
		}

		if (isset($userFilters['billing']) && !empty($userFilters['billing']))
		{
			if ($userFilters['billing'] == 'Yes')
			{
				$query->where('a.billing > 0');
			}
			else if ($userFilters['billing'] == 'No')
			{
				$query->where('a.billing = 0');
			}
		}


		if (isset($userFilters['shipping']) && !empty($userFilters['shipping']))
		{
			if ($userFilters['shipping'] == 'Yes')
			{
				$query->where('a.shipping > 0');
			}
			else if ($userFilters['shipping'] == 'No')
			{
				$query->where('a.shipping = 0');
			}
		}

		$query->select($selectedColumns);

		$query->group('a.cart_token');

		$ordersQuery = $this->getOrdersQuery();

		$query->union($ordersQuery);

		$ordering = $this->getOrdering();

		if (!empty(trim($ordering)))
		{
			$query->order($ordering);
		}

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6.0
	 */
	public function getOrdersQuery()
	{
		$db   = $this->db;
		$date = \JFactory::getDate();

		/* Create query instance by calling parents getListQuery */
		$query = $db->getQuery(true);

		//Report Filters
		$reportFilters = $this->getFilter();

		//User Filters
		$userFilters = $this->getUserFilter();

		//Selected columns
		$columns = $this->getColumns();

		$abandoned = isset($reportFilters['days_after_cart_abandoned']) ? $reportFilters['days_after_cart_abandoned'] : 30;

		$queryColumns = array(
			"user_email"        => 'a.customer_email as user_email',
			"user_id"           => '(CASE WHEN a.customer_uid > 0 THEN a.customer_uid ELSE ' . $db->quote(\JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_GUEST_USER")) . ' END) as user_id',
			"total_products"    => 'COUNT(DISTINCT c.id) as total_products',
			"billing"           => '(CASE WHEN a.bt_name != "" THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) as billing',
			"shipping"          => '(CASE WHEN a.st_name != "" THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) as shipping',
			"checkout_answered" => '(CASE WHEN a.checkout_forms != "null" THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) as checkout_answered',
			"shipment_selected" => '(CASE WHEN a.shipping_rule_id > 0 THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE (CASE WHEN c.shipping_rule_id > 0 THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) END) as shipment_selected',
			"payment_status"    => 'd.type as payment_status',
			"cart_total"        => 'a.cart_total',
			"last_modified"     => 'a.modified as last_modified',
			"time_since"        => 'null as time_since',
			"abandoned_cart"    => '(CASE WHEN a.modified = \'0000-00-00 00:00:00\' THEN \'No\' WHEN FLOOR(HOUR(TIMEDIFF(NOW(), a.modified)) / 24) > ' . $abandoned . ' THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) AS abandoned_cart',
		);

		$selectedColumns = array_values($this->processColumns($columns, $queryColumns));

		$selectedColumns[] = 'a.id';
		$selectedColumns[] = 'null as cart_token';
		$selectedColumns[] = 'null as cart_info_params';
		$selectedColumns[] = 'CONCAT(FLOOR(HOUR(TIMEDIFF(NOW(), a.created)) / 24), \' days, \',MOD(HOUR(TIMEDIFF(NOW(),a.created)), 24), \' hours\') AS TimeDiff';
		$selectedColumns[] = 'a.customer_uid as cart_user_id';
		$selectedColumns[] = 'a.order_number';

		$query->from($db->quoteName('#__sellacious_orders', 'a'));
		$query->join('INNER', $db->quoteName('#__sellacious_order_status', 'b') . ' ON (' . $db->quoteName('b.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_order_items', 'c') . ' ON (' . $db->quoteName('c.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_statuses', 'd') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('b.status') . ')');

		$query->where('b.state = 1');

		//Applying report filters
		if (isset($reportFilters['total_product']) && !empty($reportFilters['total_product']))
		{
			$query->having('COUNT(c.id) >= ' . $reportFilters['total_product']);
		}

		if (isset($reportFilters['payment_status_type']) && !empty($reportFilters['payment_status_type']))
		{
			$query->where('d.type IN (' . implode(',', $db->quote($reportFilters['payment_status_type'])) . ')');
		}

		if ((isset($reportFilters['product_categories']) && !empty($reportFilters['product_categories'])) || (isset($reportFilters['product_types']) && !empty($reportFilters['product_types'])))
		{
			array_push($selectedColumns, "GROUP_CONCAT(c.item_uid) as product_ids");
		}

		if (isset($reportFilters['last_modified_in_days']) && !empty($reportFilters['last_modified_in_days']))
		{
			$modified = (int) $reportFilters['last_modified_in_days'];
			$query->where('NOW() < DATE_ADD(a.modified, INTERVAL ' . $modified . ' DAY)');
		}

		if (isset($reportFilters['abandoned_only']) && !empty($reportFilters['abandoned_only']))
		{
			$query->having('abandoned_cart = ' . $db->quote(\JText::_("JYES")));
		}

		//Applying user filters
		if (isset($userFilters['search']) && !empty($userFilters['search']))
		{
			$query->where('a.customer_email LIKE ' . $db->quote('%' . $userFilters['search'] . '%'));
		}

		if (isset($userFilters['billing']) && !empty($userFilters['billing']))
		{
			if ($userFilters['billing'] == 'Yes')
			{
				$query->where('a.bt_name != ""');
			}
			else if ($userFilters['billing'] == 'No')
			{
				$query->where('a.bt_name = ""');
			}
		}

		if (isset($userFilters['shipping']) && !empty($userFilters['shipping']))
		{
			if ($userFilters['shipping'] == 'Yes')
			{
				$query->where('a.st_name != ""');
			}
			else if ($userFilters['shipping'] == 'No')
			{
				$query->where('a.st_name = ""');
			}
		}

		$query->select($selectedColumns);

		$query->group('a.id');

		return $query;
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
		$db    = $this->db;
		$query = $db->getQuery(true);
		$date  = \JFactory::getDate();

		//Report Filters
		$reportFilters = $this->getFilter();

		$abandoned = isset($reportFilters['days_after_cart_abandoned']) ? $reportFilters['days_after_cart_abandoned'] : 30;

		$query->select('DISTINCT a.id as cart_id, a.user_id as cart_user_id, null as cart_total, a.cart_token');
		$query->from($db->quoteName('#__sellacious_cart_info', 'a'));
		$query->join('INNER', $db->quoteName('#__sellacious_cart', 'b') . ' ON (' . $db->quoteName('b.token') . ' = ' . $db->quoteName('a.cart_token') . ')');
		$query->join('LEFT', $db->quoteName('#__users', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.user_id') . ')');

		$query->where('b.state = 1');
		$query->where($db->quote($date->toSql()) . ' > DATE_ADD(' . $db->quoteName('a.created') . ', INTERVAL 1 DAY)'); // Condition: If it has been more than one day for the cart

		//Applying report filters
		if (isset($reportFilters['total_product']) && !empty($reportFilters['total_product']))
		{
			$query->having('COUNT(b.uid) >= ' . $reportFilters['total_product']);
		}

		if ((isset($reportFilters['product_categories']) && !empty($reportFilters['product_categories'])) || (isset($reportFilters['product_types']) && !empty($reportFilters['product_types'])))
		{
			$query->select("GROUP_CONCAT(b.uid) as product_ids");
		}

		if (isset($reportFilters['last_modified_in_days']) && !empty($reportFilters['last_modified_in_days']))
		{
			$modified = (int) $reportFilters['last_modified_in_days'];
			$query->where('NOW() < DATE_ADD(a.modified, INTERVAL ' . $modified . ' DAY)');
		}

		if (isset($reportFilters['abandoned_only']) && !empty($reportFilters['abandoned_only']))
		{
			$query->having('abandoned_cart = ' . $db->quote(\JText::_("JYES")));
		}

		$query->select('(CASE WHEN a.modified = \'0000-00-00 00:00:00\' THEN \'No\' WHEN FLOOR(HOUR(TIMEDIFF(NOW(), a.modified)) / 24) > ' . $abandoned . ' THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) AS abandoned_cart');

		$query->group('a.cart_token');

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6.0
	 */
	public function getSummaryOrdersQuery()
	{
		// Orders query
		$db          = $this->db;
		$ordersQuery = $db->getQuery(true);
		$date        = \JFactory::getDate();

		//Report Filters
		$reportFilters = $this->getFilter();

		$abandoned = isset($reportFilters['days_after_cart_abandoned']) ? $reportFilters['days_after_cart_abandoned'] : 30;

		$ordersQuery->select('DISTINCT a.id as cart_id, a.customer_uid as cart_user_id, a.cart_total, null as cart_token');
		$ordersQuery->from($db->quoteName('#__sellacious_orders', 'a'));
		$ordersQuery->join('INNER', $db->quoteName('#__sellacious_order_status', 'b') . ' ON (' . $db->quoteName('b.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$ordersQuery->join('INNER', $db->quoteName('#__sellacious_order_items', 'c') . ' ON (' . $db->quoteName('c.order_id') . ' = ' . $db->quoteName('a.id') . ')');
		$ordersQuery->join('INNER', $db->quoteName('#__sellacious_statuses', 'd') . ' ON (' . $db->quoteName('d.id') . ' = ' . $db->quoteName('b.status') . ')');

		$ordersQuery->where('b.state = 1');

		//Applying report filters
		if (isset($reportFilters['total_product']) && !empty($reportFilters['total_product']))
		{
			$ordersQuery->having('COUNT(c.id) >= ' . $reportFilters['total_product']);
		}

		if (isset($reportFilters['payment_status_type']) && !empty($reportFilters['payment_status_type']))
		{
			$ordersQuery->where('d.type IN (' . implode(',', $db->quote($reportFilters['payment_status_type'])) . ')');
		}

		if ((isset($reportFilters['product_categories']) && !empty($reportFilters['product_categories'])) || (isset($reportFilters['product_types']) && !empty($reportFilters['product_types'])))
		{
			$ordersQuery->select("GROUP_CONCAT(c.item_uid) as product_ids");
		}

		if (isset($reportFilters['last_modified_in_days']) && !empty($reportFilters['last_modified_in_days']))
		{
			$modified = (int) $reportFilters['last_modified_in_days'];
			$ordersQuery->where('NOW() < DATE_ADD(a.modified, INTERVAL ' . $modified . ' DAY)');
		}

		if (isset($reportFilters['abandoned_only']) && !empty($reportFilters['abandoned_only']))
		{
			$ordersQuery->having('abandoned_cart = ' . $db->quote(\JText::_("JYES")));
		}

		$ordersQuery->select('(CASE WHEN a.modified = \'0000-00-00 00:00:00\' THEN \'No\' WHEN FLOOR(HOUR(TIMEDIFF(NOW(), a.modified)) / 24) > ' . $abandoned . ' THEN ' . $db->quote(\JText::_("JYES")) . ' ELSE ' . $db->quote(\JText::_("JNO")) . ' END) AS abandoned_cart');

		$ordersQuery->group('a.id');

		return $ordersQuery;
	}

	/**
	 * Set report summary
	 *
	 * @param   array $summary
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setSummary($summary = array())
	{
		$db      = $this->db;
		$summary = array();

		// Create query
		$query       = $this->getSummaryQuery();
		$ordersQuery = $this->getSummaryOrdersQuery();

		// Get Total Orders
		$db->setQuery($ordersQuery);
		$orders = $db->loadObjectList();
		$this->processSummary($orders);

		$totalOrders = count($orders);

		// Union of both queries
		$query->union($ordersQuery);
		$db->setQuery($query);

		$summaryItems = $db->loadObjectList();
		$this->processSummary($summaryItems);

		$cartTotals     = ArrayHelper::getColumn($summaryItems, 'cart_total');
		$totalCartValue = 0;

		foreach ($cartTotals as $total)
		{
			$totalCartValue += (int) str_replace(',', '', $total);
		}

		$abandoned = array_filter($summaryItems, function ($item) {
			return isset($item->abandoned_cart) ? ($item->abandoned_cart == \JText::_('JYES') ? true : false) : false;
		});

		$summary['total_cart']         = count($summaryItems);
		$summary['total_cart_value']   = number_format($totalCartValue, 2);
		$summary['average_cart_value'] = $summary['total_cart'] ? number_format(($totalCartValue / $summary['total_cart']), 2) : 0;
		$summary['conversion_rate']    = $summary['total_cart'] ? round(($totalOrders / $summary['total_cart']) * 100, 2) : 0;
		$summary['abandoned_cart']     = count($abandoned);

		parent::setSummary($summary);
	}

	/**
	 * Method to process report summary.
	 *
	 * @param   array $items Report data
	 *
	 * @return  null
	 *
	 * @since   1.6.0
	 */
	public function processSummary(&$items)
	{
		$db = $this->db;

		//Report Filters
		$reportFilters     = $this->getFilter();
		$productCategories = isset($reportFilters['product_categories']) ? $reportFilters['product_categories'] : array();
		$categoryChildren  = array();

		foreach ($productCategories as $productCategory)
		{
			$categoryChildren = array_merge($categoryChildren, $this->helper->category->getChildren($productCategory, false));
		}

		$productCategories = array_merge($productCategories, $categoryChildren);

		if (!empty($items))
		{
			foreach ($items as $key => $item)
			{
				if (!empty($item->cart_user_id))
				{
					$grandTotal = (float) $item->cart_total;

					if (empty($grandTotal))
					{
						$cart       = $this->helper->cart->getCart($item->cart_user_id, array('token' => $item->cart_token));
						$totals     = $cart->getTotals();
						$grandTotal = (float) $totals->get("grand_total");
					}

					if (isset($reportFilters['cart_value']) && !empty($reportFilters['cart_value']) && $grandTotal < (float) $reportFilters['cart_value'])
					{
						unset($items[$key]);
						continue;
					}
					else
					{
						$items[$key]->cart_total = number_format($grandTotal, 2);

					}
				}

				if (isset($item->product_ids))
				{
					$productUids = explode(',', $item->product_ids);
					$productIds  = array();

					foreach ($productUids as $productUid)
					{
						$productId = 0;
						$this->helper->product->parseCode($productUid, $productId);
						$productIds[] = $productId;
					}

					if (!empty($productCategories))
					{
						$query = $db->getQuery(true);
						$query->select('a.product_id');
						$query->from('#__sellacious_product_categories a');
						$query->join('INNER', '#__sellacious_products b ON b.id = a.product_id');
						$query->where('a.product_id IN (' . (implode(',', $productIds)) . ')');
						$query->where('a.category_id IN (' . (implode(',', $productCategories)) . ')');

						$db->setQuery($query);

						$products = $db->loadObjectList();

						if (empty($products))
						{
							unset($items[$key]);
							continue;
						}
						else
						{
							if (property_exists($item, 'total_products'))
							{
								$items[$key]->total_products = count($products);
							}
						}
					}

					if (isset($reportFilters['product_types']) && !empty($reportFilters['product_types']))
					{
						$productTypes = $reportFilters['product_types'];

						$query = $db->getQuery(true);
						$query->select('a.id');
						$query->from('#__sellacious_products a');
						$query->where('a.type IN (' . implode(',', $db->quote($productTypes)) . ')');
						$query->where('a.id IN (' . (implode(',', $productIds)) . ')');

						$db->setQuery($query);

						$products = $db->loadObjectList();

						if (empty($products))
						{
							unset($items[$key]);
							continue;
						}
						else
						{
							if (property_exists($item, 'total_products'))
							{
								$items[$key]->total_products = count($products);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Method to get report data.
	 *
	 * @param   \stdClass[] $selectedColumns Selected Report Columns
	 * @param   array       $queryColumns    Columns for Query
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
	 * @param   array $items Report data
	 *
	 * @return  null
	 *
	 * @since   1.6.0
	 */
	public function processData(&$items)
	{
		$db = $this->db;

		//Report Filters
		$reportFilters     = $this->getFilter();
		$productCategories = isset($reportFilters['product_categories']) ? $reportFilters['product_categories'] : array();
		$categoryChildren  = array();

		foreach ($productCategories as $productCategory)
		{
			$categoryChildren = array_merge($categoryChildren, $this->helper->category->getChildren($productCategory, false));
		}

		$productCategories = array_merge($productCategories, $categoryChildren);

		if (!empty($items))
		{
			foreach ($items as $key => $item)
			{
				if (property_exists($item, 'user_email'))
				{
					$items[$key]->user_email = $item->user_email ?: \JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_NO_USER_EMAIL");
				}

				if (property_exists($item, 'checkout_answered'))
				{
					$items[$key]->checkout_answered = $item->checkout_answered != null ? $item->checkout_answered : \JText::_("JNO");
				}

				if (property_exists($item, 'shipment_selected'))
				{
					$items[$key]->shipment_selected = $item->shipment_selected != null ? $item->shipment_selected : \JText::_("JNO");
				}

				if (property_exists($item, 'payment_status'))
				{
					$items[$key]->payment_status = $item->payment_status ? \JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_PAYMENT_" . strtoupper($item->payment_status)) : \JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_NOT_APPLICABLE");
				}

				if (!empty($item->cart_info_params))
				{
					$cartInfoParams   = new Registry($item->cart_info_params);
					$checkoutFormData = $cartInfoParams->get('checkoutformdata', array());

					if (!empty($checkoutFormData))
					{
						$items[$key]->checkout_answered = \JText::_("JYES");
					}
					else
					{
						$items[$key]->checkout_answered = \JText::_("JNO");
					}

					// User email
					if (empty($item->user_email))
					{
						$guestCheckout      = $cartInfoParams->get("guest_checkout", false);
						$guestCheckoutEmail = $cartInfoParams->get("guest_checkout_email", "");

						if ($guestCheckout && !empty($guestCheckoutEmail))
						{
							$items[$key]->user_email = $guestCheckoutEmail;
						}
						else
						{
							$items[$key]->user_email = \JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_NO_USER_EMAIL");
						}
					}
				}

				if (!empty($item->cart_token) && property_exists($item, 'shipment_selected'))
				{
					if ($item->shipment_selected != null)
					{

						// get cart products
						$query = $db->getQuery(true);
						$query->select("a.uid, a.params");
						$query->from("#__sellacious_cart a");
						$query->where("a.state = 1 AND a.token = " . $db->quote($item->cart_token));

						$db->setQuery($query);

						$cartItems = $db->loadObjectList();

						if (!empty($cartItems))
						{
							foreach ($cartItems as $cartItem)
							{
								$cartParams = json_decode($cartItem->params);
								$cartParams = unserialize($cartParams->serialized);

								if ($cartParams->getShipQuoteId())
								{
									$items[$key]->shipment_selected = \JText::_("JYES");
								}
							}
						}
					}
					else
					{
						$items[$key]->shipment_selected = \JText::_("JNO");
					}
				}

				if (!empty($item->cart_user_id) && isset($item->cart_total))
				{
					$grandTotal = (float) $item->cart_total;

					if (empty($grandTotal))
					{
						$cart       = $this->helper->cart->getCart($item->cart_user_id, array('token' => $item->cart_token));
						$totals     = $cart->getTotals();
						$grandTotal = (float) $totals->get("grand_total");
					}

					if (isset($reportFilters['cart_value']) && !empty($reportFilters['cart_value']) && $grandTotal < (float) $reportFilters['cart_value'])
					{
						unset($items[$key]);
						continue;
					}
					else
					{
						$items[$key]->cart_total = number_format($grandTotal, 2);

					}
				}

				if (isset($item->last_modified) && !empty($item->last_modified))
				{
					if (!empty($item->order_number))
					{
						$items[$key]->last_modified = \JText::sprintf("PLG_SYSTEM_SELLACIOUSREPORTSCART_ORDER", $item->order_number);
					}
					else
					{
						if ($item->last_modified == '0000-00-00 00:00:00')
						{
							$items[$key]->last_modified = \JText::_("PLG_SYSTEM_SELLACIOUSREPORTSCART_NEVER");
						}
						else
						{
							$items[$key]->last_modified = \JHtml::_('date', $item->last_modified, 'M d, Y h:i A');
						}
					}
				}

				if (property_exists($item, 'time_since') && !empty($item->TimeDiff))
				{
					if (!empty($item->order_number))
					{
						$items[$key]->time_since = \JText::sprintf("PLG_SYSTEM_SELLACIOUSREPORTSCART_ORDER", $item->order_number);
					}
					else
					{
						if (!empty($item->time_since))
						{
							$items[$key]->time_since = \JText::_('PLG_SYSTEM_SELLACIOUSREPORTSCART_NEVER');
						}
						else
						{
							$items[$key]->time_since = $item->TimeDiff;
						}
					}
				}

				if (isset($item->product_ids))
				{
					$productUids = explode(',', $item->product_ids);
					$productIds  = array();

					foreach ($productUids as $productUid)
					{
						$productId = 0;
						$this->helper->product->parseCode($productUid, $productId);
						$productIds[] = $productId;
					}

					if (!empty($productCategories))
					{
						$query = $db->getQuery(true);
						$query->select('a.product_id');
						$query->from('#__sellacious_product_categories a');
						$query->join('INNER', '#__sellacious_products b ON b.id = a.product_id');
						$query->where('a.product_id IN (' . (implode(',', array_unique($productIds))) . ')');
						$query->where('a.category_id IN (' . (implode(',', $productCategories)) . ')');

						$db->setQuery($query);

						$products = $db->loadObjectList();

						if (empty($products))
						{
							unset($items[$key]);
							continue;
						}
						else
						{
							if (property_exists($item, 'total_products'))
							{
								$items[$key]->total_products = count($products);
							}
						}
					}

					if (isset($reportFilters['product_types']) && !empty($reportFilters['product_types']))
					{
						$productTypes = $reportFilters['product_types'];

						$query = $db->getQuery(true);
						$query->select('a.id');
						$query->from('#__sellacious_products a');
						$query->where('a.type IN (' . implode(',', $db->quote($productTypes)) . ')');
						$query->where('a.id IN (' . (implode(',', array_unique($productIds))) . ')');

						$db->setQuery($query);

						$products = $db->loadObjectList();

						if (empty($products))
						{
							unset($items[$key]);
							continue;
						}
						else
						{
							if (property_exists($item, 'total_products'))
							{
								$items[$key]->total_products = count($products);
							}
						}
					}
				}

				// Unset all columns that were just added for processing data
				unset($items[$key]->id);
				unset($items[$key]->cart_token);
				unset($items[$key]->cart_info_params);
				unset($items[$key]->TimeDiff);
				unset($items[$key]->cart_user_id);
				unset($items[$key]->product_ids);
				unset($items[$key]->order_number);
			}
		}
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

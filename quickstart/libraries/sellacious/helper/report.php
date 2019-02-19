<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Sellacious cart helper.
 *
 * @since  1.1.0
 */
class SellaciousHelperReport extends SellaciousHelperBase
{
	/**
	 * @var   bool
	 *
	 * @since  1.1.0
	 */
	protected $hasTable = false;

	/**
	 * Get orders count for last <var>$days</var> days
	 *
	 * @param   int     $days     Number of days to go in past
	 * @param   string  $from     The UTC date string from where to start
	 * @param   bool    $overall  Whether to get a stat for the entire lifetime as well.
	 *                            This will be the first element of the return array
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function getOrderStats($days = 15, $from = 'now', $overall = true)
	{
		$stats    = array();
		$me       = JFactory::getUser();
		$currency = $this->helper->currency->current('code_3');
		$now      = $this->helper->core->fixDate($from, 'UTC', null);

		$now->setTime(0, 0, 0);

		$t0 = $now->format('Y-m-d 00:00:00', true);
		$t1 = $now->format('Y-m-d 23:59:59', true);

		$today      = clone $now;
		$mid_night0 = $this->helper->core->fixDate($t0, null, 'UTC');
		$mid_night1 = $this->helper->core->fixDate($t1, null, 'UTC');

		$filterC = array(
			'list.join'   => array(
				array('inner', '#__sellacious_payments AS py ON py.context = ' . $this->db->q('order') . ' AND py.order_id = a.id AND py.state > 0'),
			),
			'list.group' => 'a.id'
		);
		$forex   = '(CASE WHEN a.currency = ' . $this->db->q($currency) . ' THEN 1.0 ELSE IFNULL(fx.x_factor, 0.0) END)';
		$filterA = array(
			'list.select' => "SUM(a.grand_total * $forex) AS order_total",
			'list.join'   => array(
				array('left', '#__sellacious_forex AS fx ON fx.x_from = a.currency AND fx.x_to = ' . $this->db->q($currency)),
				array('inner', '#__sellacious_payments AS py ON py.context = ' . $this->db->q('order') . ' AND py.order_id = a.id AND py.state > 0'),
			),
			'list.group' => 'a.id'
		);

		if ($this->helper->access->check('order.list'))
		{
			$where = array();
		}
		elseif ($this->helper->access->check('order.list.own'))
		{
			$where = array('oi.seller_uid = ' . $this->db->q($me->id));

			$filterA['list.join'][] = array('inner', '#__sellacious_order_items AS oi ON oi.order_id = a.id');
			$filterC['list.join'][] = array('inner', '#__sellacious_order_items AS oi ON oi.order_id = a.id');
		}
		else
		{
			$where = array('0');
		}

		// Get overall stats of the entire lifetime if requested
		if ($overall)
		{
			if (count($where))
			{
				$filterA['list.where'] = $where;
				$filterC['list.where'] = $where;
			}

			$values = $this->helper->order->loadColumn($filterA);
			$value  = $values ? array_sum($values) : 0;

			$stat         = new stdClass;
			$stat->range  = null;
			$stat->date   = $today->format('Y-m-d', true);
			$stat->ts     = $today->toUnix() + $today->getOffsetFromGmt();
			$stat->count  = $this->helper->order->count($filterC);
			$stat->value  = $this->helper->currency->convert($value, $currency, null);
			$stat->amount = $this->helper->currency->display($value, $currency, null, true, $stat->value >= 10000 ? 0 : 2);

			$stats[] = $stat;
		}

		// Get daily stats
		for ($i = 1; $i <= (int) $days; $i++)
		{
			$whereDt = array(
				'a.created >= ' . $this->db->q($mid_night0),
				'a.created <= ' . $this->db->q($mid_night1),
			);

			$filterC['list.where'] = array_merge($where, $whereDt);
			$filterA['list.where'] = array_merge($where, $whereDt);

			$values = $this->helper->order->loadColumn($filterA);
			$value  = $values ? array_sum($values) : 0;

			$stat         = new stdClass;
			$stat->range  = "$mid_night0 - $mid_night1";
			$stat->date   = $today->format('Y-m-d', true);
			$stat->ts     = $today->toUnix() + $today->getOffsetFromGmt();
			$stat->count  = $this->helper->order->count($filterC);
			$stat->value  = $this->helper->currency->convert($value, $currency, null);
			$stat->amount = $this->helper->currency->display($stat->value, $currency, null, true, $stat->value >= 10000 ? 0 : 2);

			$stats[] = $stat;

			// Go to previous day
			$today->modify('-1 day');
			$mid_night0->modify('-1 day');
			$mid_night1->modify('-1 day');
		}

		return $stats;
	}

	/**
	 * Get page views count for last <var>$days</var> days
	 *
	 * @param   int     $days     Number of days to go in past
	 * @param   string  $from     The UTC date string from where to start
	 * @param   bool    $overall  Whether to get a stat for the entire lifetime as well.
	 *                            This will be the first element of the return array
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function getPageViewStats($days = 15, $from = 'now', $overall = true)
	{
		$stats = array();
		$now   = $this->helper->core->fixDate($from, 'UTC', null);

		$now->setTime(0, 0, 0);

		$t0 = $now->format('Y-m-d 00:00:00', true);
		$t1 = $now->format('Y-m-d 23:59:59', true);

		$today      = clone $now;
		$mid_night0 = $this->helper->core->fixDate($t0, null, 'UTC');
		$mid_night1 = $this->helper->core->fixDate($t1, null, 'UTC');

		$filterA = array(
			'list.select' => 'SUM(a.hits) AS page_count',
			'list.from'   => '#__sellacious_utm_links',
		);
		$where   = $this->helper->access->check('statistics.visitor') ? array() : array('0');

		// Get overall stats of the entire lifetime if requested
		if ($overall)
		{
			if (count($where))
			{
				$filterA['list.where'] = $where;
			}

			$value = $this->loadResult($filterA);

			$stat         = new stdClass;
			$stat->range  = null;
			$stat->date   = $today->format('Y-m-d', true);
			$stat->ts     = $today->toUnix() + $today->getOffsetFromGmt();
			$stat->count  = $value;

			$stats[] = $stat;
		}

		// Get daily stats
		for ($i = 1; $i <= (int) $days; $i++)
		{
			$whereDt = array(
				'a.created >= ' . $this->db->q($mid_night0),
				'a.created <= ' . $this->db->q($mid_night1),
			);

			$filterA['list.where'] = array_merge($where, $whereDt);

			$value = $this->helper->order->loadResult($filterA);

			$stat         = new stdClass;
			$stat->range  = "$mid_night0 - $mid_night1";
			$stat->date   = $today->format('Y-m-d', true);
			$stat->ts     = $today->toUnix() + $today->getOffsetFromGmt();
			$stat->count  = $value;

			$stats[] = $stat;

			// Go to previous day
			$today->modify('-1 day');
			$mid_night0->modify('-1 day');
			$mid_night1->modify('-1 day');
		}

		return $stats;
	}

	/**
	 * Get page view count day wise for the given date interval
	 *
	 * @param   JDate  $start
	 * @param   JDate  $end
	 *
	 * @return  array
	 *
	 * @since   1.1.0
	 */
	public function getDailyPageViews($start, $end)
	{
		$count  = array();

		if (!$this->helper->access->check('core.admin'))
		{
			return $count;
		}

		$query  = $this->db->getQuery(true);
		$date   = JHtml::_('date', 'now', 'Y-m-d H:i:s');
		$utc    = JFactory::getDate('now')->format('Y-m-d H:i:s');
		$offset = strtotime($date) - strtotime($utc);

		$s_sql  = 'DATE_FORMAT(DATE_ADD(a.created, INTERVAL %s SECOND), %s)';
		$dt_key = sprintf($s_sql, intval($offset), $this->db->q('%Y%m%d'));

		$query->select('SUM(a.hits) AS page_count')
			->select("$dt_key AS ts_date")
			->select('UNIX_TIMESTAMP(' . $dt_key . ') * 1000 AS ts')
			->from($this->db->qn('#__sellacious_utm_links', 'a'))
			->where('UNIX_TIMESTAMP(a.created) >= ' . $this->db->q($start->toUnix()))
			->where('UNIX_TIMESTAMP(a.created) < ' . $this->db->q($end->toUnix()))
			// Group by date converted to user's timezone first
			->group($dt_key)
			->order('a.created');

		$this->db->setQuery($query);

		$results = $this->db->loadObjectList();

		if (is_array($results))
		{
			foreach ($results as $result)
			{
				$ts      = intval($result->ts + $offset * 1000);
				$count[] = array($ts, intval($result->page_count));
			}
		}

		return $count;
	}

	/**
	 * Get the product stats for the shop/seller based on access granted
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function getProductCount()
	{
		$filterP = array();
		$filterV = array();

		if ($this->helper->access->check('order.list'))
		{
			$filterP['list.where'] = array('a.state = 1');

			$filterV['list.where'] = array('a.state = 1', 'p.state = 1');
			$filterV['list.join']  = array(
				array('inner', '#__sellacious_products AS p ON p.id = a.product_id'),
			);
		}
		elseif ($this->helper->access->check('order.list.own'))
		{
			$me = JFactory::getUser();

			// PSX(+T) applied
			$filterP['list.where'] = array('a.state = 1', 'psx.seller_uid = ' . (int) $me->id);
			$filterP['list.join']  = array(
				array('inner', '#__sellacious_product_sellers AS psx ON psx.product_id = a.id')
			);

			$filterV['list.where'] = array('a.state = 1', 'p.state = 1', 'psx.seller_uid = ' . (int) $me->id);
			$filterV['list.join']  = array(
				array('inner', '#__sellacious_products AS p ON p.id = a.product_id'),
				array('inner', '#__sellacious_product_sellers AS psx ON psx.product_id = a.product_id'),
			);
		}
		else
		{
			$filterP['list.where'] = array('0');
			$filterV['list.where'] = array('0');
		}

		$stat          = new stdClass;
		$stat->product = $this->helper->product->count($filterP);
		$stat->variant = $this->helper->variant->count($filterV);

		return $stat;
	}

	/**
	 * Get aggregate balance for the selected transaction heads/contexts
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function getWalletStats()
	{
		$sub = $this->db->getQuery(true);
		$sub->select('a.currency')
			->select('CASE a.crdr WHEN ' . $this->db->q('cr') . ' THEN a.amount ELSE 0 END AS ' . $this->db->qn('cr_amount'))
			->select('CASE a.crdr WHEN ' . $this->db->q('dr') . ' THEN a.amount ELSE 0 END AS ' . $this->db->qn('dr_amount'))
			->from($this->db->qn('#__sellacious_transactions', 'a'))
			// Approved-any or locked-dr
			->where("(a.state = 1 OR (a.state = 2 AND a.crdr = 'dr'))");

		if (!$this->helper->access->check('config.edit'))
		{
			$me     = JFactory::getUser();
			$filter = sprintf(
				'((%s AND %s) OR %s)',
				'a.context = ' . $this->db->q('user.id'),
				'a.context_id = ' . (int) $me->id,
				'a.user_id = ' . (int) $me->id
			);
			$sub->where($filter);
		}

		$query = $this->db->getQuery(true);
		$query->select('SUM(a.cr_amount) AS cr_amount')
			->select('SUM(a.dr_amount) AS dr_amount')
			->select('SUM(a.cr_amount) - SUM(a.dr_amount) AS diff_amount')
			->select('a.currency')
			->from($sub, 'a')
			->group('a.currency');

		try
		{
			$balances = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$balances = array();
		}

		return $balances;
	}

	/**
	 * Get the total order conversion rate since given time
	 *
	 * @param   string  $from_utc  From this date time
	 * @param   string  $to_utc    Up to this date time
	 *
	 * @return  stdClass  object($visits, $orders, $percentage)
	 *
	 * @since   1.2.0
	 */
	public function getConversionRatio($from_utc = null, $to_utc = null)
	{
		$filters = array(
			'list.select' => 'COUNT(DISTINCT a.id)',
			'list.from'   => '#__sellacious_utm',
		);

		if ($from_utc)
		{
			$filters['list.where'][] = 'a.created >= ' . $this->db->q($from_utc);
		}

		if ($to_utc)
		{
			$filters['list.where'][] = 'a.created < ' . $this->db->q($to_utc);
		}

		$visits = $this->loadResult($filters);

		$filters['list.from']   = '#__sellacious_orders';
		$filters['list.join'][] = array('inner', '#__sellacious_payments AS py ON py.context = ' . $this->db->q('order') . ' AND py.order_id = a.id AND py.state > 0');

		if (!$this->helper->access->check('order.list'))
		{
			if ($this->helper->access->check('order.list.own'))
			{
				$me = JFactory::getUser();

				$filters['list.where'][] = 'oi.seller_uid = ' . $this->db->q($me->id);
				$filters['list.join'][]  = array('inner', '#__sellacious_order_items AS oi ON oi.order_id = a.id');
			}
			else
			{
				$filters['list.where'] = 0;
			}
		}

		$orders = $this->loadResult($filters);

		return (object) array('visits' => (int) $visits, 'orders' => (int) $orders, 'percentage' => $visits ? ($orders / $visits * 100) : 0);
	}

	/**
	 * Get Sales count Product wise Or seller Wise
	 *
	 * @param   int     $record_id  Record Id (product_id/seller_uid)
	 * @param   string  $context    Context (product/seller)
	 *
	 * @return  int
	 *
	 * @since   1.6.0
	 */
	public function getSalesCount($record_id, $context)
	{
		$filters = array(
			'list.join'   => array(
				array('inner', '#__sellacious_payments AS py ON py.context = ' . $this->db->q('order') . ' AND py.order_id = a.id AND py.state > 0'),
			),
			'list.group' => 'a.id'
		);

		$filters['list.join'][] = array('inner', '#__sellacious_order_items AS oi ON oi.order_id = a.id');

		if ($context == 'seller')
		{
			$filters['list.where'] = array('oi.seller_uid = ' . $this->db->q($record_id));
		}
		elseif ($context == 'product')
		{
			$filters['list.where'] = array('oi.product_id = ' . $this->db->q($record_id));
		}
		else
		{
			$filters['list.where'] = array('0');
		}

		$result  = (int) $this->helper->order->count($filters);

		return $result;
	}
}

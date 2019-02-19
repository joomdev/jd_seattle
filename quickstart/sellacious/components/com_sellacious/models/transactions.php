<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Sellacious records.
 */
class SellaciousModelTransactions extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'state', 'a.state',
				'date_from', 'date_to', 'date_within',
				'crdr', 'reason',
				'amount', 'amount_min', 'amount_max', 'currency',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @note    Calling getState in this method will result in recursion.
	 *
	 * @param  string $ordering  An optional ordering field.
	 * @param  string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$filters = $this->app->getUserState($this->context . '.filter');

		if ($this->helper->access->check('transaction.list'))
		{
			// This is un-setting this filter
			$this->state->set('filter.date_within', null);
			$filters['date_within'] = null;
		}
		else
		{
			$search = $this->state->get('filter.search', null);

			if (!is_scalar($search))
			{
				$this->state->set('filter.search', null);
				$filters['search'] = null;
			}

			// This is un-setting this filter
			$this->state->set('filter.date_from', null);
			$filters['date_from'] = null;

			$this->state->set('filter.date_to', null);
			$filters['date_to'] = null;
		}

		if ($this->app->input->get('layout') == 'order')
		{
			$array = array('option' => 'order_id', 'text' => $this->state->get('filter.order_id'));

			$this->state->set('filter.search', $array);
		}

		$this->app->setUserState($this->context . '.filter', $filters);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array   $data     Data
	 * @param   boolean $loadData Load current data
	 *
	 * @return  JForm|bool  the JForm object or false
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			$currency = $this->_db->getQuery(true);
			$currency->select('a.currency')
				->from('#__sellacious_transactions a')
				->group('a.currency')->order('a.currency');

			if ($this->helper->access->check('transaction.list'))
			{
				$form->removeField('date_within', 'filter');
			}
			else
			{
				$form->removeField('date_from', 'filter');
				$form->removeField('date_to', 'filter');

				$form->load(
					'<form>
						<fields name="filter">
							<field
								name="search"
								type="text"
								hint="JSEARCH_FILTER"
								class="js-stools-search-string inputbox"
							/>
						</fields>
					</form>'
				);

				// Force user filter to limit to own items.
				$userId  = (int) JFactory::getUser()->id;
				$filterU = sprintf(
					'((%s AND %s) OR %s)',
					'a.context = ' . $this->_db->q('user.id'),
					'a.context_id = ' . $userId,
					'a.user_id = ' . $userId
				);
				$currency->where($filterU);
			}

			$form->setFieldAttribute('amount', 'query', (string) $currency, 'filter');
			$form->setFieldAttribute('amount', 'query', (string) $currency, 'filter');
		}

		return $form;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.2.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$search = $this->getState('filter.search');

		$id .= ':' . (is_array($search) ? implode(':', $search) : $search);
		$id .= ':' . $this->getState('filter.state');

		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->select('CASE a.crdr WHEN ' . $db->q('cr') . ' THEN a.amount ELSE 0 END AS ' . $db->qn('cr_amount'))
			->select('CASE a.crdr WHEN ' . $db->q('dr') . ' THEN a.amount ELSE 0 END AS ' . $db->qn('dr_amount'))
			->from($db->qn('#__sellacious_transactions', 'a'));

		// Join by user
		$query->select($db->qn('u.name', 'context_title'))
			->join('left', '#__users u ON (u.id = a.context_id AND a.context = ' . $db->q('user.id') . ') OR u.id = a.user_id');

		// Join by author
		$query->join('left', '#__users c ON c.id = a.created_by');

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (is_array($search))
		{
			$search_in    = ArrayHelper::getValue($search, 'option', 'all');
			$search_value = ArrayHelper::getValue($search, 'text', '');
		}
		else
		{
			$search_in    = 'notes';
			$search_value = $search;
		}

		// User filter
		if ($this->helper->access->check('transaction.list'))
		{
			if ($search_in == 'userid')
			{
				if (is_numeric($search_value))
				{
					$query->where(sprintf('(a.user_id = %1$d OR (a.context = %2$s AND a.context_id = %1$d))', $search_value, $db->q('user.id')));
				}
			}
			elseif ($search_in == 'created_by')
			{
				$wh = array();

				if (is_numeric($search_value))
				{
					$wh[] = 'a.created_by = ' . (int) $search_value;
				}

				if ($search_value != '')
				{
					$text = $db->escape($search_value, true);
					$text = $db->q("%$text%", false);

					$wh[] = 'c.name LIKE ' . $text;
					$wh[] = 'c.username LIKE ' . $text;
					$wh[] = 'c.email LIKE ' . $text;
				}

				if (count($wh))
				{
					$query->where('(' . implode(' OR ', $wh) . ')');
				}
			}
			elseif ($search_in == 'order_id' && is_numeric($search_value))
			{
				$query->where('a.reason LIKE ' . $db->q('order.%', false));
				$query->where('a.order_id = ' . (int) $search_value);
			}
			elseif ($search_in == 'order_number')
			{
				// Full order number is needed
				$order_id = $this->helper->order->loadResult(array('order_number' => $search_value, 'list.select' => 'a.id'));

				if ($order_id)
				{
					$query->where('a.reason LIKE ' . $db->q('order.%', false));
					$query->where('a.order_id = ' . (int) $order_id);
				}
				else
				{
					$query->where('0');
				}
			}
			elseif ($search_value != '')
			{
				$text = $db->escape($search_value, true);
				$text = $db->q("%$text%", false);

				if ($search_in == 'name' || $search_in == 'username' || $search_in == 'email')
				{
					$query->where($db->qn('u.' . $search_in) . ' LIKE ' . $text);
				}
				elseif ($search_in == 'notes')
				{
					$wh   = array();
					$wh[] = 'a.notes LIKE ' . $text;
					$wh[] = 'a.user_notes LIKE ' . $text;
					$wh[] = 'a.admin_notes LIKE ' . $text;

					$query->where('(' . implode(' OR ', $wh) . ')');
				}
				elseif ($search_in == 'all')
				{
					$wh = array();

					$wh[] = 'u.name LIKE ' . $text;
					$wh[] = 'u.username LIKE ' . $text;
					$wh[] = 'u.email LIKE ' . $text;

					$wh[] = 'a.notes LIKE ' . $text;
					$wh[] = 'a.user_notes LIKE ' . $text;
					$wh[] = 'a.admin_notes LIKE ' . $text;

					$wh[] = 'c.name LIKE ' . $text;
					$wh[] = 'c.username LIKE ' . $text;
					$wh[] = 'c.email LIKE ' . $text;

					if (is_numeric($search_value))
					{
						$wh[] = 'u.id = ' . (int) $search_value;
						$wh[] = 'a.created_by = ' . (int) $search_value;
					}

					$query->where('(' . implode(' OR ', $wh) . ')');
				}
			}
		}
		elseif ($this->helper->access->check('transaction.list.own'))
		{
			// Force user filter to limit to own items.
			$me      = JFactory::getUser();
			$filterU = sprintf(
				'((%s AND %s) OR %s)',
				'a.context = ' . $db->q('user.id'),
				'a.context_id = ' . (int) $me->id,
				'a.user_id = ' . (int) $me->id
			);

			$query->where($filterU);

			if ($search_value != '')
			{
				$text = $db->escape($search_value, true);
				$text = $db->q("%$text%", false);

				$wh   = array();
				$wh[] = 'a.notes LIKE ' . $text;
				$wh[] = 'a.user_notes LIKE ' . $text;
				$wh[] = 'a.admin_notes LIKE ' . $text;

				$query->where('(' . implode(' OR ', $wh) . ')');
			}
		}
		else
		{
			$query->where('0');
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}

		// Filter by reason
		$reason = $this->getState('filter.reason');

		if ($reason == 'withdraw')
		{
			$query->where('a.reason = ' . $db->q('withdraw'));
		}
		elseif ($reason == 'listing')
		{
			$query->where('a.reason = ' . $db->q('listing', false));
		}
		elseif ($reason == 'forex')
		{
			$query->where('a.reason = ' . $db->q('forex', false));
		}
		elseif ($reason == 'addfund')
		{
			$query->where('a.reason LIKE ' . $db->q('addfund%', false));
		}
		elseif ($reason == 'tax')
		{
			$query->where('a.reason LIKE ' . $db->q('%.shoprule.tax', false));
		}
		elseif ($reason == 'discount')
		{
			$query->where('a.reason LIKE ' . $db->q('%.shoprule.discount', false));
		}
		elseif ($reason == 'commission')
		{
			$query->where('a.reason LIKE ' . $db->q('%.item.sales_commission', false));
		}
		elseif ($reason == 'shipping')
		{
			$query->where('a.reason LIKE ' . $db->q('%.shippingrule.%', false));
		}
		elseif ($reason == 'other')
		{
			$wh = array(
				'a.reason = ' . $db->q('withdraw'),
				'a.reason = ' . $db->q('listing', false),
				'a.reason = ' . $db->q('forex', false),
				'a.reason LIKE ' . $db->q('addfund%', false),
				'a.reason LIKE ' . $db->q('%shoprule.tax', false),
				'a.reason LIKE ' . $db->q('%shoprule.discount', false),
				'a.reason LIKE ' . $db->q('%item.sales_commission', false),
				'a.reason LIKE ' . $db->q('%.shippingrule.%', false),
			);
			$query->where('NOT (' . implode(' OR ', $wh) . ')');
		}

		// Filter by cr/dr mode
		if ($crdr = $this->getState('filter.crdr'))
		{
			$query->where('a.crdr = ' . $db->q($crdr));
		}

		// Filter by txn amount
		$amount = (array) $this->getState('filter.amount');

		if (is_numeric($min = ArrayHelper::getValue($amount, 'min')))
		{
			$query->where('a.amount >= ' . $db->q($min));
		}

		if (is_numeric($max = ArrayHelper::getValue((array) $amount, 'max')))
		{
			$query->where('a.amount <= ' . $db->q($max));
		}

		if ($currency = ArrayHelper::getValue((array) $amount, 'currency'))
		{
			$query->where('a.currency = ' . $db->q($currency));
		}

		// Date range filter

		// Custom date range filter is only available for staffs and admin
		if ($this->helper->access->check('transaction.list'))
		{
			if ($from_date = $this->getState('filter.date_from'))
			{
				$from_date = $this->helper->core->fixDate($from_date, null, 'UTC');

				$query->where('a.txn_date > ' . $db->q($from_date->toSql()));
			}

			if ($to_date = $this->getState('filter.date_to'))
			{
				$to_date = $this->helper->core->fixDate($to_date . ' +1 day', null, 'UTC');

				$query->where('a.txn_date <= ' . $db->q($to_date));
			}
		}
		else
		{
			$date_within = $this->getState('filter.date_within', '1m');

			if (preg_match('/^(\d+)([YMWD])$/i', $date_within, $dw))
			{
				$interval  = new DateInterval('P' . strtoupper($date_within));
				$from_date = JFactory::getDate()->sub($interval);
				$to_date   = JFactory::getDate();
				$format    = $db->getDateFormat();

				$query->where('a.txn_date > ' . $db->q($from_date->format($format)));
				$query->where('a.txn_date <= ' . $db->q($to_date->format($format)));
			}
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.txn_date DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	protected function processList($items)
	{
		foreach ($items as $item)
		{
			// Optimized using left join to user table.
			if (!isset($item->context_title))
			{
				$item->context_title = $this->helper->transaction->getContext($item->context, $item->context_id);
			}
		}

		return $items;
	}

	/**
	 * Get aggregate balance for the selected transaction heads/contexts
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getBalances()
	{
		$sub   = $this->getListQuery();
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('SUM(a.cr_amount) AS cr_amount')
			->select('SUM(a.dr_amount) AS dr_amount')
			->select('SUM(a.cr_amount) - SUM(a.dr_amount) AS diff_amount')
			->select('a.currency')
			->from($sub, 'a')
			// approved [or locked (not anymore)]
			->where('a.state = 1')
			->group('a.currency');

		try
		{
			$balances = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$balances = array();
		}

		return $balances;
	}
}

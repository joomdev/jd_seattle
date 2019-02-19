<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Sellacious records.
 */
class SellaciousModelMailQueue extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'context', 'a.context',
				'subject', 'a.subject',
				'state', 'a.state',
				'created', 'a.created',
				'sent_date', 'a.sent_date',
				'retries', 'a.retries',
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
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = 'a.created', $direction = 'DESC')
	{
		parent::populateState($ordering, $direction);

		if ($context = $this->app->input->getString('context'))
		{
			$this->setState('filter.context', $context);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   12.2
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			  ->from($db->qn('#__sellacious_mailqueue', 'a'));

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			// Mailqueue record id
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int)substr($search, 3));
			}
			// User Id
			elseif (is_numeric($search))
			{
				// Match whole email
				$him   = JFactory::getUser($search);
				$email = $db->q('%"' . $db->escape($him->email, true) . '"%');

				$query->where('a.recipients LIKE ' . $email);
			}
			// Username or email
			else
			{
				// Match partial
				$email = $db->q('%' . $db->escape($search, true) . '%');
				$query->where('a.recipients LIKE ' . $email);
			}
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		elseif ($state == '')
		{
			// Default filter - queued + sent
			$query->where('a.state IN (1, 2)');
		}

		// Filter by context
		$context = $this->getState('filter.context');

		if (!empty($context))
		{
			if ($context == 'product.query' || $context == 'product_query')
			{
				$query->where('(a.context = ' . $db->q('product.query') . ' OR a.context LIKE ' . $db->q('product_query.%', false) . ')');
			}
			elseif (strpos($context, '.'))
			{
				$query->where('a.context = ' . $db->q($context));
			}
			else
			{
				$query->where('a.context LIKE ' . $db->q($db->escape($context) . '.%', false));
			}
		}

		// Filter by created date
		$created = $this->getState('filter.created');

		if (!empty($created))
		{
			$date1 = $this->helper->core->fixDate($created, null, 'UTC');
			$date2 = $this->helper->core->fixDate($created . ' +1 day', null, 'UTC');

			$cr   = array();
			$cr[] = '(' . 'a.created >= ' . $db->q($date1) . ' AND ' . 'a.created < ' . $db->q($date2) . ')';
			$cr[] = '(' . 'a.sent_date >= ' . $db->q($date1) . ' AND ' . 'a.sent_date < ' . $db->q($date2) . ')';

			$query->where('(' . implode(' OR ', $cr) . ')');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering');

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
	 * @since   12.2
	 */
	protected function processList($items)
	{
		$table = $this->getTable();

		array_walk($items, array($table, 'parseJson'));

		return $items;
	}
}

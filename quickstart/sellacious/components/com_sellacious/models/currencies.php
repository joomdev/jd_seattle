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

/**
 * Methods supporting a list of Sellacious records.
 */
class SellaciousModelCurrencies extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'code_2', 'a.code_2',
				'code_3', 'a.code_3',
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
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		if (!$this->state->get('filter.forex'))
		{
			$this->state->set('filter.forex', $this->helper->currency->getGlobal('code_3'));
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			  ->from($db->qn('#__sellacious_currencies') . ' AS a');

		$forex = $this->getState('filter.forex', 'USD');

		$query->select('forex_from.x_factor rate_to_base, forex_from.created from_created, forex_from.modified from_modified')
			  ->join('left', $db->qn('#__sellacious_forex') . ' AS forex_from ON forex_from.x_from = a.code_3 AND forex_from.x_to = ' . $db->q($forex) . ' AND forex_from.state = 1');

		$query->select('forex_to.x_factor rate_from_base, forex_to.created to_created, forex_to.modified to_modified')
			  ->join('left', $db->qn('#__sellacious_forex') . ' AS forex_to ON forex_to.x_to = a.code_3 AND forex_to.x_from = ' . $db->q($forex) . ' AND forex_to.state = 1');

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int)substr($search, 3));
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%', false);
				$query->where('(a.title LIKE ' . $search . ' OR a.code_2 LIKE ' . $search . ' OR a.code_3 LIKE ' . $search . ')');
			}
		}

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int)$state);
		}
		elseif ($state == '')
		{
			$query->where('a.state IN (0, 1)');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.code_3 ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

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
 * Methods supporting a list of Shop rules.
 */
class SellaciousModelShippingRules extends SellaciousModelList
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
				'parent_id', 'a.parent_id',
				'title', 'a.title',
				'state', 'a.state', 'level', 'a.level',
				'path', 'a.path', 'type', 'a.type',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$me    = JFactory::getUser();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*')
			->from($db->qn('#__sellacious_shippingrules', 'a'));

		// Filter by privileges
		if ($this->helper->access->check('shippingrule.list'))
		{
			// Great, nothing to hide
		}
		elseif ($this->helper->access->check('shippingrule.list.own'))
		{
			$query->where('a.owned_by = ' . $db->q($me->id));
		}
		else
		{
			$query->where('0');
		}

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$query->where('a.title LIKE ' . $db->q('%' . $db->escape($search, true) . '%', false));
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
			$query->where('a.state IN (0, 1)');
		}

		$ordering = $this->state->get('list.fullordering', 'a.ordering ASC');

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
	 */
	protected function processList($items)
	{
		foreach ($items as &$item)
		{
			$item->params = json_decode($item->params, true);
		}

		return $items;
	}

}

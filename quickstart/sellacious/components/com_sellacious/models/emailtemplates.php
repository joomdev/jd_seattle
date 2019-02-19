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
 *
 * @since   1.5.0
 */
class SellaciousModelEmailTemplates extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.5.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'context', 'a.context',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.5.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_emailtemplates') . ' AS a');

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
				$query->where('a.context LIKE ' . $db->q('%' . $db->escape($search, true) . '%', false));
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

		// Fetch all tempates
		$this->state->set('list.limit', 0);

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.context ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[] $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function processList($items)
	{
		$items    = parent::processList($items);
		$contexts = array();
		$active   = array();
		$inactive = array();

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchEmailContext', array('com_sellacious.emailtemplate', &$contexts));

		foreach ($items as $item)
		{
			$item->active = array_key_exists($item->context, $contexts);

			if ($item->active)
			{
				$active[] = $item;
			}
			else
			{
				$inactive[] = $item;
			}
		}

		return array_merge($active, $inactive);
	}
}

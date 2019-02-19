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
 * Methods supporting a list of templates.
 *
 * @since   1.5.2
 */
class ImporterModelTemplates extends SellaciousModelList
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
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   array  $items
	 *
	 * @return  array
	 *
	 * @since   1.5.2
	 */
	protected function processList($items)
	{
		foreach ($items as $item)
		{
			$item->mapping = json_decode($item->mapping, true) ?: array();
		}

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.5.2
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__importer_templates', 'a'));

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%');
				$query->where('a.title LIKE ' . $search);
			}
		}

		if ($this->helper->access->check('template.list', null, 'com_importer'))
		{
			$uid = $this->getState('filter.user_id');

			if (is_numeric($uid))
			{
				$query->where('a.user_id = ' . (int) $uid);
			}
		}
		elseif ($this->helper->access->check('template.list.own', null, 'com_importer'))
		{
			$query->where('(a.user_id = ' . (int) JFactory::getUser()->id . ' OR a.user_id = 0');
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
		elseif ($state == '')
		{
			$query->where('a.state IN (0, 1)');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.ordering ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

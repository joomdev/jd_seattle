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
 * Methods supporting a list of imports.
 *
 * @since   1.6.1
 */
class ImporterModelImports extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JControllerLegacy
	 *
	 * @since  1.6.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'state', 'a.state',
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
	 * @since   1.6.1
	 */
	protected function processList($items)
	{
		foreach ($items as $item)
		{
			$item->progress = json_decode($item->progress, true) ?: array();
		}

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6.1
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'))
			->select('CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END AS mtime')
			->from($db->qn('#__importer_imports', 'a'));

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

		if ($this->helper->access->check('import.list', null, 'com_importer'))
		{
			$uid = $this->getState('filter.user_id');

			if (is_numeric($uid))
			{
				$query->where('a.created = ' . (int) $uid);
			}
		}
		elseif ($this->helper->access->check('import.list.own', null, 'com_importer'))
		{
			$query->where('(a.created = ' . (int) JFactory::getUser()->id);
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
			$query->where('a.state IN (1, 2, 3)');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.id DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

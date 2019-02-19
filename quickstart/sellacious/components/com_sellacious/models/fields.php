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
class SellaciousModelFields extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JControllerLegacy
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'parent_id', 'a.parent_id',
				'title', 'a.title',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
				'alias', 'a.alias',
				'state', 'a.state',
				'level', 'a.level',
				'path', 'a.path',
				'type', 'a.type',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from('`#__sellacious_fields` AS a')
			->where('a.level > 0')
			// Add the level in the tree.
			->select('COUNT(DISTINCT c2.id) AS level')
			->join('LEFT OUTER', $db->qn('#__sellacious_fields') . ' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
			->group('a.id, a.lft, a.rgt, a.parent_id, a.title');

		// Filter by search in title
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

		if ($tag = $this->getState('filter.tag', ''))
		{
			$sub = $db->getQuery(true)->select('field_id')->from('#__sellacious_field_tags')->where('category_id = ' . (int) $tag);
			$query->where('a.id IN (' . (string) $sub . ')');
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('a.level <= ' . (int) $level);
		}

		// Filter on the level.
		if ($type = $this->getState('filter.type'))
		{
			$query->where('a.type = ' . $db->q($type));
		}

		// Filter on the parent group.
		if ($fieldgroup = $this->getState('filter.fieldgroup'))
		{
			$query->where('(a.parent_id = ' . $db->q($fieldgroup) . ' OR a.id = ' . $db->q($fieldgroup) . ')');
		}

		// Filter on the filterable attribute.
		$filterable = $this->getState('filter.filterable');

		if (is_numeric($filterable))
		{
			$query->where('a.filterable = ' . (int) $filterable);
		}

		// Filter on the context.
		if ($context = $this->getState('filter.context'))
		{
			// $query->join('LEFT', $db->qn('#__sellacious_fields').' AS g ON a.parent_id = g.id')
			$query->where('a.context = ' . $db->q($context));
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

		$ordering = $this->state->get('list.fullordering', 'a.lft ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  The items loaded from the database using the list query
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function processList($items)
	{
		foreach ($items as $item)
		{
			$item->tags = $this->helper->field->getTags($item->id, false, false);
		}

		return $items;
	}
}

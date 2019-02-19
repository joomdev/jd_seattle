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
class SellaciousModelShopRules extends SellaciousModelList
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
				'parent_id', 'a.parent_id',
				'title', 'a.title',
				'amount', 'a.amount',
				'publish_down', 'a.publish_down',
				'lft', 'a.lft', 'rgt', 'a.rgt',
				'state', 'a.state', 'level', 'a.level',
				'path', 'a.path', 'type', 'a.type',
			);
		}

		parent::__construct($config);
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
		foreach ($items as &$item)
		{
			$item->params = json_decode($item->params, true);
		}

		return $items;
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

		if (!$this->helper->access->check('shoprule.list'))
		{
			// Filter is not available for them
			$this->state->set('filter.seller_uid', null);
		}
	}

	/**
	 * Get the filter form
	 *
	 * @param   array   $data     data
	 * @param   boolean $loadData load current data
	 *
	 * @return  JForm/false  the JForm object or false
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('shoprule.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'))
				->from($db->qn('#__sellacious_shoprules', 'a'))
				->where('a.level > 0');

		$query->select('COUNT(DISTINCT c2.id) AS level')
				->join('LEFT OUTER', $db->qn('#__sellacious_shoprules', 'c2') . ' ON a.lft > c2.lft AND a.rgt < c2.rgt')
				->group('a.id, a.lft, a.rgt, a.parent_id, a.title');

		// Filter by search string
		if ($search = $this->getState('filter.search'))
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

		if ($this->helper->access->check('shoprule.list'))
		{
			$seller_uid = $this->getState('filter.seller_uid');

			if (is_numeric($seller_uid))
			{
				$query->where('a.seller_uid = ' . (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('shoprule.list.own'))
		{
			$me = JFactory::getUser();
			$query->where('a.seller_uid = ' . (int) $me->id);
		}
		else
		{
			$query->where('0');
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('a.level <= ' . (int)$level);
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int)$state);
		}
		elseif ($state == '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by shoprule type
		$type = $this->getState('filter.type');

		if ($type)
		{
			$query->where('a.type = ' . $db->q($type));
		}

		$ordering = $this->state->get('list.fullordering', 'a.lft ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

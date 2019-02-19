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
 * Methods supporting a list of product buttons
 *
 * @since   1.6.0
 */
class SellaciousModelProductButtons extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings
	 *
	 * @throws  Exception
	 *
	 * @see     JControllerLegacy
	 *
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'created_by', 'a.created_by',
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
	 * @since   1.6.0
	 */
	protected function processList($items)
	{
		$table = SellaciousTable::getInstance('Coupon');
		array_walk($items, array($table, 'parseJson'));

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_product_buttons', 'a'));

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

		if (!$this->helper->access->check('product.list'))
		{
			if ($this->helper->access->check('product.list.own'))
			{
				$query->where('a.created_by = ' . (int) JFactory::getUser()->id);
			}
			else
			{
				$query->where('0');
			}
		}

		$ordering = $this->state->get('list.fullordering', 'a.id ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

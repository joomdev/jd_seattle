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
class SellaciousModelRatings extends SellaciousModelList
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
				'a.id',
				'a.state',
				'a.author_name',
				'a.type',
				'a.title',
				'a.rating',
				'a.created',
				'product_title',
				'seller_company',
			);
		}

		parent::__construct($config);
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
			if (!$this->helper->access->check('rating.list'))
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
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_ratings', 'a'));

		$query->select('CONCAT(c.product_title, " ", c.variant_title) AS product_title, 
		COALESCE(c.seller_name, su.name) AS seller_company')
			->join('left', '#__sellacious_cache_products AS c ON c.product_id = a.product_id');

		$query->join('left', '#__users AS su ON su.id = a.seller_uid');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif ($this->helper->product->parseCode($search, $product_id, $variant_id, $seller_uid))
			{
				$query->where('a.product_id = ' . (int) $product_id);

				if (is_numeric($variant_id))
				{
					$query->where('a.variant_id = ' . (int) $variant_id);
				}

				if ($seller_uid > 0)
				{
					$query->where('a.seller_uid = ' . (int) $seller_uid);
				}
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%');
				$query->where('a.title LIKE ' . $search);
			}
		}

		$category = $this->getState('filter.category');

		if ($category)
		{
			$query->where('find_in_set('. (int) $category .', c.category_ids)');
		}

		if ($this->helper->access->check('rating.list'))
		{
			if ($seller_uid = $this->getState('filter.seller_uid'))
			{
				$query->where('a.seller_uid = ' . (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('rating.list.own'))
		{
			$query->where('a.seller_uid = ' . (int) JFactory::getUser()->id);
		}

		// Filter by published state
		$type = $this->getState('filter.type');

		if ($type == 'product' || $type == 'seller' || $type == 'shipment' || $type == 'packaging')
		{
			$query->where('a.type = ' . $db->q($type));
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}

		// Filter by published state
		$rating = $this->getState('filter.rating');

		if (is_numeric($rating))
		{
			$query->where('a.rating = ' . (int) $rating);
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.created DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

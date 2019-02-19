<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Stores.
 *
 * @since   1.5.3
 */
class SellaciousModelStores extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array $config An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'title',
				'a.title',
				'state',
				'a.state',
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
	 * @param  string $ordering  An optional ordering field.
	 * @param  string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 * @since  1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$show_product = $this->helper->config->get('show_store_product_count', 1);

		$this->state->set('show_product', $show_product);

		if ($store_location_custom = $this->app->getUserState('filter.store_location_custom'))
		{
			$this->state->set('filter.store_location_custom', $store_location_custom);
		}

		if ($store_location_custom_text = $this->app->getUserState('filter.store_location_custom_text'))
		{
			$this->state->set('filter.store_location_custom_text', $store_location_custom_text);
		}

		if ($shippable = $this->app->getUserState('filter.shippable'))
		{
			$this->state->set('filter.shippable', $shippable);
		}

		if ($shippable_text = $this->app->getUserState('filter.shippable_text'))
		{
			$this->state->set('filter.shippable_text', $shippable_text);
		}
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

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_sellers', 'a'))
			->where('a.state = 1')
			->select(' u.name, u.username, u.email')
			->join('INNER', $db->qn('#__users', 'u') . ' ON a.user_id = u.id')
			->where('u.block = 0')
			->order('a.created DESC');

		// Filter by store location
		$storeLocation = $this->getState('filter.store_location', 0);

		if ($storeLocation && !$this->helper->config->get('hide_store_location_filter'))
		{
			$this->filterQueryByLocation($storeLocation, $query);
		}

		$this->filterByShipping($query);

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onAfterBuildQuery', array('com_sellacious.model.stores', &$query));

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[] $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	protected function processList($items)
	{
		if (is_array($items))
		{
			foreach ($items as $item)
			{
				$item->profile       = $this->helper->profile->getItem(array('user_id' => $item->user_id));
				$item->rating        = $this->helper->rating->getSellerRating($item->user_id);
				$item->product_count = $this->getState('show_product') ? $this->helper->seller->getSellerProductCount($item->user_id) : null;
			}
		}

		return $items;
	}

	/**
	 * Filter stores query by store location
	 *
	 * @param   string           $storeLocation    Selected Discount id
	 * @param   \JDatabaseQuery  $query  The Database query
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function filterQueryByLocation($storeLocation, &$query)
	{
		$db    = JFactory::getDbo();
		$where = array();

		if ($storeLocation == 1)
		{
			$countryCode = $this->helper->location->ipToCountry();
			$countryName = $this->helper->location->ipToCountryName();

			if($countryName || $countryCode)
			{
				$filter   = array(
					'list.select' => 'a.id',
					'list.where'  => 'a.title = ' . $db->q($countryName) . ' OR a.iso_code = ' . $db->q($countryCode),
					'type'        => 'country',
				);
				$gl_ids = $this->helper->location->loadColumn($filter);

				if (count($gl_ids) && is_array($gl_ids))
				{
					$where[] = 'ad.country IN (' . implode(',', $gl_ids) . ')';
				}
			}
		}
		elseif ($storeLocation == 2)
		{
			$customLocation     = $this->getState('filter.store_location_custom', '');
			$customLocationText = $this->getState('filter.store_location_custom_text', '');
			$searchOptions      = $this->helper->config->get('store_location_custom_search_in', array('country'));

			if ((!empty($customLocation) || !empty(!empty($customLocationText))) && !empty($searchOptions))
			{
				$gl_ids = array($customLocation);

				if (empty($customLocation))
				{
					$customLocationText = explode(',', $customLocationText);
					$customLocationText = $customLocationText[0];

					$filter                 = array();
					$filter['list.select']  = 'a.id';
					$filter['list.where'][] = '(a.title = ' . $db->q($customLocationText) . ' OR a.iso_code = ' . $db->q($customLocationText) . ')';
					$filter['list.where'][] = 'a.type IN (' . implode(',', $db->q($searchOptions)) . ')';
					$filter['list.where'][] = 'a.state = 1';

					$gl_ids = $this->helper->location->loadColumn($filter);
				}

				if (count($gl_ids) && is_array($gl_ids))
				{
					if (in_array('country', $searchOptions))
					{
						$where[] = 'ad.country IN (' . implode(',', $gl_ids) . ')';
					}
					if (in_array('state', $searchOptions))
					{
						$where[] = 'ad.state_loc IN (' . implode(',', $gl_ids) . ')';
					}
					if (in_array('city', $searchOptions))
					{
						$where[] = 'ad.city IN (' . implode(',', $gl_ids) . ')';
					}
				}
				if (in_array('zip', $searchOptions))
				{
					$where[] = 'ad.zip = ' . $db->q($customLocation);
				}
			}
		}

		$query->join('left', $db->qn('#__sellacious_addresses', 'ad') . ' ON ad.user_id = a.user_id');
		$query->where('ad.is_primary = 1');

		if (count($where))
		{
			$query->where(implode(' OR ', $where));
		}
	}

	/**
	 * Filter stores query by shipping
	 *
	 * @param   \JDatabaseQuery  $query  The Database query
	 *
	 * @return   bool
	 *
	 * @since   1.6.0
	 */
	protected function filterByShipping(&$query)
	{
		$shippable      = $this->getState('filter.shippable');
		$shippableTitle = $this->getState('filter.shippable_text');

		// Nothing to filter, allow all
		if ((empty($shippable) && empty($shippableTitle)) ||  $this->helper->config->get('hide_shippable_filter'))
		{
			return true;
		}

		$shippableIds = array($shippable);
		if (empty($shippable))
		{
			$shippableTitle = explode(',', $shippableTitle);
			$shippableTitle = $shippableTitle[0];
			$searchOptions = $types = $this->helper->config->get('shippable_location_search_in', array('country'));

			$filter                 = array();
			$filter['list.select']  = 'a.id';
			$filter['list.where'][] = '(a.title = ' . $this->_db->q($shippableTitle) . ' OR a.iso_code = ' . $this->_db->q($shippableTitle) . ')';
			$filter['list.where'][] = 'a.type IN (' . implode(',', $this->_db->q($searchOptions)) . ')';
			$filter['list.where'][] = 'a.state = 1';

			$shippableIds = $this->helper->location->loadColumn($filter);
		}
		// Location code not recognised, meaning we can't ship
		if (!$shippableIds)
		{
			return false;
		}

		/*
		 * Get all ancestor locations, we'll match them with the allowed locations
		 * Do not forget that a larger region set in configuration always allows it sub-regions
		 *
		 * Todo: Check whether this location is shippable or not
		 */
		$queried = $this->helper->location->getAncestry($shippableIds, 'A');
		$global  = $this->helper->location->getShipping();
		$global  = array_reduce($global, 'array_merge', array());

		$filtered = empty($global) ? $queried : array_intersect((array) $global, (array) $queried);

		// No match with global, meaning we can't ship
		if (count($filtered) == 0)
		{
			return false;
		}

		$shipped_by        = $this->helper->config->get('shipped_by');
		$seller_preferable = $this->helper->config->get('shippable_location_by_seller');

		// Seller cannot set preference, meaning allow all as global test already passed
		if ($shipped_by != 'seller' || !$seller_preferable)
		{
			return true;
		}

		/*
		 * Now get the list of sellers that allow as they can set preference.
		 * Match with queried hierarchy list as it may contain wider scope than global.
		 */
		$query->join('left', $this->_db->qn('#__sellacious_seller_shippable', 'b') . ' ON b.seller_uid = a.user_id')
			->where('(b.gl_id = 0 OR b.gl_id = ' . implode(' OR b.gl_id = ', $queried) . ')')
			->where('b.state = 1');

		return true;
	}
}

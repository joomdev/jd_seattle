<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Sellacious records.
 *
 * @since  1.0.0
 */
class SellaciousModelProducts extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.product_id',
				'a.product_sku',
				'a.product_title',
				'a.product_active',
				'a.created_by',
				'a.seller_company',
				'a.product_price',
				'a.listing_start',
				'a.listing_end',
				'a.stock',
				'a.language',
				'category_title',
				'a.variant_count',
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
	 * Note: Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$seller_uid   = $this->state->get('filter.seller_uid');
		$multi_seller = $this->helper->config->get('multi_seller', 0);

		if (!$multi_seller && !$seller_uid)
		{
			// Removed force selection of default seller when multi-seller is off. Now it will be a fallback if not filtered by
			$seller_uid = $this->helper->config->get('default_seller');

			$this->state->set('filter.seller_uid', $seller_uid);
		}

		$this->state->set('layout', $this->app->input->get('layout'));
	}

	/**
	 * Get the filter form
	 *
	 * @param   array   $data     data
	 * @param   boolean $loadData load current data
	 *
	 * @return  JForm/false  the JForm object or false
	 *
	 * @since   1.5.3
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('product.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}

			$defLanguage = JFactory::getLanguage();
			$tag         = $defLanguage->getTag();
			$languages   = JLanguageHelper::getContentLanguages();

			$languages = array_filter($languages, function ($item) use ($tag){
				return ($item->lang_code != $tag);
			});

			if (!count($languages))
			{
				$form->removeField('language', 'filter');
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
		// We have separate logic for trashed and archived items
		$state = $this->getState('filter.state');

		if (is_numeric($state) && $state != 0 && $state != 1)
		{
			return $this->getListQueryAlt();
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.product_id AS id, a.product_id, a.seller_uid, a.owner_uid AS owned_by, a.product_title AS title, a.product_alias AS alias, a.language,'
			. ' a.product_type AS type, a.product_sku AS local_sku, a.category_ids, a.category_titles, a.spl_category_ids, a.spl_category_titles,'
			. ' a.manufacturer_sku, a.manufacturer_id, a.product_features AS features, a.product_introtext AS introtext,'
			. ' a.product_description AS description, a.variant_count, a.seller_count, a.seller_catid, a.seller_name, a.seller_username, a.seller_mobile,'
			. ' a.seller_email, a.seller_company, a.seller_active, a.seller_code, a.seller_store, a.seller_currency, a.seller_commission, a.manufacturer_name,'
			. ' a.manufacturer_username, a.manufacturer_email, a.manufacturer_catid, a.manufacturer_company, a.manufacturer_code, a.listing_type,'
			. ' a.item_condition, a.length, a.width, a.height, a.weight, a.vol_weight, a.delivery_mode, a.download_limit, a.download_period,'
			. ' a.preview_mode, a.preview_url, a.flat_shipping, a.shipping_flat_fee, a.return_days, a.exchange_days, a.psx_id, a.price_display,'
			. ' a.product_price AS sales_price, a.multi_price, a.stock, a.over_stock, a.product_active AS state, a.is_selling,'
			. ' a.listing_active AS listing_state, a.listing_start AS listing_publish_up, a.listing_end AS listing_publish_down,'
			. ' a.order_count, a.order_units, a.tags, a.metakey, a.metadesc, a.stock + a.over_stock AS stock_capacity');

		$query->from($db->qn('#__sellacious_cache_products', 'a'))->where('a.variant_id = 0');

		$this->filterSearch($query);

		if ($this->getState('layout') == 'bulk')
		{
			$query->select('p.id AS price_id, p.qty_min, p.qty_max, p.sdate, p.edate, p.cost_price, p.margin, p.margin_type, p.list_price')
				->select('p.calculated_price, p.ovr_price, p.product_price, p.is_fallback, p.state')
				->join('left', $db->qn('#__sellacious_product_prices', 'p') . ' ON p.seller_uid = a.seller_uid AND p.product_id = a.product_id');

			$query->where('a.psx_id > 0');
		}

		$ordering = $this->state->get('list.fullordering', 'a.listing_start DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Filter the list query by search text and other filters
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function filterSearch($query)
	{
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.product_id = ' . (int) substr($search, 3));
			}
			elseif (stripos(strtolower($search), 'sku:') === 0)
			{
				$search = $this->_db->q('%' . $this->_db->escape(substr($search, 4), true) . '%', false);
				$query->where('a.product_sku LIKE ' . $search);
			}
			elseif (stripos(strtolower($search), 't:') === 0)
			{
				$search = $this->_db->q('%' . $this->_db->escape(substr($search, 2), true) . '%', false);
				$query->where('a.product_title LIKE ' . $search);
			}
			else
			{
				$search = $this->_db->q('%' . $this->_db->escape($search, true) . '%', false);
				$query->where('(' . "a.product_sku LIKE {$search} OR a.product_title LIKE {$search} OR a.product_description LIKE {$search}" . ')');
			}
		}

		// Filter by price display
		$price_display = $this->getState('filter.price_display');

		if (is_numeric($price_display))
		{
			$query->where('a.price_display = ' . (int) $price_display);
		}

		// Filter by selling state
		$selling = $this->getState('list.selling');

		if (is_numeric($selling))
		{
			$query->where('a.is_selling = ' . (int) $selling);
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.product_active = ' . (int) $state);
		}

		// Filter by category
		if ($category = $this->getState('filter.category'))
		{
			$categories         = $this->helper->category->getChildren($category, true);
			$productsInCategory = 'SELECT product_id FROM #__sellacious_product_categories WHERE category_id IN (' . implode(',', $categories) . ')';

			$query->where('a.product_id IN (' . $productsInCategory . ')');
		}

		if ($type = $this->getState('filter.type'))
		{
			$query->where('a.product_type = ' . $this->_db->q($type));
		}

		// Filter by manufacturer
		if ($manufacturer = $this->getState('filter.manufacturer'))
		{
			$query->where('a.manufacturer_id = ' . (int) $manufacturer);
		}

		// Filter by seller
		if ($this->helper->access->check('product.list'))
		{
			if ($seller_uid = $this->getState('filter.seller_uid'))
			{
				$query->where('a.seller_uid = ' . (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('product.list.own'))
		{
			$me = JFactory::getUser();
			$query->where('(a.seller_uid = ' . (int) $me->id . ' OR a.owner_uid = ' . (int) $me->id . ')');
		}
		else
		{
			$query->where('0');
		}

		// Filter by language
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $this->_db->quote($language));
		}
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function processList($items)
	{
		if ($items)
		{
			$g_currency = $this->helper->currency->getGlobal('code_3');

			foreach ($items as $item)
			{
				$item->categories          = explode(',', $item->category_ids);
				$item->category_titles     = explode('|:|', $item->category_titles);
				$item->spl_categories      = explode(',', $item->spl_category_ids);
				$item->spl_category_titles = explode('|:|', $item->spl_category_titles);

				// Translate categories
				if ($item->language)
				{
					$categoryTitles = array();

					foreach ($item->categories as $key => $category)
					{
						$transCategory = new stdClass;
						$transCategory->id = $category;
						$transCategory->title = $item->category_titles[$key];

						$this->helper->translation->translateRecord($transCategory, 'sellacious_categories', $item->language);

						$categoryTitles[] = $transCategory->title;
					}

					$item->category_titles = $categoryTitles;
				}

				if (!$item->seller_currency)
				{
					$item->seller_currency = $g_currency;
				}

				if (!isset($item->order_count))
				{
					$item->order_count = $this->helper->order->getOrderCount($item->product_id, 0, $item->seller_uid);
					$item->order_units = $this->helper->order->getOrderCount($item->product_id, 0, $item->seller_uid, true);
				}
			}
		}

		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.5.0
	 */
	protected function getListQueryAlt()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__sellacious_products', 'a'));

		// Build category list as a sub-query to avoid grouping collisions.
		$cQuery = $this->_db->getQuery(true);
		$cQuery->select("GROUP_CONCAT(c.id ORDER BY c.lft SEPARATOR ',') AS category_ids")
			->select("GROUP_CONCAT(c.title ORDER BY c.lft SEPARATOR '|:|') AS category_titles")
			->from($db->qn('#__sellacious_categories', 'c'))
			->select('pc.product_id')
			->join('LEFT', $db->qn('#__sellacious_product_categories', 'pc') . ' ON c.id = pc.category_id')
			->group('pc.product_id');

		$query->select('cc.category_ids, cc.category_titles')
			->join('left', "({$cQuery}) AS cc ON cc.product_id = a.id");

		// Filter by search in name
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos(strtolower($search), 'sku:') === 0)
			{
				$search = $this->_db->q('%' . $this->_db->escape(substr($search, 4), true) . '%', false);
				$query->where('(a.local_sku LIKE ' . $search . ')');
			}
			elseif (stripos(strtolower($search), 't:') === 0)
			{
				$search = $this->_db->q('%' . $this->_db->escape(substr($search, 2), true) . '%', false);
				$query->where('(a.title LIKE ' . $search . ')');
			}
			else
			{
				$search = $this->_db->q('%' . $this->_db->escape($search, true) . '%', false);
				$where  = array(
					'a.local_sku LIKE ' . $search,
					'a.title LIKE ' . $search,
					'a.description LIKE ' . $search,
				);
				$query->where('(' . implode(' OR ', $where) . ')');
			}
		}

		$price_display = $this->getState('filter.price_display');

		if (is_numeric($price_display))
		{
			$query->where('psx.price_display = ' . (int) $price_display);
		}

		$selling = $this->getState('list.selling');

		if (is_numeric($selling))
		{
			$query->where('psx.state = ' . (int) $selling);
		}

		// Filter by published state
		$query->where('a.state = ' . (int) $this->getState('filter.state'));

		// Filter by published state
		if ($category = $this->getState('filter.category'))
		{
			$query->where('a.id IN (SELECT product_id FROM #__sellacious_product_categories WHERE category_id = ' . (int) $category . ')');
		}

		if ($type = $this->getState('filter.type'))
		{
			$query->where('a.type = ' . $db->q($type));
		}

		// Filter by published state
		if ($manufacturer = $this->getState('filter.manufacturer'))
		{
			$query->where('a.manufacturer_id = ' . (int) $manufacturer);
		}

		if ($this->helper->access->check('product.list'))
		{
			if ($seller_uid = $this->getState('filter.seller_uid'))
			{
				$query->where('p.seller_uid = ' . (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('product.list.own'))
		{
			$me = JFactory::getUser();
			$query->where('(p.seller_uid = ' . (int) $me->id . ' OR a.owned_by = ' . (int) $me->id . ')');
		}
		else
		{
			$query->where('0');
		}

		$this->extendItemQuery($query);
		$this->specialListingQuery($query);
		$this->basicListingQuery($query);

		// The ordering columns names needs to be mapped here as this does not match with the main query columns.
		$ordering = $this->state->get('list.fullordering', 'l.publish_up DESC');

		if (trim($ordering))
		{
			$orderCols = array(
				'a.product_id'     => 'a.id',
				'a.product_title'  => 'a.title',
				'a.product_active' => 'a.state',
				'a.seller_company' => 'p.seller_company',
				'a.stock'          => 'p.stock',
				'a.product_price'  => 'p.product_price',
				'a.listing_start'  => 'l.listing_start',
				'a.listing_end'    => 'l.listing_end',
			);

			@list($orderCol, $orderDir) = explode(' ', $ordering);

			$orderCol = \Joomla\Utilities\ArrayHelper::getValue($orderCols, $orderCol, null);
			$orderDir = in_array(strtoupper($orderDir), array('ASC', 'DESC')) ? $orderDir : 'ASC';

			if ($orderCol && $orderDir)
			{
				$ordering = $orderCol . ' ' . $orderDir;

				$query->order($db->escape($ordering));
			}
		}

		return $query;
	}

	/**
	 * Extend items seller etc properties to basic query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function extendItemQuery($query)
	{
		// PSX(+T) applied
		$pQuery = $this->_db->getQuery(true);

		// Add price and stock info
		$pQuery->select('pp.product_id, pp.seller_uid, pp.id AS price_id, pp.cost_price, pp.margin, pp.margin_type')
			->select('pp.list_price, pp.calculated_price, pp.product_price, pp.product_price AS sales_price, pp.ovr_price, pp.is_fallback')
			->from($this->_db->qn('#__sellacious_product_prices', 'pp'))
			->where('pp.is_fallback = 1');

		// Add seller information
		$pQuery->select('ss.title AS seller_company, ss.store_name AS seller_store')
			->select("IF (ss.state = 1 AND u.block = 0, 1, 0) AS seller_active")
			->join('LEFT', $this->_db->qn('#__sellacious_sellers', 'ss') . ' ON ss.user_id = pp.seller_uid');

		$pQuery->select('u.name AS seller_name, u.username AS seller_username, u.email AS seller_email')
			->join('INNER', $this->_db->qn('#__users', 'u') . ' ON u.id = pp.seller_uid');

		$g_currency       = $this->helper->currency->getGlobal('code_3');
		$listing_currency = $this->helper->config->get('listing_currency');
		$seller_currency  = $listing_currency ? $this->_db->qn('ss.currency') : $this->_db->q($g_currency);

		$pQuery->select($seller_currency . ' AS seller_currency')
			->join('LEFT', $this->_db->qn('#__sellacious_profiles', 'su') . ' ON su.user_id = pp.seller_uid');

		// Now append everything to the main query
		$query->select('p.*');

		if ($this->helper->config->get('shipped_by') == 'seller')
		{
			$query->select("CASE a.type WHEN 'physical' THEN psp.flat_shipping WHEN 'package' THEN psk.flat_shipping END AS flat_shipping")
				->select("CASE a.type WHEN 'physical' THEN psp.shipping_flat_fee WHEN 'package' THEN psk.shipping_flat_fee END AS shipping_flat_fee");
		}
		else
		{
			$flat_shipping     = $this->helper->config->get('flat_shipping');
			$shipping_flat_fee = $flat_shipping ? $this->helper->config->get('shipping_flat_fee') : 0;

			$query->select($this->_db->q($flat_shipping) . ' AS flat_shipping')->select($this->_db->q($shipping_flat_fee) . ' AS shipping_flat_fee');
		}

		$query->select('psx.price_display, psx.stock, psx.over_stock, psx.stock + psx.over_stock AS stock_capacity, psx.state AS is_selling')
			->select("CASE a.type WHEN 'physical' THEN psp.listing_type WHEN 'package' THEN psk.listing_type END AS listing_type")
			->select("CASE a.type WHEN 'physical' THEN psp.item_condition WHEN 'package' THEN psk.item_condition END AS item_condition")
			->select("CASE a.type WHEN 'physical' THEN psp.whats_in_box WHEN 'package' THEN psk.whats_in_box END AS whats_in_box")
			->select("CASE a.type WHEN 'physical' THEN psp.return_days WHEN 'package' THEN psk.return_days END AS return_days")
			->select("CASE a.type WHEN 'physical' THEN psp.return_tnc WHEN 'package' THEN psk.return_tnc END AS return_tnc")
			->select("CASE a.type WHEN 'physical' THEN psp.exchange_days WHEN 'package' THEN psk.exchange_days END AS exchange_days")
			->select("CASE a.type WHEN 'physical' THEN psp.exchange_tnc WHEN 'package' THEN psk.exchange_tnc END AS exchange_tnc");

		$query->select('0 AS multi_price');

		$query->join('LEFT', '(' . $pQuery . ') AS p ON p.product_id = a.id')
			->join('LEFT', $this->_db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = p.product_id AND psx.seller_uid = p.seller_uid')
			->join('LEFT', $this->_db->qn('#__sellacious_physical_sellers', 'psp') . ' ON psp.psx_id = psx.id')
			->join('LEFT', $this->_db->qn('#__sellacious_package_sellers', 'psk') . ' ON psk.psx_id = psx.id');
	}

	/**
	 * Add a comma separated list of subscribed Special Category IDs
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function basicListingQuery($query)
	{
		$db     = $this->getDbo();
		$now    = JFactory::getDate()->toSql();
		$nullDt = $db->getNullDate();

		// Special category listing
		$sub = $db->getQuery(true);
		$sub->select('l.seller_uid, l.product_id')
			->select('l.publish_up AS listing_start, l.publish_down AS listing_end, l.state AS listing_active')
			->from($db->qn('#__sellacious_seller_listing', 'l'))
			->where('l.category_id = 0')
			->where('l.publish_up != ' . $db->q($nullDt))
			->where('l.publish_down != ' . $db->q($nullDt))
			->where('l.publish_up < ' . $db->q($now))
			->where('l.publish_down > ' . $db->q($now))
			->where('l.state = 1')
			->group('l.seller_uid, l.product_id');

		$query->select('l.listing_start AS listing_publish_up, l.listing_end AS listing_publish_down, l.listing_active AS listing_state')
			->join('LEFT', "($sub) AS l ON l.product_id = a.id AND l.seller_uid = p.seller_uid");
	}

	/**
	 * Add a comma separated list of subscribed Special Category IDs
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function specialListingQuery($query)
	{
		$db     = $this->getDbo();
		$now    = JFactory::getDate()->toSql();
		$nullDt = $db->getNullDate();

		// Special category listing
		$sub = $db->getQuery(true);
		$sub->select('l.seller_uid, l.product_id')
			->select('GROUP_CONCAT(l.category_id) AS spl_category_ids')
			->from($db->qn('#__sellacious_seller_listing', 'l'))
			->where('l.category_id > 0')
			->where('l.publish_up != ' . $db->q($nullDt))
			->where('l.publish_down != ' . $db->q($nullDt))
			->where('l.publish_up < ' . $db->q($now))
			->where('l.publish_down > ' . $db->q($now))
			->where('l.state = 1')
			->group('l.seller_uid, l.product_id');

		$query->select('spl_category_ids, null AS spl_category_titles')
			->join('LEFT', "($sub) AS spl ON spl.product_id = a.id AND spl.seller_uid = p.seller_uid");
	}
}

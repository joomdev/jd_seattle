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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of Products.
 *
 * @since   1.0.0
 */
class SellaciousModelStore extends SellaciousModelList
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
				'alias', 'a.alias',
				'state', 'a.state',
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

		$storeId  = $this->app->input->getInt('id');
		$category = $this->app->input->getInt('category_id');

		$this->state->set('store.id', $storeId);

		if ($query = $this->app->input->getString('q'))
		{
			$this->state->set('filter.query', $query);
		}

		if ($category)
		{
			$this->state->set('filter.category_id', $category);
		}

		if ($offer = $this->app->input->getInt('offer_id'))
		{
			$this->state->set('filter.offer_id', $offer);
		}

		if ($splCat = $this->app->input->getInt('spl_category'))
		{
			$this->state->set('filter.spl_category', $splCat);
		}

		if ($price_from = $this->app->input->getInt('price_from'))
		{
			$this->state->set('filter.price_from', $price_from);
		}

		if ($price_to = $this->app->input->getInt('price_to'))
		{
			$this->state->set('filter.price_to', $price_to);
		}

		if ($order = $this->app->input->getString('custom_ordering'))
		{
			$this->state->set('list.custom_ordering', $order);
		}
	}

	/**
	 * Method to build the list query.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		try
		{
			$shippable = $this->isShippableAt();
		}
		catch (Exception $e)
		{
			$shippable = true;
		}

		if (!$shippable)
		{
			return $this->_db->getQuery(true)->select('id')->from('#__sellacious_products')->where('0');
		}

		$query = $this->getItemsQuery();

		// Filter by out of stock
		$stock = $this->getState('filter.hide_out_of_stock', 0);

		if ($this->helper->config->get('hide_out_of_stock') || ($stock && !$this->helper->config->get('hide_stock_filter')))
		{
			$query->where('a.stock + a.over_stock > 0');
		}

		// Filter by offers
		$offerId = $this->state->get('filter.offer_id', 0);

		if ($offerId && !$this->helper->config->get('hide_special_offer_filter'))
		{
			$offer_filter = $this->helper->shopRule->getDiscountFilter($offerId);

			if (count($offer_filter))
			{
				$query->where('((' . implode(")\nAND (", $offer_filter) . '))');
			}
		}

		// Filter by shipping
		$shipping = $this->getState('filter.shipping', 0);

		if ($shipping && !$this->helper->config->get('hide_shipping_filter'))
		{
			$shipped_by = $this->helper->config->get('shipped_by');

			$query->where('a.product_type <> ' . $this->_db->q('electronic'));

			if ($shipped_by == 'shop')
			{
				$flat_shipping     = $this->helper->config->get('flat_shipping');
				$shipping_flat_fee = $flat_shipping ? $this->helper->config->get('shipping_flat_fee') : 0;
				if (abs($shipping_flat_fee - 0.00) >= 0.01)
				{
					$query->where('0');
				}
			}
			else
			{
				$itemisedShip = $this->helper->config->get('itemised_shipping');
				if ($itemisedShip)
				{
					$query->where('a.flat_shipping = 1');
					$query->where('a.shipping_flat_fee = 0');
				}
			}
		}

		// Filter by special category
		$splCategory = $this->getState('filter.spl_category', 0);

		if ($splCategory && !$this->helper->config->get('hide_special_category_filter'))
		{
			$query->where('FIND_IN_SET(' . (int)$splCategory . ', a.spl_category_ids)');
		}

		$query->where('a.seller_uid = ' . (int) $this->getState('store.id'));

		$query->select('a.variant_price, a.variant_price + p.product_price AS basic_price, a.sales_price');
		
		if (!$this->helper->config->get('hide_price_filter'))
		{
			// Filter by Min Price
			if ($price_from = $this->getState('filter.price_from'))
			{
				$query->where('(a.variant_price + p.product_price) * a.forex_rate  >= ' . (int) $price_from);
			}

			// Filter by Max Price
			if ($price_to = $this->getState('filter.price_to'))
			{
				$query->where('(a.variant_price + p.product_price) * a.forex_rate <= ' . (int) $price_to);
			}
		}

		// Filter by keyword
		$query->join('left', $this->_db->qn('#__sellacious_cache_specifications', 's') . ' ON s.x__product_id = a.product_id AND s.x__variant_id = a.variant_id');

		$fFields  = $this->helper->field->loadColumn(array('list.select' => 'a.id', 'filterable' => 1, 'state' => 1));
		$keywords = $this->getState('filter.query');

		foreach (explode(' ', $keywords) as $keyword)
		{
			$cond = array();
			$kw   = $this->_db->q('%' . $this-> _db->escape($keyword, true) . '%', false);

			$cond[] = 'a.product_title LIKE ' . $kw;
			$cond[] = 'a.product_sku LIKE ' . $kw;
			$cond[] = 'a.product_introtext LIKE ' . $kw;
			$cond[] = 'a.variant_title LIKE ' . $kw;
			$cond[] = 'a.variant_sku LIKE ' . $kw;

			foreach ($fFields as $fid)
			{
				$cond[] = "s.spec_$fid LIKE " . $kw;
			}

			$query->where('(' . implode(' OR ', $cond) . ')');
		}

		$multi_seller     = $this->helper->config->get('multi_seller', 0);
		$multi_variant    = $this->helper->config->get('multi_variant', 0);
		$seller_separate  = $multi_seller == 2;
		$variant_separate = $multi_variant == 2;

		$grouping = array('a.product_id');

		if ($multi_variant && $variant_separate)
		{
			$grouping[] = 'a.variant_id';
		}

		if ($multi_seller && $seller_separate)
		{
			$grouping[] = 'a.seller_uid';
		}

		$query->group($grouping);

		// In any ordering, no-price, no-stock items are always at end
		$query->order('a.stock = 0 ASC');
		$query->order('a.price_display ASC');
		$query->order('a.sales_price = 0 ASC');
		$query->order('p.is_fallback ASC');

		$customOrder = $this->getState('list.custom_ordering');

		if ($customOrder == 'order_max')
		{
			$query->order('a.order_units DESC');
		}
		elseif ($customOrder == 'rating_max')
		{
			$query->order('a.product_rating DESC');
		}
		elseif ($customOrder == 'price_min')
		{
			$query->order('a.sales_price * forex_rate ASC');
		}
		elseif ($customOrder == 'price_max')
		{
			$query->order('a.sales_price * forex_rate DESC');
		}

		$query->order('a.spl_category_ids = ' . $this->_db->q('') . ' ASC');

		return $query;
	}

	/**
	 * Query for products and variants
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getItemsQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$columns = array(
			'id'                  => 'a.product_id',
			'title'               => 'a.product_title',
			'type'                => 'a.product_type',
			'local_sku'           => 'a.product_sku',
			'manufacturer_sku'    => 'a.manufacturer_sku',
			'manufacturer_id'     => 'a.manufacturer_id',
			'features'            => 'a.product_features',
			'introtext'           => 'a.product_introtext',
			'description'         => 'a.product_description',
			'variant_id'          => 'a.variant_id',
			'variant_title'       => 'a.variant_title',
			'variant_sku'         => 'a.variant_sku',
			'variant_description' => 'a.variant_description',
			'variant_features'    => 'a.variant_features',
			'variant_count'       => 'a.variant_count',
			'metakey'             => 'a.metakey',
			'metadesc'            => 'a.metadesc',
			'tags'                => 'a.tags',
			'price_display'       => 'a.price_display',
			'listing_type'        => 'a.listing_type',
			'item_condition'      => 'a.item_condition',
			'flat_shipping'       => 'a.flat_shipping',
			'shipping_flat_fee'   => 'a.shipping_flat_fee',
			'return_days'         => 'a.return_days',
			'exchange_days'       => 'a.exchange_days',
			'seller_uid'          => 'a.seller_uid',
			'seller_company'      => 'a.seller_company',
			'seller_code'         => 'a.seller_code',
			'seller_name'         => 'a.seller_name',
			'seller_username'     => 'a.seller_username',
			'seller_mobile'       => 'a.seller_mobile',
			'seller_email'        => 'a.seller_email',
			'seller_currency'     => 'a.seller_currency',
			'seller_count'        => 'a.seller_count',
			'forex_rate'          => 'a.forex_rate',
			'price_mod'           => 'a.variant_price_mod',
			'price_mod_perc'      => 'a.variant_price_mod_perc',
			'stock'               => 'a.stock',
			'over_stock'          => 'a.over_stock',
			'category_ids'        => 'a.category_ids',
			'category_titles'     => 'a.category_titles',
			'spl_category_ids'    => 'a.spl_category_ids',
			'spl_category_titles' => 'a.spl_category_titles',
			'rating'              => 'a.product_rating',
		);

		$query->select($this->_db->quoteName(array_values($columns), array_keys($columns)))
			->select($this->_db->qn('a.stock') . ' + ' . $this->_db->qn('a.over_stock') . ' AS ' . $this->_db->qn('stock_capacity'))
			->from($db->qn('#__sellacious_cache_products', 'a'))
			->where('a.is_selling = 1')
			->where('a.seller_active = 1')
			->where('a.listing_active = 1')
			->where('a.product_active = 1');

		$where_filter = array();

		list($pksP) = $this->getFilteredIds('products');

		// Validate against filters
		if (!isset($pksP))
		{
			// ALLOW all main products - don't use absolute TRUTH'Y here, we must only handle main products
			$where_filter[] = 'a.variant_id = 0';
		}
		elseif (is_array($pksP) && count($pksP) != 0)
		{
			$pids = implode(', ', $db->quote(array_unique($pksP)));

			$where_filter[] = "a.product_id IN ($pids) AND a.variant_id = 0";
		}
		else
		{
			// Suppress all main products, don't add a criteria to be OR'ed
		}

		$multi_variant = $this->helper->config->get('multi_variant', 0);

		if ($multi_variant)
		{
			list($vks_p, $vks_v) = $this->getFilteredIds('variants');

			if (!isset($vks_p))
			{
				// ALLOW any product if the below vv criteria matches
				$where_filter_vp = '1';
			}
			elseif (!is_array($vks_p) || count($vks_p) == 0)
			{
				// Suppress all variants, how??
				$where_filter_vp = '0';
			}
			else
			{
				$pids = implode(', ', $db->quote(array_unique($vks_p)));

				$where_filter_vp = "a.product_id IN ($pids)";
			}

			if (!isset($vks_v))
			{
				// ALLOW all variants if the above vp criteria matches
				$where_filter_vv = '1';
			}
			elseif (!is_array($vks_v) || count($vks_v) == 0)
			{
				// Suppress all variants, how??
				$where_filter_vv = '0';
			}
			else
			{
				$vids = implode(', ', $db->quote(array_unique($vks_v)));

				$where_filter_vv = "a.variant_id IN ($vids)";
			}

			// ALLOW all variants - don't use absolute TRUTH'Y here, we must only handle variants

			if ($where_filter_vp == '0' || $where_filter_vv == '0')
			{
				// We want to suppress all variants, don't add a criteria to be OR'ed
			}
			elseif ($where_filter_vp == '1' && $where_filter_vv == '1')
			{
				// We want to allow all variants irrespective of parent product
				$where_filter[] = 'a.variant_id > 0';
			}
			else
			{
				// If we want to depend on both or the other only (X AND 1 = X :: X AND Y = X AND Y) so just - AND - both criteria
				$where_filter[] = $where_filter_vp . ' AND ' . $where_filter_vv;
			}
		}

		if (count($where_filter))
		{
			$query->where('((' . implode(")\nOR (", $where_filter) . '))');
		}
		else
		{
			// Mock a default FALSE'Y so as to allowing OR'ing allow suppress of all products/variants by filter.
			$query->where('0');
		}

		// Filter by listing type
		$listing_type = $this->state->get('filter.listing_type', 0);

		if ($listing_type > 0 && !$this->helper->config->get('hide_listing_type_filter'))
		{
			$query->where('a.listing_type = ' . (int)$listing_type);
		}

		// Filter by item condition
		$item_condition = $this->state->get('filter.item_condition', 0);

		if ($item_condition > 0 && in_array($listing_type, array(2, 3)) && !$this->helper->config->get('hide_item_condition_filter'))
		{
			$query->where('a.item_condition = ' . (int)$item_condition);
		}

		$uid   = JFactory::getUser()->id;
		$catId = (int) $this->helper->client->getCategory($uid, true);

		$columns = array(
			'price_id'           => 'p.price_id',
			'qty_min'            => 'p.qty_min',
			'qty_max'            => 'p.qty_max',
			'cost_price'         => 'p.cost_price',
			'margin'             => 'p.margin',
			'margin_type'        => 'p.margin_type',
			'list_price'         => 'p.product_list_price',
			'calculated_price'   => 'p.calculated_price',
			'ovr_price'          => 'p.ovr_price',
			'is_fallback'        => 'p.is_fallback',
			'product_price'      => 'p.product_price',
			'client_catid'       => 'p.client_catid',
		);

		$nullDt = $this->_db->getNullDate();
		$now    = JFactory::getDate()->format('Y-m-d');
		$sdate  = '(p.sdate <= ' . $this->_db->q($now) . ' OR p.sdate = ' . $this->_db->q($nullDt) . ')';
		$edate  = '(p.edate >= ' . $this->_db->q($now) . ' OR p.edate = ' . $this->_db->q($nullDt) . ')';

		$query->select($this->_db->quoteName(array_values($columns), array_keys($columns)));

		$query->join('inner', $this->_db->qn('#__sellacious_cache_prices', 'p') . ' ON p.product_id = a.product_id AND p.seller_uid = a.seller_uid')
			// Matched: client_id OR (NULL => ALL) OR (0 = ALL)
			->where('(p.client_catid = ' . $catId . ' OR p.client_catid IS NULL OR p.client_catid = 0)')
			->where('(' . "p.is_fallback = 1 OR ($sdate AND $edate)" . ')');

		if ($this->helper->config->get('hide_zero_priced'))
		{
			$query->where('(p.product_price > 0 OR a.price_display > 0)');
		}

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  List loaded from the listQuery
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.3.0
	 */
	protected function processList($items)
	{
		foreach ($items as &$item)
		{
			$item->categories     = explode(',', $item->category_ids);
			$item->spl_categories = explode(',', $item->spl_category_ids);
			$item->images         = $this->helper->product->getImages($item->id, $item->variant_id, true);
			$item->code           = $this->helper->product->getCode($item->id, $item->variant_id, $item->seller_uid);
			$item->basic_price    = $item->sales_price;

			// DO NOT evaluate shoprules in list views
			$item->tax_amount      = null;
			$item->discount_amount = null;
			$item->sales_price     = max(0, $item->basic_price);

			// Following items should not be used, given for B/C only
			$item->spl_listing_catid  = reset($item->spl_categories);
			$item->spl_listing_params = $this->getListingParams($item->spl_listing_catid);
		}

		return $items;
	}

	/**
	 * Get fully qualified filter list; i.e. all values, available values and selected values included
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.3.0
	 */
	public function getFilters()
	{
		// We only have to get disable/enable choice for custom filters.
		$filters = $this->getCustomFilters();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('field_value, is_json')
			->from('#__sellacious_field_values')
			->group('field_value');

		foreach ($filters as $key => $field)
		{
			list($p_pks) = $this->getFilteredIds('products', "f$field->id");
			list($v_pks, $v_vks) = $this->getFilteredIds('variants', "f$field->id");

			// Filter for products
			$p_where   = array();
			$p_where[] = 'table_name = ' . $db->quote('products');
			$p_where[] = 'field_id = ' . (int) $field->id;

			if (isset($p_pks))
			{
				$p_where[] = count($p_pks) ? 'record_id IN (' . implode(', ', $db->quote($p_pks)) . ')' : '0';
			}

			// Filter for variants
			if (isset($v_pks))
			{
				// Get variant ids from product ids
				$v_pid = count($v_pks) ? 'a.product_id IN (' . implode(', ', $db->quote($v_pks)) . ')' : '0';
				$v_ids = $this->helper->variant->loadColumn(array('list.select' => 'a.id', 'list.where' => $v_pid));

				// Intersect with the in hand variant ids if any
				$v_vks = isset($v_vks) ? array_intersect($v_vks, $v_ids) : $v_ids;
			}

			$v_where   = array();
			$v_where[] = 'table_name = ' . $db->quote('variants');
			$v_where[] = 'field_id = ' . (int) $field->id;

			if (isset($v_vks))
			{
				$v_where[] = count($v_vks) ? 'record_id IN (' . implode(', ', $db->quote($v_vks)) . ')' : '0';
			}

			// Build the query
			$subQueryP = $db->getQuery(true);
			$subQueryP->select('a.product_id')
				->from($db->qn('#__sellacious_product_sellers', 'a'))
				->where('a.state = 1')
				->where('a.stock + a.over_stock > 0');
			$subQueryP->where('a.seller_uid = ' . (int) $this->getState('store.id'));

			$subQueryV = $db->getQuery(true);
			$subQueryV->select('a.variant_id')
				->from($db->qn('#__sellacious_variant_sellers', 'a'))
				->where('a.state = 1')
				->where('a.stock + a.over_stock > 0');
			$subQueryV->where('a.seller_uid = ' . (int) $this->getState('store.id'));

			$query->clear('where')->where('((' . implode(' AND ', $p_where) . ') OR (' . implode(' AND ', $v_where) . '))');
			$query->where("((table_name = 'products' AND record_id IN (" . $subQueryP . ")) OR (table_name = 'variants' AND record_id IN (" . $subQueryV . ')))');

			$available = array();
			$objList   = (array) $db->setQuery($query)->loadObjectList();

			foreach ($objList as $obj)
			{
				$available[] = (array) ($obj->is_json ? json_decode($obj->field_value) : $obj->field_value);
			}

			$field->available = array();

			foreach ($available as $av)
			{
				foreach (array_filter($av, 'strlen') as $avv)
				{
					$field->available[] = $avv;
				}
			}

			$field->available = array_unique($field->available);

			foreach ($field->choices as $chi => $ch)
			{
				$choice = new stdClass;

				$choice->value    = $ch;
				$choice->disabled = !in_array($choice->value, $field->available);
				$choice->selected = !$choice->disabled && in_array($choice->value, $field->selected);

				$field->choices[$chi] = $choice;
			}

			$field->choices = ArrayHelper::sortObjects($field->choices, array('selected', 'disabled'), array(-1, 1));
		}

		return $filters;
	}

	/**
	 * Get a list of products/variants that matches user filter based on category / specifications etc.
	 *
	 * @param   string  $type     Whether to load 'variants' or 'products'
	 * @param   string  $exclude  Filter field id to skip while calculating intersection of filters
	 *
	 * @return  array
	 *
	 * @since   1.3.0
	 */
	public function getFilteredIds($type, $exclude = null)
	{
		$filter_lists = $this->applyFilters();

		$pk_products  = null;
		$pk_variants  = null;

		// If we have any item in the filter criteria list which needs to be excluded, we just unset it here.
		if (isset($exclude) && isset($filter_lists[$exclude]))
		{
			unset($filter_lists[$exclude]);
		}

		$filters = ArrayHelper::getColumn($filter_lists, $type);
		$pids    = ArrayHelper::getColumn($filters, 'product_id');
		$vids    = ArrayHelper::getColumn($filters, 'variant_id');

		// ArrayHelper::getColumn already ignores null values, still checked here for just in case this behavior changes.
		$pids = array_filter($pids, 'is_array');
		$vids = array_filter($vids, 'is_array');

		foreach ($pids as $pid)
		{
			$pk_products = isset($pk_products) ? array_intersect($pk_products, $pid) : $pid;
		}

		foreach ($vids as $vid)
		{
			$pk_variants = isset($pk_variants) ? array_intersect($pk_variants, $vid) : $vid;
		}

		return array($pk_products, $pk_variants);
	}

	/**
	 * Get the special category params for the given special category id
	 *
	 * @param   int  $categoryId  The special category id
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	protected function getListingParams($categoryId)
	{
		static $cache = array();

		if (!isset($cache[$categoryId]))
		{
			$params = '';

			if ($categoryId)
			{
				$filter = array('list.select' => 'a.params', 'id' => (int) $categoryId);
				$params = $this->helper->splCategory->loadResult($filter);
			}

			$cache[$categoryId] = $params;
		}

		return $cache[$categoryId];
	}

	/**
	 * Apply each filter one by one individually without considering any of the others at the same time.
	 * This is supposed to also include basic filters as well as the custom (specifications) filters.
	 *
	 * @return  array
	 *
	 * @since   1.3.0
	 */
	protected function applyFilters()
	{
		static $filtered = null;

		if ($filtered instanceof Registry)
		{
			return $filtered->toArray();
		}

		$filtered = new Registry;

		// Apply each filter and generate items list separately for each of them.
		// Later we'll intersect those lists as needed.
		$pIds = $this->categoryFilter();

		if (isset($pIds))
		{
			$filtered->set("categories.products.product_id", $pIds);
			$filtered->set("categories.products.variant_id", array(0));
			$filtered->set("categories.variants.product_id", $pIds);
			$filtered->set("categories.variants.variant_id", null);
		}

		// Process main products for custom filters.
		$filtersA = $this->getCustomFilters();

		foreach ($filtersA as $field)
		{
			$pIds = $this->specFilter($field, 'products');

			if (isset($pIds))
			{
				$filtered->set("f$field->id.products.product_id", $pIds);
				$filtered->set("f$field->id.products.variant_id", array(0));
			}
		}

		// Process product variants for custom filters.
		$multi_variant = $this->helper->config->get('multi_variant', 0);

		if ($multi_variant)
		{
			$filtersC = $this->getCustomFilters('core');
			$filtersV = $this->getCustomFilters('variant');

			foreach ($filtersC as $field)
			{
				$pIds = $this->specFilter($field, 'products');

				if (isset($pIds))
				{
					$filtered->set("f$field->id.variants.product_id", $pIds);
					$filtered->set("f$field->id.variants.variant_id", null);
				}
			}

			foreach ($filtersV as $field)
			{
				$v_ids = $this->specFilter($field, 'variants');

				if (isset($v_ids))
				{
					$filtered->set("f$field->id.variants.product_id", null);
					$filtered->set("f$field->id.variants.variant_id", $v_ids);
				}
			}
		}

		return $filtered->toArray();
	}

	/**
	 * Get a list of sellers who are capable of shipping to the selected zip code if any
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	protected function isShippableAt()
	{
		$db             = JFactory::getDbo();
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
			$searchOptions  = $types = $this->helper->config->get('shippable_location_search_in', array('country'));

			$filter                 = array();
			$filter['list.select']  = 'a.id';
			$filter['list.where'][] = '(a.title = ' . $db->q($shippableTitle) . ' OR a.iso_code = ' . $db->q($shippableTitle) . ')';
			$filter['list.where'][] = 'a.type IN (' . implode(',', $db->q($searchOptions)) . ')';
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
		$queried  = $this->helper->location->getAncestry($shippableIds, 'A');
		$global   = $this->helper->location->getShipping();
		$global   = array_reduce($global, 'array_merge', array());
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
		$query      = $this->_db->getQuery(true);
		$query->select('COUNT(1)')
			->from($this->_db->qn('#__sellacious_seller_shippable', 'a'))
			->where('(a.gl_id = 0 OR a.gl_id = ' . implode(' OR a.gl_id = ', $queried) . ')')
			->where('a.state = 1');
		$query->where('seller_uid = ' . (int) $this->getState('store.id'));

		$count = $this->_db->setQuery($query)->loadResult();

		return $count > 0;
	}

	/**
	 * Get a list of product ids filtered by selected category and its all sub-categories
	 *
	 * @return  mixed
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	protected function categoryFilter()
	{
		$db     = $this->_db;
		$query  = $db->getQuery(true);
		$cat_id = $this->state->get('filter.category_id', 1);
		$cats   = $this->helper->category->getChildren($cat_id, true);
		$cats[] = 0;

		$query->select('a.product_id')
			->from($db->qn('#__sellacious_product_categories', 'a'))
			// Todo: Show only published categories
			// ->where('c.state = 1')
			->where('a.category_id IN (' . implode(',', $db->quote($cats)) . ')');

		$p_ids = $db->setQuery($query)->loadColumn();

		return $p_ids;
	}

	/**
	 * Apply a single specification field
	 *
	 * @param   stdClass  $field
	 * @param   string    $table_name
	 *
	 * @return  array
	 *
	 * @since   1.3.0
	 */
	protected function specFilter($field, $table_name)
	{
		$values = null;

		if (count($field->selected))
		{
			$query = $this->_db->getQuery(true);
			$where = array();

			// All stored values are json_encoded with string type elements in JSON
			foreach ($field->selected as $sel)
			{
				$valueV = $this->_db->q('%' . $this->_db->escape(strval($sel), true) . '%', false);
				$valueJ = $this->_db->q('%' . $this->_db->escape(json_encode(strval($sel)), true) . '%', false);

				$where[] = '(a.field_value LIKE ' . $valueV . ' AND a.is_json = 0)';
				$where[] = '(a.field_value LIKE ' . $valueJ . ' AND a.is_json = 1)';
			}

			$query->select('a.record_id')
				->from($this->_db->qn('#__sellacious_field_values', 'a'))
				->where('a.table_name = ' . $this->_db->q($table_name))
				->where('a.field_id = ' . (int) $field->id)
				->where('(' . implode(' OR ' , $where) . ')');

			$values = $this->_db->setQuery($query)->loadColumn();
		}

		return $values;
	}

	/**
	 * Get the filter values for the products based on the selected category
	 *
	 * @param   string  $type  The field context in the category, 'core' or 'variant' or null for both
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.3.0
	 */
	protected function getCustomFilters($type = null)
	{
		$cat_id   = $this->state->get('filter.category_id', 1);
		$registry = new Registry($this->state->get('filter.fields'));

		$filterFields = $this->helper->category->getFilterFields($cat_id, $type);

		foreach ($filterFields as $index => &$filterField)
		{
			$filterField->selected = (array) $registry->get("f$filterField->id");
		}

		return $filterFields;
	}
}

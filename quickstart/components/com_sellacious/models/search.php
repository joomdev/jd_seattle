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
 * Methods supporting a list of products.
 *
 * @since   1.5.2
 */
class SellaciousModelSearch extends SellaciousModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$integration = $this->app->getUserStateFromRequest('com_sellacious.search.integration', 'i', 'default', 'string');

		$this->state->set('search.integration', $integration);

		$category = $this->app->input->get('category', 0, 'int');

		$this->state->set('search.category', $category);

		$parentCategory = $this->app->input->get('parent_category', 0, 'int');

		$this->state->set('search.parent_category', $parentCategory);

		$seller = $this->app->input->get('seller', 0, 'int');

		$this->state->set('search.seller', $seller);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		$uid = JFactory::getUser()->id;

		$id .= ':' . $this->helper->client->getCategory($uid, true);
		$id .= ':' . (int) $this->helper->config->get('hide_out_of_stock');
		$id .= ':' . (int) $this->helper->config->get('multi_variant', 0);
		$id .= ':' . (int) $this->helper->config->get('hide_zero_priced');
		$id .= ':' . $this->getState('search.integration');
		$id .= ':' . $this->getState('filter.query');
		$id .= ':' . $this->getState('list.start', 0);
		$id .= ':' . $this->getState('list.limit', 20);

		return parent::getStoreId($id);
	}

	/**
	 * Method to build the list query.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   1.5.2
	 */
	protected function getListQuery()
	{
		$query = $this->getItemsQuery();

		if ($this->helper->config->get('hide_out_of_stock'))
		{
			$query->where('a.stock + a.over_stock > 0');
		}

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
			$cond[] = 'a.tags LIKE ' . $kw;

			foreach ($fFields as $fid)
			{
				$cond[] = "s.spec_$fid LIKE " . $kw;
			}

			$query->where('(' . implode(' OR ', $cond) . ')');
		}

		$category = $this->getState('search.category');

		if ($category)
		{
			$query->where('FIND_IN_SET(' . $category . ', a.category_ids)');
		}

		$parentCategory = $this->getState('search.parent_category');

		if ($parentCategory)
		{
			$childCategories = $this->helper->category->getChildren($parentCategory, true);
			$whereCondition  = array();

			foreach ($childCategories as $childCategory)
			{
				$whereCondition[] = 'FIND_IN_SET(' . $childCategory . ', a.category_ids)';
			}

			$query->where('(' . implode(' OR ', $whereCondition) . ')');
		}

		$seller = $this->getState('search.seller');

		if ($seller)
		{
			$query->where('a.seller_uid = ' . $seller);
		}

		$query->select('a.variant_price, a.variant_price + p.product_price AS basic_price, a.sales_price');

		$multiVariant    = $this->helper->config->get('multi_variant', 0);
		$variantSeparate = $multiVariant == 2;

		$grouping = array('a.product_id');

		if ($multiVariant && $variantSeparate)
		{
			$grouping[] = 'a.variant_id';
		}

		$query->group($grouping);

		$order = array(
			'a.price_display ASC',
			'p.product_price = 0 ASC',
		);
		$query->order($order);

		$query->order('a.spl_category_ids = ' . $this->_db->q('') . ' ASC');
		$query->order('a.price_display ASC');
		$query->order('a.sales_price = 0 ASC');
		$query->order('p.is_fallback ASC');
		$query->order('a.sales_price * forex_rate ASC');
		$query->order('a.stock DESC');

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onAfterBuildQuery', array('com_sellacious.model.search', &$query));

		return $query;
	}

	/**
	 * Query for products and variants
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.5.2
	 */
	protected function getItemsQuery()
	{
		$db      = $this->getDbo();
		$query   = $db->getQuery(true);
		$columns = array(
			'id'                  => 'a.product_id',
			'code'                => 'a.code',
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
			'seller_email'        => 'a.seller_email',
			'seller_mobile'       => 'a.seller_mobile',
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
			->where('a.listing_active = 1')
			->where('a.product_active = 1')
			->where('a.seller_active = 1');

		$uid    = JFactory::getUser()->id;
		$catId  = $this->helper->client->getCategory($uid, true);

		$nullDt = $this->_db->getNullDate();
		$now    = JFactory::getDate()->format('Y-m-d');
		$sdate  = '(pr.sdate <= ' . $this->_db->q($now) . ' OR pr.sdate = ' . $this->_db->q($nullDt) . ')';
		$edate  = '(pr.edate >= ' . $this->_db->q($now) . ' OR pr.edate = ' . $this->_db->q($nullDt) . ')';

		$sub = $db->getQuery(true);
		$sub->select('pr.*')
			->from($this->_db->qn('#__sellacious_cache_prices', 'pr'))
			// Matched: client_id OR (NULL => ALL) OR (0 = ALL)
			->where('(pr.client_catid = ' . $catId . ' OR pr.client_catid IS NULL OR pr.client_catid = 0)')
			->where('(' . "pr.is_fallback = 1 OR ($sdate AND $edate)" . ')')
			->order('pr.product_price = 0 ASC, pr.is_fallback ASC, pr.product_price ASC');

		$columns = array(
			'price_id'         => 'p.price_id',
			'qty_min'          => 'p.qty_min',
			'qty_max'          => 'p.qty_max',
			'cost_price'       => 'p.cost_price',
			'margin'           => 'p.margin',
			'margin_type'      => 'p.margin_type',
			'list_price'       => 'p.product_list_price',
			'calculated_price' => 'p.calculated_price',
			'ovr_price'        => 'p.ovr_price',
			'is_fallback'      => 'p.is_fallback',
			'product_price'    => 'p.product_price',
			'client_catid'     => 'p.client_catid',
		);

		$query->select($this->_db->quoteName(array_values($columns), array_keys($columns)));

		$query->join('left', "($sub) AS p " . ' ON p.product_id = a.product_id AND p.seller_uid = a.seller_uid');

		if ($this->helper->config->get('hide_zero_priced'))
		{
			$query->where('(p.product_price > 0 OR a.price_display > 0)');
		}

		return $query;
	}
}

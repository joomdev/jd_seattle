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
namespace Sellacious\Cache;

defined('_JEXEC') or die;

use Sellacious\Cache;

/**
 * Sellacious Prices Cache Object.
 *
 * @since  1.5.0
 */
class Prices extends Cache
{
	/**
	 * Generate cache for prices for the product/variant + seller
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function build()
	{
		// Purge existing
		$this->db->truncateTable('#__sellacious_cache_prices');

		// Rebuild cache
		$query   = $this->getQuery();
		$columns = $this->getColumns();

		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));

		$insert  = sprintf(
			'INSERT INTO %1$s (%2$s) %3$s;',
			$this->db->quoteName('#__sellacious_cache_prices'),
			implode(', ', $this->db->quoteName(array_keys($columns))),
			$query
		);

		$this->db->setQuery($insert)->execute();

		$this->updateCurrency();

		$this->updateSalesPrice();
	}

	/**
	 * Build the cache for price for given filter keys
	 *
	 * @param   string     $key     The table that needs to be updated in the cache
	 * @param   int|int[]  $values  The ids of the records that were updated in the said table
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function refresh($key, $values)
	{
		if ($key == 'products')
		{
			// Clear expired cache
			$pks   = array_map('intval', (array) $values);
			$query = $this->db->getQuery(true);
			$query->delete($this->db->quoteName('#__sellacious_cache_prices'))->where('product_id IN (' . implode(', ', $pks) . ')');
			$this->db->setQuery($query)->execute();

			// Build specific cache afresh
			$query   = $this->getQuery();
			$columns = $this->getColumns();

			$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));
			$query->where('pp.product_id IN (' . implode(', ', $pks) . ')');

			$insert  = sprintf(
				'INSERT INTO %1$s (%2$s) %3$s;',
				$this->db->quoteName('#__sellacious_cache_prices'),
				implode(', ', $this->db->quoteName(array_keys($columns))),
				$query
			);

			$this->db->setQuery($insert)->execute();
			$this->updateCurrency();
			$this->updateSalesPrice();
		}
		elseif ($key == 'prices')
		{
			// Clear expired cache
			$pks   = array_map('intval', (array) $values);
			$query = $this->db->getQuery(true);
			$query->delete($this->db->quoteName('#__sellacious_cache_prices'))->where('price_id IN (' . implode(', ', $pks) . ')');
			$this->db->setQuery($query)->execute();

			// Build specific cache afresh
			$query   = $this->getQuery();
			$columns = $this->getColumns();

			$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));
			$query->where('pp.id IN (' . implode(', ', $pks) . ')');

			$insert  = sprintf(
				'INSERT INTO %1$s (%2$s) %3$s;',
				$this->db->quoteName('#__sellacious_cache_prices'),
				implode(', ', $this->db->quoteName(array_keys($columns))),
				$query
			);

			$this->db->setQuery($insert)->execute();
			$this->updateCurrency();
			$this->updateSalesPrice();
		}
	}

	/**
	 * Get the main query for the cache
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.5.0
	 */
	protected function getQuery()
	{
		$query = $this->db->getQuery(true);

		$query->from($this->db->quoteName('#__sellacious_product_prices', 'pp'))->where('pp.state = 1');

		if (!$this->helper->config->get('multi_seller'))
		{
			$query->where('pp.seller_uid = ' . (int) $this->helper->config->get('default_seller'));
		}

		$query->join('left', $this->db->quoteName('#__sellacious_sellers', 's') . ' ON s.user_id = pp.seller_uid');
		$query->join('left', $this->db->quoteName('#__sellacious_productprices_clientcategory_xref', 'pcx') . ' ON pcx.product_price_id = pp.id');

		return $query;
	}

	/**
	 * Get the main columns for the products cache
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getColumns()
	{
		// Insert Product prices
		$columns = array(
			'price_id'         => 'pp.id',
			'product_id'       => 'pp.product_id',
			'seller_uid'       => 'pp.seller_uid',
			'qty_min'          => 'pp.qty_min',
			'qty_max'          => 'pp.qty_max',
			'cost_price'       => 'pp.cost_price',
			'margin'           => 'pp.margin',
			'margin_type'      => 'pp.margin_type',
			'list_price'       => 'pp.list_price',
			'calculated_price' => 'pp.calculated_price',
			'ovr_price'        => 'pp.ovr_price',
			'product_price'    => 'pp.product_price',
			'is_fallback'      => 'pp.is_fallback',
			'sdate'            => 'pp.sdate',
			'edate'            => 'pp.edate',
			'client_catid'     => 'pcx.cat_id',
			'currency'         => 's.currency',
		);

		return $columns;
	}

	/**
	 * Update the seller currency in cache
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function updateCurrency()
	{
		$query      = $this->db->getQuery(true);
		$g_currency = $this->helper->currency->getGlobal('code_3');

		$query->update($this->db->quoteName('#__sellacious_cache_prices', 'a'))
			->set('a.currency = ' . $this->db->quote($g_currency));

		// Update all if listing currency is disabled
		if ($this->helper->config->get('listing_currency'))
		{
			$query->where('a.currency = ' . $this->db->quote(''));
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update sales price in cache
	 *
	 * @param   int|int[]  $values  The ids of the records that were updated in the said table
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.6.0
	 */
	protected function updateSalesPrice($values = null)
	{
		$pks = $values ? array_map('intval', (array) $values) : array();

		// Get Products from cache
		$query = $this->db->getQuery(true);

		$query->select('0 as variant_id, a.*, a.product_price as basic_price');
		$query->from($this->db->quoteName('#__sellacious_cache_prices', 'a'));

		$query->where('a.is_fallback = 1');

		if (!empty($pks))
		{
			$query->where('a.product_id IN (' . implode(', ', $pks) . ')');
		}

		$this->db->setQuery($query);

		$prices = $this->db->loadObjectList();

		foreach ($prices as $price)
		{
			if (!$price->product_id)
			{
				continue;
			}

			// Apply shop rules on product
			$this->helper->shopRule->toProduct($price, false, true);

			// update sales price
			$query = $this->db->getQuery(true);

			$query->update($this->db->quoteName('#__sellacious_cache_prices', 'a'))
				->set('a.sales_price = ' . $price->sales_price);

			$query->where('a.product_id = ' . $price->product_id);
			$query->where('a.seller_uid = ' . $price->seller_uid);
			$query->where('a.is_fallback = 1');

			if (abs($price->list_price) >= 0.01)
			{
				// Apply shop rules on list price also
				$query->set('a.product_list_price = ' . $price->list_price_final);
			}

			$this->db->setQuery($query)->execute();
		}
	}
}

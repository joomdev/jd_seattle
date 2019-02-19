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
 * Sellacious Products Cache Object.
 *
 * @since  1.5.0
 */
class Products extends Cache
{
	/**
	 * Build the cache for main product/variant items with type attributes and seller attributes wherever applicable.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function build()
	{
		// Purge Existing
		$this->db->truncateTable('#__sellacious_cache_products');

		// Columns List and list query
		$columns = $this->getColumns();
		$query   = $this->getQuery();

		// Product Cache
		$this->productsCache(clone $query, $columns);

		// Variant Cache
		$this->variantsCache(clone $query, $columns);

		// Extended Properties
		$this->itemsAttributesCache();

		// Reset expired - not expired
		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__sellacious_cache_products'))->set('state = 1')->where('state = 0');
		$this->db->setQuery($query)->execute();
	}

	/**
	 * Build the cache for product/variant for given filter keys
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
			$query->delete($this->db->quoteName('#__sellacious_cache_products'))
				->where('product_id IN (' . implode(', ', $pks) . ')');
			$this->db->setQuery($query)->execute();

			// Build specific cache afresh
			$columns = $this->getColumns();
			$query   = $this->getQuery();
			$query->where('a.id IN (' . implode(', ', $pks) . ')');

			$this->productsCache(clone $query, $columns);
			$this->variantsCache(clone $query, $columns);
			$this->itemsAttributesCache(true);
		}
		elseif ($key == 'productratings')
		{
			$this->updateProductRating(false, $values);
		}
		elseif ($key == 'variants')
		{
			// Clear expired cache
			$pks   = array_map('intval', (array) $values);
			$query = $this->db->getQuery(true);
			$query->delete($this->db->quoteName('#__sellacious_cache_products'))
				->where('variant_id IN (' . implode(', ', $pks) . ')');

			$this->db->setQuery($query)->execute();

			// Build specific cache afresh
			$columns = $this->getColumns();
			$query   = $this->getQuery();
			$query->where('v.id IN (' . implode(', ', $pks) . ')');

			$this->productsCache(clone $query, $columns);
			$this->variantsCache(clone $query, $columns);
			$this->itemsAttributesCache(true);
		}
		elseif ($key == 'users')
		{
			// Mark expired cache
			$pks   = array_map('intval', (array) $values);
			$query = $this->db->getQuery(true);

			$query->update($this->db->quoteName('#__sellacious_cache_products'))
				->set('state = 0')
				->where('manufacturer_id IN (' . implode(', ', $pks) . ')', ('OR'))
				->where('seller_uid IN (' . implode(', ', $pks) . ')');
			$this->db->setQuery($query)->execute();

			// Update expired cache
			$this->updateManufacturer(true);
			$this->updateSeller(true);
		}
		elseif ($key == 'prices')
		{
			// Mark expired cache
			$pks      = array_map('intval', (array) $values);
			$iterator = $this->helper->price->getIterator(array('list.select' => 'a.product_id, a.seller_uid', 'id' => $pks));

			foreach ($iterator as $obj)
			{
				$obj->state = 0;

				$this->db->updateObject('#__sellacious_cache_products', $obj, array('product_id', 'seller_uid'));
			}

			// Update expired cache
			$this->updateDefaultPrice(true);
			$this->updateAdvancedPrices(true);
		}
		elseif ($key == 'psx')
		{
			// Mark expired cache
			$pks      = array_map('intval', (array) $values);
			$filter   = array('list.select' => 'a.product_id, a.seller_uid', 'list.from' => '#__sellacious_product_sellers', 'id' => $pks);
			$iterator = $this->helper->product->getIterator($filter);

			foreach ($iterator as $obj)
			{
				$obj->state = 0;

				$this->db->updateObject('#__sellacious_cache_products', $obj, array('product_id', 'seller_uid'));
			}

			// Update expired cache
			$this->updatePsxAttributes(true);
		}
		else
		{
			return;
		}

		// Reset expired - not expired
		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__sellacious_cache_products'))->set('state = 1')->where('state = 0');
		$this->db->setQuery($query)->execute();
	}

	/**
	 * Build the cache for main products
	 *
	 * @param   \JDatabaseQuery  $query    The base products query
	 * @param   array            $columns  The columns for base products
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function productsCache($query, $columns)
	{
		// Columns are not already in the select statement
		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));

		$query->group('a.id, psx.seller_uid');

		$insert = sprintf(
			'INSERT INTO %1$s (%2$s) %3$s;',
			$this->db->quoteName('#__sellacious_cache_products'),
			implode(', ', $this->db->quoteName(array_keys($columns))),
			$query
		);

		$this->db->setQuery($insert)->execute();
	}

	/**
	 * Build the cache for variants
	 *
	 * @param   \JDatabaseQuery  $query    The base products query
	 * @param   array            $columns  The columns for base products
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function variantsCache($query, $columns)
	{
		if (!$this->helper->config->get('multi_variant', 0))
		{
			return;
		}

		$columns = array_merge($columns, array(
			'variant_id'             => 'v.id',
			'variant_title'          => 'v.title',
			'variant_alias'          => 'v.alias',
			'variant_sku'            => 'v.local_sku',
			'variant_description'    => 'v.description',
			'variant_features'       => 'v.features',
			'variant_active'         => 'v.state',
			'vsx_id'                 => 'vsx.id',
			'variant_price_mod'      => 'vsx.price_mod',
			'variant_price_mod_perc' => 'vsx.price_mod_perc',
			'stock'                  => 'vsx.stock',
			'over_stock'             => 'vsx.over_stock',
			'stock_reserved'         => 'vsx.stock_reserved',
			'stock_sold'             => 'vsx.stock_sold',
			'is_selling_variant'     => 'vsx.state',
		));

		// Columns are not already in the select statement
		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));

		$query->group('a.id, psx.seller_uid, v.id');

		// Variant
		$query->join('left', $this->db->quoteName('#__sellacious_variants', 'v') . ' ON v.product_id = a.id')
			->where('v.id IS NOT NULL');

		// Variant Sellers
		$query->join('left', $this->db->quoteName('#__sellacious_variant_sellers', 'vsx') . ' ON vsx.variant_id = v.id AND vsx.seller_uid = psx.seller_uid');

		$insert = sprintf(
			'INSERT INTO %1$s (%2$s) %3$s;',
			$this->db->quoteName('#__sellacious_cache_products'),
			implode(', ', $this->db->quoteName(array_keys($columns))),
			$query
		);

		$this->db->setQuery($insert)->execute();
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
		$multi_seller  = $this->helper->config->get('multi_seller', 0);
		$allowed       = $this->helper->config->get('allowed_product_type');
		$allow_package = $this->helper->config->get('allowed_product_package');

		$now    = \JFactory::getDate()->toSql();
		$nullDt = $this->db->getNullDate();
		$query  = $this->db->getQuery(true);

		// Product
		$query->from($this->db->quoteName('#__sellacious_products', 'a'))
			->where('(a.state = 0 OR a.state = 1)');

		// Product Sellers
		$query->join('left', $this->db->quoteName('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = a.id');

		if (!$multi_seller)
		{
			$default_seller = $this->helper->config->get('default_seller', 0);

			$query->where('psx.seller_uid = ' . (int) $default_seller);
		}

		$allowed = $allowed == 'both' ? array('physical', 'electronic') : array($allowed);

		if ($allow_package)
		{
			$allowed[] = 'package';
		}

		$query->where('(a.type = ' . implode(' OR a.type = ', $this->db->quote($allowed)) . ')');

		// Listing
		$conditions = array(
			'l.product_id = a.id',
			'l.seller_uid = psx.seller_uid',
			'l.publish_up != ' . $this->db->q($nullDt),
			'l.publish_up <= ' . $this->db->q($now),
			'l.publish_down != ' . $this->db->q($nullDt),
			'l.publish_down > ' . $this->db->q($now),
			'l.category_id = 0',
			'l.state = 1',
		);

		$query->join('left', $this->db->quoteName('#__sellacious_seller_listing', 'l') . ' ON ' . implode(' AND ', $conditions));

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
		static $columns;

		if (!$columns)
		{
			$free_listing = $this->helper->config->get('free_listing');

			$columns = array(
				'product_id'          => 'a.id',
				'product_title'       => 'a.title',
				'product_alias'       => 'a.alias',
				'product_type'        => 'a.type',
				'product_sku'         => 'a.local_sku',
				'manufacturer_sku'    => 'a.manufacturer_sku',
				'manufacturer_id'     => 'a.manufacturer_id',
				'product_features'    => 'a.features',
				'product_introtext'   => 'a.introtext',
				'product_description' => 'a.description',
				'product_active'      => 'a.state',
				'metakey'             => 'a.metakey',
				'metadesc'            => 'a.metadesc',
				'primary_video_url'   => 'a.primary_video_url',
				'product_location'    => 'a.location',
				'tags'                => 'a.tags',
				'owner_uid'           => 'a.owned_by',
				'psx_id'              => 'psx.id',
				'is_selling'          => 'psx.state',
				'seller_uid'          => 'psx.seller_uid',
				'price_display'       => 'psx.price_display',
				'quantity_min'        => 'psx.quantity_min',
				'quantity_max'        => 'psx.quantity_max',
				'stock'               => 'psx.stock',
				'over_stock'          => 'psx.over_stock',
				'stock_reserved'      => 'psx.stock_reserved',
				'stock_sold'          => 'psx.stock_sold',
				'listing_purchased'   => 'l.subscription_date',
				'listing_start'       => $free_listing ? 'a.created' : 'l.publish_up',
				'listing_end'         => 'l.publish_down',
				'listing_active'      => 'l.state',
				'language'            => 'a.language',
			);
		}

		return $columns;
	}

	/**
	 * Add extended attributes to the cached items in the cache table
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function itemsAttributesCache($state = false)
	{
		/**
		 * Order of following methods is important due to dependency chain.
		 */
		$this->updateManufacturer($state);

		$this->updateSeller($state);

		$this->updateForex($state);

		$this->updateCategories($state);

		$this->updateSpecialCategories($state);

		$this->updateSpecFields();

		$this->updateVariantCount($state);

		$this->updateSellerCount($state);

		$this->updateDefaultPrice($state);

		$this->updateVariantPrice($state);

		$this->updateSalesPrice($state);

		$this->updateAdvancedPrices($state);

		$this->updateOrderStats($state);

		$this->updateProductRating($state);

		$this->updateProductCode($state);

		$allowed      = $this->helper->config->get('allowed_product_type');
		$allowPackage = $this->helper->config->get('allowed_product_package');
		$allowedTypes = ($allowed == 'physical' || $allowed == 'electronic') ? array($allowed) : array('physical', 'electronic');

		if ($allowPackage)
		{
			$allowedTypes[] = 'package';
		}

		$this->updateTypeAttributes($allowedTypes, $state);

		$this->updateTypeAttributeSeller($allowedTypes, $state);

		$this->updateListing($state);
	}

	/**
	 * Update manufacturer info
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateManufacturer($state = false)
	{
		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.manufacturer_name = u.name')
			->set('a.manufacturer_username = u.username')
			->set('a.manufacturer_email = u.email')
			->set('a.manufacturer_company = m.title')
			->set('a.manufacturer_catid = m.category_id')
			->set('a.manufacturer_code = m.code');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$query->join('left', $this->db->quoteName('#__sellacious_manufacturers', 'm') . ' ON m.user_id = a.manufacturer_id');
		$query->join('left', $this->db->quoteName('#__users', 'u') . ' ON u.id = a.manufacturer_id');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update Seller info
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateSeller($state = false)
	{
		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.seller_name = u.name')
			->set('a.seller_username = u.username')
			->set('a.seller_email = u.email')
			->set('a.seller_company = s.title')
			->set('a.seller_catid = s.category_id')
			->set('a.seller_code = s.code')
			->set('a.seller_store = s.store_name')
			->set('a.store_address = s.store_address')
			->set('a.store_location = s.store_location')
			->set('a.seller_currency = s.currency')
			->set('a.seller_commission = s.commission')
			->set('a.seller_mobile = p.mobile')
			->set('a.seller_website = p.website')
			->set('a.seller_active = (s.state = 1 AND u.block = 0)');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$query->join('left', $this->db->quoteName('#__sellacious_sellers', 's') . ' ON s.user_id = a.seller_uid');
		$query->join('left', $this->db->quoteName('#__sellacious_profiles', 'p') . ' ON p.user_id = a.seller_uid');
		$query->join('left', $this->db->quoteName('#__users', 'u') . ' ON u.id = a.seller_uid');

		$this->db->setQuery($query)->execute();

		// Default seller currency
		$query = $this->db->getQuery(true);

		$g_currency = $this->helper->currency->getGlobal('code_3');

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.seller_currency = ' . $this->db->quote($g_currency));

		// Update all if listing currency is disabled
		if ($this->helper->config->get('listing_currency'))
		{
			$query->where('a.seller_currency = ' . $this->db->quote(''));
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update Forex for all seller currency
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateForex($state = false)
	{
		$query = $this->db->getQuery(true);

		$query->select('a.seller_currency')
			->from($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->group('a.seller_currency');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$iterator   = $this->db->setQuery($query)->getIterator();
		$g_currency = $this->helper->currency->getGlobal('code_3');

		foreach ($iterator as $cObj)
		{
			$cObj->forex_rate = $this->helper->currency->getRate($cObj->seller_currency, $g_currency);

			$this->db->updateObject('#__sellacious_cache_products', $cObj, array('seller_currency'));
		}
	}

	/**
	 * Update categories assignments
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateCategories($state = false)
	{
		$query = $this->db->getQuery(true);
		$subQ  = $this->db->getQuery(true);

		$subQ->select('pc.product_id')
			->select("GROUP_CONCAT(c.id ORDER BY c.lft SEPARATOR ',')" . " AS category_ids")
			->select("GROUP_CONCAT(c.title ORDER BY c.lft SEPARATOR '|:|')" . " AS category_titles")
			->from($this->db->quoteName('#__sellacious_categories', 'c'))
			->join('inner', $this->db->quoteName('#__sellacious_product_categories', 'pc') . ' ON c.id = pc.category_id')
			->group('pc.product_id');

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.category_ids = cc.category_ids')
			->set('a.category_titles = cc.category_titles')
			->join('inner', '(' . $subQ . ') AS cc' . ' ON cc.product_id = a.product_id');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update Special categories assignments
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateSpecialCategories($state = false)
	{
		$query  = $this->db->getQuery(true);
		$subQ   = $this->db->getQuery(true);
		$nullDt = $this->db->getNullDate();
		$now    = \JFactory::getDate()->toSql();

		$conditions = array(
			'l.category_id = c.id',
			'l.category_id > 0',
			'l.publish_up != ' . $this->db->q($nullDt),
			'l.publish_up <= ' . $this->db->q($now),
			'l.publish_down != ' . $this->db->q($nullDt),
			'l.publish_down > ' . $this->db->q($now),
			'l.state = 1',
		);

		$subQ->select('l.seller_uid, l.product_id')
			->select("GROUP_CONCAT(c.id ORDER BY c.lft SEPARATOR ',')" . " AS spl_category_ids")
			->select("GROUP_CONCAT(c.title ORDER BY c.lft SEPARATOR '|:|')" . " AS spl_category_titles")
			->from($this->db->quoteName('#__sellacious_splcategories', 'c'))
			->join('inner', $this->db->quoteName('#__sellacious_seller_listing', 'l') . ' ON ' . implode(' AND ', $conditions))
			->group('l.seller_uid, l.product_id');

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.spl_category_ids = sc.spl_category_ids')
			->set('a.spl_category_titles = sc.spl_category_titles')
			->join('inner', '(' . $subQ . ') AS sc' . ' ON sc.product_id = a.product_id AND sc.seller_uid = a.seller_uid');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update specification fields list based on existing categories assignments
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateSpecFields()
	{
		$cores    = array();
		$variants = array();

		$cQuery = $this->db->getQuery(true);
		$cQuery->select('a.id, a.parent_id, a.core_fields, a.variant_fields')
			->from($this->db->quoteName('#__sellacious_categories', 'a'))
			->order('a.lft');

		$cIterator = $this->db->setQuery($cQuery)->getIterator();

		foreach ($cIterator as $category)
		{
			$cores[$category->id]    = array_map('intval', json_decode($category->core_fields, true) ?: array());
			$variants[$category->id] = array_map('intval', json_decode($category->variant_fields, true) ?: array());

			/**
			 * Since we order by 'lft' all parent categories get processed before their children,
			 * So it is safe to merge with the parent fields categories directly.
			 */
			if (isset($cores[$category->parent_id]))
			{
				$cores[$category->id] = array_merge($cores[$category->id], $cores[$category->parent_id]);
			}

			if (isset($variants[$category->parent_id]))
			{
				$variants[$category->id] = array_merge($variants[$category->id], $variants[$category->parent_id]);
			}
		}

		$tQuery = $this->db->getQuery(true);
		$tQuery->select('DISTINCT a.category_ids')
			->from($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->where('a.category_ids != ' . $this->db->q(''));

		$iterator = $this->db->setQuery($tQuery)->getIterator();

		foreach ($iterator as $item)
		{
			$fieldsC = array();
			$fieldsV = array();
			$catIds  = explode(',', $item->category_ids);

			foreach ($catIds as $id)
			{
				if (isset($cores[$id]))
				{
					$fieldsC = array_merge($fieldsC, $cores[$id]);
				}

				if (isset($variants[$id]))
				{
					$fieldsV = array_merge($fieldsV, $variants[$id]);
				}
			}

			// If something is in variant fields, it must not be in core fields.
			$item->core_fields    = json_encode(array_values(array_unique(array_diff($fieldsC, $fieldsV))));
			$item->variant_fields = json_encode(array_values(array_unique($fieldsV)));

			$this->db->updateObject('#__sellacious_cache_products', $item, array('category_ids'));
		}
	}

	/**
	 * Update variant count for each product
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateVariantCount($state = false)
	{
		if ($this->helper->config->get('multi_variant', 0))
		{
			$query = $this->db->getQuery(true);
			$subQ  = $this->db->getQuery(true);

			$subQ->select('v.product_id, COUNT(v.id) variant_count')
				->from($this->db->quoteName('#__sellacious_variants', 'v'))
				->where('v.state = 1')
				->group('v.product_id');

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.variant_count = vc.variant_count')
				->join('inner', '(' . $subQ . ') AS vc' . ' ON vc.product_id = a.product_id');

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update seller count for each product
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateSellerCount($state = false)
	{
		if ($this->helper->config->get('multi_seller', 0))
		{
			$query = $this->db->getQuery(true);
			$subQ  = $this->db->getQuery(true);

			$subQ->select('psx.product_id, COUNT(psx.seller_uid) seller_count')
				->from($this->db->quoteName('#__sellacious_product_sellers', 'psx'))
				->where('psx.state = 1')
				->group('psx.product_id');

			$subQ->join('left', $this->db->quoteName('#__sellacious_sellers', 's') . ' ON s.user_id = psx.seller_uid')->where('s.state = 1');
			$subQ->join('left', $this->db->quoteName('#__users', 'u') . ' ON u.id = psx.seller_uid')->where('u.block = 0');

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.seller_count = sc.seller_count')
				->join('inner', '(' . $subQ . ') AS sc' . ' ON sc.product_id = a.product_id');

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update default price for each item
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateDefaultPrice($state = false)
	{
		$pricing_model = $this->helper->config->get('pricing_model');

		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.product_price = ' . ($pricing_model == 'flat' ? 'p.ovr_price' : 'p.product_price'))
			->set('a.multi_price = 0');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$query->join('LEFT', $this->db->quoteName('#__sellacious_product_prices', 'p') . ' ON p.product_id = a.product_id AND p.seller_uid = a.seller_uid AND p.is_fallback = 1');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update variant price for each item
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	protected function updateVariantPrice($state = false)
	{
		$variantPrice = 'IF(a.variant_price_mod_perc, p.product_price * a.variant_price_mod / 100.0, a.variant_price_mod)';

		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.variant_price = ' . $variantPrice);

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$query->join('LEFT', $this->db->quoteName('#__sellacious_product_prices', 'p') . ' ON p.product_id = a.product_id AND p.seller_uid = a.seller_uid AND p.is_fallback = 1');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update sales price for each item
	 *
	 * @param   bool       $state  Whether to update items only with expired cache
	 * @param   int|int[]  $values  The ids of the records that were updated in the said table
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	protected function updateSalesPrice($state = false, $values = null)
	{
		$pks = $values ? array_map('intval', (array) $values) : array();

		// Get Products from cache
		$query = $this->db->getQuery(true);

		$query->select('a.product_id, a.variant_id, a.seller_uid, p.*, a.product_price + a.variant_price as basic_price');
		$query->from($this->db->quoteName('#__sellacious_cache_products', 'a'));
		$query->join('LEFT', $this->db->quoteName('#__sellacious_product_prices', 'p') . ' ON p.product_id = a.product_id AND p.seller_uid = a.seller_uid AND p.is_fallback = 1');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		if (!empty($pks))
		{
			$query->where('a.product_id IN (' . implode(', ', $pks) . ')');
		}

		$this->db->setQuery($query);

		$products = $this->db->loadObjectList();

		foreach ($products as $product)
		{
			if (!$product->id)
			{
				continue;
			}

			// Apply shop rules on product
			$this->helper->shopRule->toProduct($product, false, true);

			// update sales price
			$query = $this->db->getQuery(true);

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.sales_price = ' . $product->sales_price);

			$query->where('a.product_id = ' . $product->product_id);
			$query->where('a.variant_id = ' . $product->variant_id);
			$query->where('a.seller_uid = ' . $product->seller_uid);

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update multi-prices count if applicable
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateAdvancedPrices($state = false)
	{
		$pricing_model = $this->helper->config->get('pricing_model');

		if ($pricing_model == 'advance')
		{
			$query = $this->db->getQuery(true);
			$subQ  = $this->db->getQuery(true);

			$subQ->select('p.product_id, p.seller_uid, COUNT(p.id) multi_price')
				->from($this->db->quoteName('#__sellacious_product_prices', 'p'))
				->where('p.is_fallback = 0')
				->where('p.state = 1')
				->group('p.product_id, p.seller_uid');

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.multi_price = mp.multi_price')
				->join('inner', '(' . $subQ . ') AS mp' . ' ON mp.product_id = a.product_id');

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update order count and order units statistics
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateOrderStats($state = false)
	{
		$query = $this->db->getQuery(true);
		$subQ  = $this->db->getQuery(true);

		$subQ->select('o.product_id, o.variant_id, o.seller_uid, COUNT(o.id) order_count, SUM(o.quantity) order_units')
			->from($this->db->quoteName('#__sellacious_order_items', 'o'))
			->group('o.product_id, o.variant_id, o.seller_uid');

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.order_count = os.order_count')
			->set('a.order_units = os.order_units')
			->join('inner', '(' . $subQ . ') AS os' . ' ON os.product_id = a.product_id AND os.variant_id = a.variant_id AND os.seller_uid = a.seller_uid');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update product rating
	 *
	 * @param   bool      $state  Whether to update items only with expired cache
	 * @param   int|int[] $values The product codes
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateProductRating($state = false, $values = null)
	{
		$query = $this->db->getQuery(true);
		$subQ  = $this->db->getQuery(true);

		$subQ->select(array('r.product_id', 'COUNT(r.rating) AS rating_count', 'SUM(r.rating) AS rating_total'))
			->from($this->db->qn('#__sellacious_ratings', 'r'))
			->where('r.type = ' . $this->db->q('product'))
			->where('r.state = 1')
			->where('r.rating > 0')
			->group('r.product_id');

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.product_rating = rc.rating_total / rc.rating_count')
			->join('inner', '(' . $subQ . ') AS rc' . ' ON rc.product_id = a.product_id AND rc.rating_count > 0');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		if (!empty($values))
		{
			$values = array_map('strval', (array) $values);
			$query->where('a.product_id IN (' . implode(', ', $values) . ')');
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update product item code for all product items
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateProductCode($state = false)
	{
		$query = $this->db->getQuery(true);

		$query->select('a.product_id, a.variant_id, a.seller_uid')
			->from($this->db->quoteName('#__sellacious_cache_products', 'a'));

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$iterator = $this->db->setQuery($query)->getIterator();

		foreach ($iterator as $cObj)
		{
			$cObj->code = $this->helper->product->getCode($cObj->product_id, $cObj->variant_id, $cObj->seller_uid);

			$this->db->updateObject('#__sellacious_cache_products', $cObj, array('product_id', 'variant_id', 'seller_uid'));
		}
	}

	/**
	 * Update type attributes for each product
	 *
	 * @param   string[]  $allowed  The list of product types which are allowed in the global configuration
	 * @param   bool      $state    Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateTypeAttributes($allowed, $state = false)
	{
		if (in_array('physical', $allowed))
		{
			$query = $this->db->getQuery(true);

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.length = ba.length')
				->set('a.width = ba.width')
				->set('a.height = ba.height')
				->set('a.weight = ba.weight')
				->set('a.vol_weight = ba.vol_weight')
				->set('a.whats_in_box = ba.whats_in_box');

			$query->join('left', $this->db->quoteName('#__sellacious_product_physical', 'ba') . ' ON ba.product_id = a.product_id')
				->where('a.product_type = ' . $this->db->quote('physical'));

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update seller level type attributes for each item
	 *
	 * @param   string[]  $allowed  The list of product types which are allowed in the global configuration
	 * @param   bool      $state    Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateTypeAttributeSeller($allowed, $state = false)
	{
		$phy = in_array('physical', $allowed);
		$pkg = in_array('package', $allowed);

		if ($phy || $pkg)
		{
			$query = $this->db->getQuery(true);

			// Todo: whats_in_box
			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.listing_type = sa.listing_type')
				->set('a.item_condition = sa.item_condition')
				->set('a.flat_shipping = sa.flat_shipping')
				->set('a.shipping_flat_fee = sa.shipping_flat_fee')
				->set('a.return_days = sa.return_days')
				->set('a.exchange_days = sa.exchange_days');

			if ($phy)
			{
				$query->join('left', $this->db->quoteName('#__sellacious_physical_sellers', 'sa') . ' ON sa.psx_id = a.psx_id')
					->where('a.product_type = ' . $this->db->quote('physical'));

				if ($state)
				{
					$query->where('a.state = 0');
				}

				$this->db->setQuery($query)->execute();
			}

			if ($pkg)
			{
				$query->clear('where')->clear('join')
					->join('left', $this->db->quoteName('#__sellacious_package_sellers', 'sa') . ' ON sa.psx_id = a.psx_id')
					->where('a.product_type = ' . $this->db->quote('package'));

				if ($state)
				{
					$query->where('a.state = 0');
				}

				$this->db->setQuery($query)->execute();
			}
		}

		if (in_array('electronic', $allowed))
		{
			$query = $this->db->getQuery(true);

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.delivery_mode = sa.delivery_mode')
				->set('a.download_limit = sa.download_limit')
				->set('a.download_period = sa.download_period')
				->set('a.preview_mode = sa.preview_mode')
				->set('a.preview_url = sa.preview_url');

			$query->join('LEFT', $this->db->quoteName('#__sellacious_eproduct_sellers', 'sa') . ' ON sa.psx_id = a.psx_id')
				->where('a.product_type = ' . $this->db->quote('electronic'));

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update product-seller listing attributes for each item
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updatePsxAttributes($state = false)
	{
		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
			->set('a.psx_id = psx.id')
			->set('a.is_selling = psx.state')
			->set('a.price_display = psx.price_display')
			->set('a.quantity_min = psx.quantity_min')
			->set('a.quantity_max = psx.quantity_max')
			->set('a.stock = psx.stock')
			->set('a.over_stock = psx.over_stock')
			->set('a.stock_reserved = psx.stock_reserved')
			->set('a.stock_sold = psx.stock_sold');

		if ($state)
		{
			$query->where('a.state = 0');
		}

		$query->join('left', $this->db->quoteName('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = a.product_id AND psx.seller_uid = a.seller_uid');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Update products listing if its free
	 *
	 * @param   bool  $state  Whether to update items only with expired cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function updateListing($state = false)
	{
		if ($this->helper->config->get('free_listing'))
		{
			$query = $this->db->getQuery(true);
			$eDate = \JFactory::getDate()->modify('+1 year')->format('Y-12-31 23:59:59');

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.listing_end = ' . $this->db->q($eDate))
				->set('a.listing_active = 1');

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
		else
		{
			$query = $this->db->getQuery(true);
			$eDate = \JFactory::getDate()->toSql();

			$query->update($this->db->quoteName('#__sellacious_cache_products', 'a'))
				->set('a.listing_active = 0')
				->where('a.listing_end <= ' . $this->db->q($eDate));

			if ($state)
			{
				$query->where('a.state = 0');
			}

			$this->db->setQuery($query)->execute();
		}
	}
}

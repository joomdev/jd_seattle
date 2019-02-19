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

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of variants.
 */
class SellaciousModelVariants extends SellaciousModelList
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @param   string  $ordering
	 * @param   string  $direction
	 *
	 * @throws  Exception
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		/** @var  array  $keys */
		$keys = $this->app->getUserStateFromRequest('com_sellacious.variants.products', 'cid', array(), 'array');

		// No default selection for Admin, but a must for sellers
		$pks  = array();

		foreach ($keys as $key)
		{
			if (preg_match('/\d+:\d+/', $key))
			{
				list($product_id, $seller_uid) = explode(':', $key);

				$me      = JFactory::getUser();
				$canEdit = $this->helper->access->checkAny(array('seller', 'pricing'), 'product.edit.');
				$editOwn = $this->helper->access->checkAny(array('seller.own', 'pricing.own'), 'product.edit.');

				if ($canEdit || ($editOwn && $me->id == $seller_uid))
				{
					$pks[] = array($product_id, $seller_uid);
				}
			}
		}

		$this->state->set('variants.products', $pks);
	}

	/**
	 * Method to get a list of selected products.
	 *
	 * @param   bool  $loadData  Load form usable data from db
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 */
	public function getItems($loadData = true)
	{
		/** @var  array $pks */
		$items = array();
		$pks   = $this->getState('variants.products', array());

		foreach ($pks as $pk_array)
		{
			list($product_id, $seller_uid) = $pk_array;

			if ($item = $this->getProduct($product_id))
			{
				$item->product_id = $product_id;
				$item->seller_uid = $seller_uid;
				$item->seller     = $this->helper->product->getSeller($product_id, $seller_uid);
				$item->variants   = $this->getVariants($product_id, $seller_uid, $loadData);
				$item->price      = $this->getPrice($product_id, $seller_uid);

				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Method to save the price and stock information for product and variants.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @throws  Exception
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function savePriceAndStock($data)
	{
		$sellerUid = ArrayHelper::getValue($data, 'seller_uid', 0, 'uint');
		$productId = ArrayHelper::getValue($data, 'product_id', 0, 'uint');
		$stock     = ArrayHelper::getValue($data, 'stock', 0, 'int');
		$overStock = ArrayHelper::getValue($data, 'over_stock', 0, 'int');
		$price     = ArrayHelper::getValue($data, 'product_price', 0, 'float');
		$variants  = ArrayHelper::getValue($data, 'variants', array(), 'array');

		$this->helper->product->setStock($productId, $sellerUid, $stock, $overStock);
		$this->helper->product->setPrice($productId, $sellerUid, $price);

		if (count($variants) && $this->helper->config->get('multi_variant', 0))
		{
			foreach ($variants as $variant)
			{
				if ($variantId = ArrayHelper::getValue($variant, 'variant_id', 0, 'int'))
				{
					$stock        = ArrayHelper::getValue($variant, 'stock', 0, 'int');
					$overStock    = ArrayHelper::getValue($variant, 'over_stock', 0, 'int');
					$priceMod     = ArrayHelper::getValue($variant, 'price_mod', 0, 'float');
					$priceModPerc = ArrayHelper::getValue($variant, 'price_mod_perc', 0, 'int');

					$this->helper->variant->setPriceAndStock($productId, $variantId, $sellerUid, $stock, $overStock, $priceMod, $priceModPerc);
				}
			}
		}

		return true;
	}

	/**
	 * Load a product item for the given seller
	 *
	 * @param   int  $product_id
	 *
	 * @return  stdClass
	 * @throws  Exception
	 */
	protected function getProduct($product_id)
	{
		$filters = array(
			'id'          => $product_id,
			'list.select' => 'a.id, a.title, a.type, a.local_sku, a.manufacturer_sku, a.manufacturer_id, a.features, a.state, a.owned_by',
		);
		$product = $this->helper->product->loadObject($filters);

		if ($product)
		{
			$db    = $this->_db;
			$query = $db->getQuery(true);

			$query->select('c.title')
				->from($db->qn('#__sellacious_product_categories', 'pc'))
				->where('pc.product_id = ' . (int) $product_id)
				->join('left', $db->qn('#__sellacious_categories', 'c') . ' ON c.id = pc.category_id')
				->order('c.lft');

			$product->categories = $db->setQuery($query)->loadColumn();
		}

		return $product;
	}

	/**
	 * Get the basic price of the given product for selected seller
	 *
	 * @param   int  $product_id  Product id for which queried
	 * @param   int  $seller_uid  Seller uid for which queried
	 *
	 * @return  stdClass
	 */
	protected function getPrice($product_id, $seller_uid)
	{
		$query = $this->_db->getQuery(true);

		$query->select('pp.id AS price_id, pp.cost_price, pp.margin, pp.margin_type')
			->select('pp.list_price, pp.calculated_price, pp.ovr_price, pp.product_price')
			->from($this->_db->qn('#__sellacious_product_prices', 'pp'))
			->where('pp.product_id = ' . (int) $product_id)
			->where('pp.seller_uid = ' . (int) $seller_uid)
			->where('pp.is_fallback = 1');

		$query->select('psx.stock, psx.over_stock')
			->join('left', $this->_db->qn('#__sellacious_product_sellers', 'psx') . ' ON (psx.product_id = pp.product_id AND psx.seller_uid = pp.seller_uid)');

		try
		{
			$price = $this->_db->setQuery($query)->loadObject();
		}
		catch (Exception $e)
		{
			$price = (object) array(
				'price_id'         => 0,
				'cost_price'       => 0,
				'margin'           => 0,
				'margin_type'      => 0,
				'list_price'       => 0,
				'calculated_price' => 0,
				'ovr_price'        => 0,
				'product_price'    => 0,
				'stock'            => 0,
				'over_stock'       => 0,
			);

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $price;
	}

	/**
	 * Get a list of all variants of a product along with their price and stock info
	 *
	 * @param   int   $product_id  Product id for which variants are required
	 * @param   int   $seller_uid  Seller uid for which variants data is required
	 * @param   bool  $loadData    Whether to load the price and stock info or not
	 *
	 * @return  stdClass[]
	 * @throws  Exception
	 */
	protected function getVariants($product_id, $seller_uid, $loadData)
	{
		if (!$this->helper->config->get('multi_variant', 0))
		{
			return false;
		}

		$filter = array('product_id' => $product_id, 'list.select' => array('a.id, a.title, a.local_sku'));

		if ($loadData)
		{
			$filter['list.select'][] = 'vp.price_mod, vp.price_mod_perc, vp.stock, vp.over_stock';
			$filter['list.join'][]   = array('left', '#__sellacious_variant_sellers vp ON (vp.variant_id = a.id AND vp.seller_uid = ' . (int) $seller_uid . ')');
		}

		try
		{
			$variants = $this->helper->variant->loadObjectList($filter, 'id');
		}
		catch (Exception $e)
		{
			$variants = array();

			JLog::add(JText::_('COM_SELLACIOUS_VARIANTS_LOAD_VARIANTS_FAILED'), JLog::WARNING, 'jerror');
		}

		return $variants;
	}

	/**
	 * Ajax query suggestion list
	 *
	 * @param   string  $key  Searched key
	 *
	 * @return  stdClass[]
	 */
	public function suggest($key)
	{
		$db      = $this->getDbo();
		$vQuery  = $db->getQuery(true);
		$keyword = $db->q('%' . $db->escape($key) . '%', false);

		// Main Products
		$pQuery = $db->getQuery(true);

		$pQuery->select($db->qn(array('a.id', 'a.title' , 'a.local_sku'), array('product_id', 'product_title', 'product_sku')))
			->select(array('null AS variant_id', 'null AS variant_title', 'null AS variant_sku'))
			->select(array('a.title AS item_title', 'a.local_sku AS item_sku'))
			->from('#__sellacious_products a')
			->where('(a.title LIKE ' . $keyword . ' OR a.local_sku LIKE ' . $keyword . ')')
			->where('a.state = 1');

		if ($this->helper->config->get('multi_variant'))
		{
			// Variants
			$vQuery->select($db->qn(array('a.id', 'a.title' , 'a.local_sku'), array('product_id', 'product_title', 'product_sku')))
				->from('#__sellacious_products a')
				->where('a.state = 1');

			$vQuery->select($db->qn(array('v.id', 'v.title' , 'v.local_sku'), array('variant_id', 'variant_title', 'variant_sku')))
				->join('left', '#__sellacious_variants v ON v.product_id = a.id');

			$vQuery->select($vQuery->concatenate(array('a.title', 'v.title'), ' ') . 'AS item_title')
				->select($vQuery->concatenate(array('a.local_sku', 'v.local_sku'), ' ') . ' AS item_sku');

			$cond[] = $vQuery->concatenate(array('a.title', 'v.title'), ' ') . ' LIKE ' . $keyword;
			$cond[] = $vQuery->concatenate(array('a.local_sku', 'v.local_sku'), ' ') . ' LIKE ' . $keyword;

			$vQuery->where('(' . implode(' OR ', $cond) . ')');

			$pQuery->union($vQuery);
		}

		try
		{
			$items = $db->setQuery($pQuery)->loadObjectList();

			if ($items)
			{
				foreach ($items as $item)
				{
					$item->code = $this->helper->product->getCode($item->product_id, $item->variant_id, 0);
				}
			}
		}
		catch (Exception $e)
		{
			$items = array();
		}

		return $items;
	}

	/**
	 * Ajax query suggestion list
	 *
	 * @param   array  $keys  Saved keys
	 *
	 * @return  stdClass[]
	 */
	public function getSuggested($keys)
	{
		$where = array();

		foreach ($keys as $key)
		{
			$this->helper->product->parseCode($key, $product_id, $variant_id);

			if ($variant_id)
			{
				$where[] = '(s.product_id = ' . (int) $product_id . ' AND s.variant_id = ' . (int) $variant_id . ')';
			}
			else
			{
				$where[] = '(s.product_id = ' . (int) $product_id . ' AND s.variant_id IS NULL)';
			}
		}

		if (count($where) == 0)
		{
			return array();
		}

		$db     = $this->getDbo();
		$pQuery = $db->getQuery(true);

		$pQuery->select($db->qn(array('a.id', 'a.title' , 'a.local_sku'), array('product_id', 'product_title', 'product_sku')))
			->select(array('null AS variant_id', 'null AS variant_title', 'null AS variant_sku'))
			->select(array('a.title AS item_title', 'a.local_sku AS item_sku'))
			->from('#__sellacious_products a');

		if ($this->helper->config->get('multi_variant'))
		{
			// Variants
			$vQuery = $db->getQuery(true);

			$vQuery->select($db->qn(array('a.id', 'a.title' , 'a.local_sku'), array('product_id', 'product_title', 'product_sku')))
				->from('#__sellacious_products a')
				->where('a.state = 1');

			$vQuery->select($db->qn(array('v.id', 'v.title' , 'v.local_sku'), array('variant_id', 'variant_title', 'variant_sku')))
				->join('left', '#__sellacious_variants v ON v.product_id = a.id');

			$vQuery->select($vQuery->concatenate(array('a.title', 'v.title'), ' ') . 'AS item_title')
				->select($vQuery->concatenate(array('a.local_sku', 'v.local_sku'), ' ') . ' AS item_sku');

			$pQuery->union($vQuery);
		}

		$query = $db->getQuery(true);
		$query->select('s.*')->from($pQuery, 's')->where($where, 'OR');

		try
		{
			if ($items = $db->setQuery($query)->loadObjectList())
			{
				foreach ($items as $item)
				{
					$item->code = $this->helper->product->getCode($item->product_id, $item->variant_id, 0);
				}
			}
		}
		catch (Exception $e)
		{
			$items = array();
		}

		return $items;
	}
}

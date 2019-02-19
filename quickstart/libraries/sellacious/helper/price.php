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
 * Sellacious product helper
 *
 * @since  1.0.0
 */
class SellaciousHelperPrice extends SellaciousHelperBase
{
	const PRICE_DISPLAY_DEFINED = 0x0;

	const PRICE_DISPLAY_CALL = 0x1;

	const PRICE_DISPLAY_EMAIL = 0x2;

	const PRICE_DISPLAY_FORM = 0x3;

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $hasTable = false;

	/**
	 * Get product price
	 *
	 * @param   int  $productId   The Product's id
	 * @param   int  $variantId   The variant id if queried for a variant of the main product
	 * @param   int  $seller_uid  The seller's user id for whom the prices to reflect
	 * @param   int  $client_uid  Possible values are: false => no client, null => current user, int => given user id
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use Product object directly
	 */
	public function get($productId, $variantId, $seller_uid = null, $client_uid = null)
	{
		$product = new Sellacious\Product($productId, $variantId);
		$price   = $product->getPrice($seller_uid, null, $client_uid);

		return $price;
	}

	/**
	 * Get a sql query for retrieving product prices.
	 *
	 * @param  int       $product_id  Product id in question
	 * @param  int       $seller_uid  Seller id in question
	 * @param  bool|int  $client_uid  The user id for whom to calc; false == none, null == current user, int == given user
	 * @param  int       $quantity    The quantity of the product in question, null to ignore quantity
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   This will be removed along with the getPrices method in this class
	 */
	public function getPriceQuery($product_id, $seller_uid, $client_uid, $quantity = null)
	{
		$query   = $this->db->getQuery(true);
		$now     = JFactory::getDate()->format('Y-m-d');
		$nullDt  = $this->db->getNullDate();
		$qNow    = $this->db->q($now);
		$qNullDt = $this->db->q($nullDt);

		// Select fields from prices table for selected product.
		$query->select('pp.id, pp.product_id, pp.seller_uid')
			->select('pp.cost_price, pp.margin, pp.margin_type, pp.list_price, pp.calculated_price, pp.ovr_price')
			->select('pp.product_price, pp.product_price AS sales_price, pp.is_fallback')
			->select('pp.qty_min, pp.qty_max')
			->from($this->db->qn('#__sellacious_product_prices', 'pp'))
			->where('pp.product_id = ' . (int) $product_id)
			->where('pp.state = 1');

		// Must be in valid date range or can be a fallback price
		$sdate = "(pp.sdate <= $qNow OR pp.sdate = $qNullDt)";
		$edate = "(pp.edate >= $qNow OR pp.edate = $qNullDt)";

		$query->where("(($sdate AND $edate) OR is_fallback = 1)");

		// Must be a seller who is actively selling the product, PSX(+T) applied
		$query->select('psx.price_display')
			->join('LEFT', $this->db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = pp.product_id AND psx.seller_uid = pp.seller_uid')
			->where('psx.state = 1');

		// Price display option must be either 'no display' type or otherwise the price value must be set > 0.
		$query->where('(pp.product_price > 0 OR psx.price_display > 0)');

		// todo: Also check for active listing status, currently we're calling from already verified context so can be ignored.

		// Seller's user account must be active.
		$query->select('u.email AS seller_email')
			->join('inner', '#__users u ON u.id = pp.seller_uid')
			->where('u.block = 0');

		$query->select('su.mobile AS seller_mobile')
			->join('LEFT', $this->db->qn('#__sellacious_profiles', 'su') . ' ON su.user_id = pp.seller_uid');

		$query->select('ss.title AS seller_company')
			->join('LEFT', $this->db->qn('#__sellacious_sellers', 'ss') . ' ON ss.user_id = pp.seller_uid');

		// Add forex
		$currency   = $this->helper->currency->current('code_3');
		$g_currency = $this->helper->currency->getGlobal('code_3');

		if ($this->helper->config->get('listing_currency'))
		{
			$seller_currency = "COALESCE(NULLIF(ss.currency, ''), " . $this->db->q($g_currency) . ")";

			$query->select($seller_currency . ' AS seller_currency');
			$query->select('fx.x_factor AS forex_rate')
				->join('LEFT', $this->db->qn('#__sellacious_forex', 'fx') . ' ON fx.x_from = ' . $seller_currency . ' AND fx.state = 1 AND fx.x_to = ' . $this->db->q($currency));
		}
		else
		{
			$x_factor = $this->helper->currency->getRate($g_currency, $currency) ?: null;

			$query->select($this->db->q($g_currency) . ' AS seller_currency');
			$query->select($this->db->q($x_factor) . ' AS forex_rate');
		}

		if (isset($quantity))
		{
			$quantity = (int) $quantity;
			$query->where('(pp.qty_min = 0 OR pp.qty_min <= ' . $quantity . ')');
			$query->where('(pp.qty_max = 0 OR pp.qty_max >= ' . $quantity . ')');
		}

		if ($client_uid !== false)
		{
			$him   = JFactory::getUser($client_uid);
			$uid   = array('user_id' => $him->id);
			$c_cat = (int) $this->helper->client->getCategory($him->id, true);

			$query->select('pcx.cat_id AS client_catid')
				->join('left', $this->db->qn('#__sellacious_productprices_clientcategory_xref', 'pcx') . ' ON pcx.product_price_id = pp.id');

			// If specified, the client category must match
			// If a price rule is set for no category (i.e. - implicitly all categories) we must take it too.
			$query->where('(pcx.cat_id = ' . $c_cat . ' OR pcx.cat_id IS NULL)');
		}

		// The available prices must be prioritized as:
		// Visible price > Non Zero Price > rule based price first, lowest price first.
		$query->order('psx.price_display ASC')
			->order('pp.product_price = 0 ASC')
			->order('pp.is_fallback ASC')
			->order('pp.product_price ASC');

		// For the specified (if any) seller
		if ($seller_uid)
		{
			$query->where('pp.seller_uid = ' . (int) $seller_uid);
		}

		return $query;
	}

	/**
	 * Retrieve the product price from the database.
	 *
	 * @param   int       $product_id  Product id in question
	 * @param   int       $seller_uid  Seller id in question
	 * @param   bool|int  $client_uid  false == none, null == current user, int == given user
	 *
	 * @return  stdClass
	 */
	protected function getPrice($product_id, $seller_uid, $client_uid)
	{
		// Todo: Variant price simplification within the priceQuery for product price or other suitable way!

		try
		{
			$query = $this->getPriceQuery($product_id, $seller_uid, $client_uid);
			$price = $this->db->setQuery($query, 0, 1)->loadObject();
		}
		catch (Exception $e)
		{
			$price = null;

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $price;
	}

	/**
	 * Retrieve the several product prices from the database.
	 *
	 * @param   int       $product_id  Product Id in question
	 * @param   int       $variant_id  Variant Id
	 * @param   int       $seller_uid  Seller id in question
	 * @param   bool|int  $client_uid  false == none, null == current user, int == given user
	 * @param   int       $quantity    Product Quantity
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use Product object
	 */
	public function getPrices($product_id, $variant_id, $seller_uid, $client_uid, $quantity = null)
	{
		// Get override price first, if available,
		// otherwise it will return fallback price according to the set ordering.
		// Todo: Variant price simplification within the priceQuery for product price or other suitable way!

		try
		{
			$query = $this->getPriceQuery($product_id, $seller_uid, $client_uid, $quantity);

			if ($variant_id)
			{
				$sQuery = (string) $query;
				$query  = $this->db->getQuery(true);

				$query->select('pp.id, pp.product_id, pp.seller_uid, pp.cost_price, pp.margin, pp.margin_type, pp.qty_min, pp.qty_max')
					->select('pp.list_price, pp.calculated_price, pp.ovr_price, pp.product_price, pp.is_fallback, pp.price_display')
					->select('pp.seller_email, pp.seller_mobile, pp.seller_company, pp.forex_rate, pp.client_catid')
					// This is already processed in sub-query
					->select('pp.seller_currency AS seller_currency')
 					->from("($sQuery) AS pp");

				// Variant stock and prices
				$price_mod     = 'IFNULL(vs.price_mod, 0)';
				$mod_percent   = 'IFNULL(vs.price_mod_perc, 0)';
				$variant_price = "IF($mod_percent, pp.product_price * $price_mod / 100.0, $price_mod)";

				$query->select("$variant_id AS variant_id");
				$query->select("$price_mod AS price_mod");
				$query->select("$mod_percent AS price_mod_perc");
				$query->select("$variant_price AS variant_price");
				$query->select("$variant_price + pp.product_price AS sales_price");

				$query->join('LEFT', $this->db->qn('#__sellacious_variant_sellers', 'vs') . ' ON vs.variant_id = ' . (int) $variant_id . ' AND vs.seller_uid = pp.seller_uid');
			}

			$query_o = clone $query;
			$query_b = clone $query;

			$query_o->where('pp.is_fallback = 0');
			$query_b->where('pp.is_fallback = 1');

			$this->db->setQuery($query_o);
			$price_o = $this->db->loadObjectList();

			$this->db->setQuery($query_b);
			$price_b = $this->db->loadObjectList();

			$prices = array();

			// First collect all override prices.
			foreach ($price_o as $price)
			{
				$key = $price->seller_uid;

				if (!isset($prices[$key]))
				{
					$prices[$key]   = array($price);
				}
				else
				{
					$prices[$key][] = $price;
				}
			}

			// Now include all fallback prices.
			foreach ($price_b as $price)
			{
				$key = $price->seller_uid;

				if (!isset($prices[$key]))
				{
					$prices[$key] = array($price);
				}
				else
				{
					// Comment this out if we need to ignore fallback price for sellers having overrides.
					$prices[$key][] = $price;
				}
			}
		}
		catch (Exception $e)
		{
			$prices = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $seller_uid ? reset($prices) : $prices;
	}
}

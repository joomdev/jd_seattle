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
 * Methods supporting a list of Products.
 */
class SellaciousModelWishlist extends SellaciousModelList
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

		$uid  = $this->app->input->get('user_id', null);
		$user = JFactory::getUser($uid);

		$this->state->set('wishlist.user_id', $user->id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db      = $this->getDbo();
		$query   = $db->getQuery(true);

		$query->select('a.code,
						  a.product_id AS id,
						  a.product_id,
						  a.product_title AS title,
						  a.product_type AS type,
						  a.product_sku AS local_sku,
						  a.manufacturer_sku,
						  a.manufacturer_id,
						  a.product_features AS features,
						  a.product_introtext AS introtext,
						  a.product_description AS description,
						  a.metakey,
						  a.metadesc,
						  a.product_active AS state,
						  a.product_ordering AS ordering,
						  a.tags,
						  a.variant_id,
						  a.variant_title,
						  a.variant_sku,
						  a.variant_description,
						  a.variant_features,
						  a.seller_uid,
						  a.product_price,
						  a.product_price AS sales_price,
						  a.price_display,
						  a.stock,
						  a.over_stock,
						  a.stock + a.over_stock AS stock_capacity,
						  a.variant_price_mod AS price_mod,
						  a.variant_price_mod_perc AS price_mod_perc,
						  a.seller_email,
						  a.seller_mobile,
						  a.seller_company,
						  a.seller_currency,
						  a.forex_rate,
						  product_rating AS rating')
			->from($db->qn('#__sellacious_cache_products', 'a'));

		// Following columns are included for backward compatibility, the values are not correct.
		$query->select('null AS params,
						0 AS price_id,
						0 AS cost_price,
						0 AS margin,
						0 AS margin_type,
						0 AS list_price,
						0 AS calculated_price,
						product_price AS ovr_price,
						1 AS is_fallback,
						null AS client_catid,
						0 AS variant_price');

		$query->join('INNER', '#__sellacious_wishlist AS w ON w.product_id = a.product_id AND w.variant_id = a.variant_id');

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function processList($items)
	{
		foreach ($items as &$item)
		{
			$item->images      = $this->helper->product->getImages($item->id, $item->variant_id, true);
			$item->basic_price = $item->sales_price;
			$item->shoprules   = $this->helper->shopRule->toProduct($item);
		}

		return $items;
	}
}

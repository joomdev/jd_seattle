<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Sellacious\Product;

defined('_JEXEC') or die;

/**
 * Methods supporting a list of Reviews and ratings.
 *
 * @since   1.6.0
 */
class SellaciousModelReviews extends SellaciousModelList
{
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

		$this->state->set('filter.product_category_id', 0);

		if ($productCategoryId = $this->app->input->getInt('product_category_id'))
		{
			$this->state->set('filter.product_category_id', $productCategoryId);
		}

		$this->state->set('filter.seller_category_id', 0);

		if ($sellerCategoryId = $this->app->input->getInt('seller_category_id'))
		{
			$this->state->set('filter.seller_category_id', $sellerCategoryId);
		}

		$this->state->set('filter.seller_uid', 0);

		if ($sellerUid = $this->app->input->getInt('seller_uid'))
		{
			$this->state->set('filter.seller_uid', $sellerUid);
		}

		$this->state->set('filter.product_id', 0);

		if ($productId = $this->app->input->getInt('product_id'))
		{
			$this->state->set('filter.product_id', $productId);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$productCategoryId = $this->getState('filter.product_category_id', 0);
		$sellerCategoryId  = $this->getState('filter.seller_category_id', 0);
		$sellerUid         = $this->getState('filter.seller_uid', 0);
		$productId         = $this->getState('filter.product_id', 0);

		$query->select('a.*');
		$query->from($db->qn('#__sellacious_ratings', 'a'));

		if ($productCategoryId)
		{
			$query->join('left', $db->qn('#__sellacious_product_categories', 'b') . ' ON b.product_id = a.product_id');
		}

		if ($sellerCategoryId)
		{
			$query->join('left', $db->qn('#__sellacious_sellers', 'c') . ' ON c.user_id = a.seller_uid');
		}

		$query->join('left', $db->qn('#__sellacious_products', 'd') . ' ON d.id = a.product_id');
		$query->join('left', $db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.seller_uid = a.seller_uid AND psx.product_id = a.product_id');

		$query->where('a.state = 1');
		$query->where('a.comment != ' . $db->quote(''));
		$query->where('a.type = ' . $db->quote('product'));

		// Whether the product is published
		$query->where('d.state = 1');

		// Whether the product is being sold by the seller
		$query->where('psx.state = 1');

		// Filter by product category
		if ($productCategoryId)
		{
			$query->where('b.category_id = ' . $productCategoryId);
		}

		// Filter by seller category
		if ($sellerCategoryId)
		{
			$query->where('c.category_id = ' . $sellerCategoryId);
		}

		// Filter by seller id
		if ($sellerUid)
		{
			$query->where('a.seller_uid = ' . $sellerUid);
		}

		// Filter by product id
		if ($productId)
		{
			$query->where('a.product_id = ' . $productId);
		}

		$query->order('a.product_id ASC, a.created DESC');

		return $query;
	}

	/**
	 * Process list to add items in review
	 *
	 * @param   array  $items
	 *
	 * @return  array
	 */
	protected function processList($items)
	{
		if (is_array($items))
		{
			foreach ($items as &$item)
			{
				$product = new Product($item->product_id, $item->variant_id, $item->seller_uid);

				$item->product       = $product;
				$item->product_image = $this->helper->product->getImage($item->product_id, $item->variant_id);
			}
		}
		return parent::processList($items);
	}

	/**
	 * Method to get seller reviews
	 *
	 * @since 1.6.0
	 */
	public function getSellerReviews()
	{
		$sellerUid = $this->getState('filter.seller_uid', 0);

		/** @var JDatabaseDriver $db */
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->qn('#__sellacious_ratings', 'a'));

		$query->where('a.state = 1');
		$query->where('a.comment != ""');
		$query->where('a.type = ' . $db->quote('seller'));

		$query->where('a.seller_uid = ' . $sellerUid);

		$query->order('a.created DESC');

		$db->setQuery($query);

		$reviews = $db->loadObjectList();

		return $reviews;
	}
}

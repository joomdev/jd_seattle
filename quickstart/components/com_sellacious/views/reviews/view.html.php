<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of reviews.
 *
 * @since  1.6.0
 */
class SellaciousViewReviews extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 */
	protected $items;

	/** @var  JPagination */
	protected $pagination;

	/** @var  JObject */
	protected $state;

	/** @var  stdClass */
	protected $seller;

	/** @var  stdClass[] */
	protected $seller_reviews;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Sub-layout to load
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		// Preserve state info
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$storeId = $this->state->get('filter.seller_uid', 0);

		$profile = $this->helper->profile->getItem(array('user_id' => $storeId));
		$seller  = $this->helper->seller->getItem(array('user_id' => $storeId));
		$rating  = $this->helper->rating->getSellerRating($storeId);

		$product_count = $this->helper->seller->getSellerProductCount($storeId);

		$this->seller          = $seller;
		$this->seller->profile = $profile;
		$this->seller->rating  = $rating;

		$this->seller->product_count = $product_count;

		$this->seller_reviews = $this->get('SellerReviews');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::ERROR, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}
}

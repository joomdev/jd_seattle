<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of products by a seller.
 */
class SellaciousViewStore extends SellaciousView
{
	/** @var  array */
	public $activeFilters;

	/** @var  JForm */
	public $filterForm;

	/** @var  stdClass[] */
	protected $items;

	/** @var  stdClass[] */
	protected $categories;

	/** @var  stdClass[] */
	protected $filters;

	/** @var  JPagination */
	protected $pagination;

	/** @var  JObject */
	protected $state;

	/** @var  stdClass */
	protected $seller;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.2.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$storeId     = $this->state->get('store.id');

		if (!$storeId)
		{
			JLog::add(JText::_('COM_SELLACIOUS_STORE_SELECTED_INVALID_SHOWING_ALL_STORES_MESSAGE'), JLog::WARNING, 'jerror');

			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=products', false));
		}

		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filters    = $this->get('Filters');

		$profile = $this->helper->profile->getItem(array('user_id' => $storeId));
		$seller  = $this->helper->seller->getItem(array('user_id' => $storeId));
		$rating  = $this->helper->rating->getSellerRating($storeId);

		$product_count = $this->helper->seller->getSellerProductCount($storeId);

		$this->seller          = $seller;
		$this->seller->profile = $profile;
		$this->seller->rating  = $rating;

		$this->seller->product_count  = $product_count;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}
}

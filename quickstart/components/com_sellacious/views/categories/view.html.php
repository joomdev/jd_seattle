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
 * View class for a list of categories.
 *
 * @since   1.0.0
 */
class SellaciousViewCategories extends SellaciousView
{
	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	public $activeFilters;

	/**
	 * @var  JForm
	 *
	 * @since   1.0.0
	 */
	public $filterForm;

	/**
	 * @var  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected $items;

	/**
	 * @var  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected $products;

	/**
	 * @var  JPagination
	 *
	 * @since   1.0.0
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since   1.0.0
	 */
	protected $state;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected $ordering;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected $types;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $sidebar;

	/**
	 * @var  JObject
	 *
	 * @since   1.0.0
	 */
	protected $current;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->products   = $this->getProducts();
		// $this->filterForm    = $this->get('FilterForm');
		// $this->activeFilters = $this->get('ActiveFilters');

		$this->current = $this->getCurrent();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		foreach ($this->items as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		$this->types   = $this->helper->category->getTypes(true);
		$this->sidebar = JHtmlSidebar::render();

		$this->setPathway();

		return parent::display($tpl);
	}

	/**
	 * Method to add pathway for the breadcrumbs to the view
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setPathway()
	{
		$catid = $this->state->get('categories.id');

		if ($catid)
		{
			$pks    = $this->helper->category->getParents($catid, true);
			$filter = array('id' => $pks, 'list.select' => 'a.id, a.title', 'list.where' => 'a.level > 0');
			$cats   = $this->helper->category->loadObjectList($filter);
			$crumbs = array();

			foreach ($cats as $cat)
			{
				$crumb = new stdClass;
				$link  = JRoute::_('index.php?option=com_sellacious&view=categories&category_id=' . (int) $cat->id);

				$crumb->name = $cat->title;
				$crumb->link = $link;

				$crumbs[] = $crumb;
			}

			$this->app->getPathway()->setPathway($crumbs);
		}
	}

	/**
	 * Get current category which is selected as parent
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getCurrent()
	{
		$catId = $this->state->get('categories.id', 1);

		if ($catId <= 1)
		{
			return null;
		}

		/** @var  SellaciousModelCategories  $model */
		$model = $this->getModel();
		$item = $this->helper->category->getItem($catId);
		$this->helper->translation->translateRecord($item, 'sellacious_categories');

		$item->subcat_count  = 0;
		$item->product_count = 0;

		if ($item->level > 0)
		{
			$children = $this->helper->category->getChildren($item->id, false, array('a.state = 1'));

			$item->subcat_count  = count($children);
			$item->product_count = $model->getCountItems($item->id, false);
		}

		$suffixStore        = $this->state->get('stores.id') ? '&store_id=' . $this->state->get('stores.id') : '';
		$suffixManufacturer = $this->state->get('manufacturers.id') ? '&manufacturer_id=' . $this->state->get('manufacturers.id') : '';

		if ($item->subcat_count == 0 && $this->helper->config->get('category_no_child_redirect'))
		{
			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $item->id . $suffixStore . $suffixManufacturer, false));
		}

		$max_level = $this->helper->config->get('category_level_limit');

		if ($max_level > 0 && $item->level > ($max_level - 1))
		{
			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $item->id . $suffixStore . $suffixManufacturer, false));
		}

		return $item;
	}

	/**
	 * Get a list of products to display
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected function getProducts()
	{
		$items = array();
		$limit = $this->helper->config->get('category_page_product_limit', 3);
		$show  = $this->helper->config->get('show_category_products', 1);

		if ($show && $limit > 0)
		{
			$catId = $this->state->get('categories.id', 1);

			$sellerUid      = $this->state->get('stores.id', '');
			$manufacturerId = $this->state->get('manufacturers.id', '');

			/** @var  SellaciousModelProducts  $model */
			$model = JModelLegacy::getInstance('Products', 'SellaciousModel', array('ignore_request' => true));

			if ($query = $this->app->input->getString('q'))
			{
				$model->setState('filter.query', $query);
			}

			$model->setState('filter.category_id', $catId);
			$model->setState('filter.seller_uid', $sellerUid);
			$model->setState('filter.manufacturer', $manufacturerId);
			$model->setState('list.start', 0);
			$model->setState('list.limit', $limit);

			$items = $model->getItems();
		}

		return $items;
	}
}

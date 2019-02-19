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

/**
 * View class for a list of categories.
 *
 * @since   1.0.0
 */
class SellaciousViewCategories extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'category';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = 'category';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'categories';

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $is_nested = true;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected $types = array();

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareDisplay()
	{
		$this->types = $this->helper->category->getTypes(true);

		$table = SellaciousTable::getInstance('Category');

		array_walk($this->items, array($table, 'parseJson'));

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$typeFilter = $this->state->get('filter.type');

		if ($this->helper->access->check('product.create') && strpos($typeFilter, 'product/') === 0)
		{
			// Show sync Categories with Menu Button
			$syncOpts = $this->helper->config->get('category_menu') ?: array();
			$syncOpts = array_values((array) $syncOpts);

			if (isset($syncOpts[0], $syncOpts[0]->enable_sync) && $syncOpts[0]->enable_sync)
			{
				JToolBarHelper::custom($this->view_list . '.syncMenus', 'refresh', 'refresh', 'COM_SELLACIOUS_CATEGORIES_SYNC_MENUS', false);
			}
		}

		// Let toolbar title use filter
		$uri     = JUri::getInstance();
		$filters = $uri->getVar('filter');

		$filters['type'] = $this->state->get('filter.type');

		$uri->setVar('filter', $filters);

		parent::addToolbar();

		// As we have utilised the URL parameter of type-filter lets remove it
		unset($filters['type']);

		$uri->setVar('filter', $filters);
	}
}

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

class SellaciousViewSplCategories extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'splcategory';

	/** @var  string */
	protected $view_item = 'splcategory';

	/** @var  string */
	protected $view_list = 'splcategories';

	/** @var  bool */
	protected $is_nested = true;

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		if ($this->helper->access->check($this->action_prefix . '.edit.state'))
		{
			JToolBarHelper::custom($this->view_list . '.revokeActiveSubscriptions', 'unpublish.png', 'unpublish.png', 'COM_SELLACIOUS_SPLCATEGORIES_REVOKE_ACTIVE_SUBSCRIPTIONS_BUTTON', true);
		}

		parent::addToolbar();
	}
}

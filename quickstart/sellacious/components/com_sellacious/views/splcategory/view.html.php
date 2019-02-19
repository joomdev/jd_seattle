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
 * View to edit
 */
class SellaciousViewSplCategory extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'splcategory';

	/** @var  string */
	protected $view_item = 'splcategory';

	/** @var  string */
	protected $view_list = 'splcategories';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		if (!$isNew && $this->helper->access->check($this->action_prefix . '.edit'))
		{
			JToolBarHelper::custom($this->view_item . '.save2update', 'save.png', 'save.png', 'COM_SELLACIOUS_SPLCATEGORIES_SAVE_AND_APPLY_TO_ACTIVE_SUBSCRIPTIONS_BUTTON', false);
		}

		parent::addToolbar();
	}
}

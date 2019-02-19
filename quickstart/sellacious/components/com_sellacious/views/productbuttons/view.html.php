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
 * View class for a list of custom product buttons
 *
 * @since   1.6.0
 */
class SellaciousViewProductButtons extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $action_prefix = 'productbutton';

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $view_item = 'productbutton';

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $view_list = 'productbuttons';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		$this->setPageTitle();

		$editable = file_exists(JPATH_COMPONENT . '/views/' . $this->view_item);

		if ($this->helper->access->check('product.create'))
		{
			JToolBarHelper::addNew($this->view_item . '.add', 'JTOOLBAR_NEW');
		}

		if (count($this->items))
		{
			if ($editable && $this->helper->access->checkAny(array('.edit.basic', '.edit.basic.own'), 'product'))
			{
				JToolBarHelper::editList($this->view_item . '.edit', 'JTOOLBAR_EDIT');
			}

			if ($this->helper->access->checkAny(array('.delete', '.delete.own'), 'product'))
			{
				JToolBarHelper::trash($this->view_list . '.delete', 'JTOOLBAR_DELETE');
			}
		}
	}
}

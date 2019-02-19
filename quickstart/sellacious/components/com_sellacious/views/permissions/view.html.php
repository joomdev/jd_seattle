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
class SellaciousViewPermissions extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'permissions';

	/** @var  string */
	protected $view_item = 'permissions';

	/** @var  string */
	protected $view_list = null;

	/**
	 * Method to prepare data/view before rendering the display. Child classes can override this to alter view object
	 * before actual display is called.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		$this->setLayout('edit');

		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if ($this->helper->access->check('permissions.edit'))
		{
			JToolBarHelper::apply('permissions.apply', 'JTOOLBAR_APPLY');
		}
	}
}

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * View to edit email option
 *
 * @since   1.6.0
 */
class SellaciousViewEmailOption extends SellaciousViewForm
{
	/**
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $action_prefix = 'emailoption';

	/**
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $view_item = 'emailoption';

	/**
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $view_list = 'emailtemplates';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	public function addToolbar()
	{
		JToolBarHelper::apply('emailoption.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::apply('emailoption.save', 'JTOOLBAR_SAVE');

		JToolBarHelper::cancel('emailoption.cancel', 'JTOOLBAR_CANCEL');

		$this->setPageTitle();
	}
}

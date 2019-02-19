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
 * View to edit a template
 *
 * @since   1.5.2
 */
class ImporterViewTemplate extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $action_prefix = 'template';

	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $view_item = 'template';

	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $view_list = 'templates';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.5.2
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		$me        = JFactory::getUser();
		$createdBy = $this->item->get('created_by');
		$allowEdit = $this->helper->access->check($this->action_prefix . '.edit') ||
			($this->helper->access->check($this->action_prefix . '.edit.own') && $createdBy == $me->id);

		if ($allowEdit)
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', 'JTOOLBAR_CLOSE');
	}
}

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
class SellaciousViewTransaction extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'transaction';

	/** @var  string */
	protected $view_item = 'transaction';

	/** @var  string */
	protected $view_list = 'transactions';

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		if ($this->_layout != 'edit')
		{
			$this->app->input->set('hidemainmenu', 0);

			return;
		}

		$this->setPageTitle();

		if ($this->helper->access->check($this->action_prefix . '.addfund.direct') ||
			$this->helper->access->check($this->action_prefix . '.addfund.gateway') ||
			$this->helper->access->check($this->action_prefix . '.withdraw') ||
			$this->helper->access->check($this->action_prefix . '.withdraw.own')
		)
		{
			JToolBarHelper::apply($this->view_item . '.save', 'JSUBMIT');
			// JToolBarHelper::custom($this->view_item . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', 'JTOOLBAR_CANCEL');
	}
}

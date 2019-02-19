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
 * View class for a list of messages.
 */
class SellaciousViewMessages extends SellaciousViewList
{
	/**
	 * @var  string
	 */
	protected $action_prefix = 'message';

	/**
	 * @var  string
	 */
	protected $view_item = 'message';

	/**
	 * @var  string
	 */
	protected $view_list = 'messages';

	/**
	 * @var  bool
	 */
	protected $is_nested = true;

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if ($this->helper->access->check($this->action_prefix . '.create'))
		{
			JToolBarHelper::addNew($this->view_item . '.add', 'COM_SELLACIOUS_MESSAGE_COMPOSE_TITLE');
		}

		if (count($this->items))
		{
			if ($this->helper->access->checkAny(array('reply', 'reply.own'), $this->action_prefix . '.'))
			{
				JToolBarHelper::custom($this->view_item . '.reply', 'reply', 'reply', 'COM_SELLACIOUS_MESSAGE_REPLY_TITLE', true);
			}

			if ($this->helper->access->check($this->action_prefix . '.delete'))
			{
				JToolBarHelper::deleteList('', $this->view_list . '.delete', 'JTOOLBAR_DELETE');
			}
		}
	}
}

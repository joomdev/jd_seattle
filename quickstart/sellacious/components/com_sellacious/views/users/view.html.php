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
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;
defined('_JEXEC') or die;

/**
 * View class for a list of Sellacious users.
 *
 * @since   1.2.0
 */
class SellaciousViewUsers extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $action_prefix = 'user';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_item = 'user';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = 'users';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.2.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		if ($this->helper->access->check($this->action_prefix . '.create'))
		{
			JToolBarHelper::addNew($this->view_item . '.add', 'JTOOLBAR_NEW');
		}

		if (count($this->items))
		{
			$filter_state = $state->get('filter.state');

			$toolbar = Toolbar::getInstance();
			$gState  = new ButtonGroup('state', 'COM_SELLACIOUS_BUTTON_GROUP_BULK_OPTIONS');
			$toolbar->appendGroup($gState);

			if ($this->helper->access->check($this->action_prefix . '.edit.state'))
			{
				if (!is_numeric($filter_state) || $filter_state != '1')
				{
					$gState->appendButton(new StandardButton('publish', 'COM_SELLACIOUS_USERS_ACTIVE', $this->view_list . '.publish', true));
				}

				if (!is_numeric($filter_state) || $filter_state != '0')
				{
					$gState->appendButton(new StandardButton('unpublish', 'COM_SELLACIOUS_USERS_BLOCK', $this->view_list . '.unpublish', true));
				}

				$gState->appendButton(new StandardButton('refresh', 'COM_SELLACIOUS_USERS_RESEND_VERIFICATION', $this->view_list . '.resendActivationMail', true));
				$gState->appendButton(new StandardButton('lock', 'COM_SELLACIOUS_USERS_RESET_PASSWORD_MAIL', $this->view_list . '.resetPasswordMail', true));
			}

			// We can allow direct 'delete' implicitly for if so permitted, *warning* User table does not support trash.
			if ($this->helper->access->check($this->action_prefix . '.delete'))
			{
				JToolBarHelper::custom($this->view_list . '.delete', 'delete.png', 'delete.png', 'JTOOLBAR_DELETE', true);
			}
		}

		if ($this->is_nested && $this->helper->access->check('user.list'))
		{
			JToolBarHelper::custom($this->view_list . '.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}

		// Let toolbar title use filter
		$uri     = JUri::getInstance();
		$filters = $uri->getVar('filter');

		$filters['profile_type'] = $this->state->get('filter.profile_type');

		$uri->setVar('filter', $filters);

		$this->setPageTitle();

		$uri->setVar('filter', $filters);
	}
}

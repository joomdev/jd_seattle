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
 *
 * @since   1.2.0
 */
class SellaciousViewMessage extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $action_prefix = 'message';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_item = 'message';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = 'messages';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $tags = array();

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $selected = array();

	/**
	 * Method to prepare data/view before rendering the display.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function prepareDisplay()
	{
		$this->selected = array();
		$this->tags     = $this->helper->access->check('message.create.bulk') ? $this->helper->message->getRecipientGroups() : array();

		if ($this->state->get('message.id') && $this->getLayout() != 'default')
		{
			$this->setLayout('default');
		}

		if ($this->form instanceof JForm)
		{
			$selected = array();
			$values   = explode(',', $this->form->getValue('recipients'));

			// Selected groups
			foreach ($this->tags as $group)
			{
				if (isset($group->id) && in_array($group->id, $values))
				{
					$selected[] = $group;
				}
			}

			// Now selected specific users
			$uids  = array_filter($values, 'is_numeric');
			$users = $this->helper->user->loadObjectList(array('list.select' => 'a.id, a.email, a.name AS text, a.username', 'id' => $uids));

			$this->selected = array_merge($selected, $users);
		}
		elseif ($this->item->get('recipient') == -1)
		{
			// No reply form for broadcasts
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_MESSAGE_MESSAGE_NO_REPLY_BROADCAST'), 'warning');
		}
		else
		{
			// No reply authorised
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_MESSAGE_MESSAGE_NO_REPLY_BROADCAST'), 'warning');
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
		$isNew = ($this->item->get('id') == 0);

		if ($this->_layout == 'edit')
		{
			$this->setPageTitle();
		}

		// No generic toolbar here for replies. We'll add custom send/cancel button under compose editor.
		if ($isNew && $this->helper->access->check($this->action_prefix . '.create'))
		{
			JToolBarHelper::apply($this->view_item . '.save', 'COM_SELLACIOUS_MESSAGE_COMPOSE_SEND_BUTTON');
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}

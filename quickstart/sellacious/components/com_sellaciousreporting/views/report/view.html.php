<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Report View
 *
 * @since  1.6.0
 */
class SellaciousreportingViewReport extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'report';

	/** @var  string */
	protected $view_item = 'report';

	/** @var  string */
	protected $view_list = 'reports';

	/** @var  bool */
	protected $canEdit = true;

	/**
	 * Display the view
	 *
	 * @param  string $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		if (!isset($this->item))
		{
			$this->item  = $this->get('Item');
		}

		if ($this->item->id)
		{
			ReportingHelper::canEditReport($this->item->id, $this->canEdit);

			if (!$this->canEdit)
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('COM_SELLACIOUSREPORTING_REPORT_NOT_PERMITTED'), 'error');
				$app->redirect('index.php?option=com_sellaciousreporting&view=reports');
			}
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		if ($isNew ? $this->helper->access->check($this->action_prefix . '.create') : ($this->helper->access->check($this->action_prefix . '.edit') || $this->canEdit))
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');

			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');

			if ($this->helper->access->check($this->action_prefix . '.create'))
			{
				JToolBarHelper::custom($this->view_item . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);

				if (!$isNew)
				{
					JToolBarHelper::custom($this->view_item . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function setPageTitle()
	{
		$isNew = ($this->item->get('id') == 0);

		if ($isNew)
		{
			JToolBarHelper::title(JText::_(strtoupper($this->getOption() . '_TITLE_ADD_' . $this->getName())), 'file');
		}
		else
		{
			JToolBarHelper::title(JText::_(strtoupper($this->getOption() . '_TITLE_EDIT_' . $this->getName())) . ' (' . $this->item->get('title') . ')', 'file');
		}
	}
}

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View to edit
 *
 * @property  int  counter
 *
 * @since   1.2.0
 */
class SellaciousViewOrder extends SellaciousViewForm
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = false;

		// Order history
		$this->item->history = $this->helper->order->getStatusLog($this->item->id, '');

		foreach ($this->item->get('items') as $item)
		{
			// Order item history
			$item->history = $this->helper->order->getStatusLog($this->item->id, $item->item_uid);
		}

		return parent::display($tpl);
	}

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function prepareDisplay()
	{
		// However currently edit is not supported
		if ($this->getLayout() == 'edit')
		{
			$this->app->input->set('hidemainmenu', true);
		}

		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function setPageTitle()
	{
		if ($this->_layout == 'invoice')
		{
			JToolBarHelper::title(JText::sprintf('COM_SELLACIOUS_TITLE_ORDER_INVOICE_NUM', $this->item->get('order_number')), 'file-text');
		}
		elseif ($this->_layout == 'receipt')
		{
			JToolBarHelper::title(JText::sprintf('COM_SELLACIOUS_TITLE_ORDER_RECEIPT_NUM', $this->item->get('order_number')), 'file-text');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_SELLACIOUS_TITLE_ORDER'), 'file');
		}
	}
}

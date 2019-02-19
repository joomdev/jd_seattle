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
 * View class for a list of orders.
 *
 * @since  3.0
 */
class SellaciousViewOrders extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'order';

	/** @var  string */
	protected $view_item = 'order';

	/** @var  string */
	protected $view_list = 'orders';

	/** @var array */
	protected $lists = array();

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		$statuses              = $this->helper->order->getStatuses(null);
		$this->lists['status'] = $this->helper->core->arrayAssoc($statuses, 'id', 'title');

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		JToolBarHelper::custom($this->view_list . '.export', 'export', 'export', 'COM_SELLACIOUS_ORDERS_EXPORT_ORDERS', false);

		$toolbar = Toolbar::getInstance();
		$gDelete = new ButtonGroup('delete', 'JTOOLBAR_DELETE');
		$toolbar->appendGroup($gDelete);

		if ($this->helper->access->check($this->action_prefix . '.delete')
			|| $this->helper->access->check($this->action_prefix . '.delete.own'))
		{
			$gDelete->appendButton(new StandardButton('delete', 'COM_SELLACIOUS_ORDERS_DELETE_WITH_TRANSACTIONS', $this->view_list . '.delete', true));
			$gDelete->appendButton(new StandardButton('delete', 'COM_SELLACIOUS_ORDERS_DELETE_WITHOUT_TRANSACTIONS', $this->view_list . '.deletewot', true));
		}
		// todo: Verify and fix ajax based operations on this view
		// todo: Allow order status changing --> modal + ajax maybe
	}
}

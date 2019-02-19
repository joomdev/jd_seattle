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
 * View class for a list of transactions.
 */
class SellaciousViewTransactions extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'transaction';

	/** @var  string */
	protected $view_item = 'transaction';

	/** @var  string */
	protected $view_list = 'transactions';

	/** @var   stdClass[] */
	protected  $balances;

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		parent::prepareDisplay();

		$this->balances = $this->get('Balances');

		// Joomla only clears 'select' lists in the filter container
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('
			jQuery(document).on("click", ".js-stools-btn-clear", function () {
				jQuery(".js-stools-container-filters").find(":input").val("");
			});
		');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if ($this->helper->access->check($this->action_prefix . '.addfund.direct')
			|| $this->helper->access->check($this->action_prefix . '.addfund.direct.own')
			|| $this->helper->access->check($this->action_prefix . '.addfund.gateway')
			|| $this->helper->access->check($this->action_prefix . '.addfund.gateway.own'))
		{
			JToolBarHelper::addNew($this->view_item . '.add', 'COM_SELLACIOUS_TRANSACTION_BTN_ADDFUND');
		}

		if ($this->helper->access->check($this->action_prefix . '.withdraw')
			|| $this->helper->access->check($this->action_prefix . '.withdraw.own'))
		{
			JToolBarHelper::addNew($this->view_item . '.withdraw', 'COM_SELLACIOUS_TRANSACTION_BTN_WITHDRAW');
		}

		if ($this->helper->access->check($this->action_prefix . '.delete')
			|| $this->helper->access->check($this->action_prefix . '.delete.own'))
		{
			JToolBarHelper::deleteList('', $this->view_list  . '.delete', 'JTOOLBAR_DELETE');
		}

	}
}

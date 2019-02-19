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
 * View class for a list of Sellacious.
 */
class SellaciousViewCurrencies extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'currency';

	/** @var  string */
	protected $view_item = 'currency';

	/** @var  string */
	protected $view_list = 'currencies';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		if ($this->helper->access->check('currency.edit.forex'))
		{
			JToolBarHelper::apply('currencies.save', 'JTOOLBAR_APPLY');
			JToolBarHelper::custom('currencies.updateForex', 'refresh.png', 'refresh_f2.png', 'COM_SELLACIOUS_CURRENCY_FOREX_REFRESH', false);
		}

		parent::addToolbar();
	}
}

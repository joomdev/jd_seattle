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
 * View class for a list of order payment methods.
 */
class SellaciousViewPaymentMethods extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'paymentmethod';

	/** @var  string */
	protected $view_item = 'paymentmethod';

	/** @var  string */
	protected $view_list = 'paymentmethods';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		parent::addToolbar();

		JToolbarHelper::custom($this->view_list . '.discover', 'refresh', 'refresh', 'COM_SELLACIOUS_TOOLBAR_DISCOVER', false, false);
	}
}

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
 * View class for a seller form.
 */
class SellaciousViewSeller extends SellaciousViewForm
{
	/**
	 * @var  string
	 */
	protected $action_prefix = 'seller';

	/**
	 * @var  string
	 */
	protected $view_item = 'seller';

	/**
	 * @var  string
	 */
	protected $view_list = 'sellers';

	/**
	 * Display the view
	 *
	 * @param  string $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		if ($this->helper->seller->is())
		{
			JLog::add(JText::_('COM_SELLACIOUS_SELLER_REGISTER_ALREADY_REGISTERED'), JLog::NOTICE, 'jerror');

			return false;
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
	}
}

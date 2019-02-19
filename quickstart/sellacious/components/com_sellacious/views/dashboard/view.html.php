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
 * Dashboard view class
 *
 * @since   1.0.0
 */
class SellaciousViewDashboard extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected $balances;

	/**
	 * @var  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected $orderStats;

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $show_banners;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->show_banners = $this->helper->config->get('show_advertisement', 1) || !$this->helper->access->isSubscribed();

		return parent::display($tpl);
	}
}

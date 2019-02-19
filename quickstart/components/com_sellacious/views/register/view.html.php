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
 * View to edit a sellacious user account
 */
class SellaciousViewRegister extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'register';

	/** @var  string */
	protected $view_item = 'register';

	/** @var  string */
	protected $view_list = null;

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
		$me = JFactory::getUser();

		if (!$me->guest)
		{
			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=profile', false));
		}

		return parent::display($tpl);
	}

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	protected function prepareDisplay()
	{
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

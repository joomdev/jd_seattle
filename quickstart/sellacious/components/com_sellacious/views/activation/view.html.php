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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Sellacious Activation View
 *
 * @since   1.5.0
 */
class SellaciousViewActivation extends SellaciousView
{
	/**
	 * @var    Registry
	 *
	 * @since   1.5.0
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise a Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 *
	 * @since   12.2
	 */
	public function display($tpl = null)
	{
		$this->app->input->set('tmpl', 'component');

		if (!$this->helper->access->check('config.edit'))
		{
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		$this->item = $this->get('item');

		if ($this->_layout == 'default')
		{
			if ($this->item->get('license.active'))
			{
				$this->setLayout('registered');
			}
			else
			{
				$this->setLayout('register');
			}
		}

		return parent::display($tpl);
	}
}

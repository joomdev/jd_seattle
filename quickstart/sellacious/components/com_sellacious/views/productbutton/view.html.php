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
 * View to edit
 *
 * @since   1.6.0
 */
class SellaciousViewProductButton extends SellaciousViewForm
{
	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $action_prefix = 'productbutton';

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $view_item = 'productbutton';

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $view_list = 'productbuttons';

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function display($tpl = null)
	{
		try
		{
			$this->state = $this->get('State');
			$this->form  = $this->get('Form');
			$this->item  = $this->get('Item');

			if (!$this->form)
			{
				throw new Exception(implode('<br>', $this->get('Errors')));
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_SELLACIOUS_PRODUCT_BUTTON_VIEW_TITLE'), 'shopping-cart');

		JToolbarHelper::link('#', 'JTOOLBAR_APPLY', 'save');
		JToolbarHelper::cancel('productbutton.cancel', 'JTOOLBAR_CLOSE');
	}
}

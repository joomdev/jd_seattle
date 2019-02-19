<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for the Opc component
 *
 * @since  1.6.0
 */
class SellaciousOpcViewOpc extends SellaciousView
{
	/**
	 * Display a view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6.0
	 */
	public function display($tpl = null)
	{
		if (!$this->helper->config->get('allow_checkout'))
		{
			JLog::add(JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_DISABLED_MESSAGE'), JLog::WARNING, 'jerror');

			$redirect = $this->helper->config->get('redirect', 'index.php');

			JFactory::getApplication()->redirect($redirect);

			return;
		}

		$this->model    = $this->getModel();
		$this->state    = $this->get('State');
		$this->cart     = $this->get('Cart');
		$this->sections = $this->get('CartSections');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->setLayout($this->cart->count() == 0 ? 'empty' : 'default');

		$checkoutType = $this->helper->config->get('checkout_type', 1);

		if ($checkoutType == 1)
		{
			//Redirect to One page checkout
			JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_sellacious&view=cart&layout=aio'));
		}

		$doc = JFactory::getDocument();
		$doc->setTitle(JText::_('COM_SELLACIOUSOPC_CART_TITLE'));

		parent::display($tpl);
	}
}

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Payment Methods list controller class.
 *
 * @since   1.3.3
 */
class SellaciousControllerPaymentMethods extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 *
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PAYMENTMETHODS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.3.3
	 */
	public function getModel($name = 'PaymentMethod', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Reload the list of installed extensions and sync the payment methods that are linked to a plugin handler
	 *
	 * @return  bool
	 *
	 * @since   1.3.3
	 */
	public function discover()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			// Silently ignore if the user has no access for this.
			if ($this->helper->access->check('paymentmethod.create'))
			{
				$this->helper->paymentMethod->discover();
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}
}

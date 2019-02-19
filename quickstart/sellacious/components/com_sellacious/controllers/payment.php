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

class SellaciousControllerPayment extends SellaciousControllerBase
{
	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PAYMENT';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 * @since   12.2
	 */
	public function getModel($name = 'Payment', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Start processing the payment
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function initialize()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$payment_id = (int) $this->app->getUserState('com_sellacious.payment.execution.id');

		try
		{
			$payment = $this->helper->payment->getItem($payment_id);

			if ($payment->state != 0)
			{
				throw new Exception(JText::_($this->text_prefix . '_ALREADY_EXECUTED'));
			}

			// Throws exception in case of any error.
			$success = $this->helper->payment->initPayment();

			if ($success)
			{
				$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_SUCCESS'), 'success');

				$return = $this->app->getUserState('com_sellacious.payment.execution.success');
			}
			else
			{
				$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_FAILED'), 'warning');

				$return = $this->app->getUserState('com_sellacious.payment.execution.failure');
			}

			$this->setRedirect($return);
			$this->app->setUserState('com_sellacious.payment.execution', null);
		}
		catch (Exception $e)
		{
			$return = $this->app->getUserState('com_sellacious.payment.execution.cancel');

			$this->setMessage(JText::sprintf($this->text_prefix . '_CALL_FAILED', $e->getMessage()), 'error');
			$this->setRedirect($return);
			$this->app->setUserState('com_sellacious.payment.execution', null);

			return false;
		}

		return true;
	}

	/**
	 * In case a payment gateway requires a callback/redirect mechanism then the request
	 * would be forwarded the candidate plugin from here
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function callback()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			if ($this->helper->payment->executePayment())
			{
				$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_SUCCESS'), 'success');

				$return = $this->app->getUserState('com_sellacious.payment.execution.success');
			}
			else
			{
				$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_FAILED'), 'warning');

				$return = $this->app->getUserState('com_sellacious.payment.execution.failure');
			}

			$this->app->setUserState('com_sellacious.payment.execution', null);
			$this->setRedirect($return);
		}
		catch (Exception $e)
		{
			$return = $this->app->getUserState('com_sellacious.payment.execution.cancel');

			$this->app->setUserState('com_sellacious.payment.execution', null);
			$this->setMessage(JText::sprintf($this->text_prefix . '_RESPONSE_PROCESS_FAILED', $e->getMessage()), 'error');

			$this->setRedirect($return);

			return false;
		}

		return true;
	}

	/**
	 * In case user has selected to cancel the payment during the flow this method should be called.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function cancel()
	{
		jexit(__METHOD__ . ' used from where?');

		$return = $this->app->getUserState('com_sellacious.payment.execution.cancel');

		$this->setRedirect($return);
		$this->setMessage('Customer cancelled the payment transaction.', 'error');
		$this->app->setUserState('com_sellacious.payment.execution', null);

		return true;
	}
}

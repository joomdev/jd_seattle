<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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

			// Throws exception in case of any error, false for failed transaction.
			if ($this->helper->payment->initPayment())
			{
				if ($payment->handler == 'cod' || $payment->handler == 'custom')
				{
					$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_ORDER_SUCCESS'), 'success');
				}
				else
				{
					$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_SUCCESS'), 'success');
				}

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
	 *
	 * @since   1.0.0
	 */
	public function callback()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			// We don't need to know which plugin redirected in what context. We just forward the request
			$success = $this->helper->payment->executePayment();

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

			$this->app->setUserState('com_sellacious.payment.execution', null);
		}
		catch (Exception $e)
		{
			$this->app->setUserState('com_sellacious.payment.execution', null);
			$this->setMessage(JText::sprintf($this->text_prefix . '_RESPONSE_PROCESS_FAILED', $e->getMessage()), 'error');

			$return = $this->app->getUserState('com_sellacious.payment.execution.cancel');
		}

		if ($return)
		{
			$this->setRedirect($return);
		}
		else
		{
			$this->setRedirect('index.php');
		}

		return true;
	}

	/**
	 * In case a payment gateway requires a notification (e.g. PayPal IPN) mechanism then the request
	 * would be forwarded the candidate plugin from here. This may be called in a stateless environment so make sure
	 * that the feedback includes all necessary parameters to identify the context and state.
	 * Plugins should handle all the actions themselves as no further operations will be taken on the return from plugins.
	 *
	 * This is an unsafe interface and should be avoided as much as possible.
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function feedback()
	{
		try
		{
			/**
			 * We don't need to know which plugin redirected in what context.
			 * We just forward the request and terminate afterwards
			 */
			$this->helper->payment->apiFeedback();
		}
		catch (Exception $e)
		{
		}

		jexit();
	}
}

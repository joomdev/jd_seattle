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
 * Sellacious helper.
 *
 * @since  1.0.0
 */
class SellaciousHelperPayment extends SellaciousHelperBase
{
	/**
	 * Initiate payment process via online payment gateway
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function initPayment()
	{
		// Plugins should update the status of the payment including any relevant response information there.
		$dispatcher = $this->helper->core->loadPlugins();
		$results    = $dispatcher->trigger('onRequestPayment', array('com_sellacious.payment'));

		if (count($results) == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PAYMENT_NO_HANDLER_ACCEPTED_REQUEST'));
		}

		// Lookup for the payment record status for result
		$app        = JFactory::getApplication();
		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$payment    = $this->helper->payment->getItem($payment_id);

		$app->enqueueMessage($payment->response_message, 'info');

		return $payment->state > 0;
	}

	/**
	 * Execute payment process via online payment gateway post callback / redirect
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function executePayment()
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$results    = $dispatcher->trigger('onPaymentCallback', array('com_sellacious.payment'));

		if (count($results) == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PAYMENT_NO_HANDLER_ACCEPTED_APPROVAL_RESPONSE'));
		}

		// Lookup for the payment record status for result
		$app        = JFactory::getApplication();
		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$payment    = $this->helper->payment->getItem($payment_id);

		$app->enqueueMessage($payment->response_message, 'info');

		return $payment->state > 0;
	}

	/**
	 * Execute payment process via online payment gateway post callback / redirect
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function apiFeedback()
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onPaymentFeedback', array('com_sellacious.payment'));
	}

	/**
	 * Create a new payment which would be later called for execution with the respective handler
	 *
	 * @param   string  $context    Context/entity for which the payment is to be made
	 * @param   int     $order_id   Record id for the entity
	 * @param   int     $method_id  Payment method id to be used
	 * @param   float   $amount     Amount to be charged for the order; Transaction fees will be added by the handler.
	 * @param   string  $currency   Transaction currency
	 * @param   array   $data       Any form data submitted for the handler's payment form
	 *
	 * @return  int  The payment id
	 *
	 * @throws  Exception
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.0.0
	 */
	public function create($context, $order_id, $method_id, $amount, $currency, $data = null)
	{
		if (empty($context) || empty($order_id))
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PAYMENT_INVALID_CONTEXT'));
		}

		$method = $this->helper->paymentMethod->getItem($method_id);

		if (empty($method->id) || empty($method->handler))
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PAYMENT_INIT_INVALID_METHOD'));
		}

		if (empty($amount) || empty($currency))
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PAYMENT_INVALID_AMOUNT_OR_CURRENCY'));
		}

		// If the payment has already executed and was successful, we do not allow
		$keys = array('context' => $context, 'order_id' => $order_id, 'list.where' => 'a.state > 0');

		if ($this->helper->payment->count($keys) > 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PAYMENT_ALREADY_PAID'));
		}

		// Now everything looks ok. Attempt to create the payment record
		$table = $this->getTable();

		$g_currency  = $this->helper->currency->getGlobal('code_3');
		$flat_fee    = $this->helper->currency->convert($method->flat_fee, $g_currency, $currency);
		$percent_fee = ($amount * $method->percent_fee) / 100.0;

		$array = array(
			'context'        => $context,
			'order_id'       => $order_id,
			'method_id'      => $method->id,
			'method_name'    => $method->title,
			'handler'        => $method->handler,
			'data'           => $data,
			'currency'       => $currency,
			'order_amount'   => $amount,
			'flat_fee'       => $flat_fee,
			'percent_fee'    => $method->percent_fee,
			'fee_amount'     => $flat_fee + $percent_fee,
			'amount_payable' => round($amount + $flat_fee + $percent_fee, 2),
		);

		$table->bind($array);
		$table->check();
		$table->store();

		return $table->get('id');
	}

	/**
	 * Create a new empty payment record. Useful for free orders.
	 *
	 * @param   string  $context  Context/entity for which the payment is to be made
	 * @param   int     $orderId  Record id for the entity
	 *
	 * @return  int  The payment id
	 *
	 * @throws  Exception
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.0.0
	 */
	public function createEmpty($context, $orderId)
	{
		if (empty($context) || empty($orderId))
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PAYMENT_INVALID_CONTEXT'));
		}

		// If the payment has already executed and was successful, we do not allow
		$keys = array('context' => $context, 'order_id' => $orderId, 'list.where' => 'a.state > 0');

		if ($this->helper->payment->count($keys) > 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PAYMENT_ALREADY_PAID'));
		}

		// Now everything looks ok. Attempt to create the payment record
		$table    = $this->getTable();
		$currency = $this->helper->currency->getGlobal('code_3');

		$array = array(
			'context'        => $context,
			'order_id'       => $orderId,
			'method_id'      => 0,
			'method_name'    => JText::_('JNONE'),
			'handler'        => 'none',
			'data'           => null,
			'currency'       => $currency,
			'order_amount'   => 0.00,
			'flat_fee'       => 0.00,
			'percent_fee'    => 0.00,
			'fee_amount'     => 0.00,
			'amount_payable' => 0.00,
			'state'          => 2,
		);

		$table->bind($array);
		$table->check();
		$table->store();

		return $table->get('id');
	}
}

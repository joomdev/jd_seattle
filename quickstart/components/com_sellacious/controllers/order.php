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

use Joomla\Utilities\ArrayHelper;

/**
 * list controller class
 *
 * @since  1.1.0
 */
class SellaciousControllerOrder extends SellaciousControllerBase
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_ORDER';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 *
	 * @see     JControllerLegacy
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->registerTask('placeReturn', 'placeRequest');
		$this->registerTask('placeExchange', 'placeRequest');
	}

	/**
	 * Place a return or exchange request for an order item
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function placeRequest()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$data = $this->input->get('jform', array(), 'array');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=orders&layout=tiles', false));

		if (empty($data['item_uid']) || empty($data['order_id']))
		{
			$this->setMessage(JText::_($this->text_prefix . '_NO_ORDER_ITEM_SELECTED'));

			return false;
		}

		try
		{
			if (strcasecmp($this->getTask(), 'placeReturn') == 0)
			{
				$data['status'] = $this->helper->order->getStatusId('return_placed', true, 'order.physical');

				$message = JText::_($this->text_prefix . '_ITEM_RETURN_PLACED_SUCCESS');
			}
			elseif (strcasecmp($this->getTask(), 'placeExchange') == 0)
			{
				$data['status'] = $this->helper->order->getStatusId('exchange_placed', true, 'order.physical');

				$message = JText::_($this->text_prefix . '_ITEM_EXCHANGE_PLACED_SUCCESS');
			}
			else
			{
				return false;
			}

			// todo: May be validate at some point, and access check
			$this->helper->order->setStatus($data);

			try
			{
				$dispatcher = JEventDispatcher::getInstance();
				JPluginHelper::importPlugin('sellacious');
				$dispatcher->trigger('onAfterOrderChange', array('com_sellacious.order', $data['order_id']));
			}
			catch (Exception $e)
			{
				// Email sending failed. Ignore for now
			}

			$this->setMessage($message);
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Set payment info to an existing order
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function setPaymentAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			// Values are: [method_id, handler, [params]];
			$orderId  = $this->input->post->getInt('id');
			$formData = $this->input->post->get('jform', array(), 'array');

			// We can get "handler" from method object
			$methodId = ArrayHelper::getValue($formData, 'method_id');

			// Validate order id selection first
			$order = $this->helper->order->getTable();
			$order->load($orderId);

			if (!$order->get('id'))
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_ITEM'));
			}

			$me = JFactory::getUser();

			if ($order->get('customer_uid') != $me->id && !$this->helper->access->check('core.admin'))
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_ACCESS'));
			}

			$dispatcher = $this->helper->core->loadPlugins();

			// Check whether this is a Free Order
			if (abs($order->get('grand_total')) < 0.01)
			{
				$formData = new stdClass;

				$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.payment', &$formData, true));

				// Set payment parameters
				$paymentId = $this->helper->payment->createEmpty('order', $order->get('id'));

				$formData->payment_id = $paymentId;

				$dispatcher->trigger('onContentAfterSave', array('com_sellacious.payment', $formData, true));

				$token    = JSession::getFormToken();
				$link     = 'index.php?option=com_sellacious&task=order.onPayment&status=success&payment_id=' . $paymentId . '&' . $token . '=1';
				$response = array(
					'message'  => '',
					'data'     => $order->get('id'),
					'redirect' => $link,
					'status'   => 1,
				);
			}
			else
			{
				if (empty($methodId))
				{
					throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INVALID_METHOD'));
				}

				$method = $this->helper->paymentMethod->getMethod($methodId);

				if (!$method)
				{
					throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INVALID_METHOD'));
				}

				$form = $this->helper->paymentMethod->getForm($methodId);

				if (!($form instanceof JForm))
				{
					throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INVALID_METHOD_FORM'));
				}

				if (isset($method->credit_limit) && (abs($method->credit_limit - $order->get('grand_total')) < 0.01))
				{
					throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INSUFFICIENT_CREDIT_LIMIT'));
				}

				if (!$form->validate($formData))
				{
					$messages = array();
					$errors   = $form->getErrors();

					foreach ($errors as $error)
					{
						$messages[] = $error instanceof Exception ? $error->getMessage() : $error;
					}

					throw new Exception(JText::sprintf($this->text_prefix . '_PAYMENT_INFO_INVALID_FORM_PARAMS', implode('<br>', $messages)));
				}

				$params = ArrayHelper::getValue($formData, $method->handler, array(), 'array');

				if (empty($params))
				{
					// B/C for release before 1.5.3
					$params = ArrayHelper::getValue($formData, 'params', array(), 'array');
				}

				$formData = (object) $formData;

				$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.payment', &$formData, true));

				// Set payment parameters
				$paymentId = $this->helper->payment->create('order', $order->get('id'), $methodId, $order->get('grand_total'), $order->get('currency'));

				$formData->payment_id = $paymentId;

				$dispatcher->trigger('onContentAfterSave', array('com_sellacious.payment', $formData, true));

				// These URL success parameters are for quick context identification only.
				// Actual response can't be faked as we check the payment response back here again.
				$token = JSession::getFormToken();

				$successLink = 'index.php?option=com_sellacious&task=order.onPayment&status=success&payment_id=' . $paymentId;
				$failureLink = 'index.php?option=com_sellacious&task=order.onPayment&status=failure&payment_id=' . $paymentId;
				$cancelLink  = 'index.php?option=com_sellacious&task=order.onPayment&status=cancel&payment_id=' . $paymentId;

				$this->app->setUserState('com_sellacious.payment.execution.id', $paymentId);
				$this->app->setUserState('com_sellacious.payment.execution.params', $params);
				$this->app->setUserState('com_sellacious.payment.execution.success', $successLink . '&' . $token . '=1');
				$this->app->setUserState('com_sellacious.payment.execution.failure', $failureLink . '&' . $token . '=1');
				$this->app->setUserState('com_sellacious.payment.execution.cancel', $cancelLink . '&' . $token . '=1');

				$response = array(
					'message'  => '',
					'data'     => $order->get('id'),
					'redirect' => 'index.php?option=com_sellacious&task=payment.initialize&' . $token . '=1',
					'status'   => 1,
				);
			}
		}
		catch (Exception $e)
		{
			$this->app->setUserState('com_sellacious.payment.execution', null);

			$response = array(
				'message'  => JText::sprintf($this->text_prefix . '_PAYMENT_CONFIGURATION_FAILED', $e->getMessage()),
				'data'     => null,
				'status'   => 0,
			);

			if (isset($orderId))
			{
				$response['redirect'] = JRoute::_('index.php?option=com_sellacious&view=order&id=' . $orderId, false);
			}
			else
			{
				$response['redirect'] = JRoute::_('index.php?option=com_sellacious&view=orders', false);
			}
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Post process the order payment. The order status will be updated and the user will be notified according to the payment status.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onPayment()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$payment_id = $this->input->getInt('payment_id');
		// $state   = $this->input->getCmd('status');

		$payment  = $this->helper->payment->getItem($payment_id);
		$order_id = $payment->order_id;

		if ($payment->context != 'order' || !$order_id)
		{
			$this->setMessage(JText::_($this->text_prefix . '_INVALID_PAYMENT_RESPONSE'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=orders', false));

			return false;
		}

		if ($payment->state == 3)
		{
			// Set payment Authorised
			$this->helper->order->setStatusByType('order', 'authorized', $order_id, '', true, true, 'Payment Authorized');

			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=order&id=' . $order_id . '&layout=complete', false));
		}
		if ($payment->state == 2)
		{
			// Set payment Approved
			$this->helper->order->setStatusByType('order', 'approved', $order_id, '', true, true, 'Payment Approved');

			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=order&id=' . $order_id . '&layout=complete', false));
		}
		elseif ($payment->state == 1)
		{
			// Set payment Approval pending
			$this->helper->order->setStatusByType('order', 'paid', $order_id, '', true, true, 'Payment Approval Pending');

			// Don't execute any transactions yet
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=order&id=' . $order_id . '&layout=complete', false));
		}
		elseif ($payment->state == 0)
		{
			// We leave the order status to "pending" status in case payment "failed" or was "aborted" by the customer.
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=order&id=' . $order_id . '&layout=cancelled', false));
		}
		elseif ($payment->state == -1)
		{
			// We leave the order status to "pending" status in case payment "failed" or was "aborted" by the customer.
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=order&id=' . $order_id . '&layout=failed', false));
		}

		try
		{
			// Call plugins
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onAfterOrderPayment', array('com_sellacious.order', $payment));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'warning');
		}

		return true;
	}

	/**
	 * Track an order/item by source id or transaction id or cart id,
	 * The response is a JSON string.
	 *
	 * @since  1.4.5
	 */
	public function trackExternal()
	{
		$cartId = $this->input->getString('cart_id');
		$txnId  = $this->input->getString('transaction_id');
		$srcId  = $this->input->get('source_id');
		$fields = $this->input->getString('fields');

		try
		{
			$items = array();

			if ($cartId != '' || $txnId != '' || $srcId != '')
			{
				$items = $this->helper->order->trackOrder($cartId, $txnId, $srcId);
			}

			if ($fields && $items)
			{
				$fields = array_filter(explode(',', $fields));

				foreach ($items as $index => $item)
				{
					$tmp = new stdClass;

					foreach ($fields as $field)
					{
						if (isset($item->$field))
						{
							$tmp->$field = $item->$field;
						}
					}

					$items[$index] = $tmp;
				}
			}

			$response = array(
				'state'   => 1,
				'message' => '',
				'data'    => $items,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => sprintf('%s at %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}
}

<?php
/**
 * @version     1.6.1
 * @package     Sellacious Payment - PayPal Standard
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

// require the PayPal Standard SDK Auto-loader
JLoader::import('sellacious.loader');

/**
 * Plugin to manage payment via 'PayPal Standard' for sellacious shops checkout process
 *
 * @subpackage  PayPal  Standard - Sellacious Payments
 *
 * @since  1.2.0
 */
class plgSellaciousPaymentPayPalStandard extends SellaciousPluginPayment
{
	/**
	 * Returns handlers to the payment methods that will be managed by this plugin
	 *
	 * @param   string  $context    The calling context, must be 'com_sellacious.payment' to effect
	 * @param   array   &$handlers  ByRef, associative array of handlers
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function onCollectHandlers($context, array &$handlers)
	{
		// PayPal Standard library must exist, if not then skip
		if ($context == 'com_sellacious.payment')
		{
			$handlers['paypalstandard'] = JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_API');
		}

		return true;
	}

	/**
	 * Triggers payment method to make a payment for the given transaction
	 * The order must have been created in the database prior calling this method.
	 * Since we need a redirection mechanism in between, we need to alter the calling sequence.
	 *
	 * @param   string  $context  The calling context, must be 'com_sellacious.payment' to effect
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onRequestPayment($context)
	{
		$app = JFactory::getApplication();

		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$handler    = $this->helper->payment->getFieldValue($payment_id, 'handler');

		$result   = true;
		$handlers = array();
		$this->onCollectHandlers($context, $handlers);

		if (array_key_exists($handler, $handlers))
		{
			$invoice = $this->getInvoice();
			$this->initPayment($invoice);
		}

		return $result;
	}

	/**
	 * Triggers payment execution on callback return after paypalstandard approval
	 *
	 * @param   string  $context  The calling context, must be 'com_sellacious.payment' to effect
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onPaymentCallback($context)
	{
		$app = JFactory::getApplication();

		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$handler    = $this->helper->payment->getFieldValue($payment_id, 'handler');

		$result = true;

		if ($context == 'com_sellacious.payment' && $handler == 'paypalstandard')
		{
			$response = $this->executePayment();
			$result   = $this->handleResponse($response);
		}

		return $result;
	}

	/**
	 * Sellacious do not bother about the details a plugin might need.
	 * Plugins are set free to fetch what they want. However basic data is directly accessible.
	 *
	 * @return  array  All the required details for the transaction execution with the Payment Gateway
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function getInvoice()
	{
		$app = JFactory::getApplication();

		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$payment    = $this->helper->payment->getItem($payment_id);

		$array = array(
			'order_id'    => $payment->order_id,
			'payment_id'  => $payment_id,
			'currency'    => $payment->currency,
			'amount'      => $payment->amount_payable,
			'description' => $app->get('sitename'),
		);

		return $array;
	}

	/**
	 * Initialize SDK configurations using client key and secret or email with additional connection settings
	 *
	 * @return  stdClass  config for PayPal Standard
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function getApiContext()
	{
		$config = $this->getParams();

		$api       = new stdClass();
		$api->mode = $config->get('api_mode', 'sandbox');

		if ($api->mode == 'sandbox')
		{
			$api->sandbox      = true;
			$api->paypal_email = $config->get('sandbox_paypal_email');
		}
		else
		{
			$api->sandbox      = false;
			$api->paypal_email = $config->get('live_paypal_email');
		}

		if (empty($api->paypal_email))
		{
			throw new Exception(JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_API_CONFIG_NOT_SET'));
		}

		return $api;
	}

	/**
	 * Create a payment using the buyer's paypalstandard account as the funding instrument.
	 * The app will have to redirect the buyer to the paypalstandard website, obtain their consent to the payment
	 * and subsequently execute the payment using the execute API call.
	 *
	 * @param   mixed  $invoice  The data required by the payment gateway to execute the transaction
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function initPayment($invoice)
	{
		if (empty($invoice['amount']))
		{
			throw new Exception(JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_AMOUNT_IS_NOT_VALID'));
		}

		$callBackUrl = $this->getCallbackUrl(array('status' => 'approved'));
		$cancelUrl   = $this->getCallbackUrl(array('status' => 'cancelled'));

		$context    = $this->helper->payment->getFieldValue($invoice['payment_id'], 'context');
		$userDetail = $this->getUserDetail($invoice['order_id'], $context);

		$api = $this->getApiContext();

		$data                  = array();
		$data['business']      = $api->paypal_email;
		$data['cmd']           = '_xclick';
		$data['rm']            = '2';
		$data['business_name'] = $api->paypal_email;
		$data['item_name']     = $invoice['description'];
		$data['item_number']   = $invoice['order_id'];
		$data['amount']        = $invoice['amount'];
		$data['currency_code'] = $invoice['currency'];
		$data['return']        = $callBackUrl;
		$data['cancel_return'] = $cancelUrl;

		$paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';

		if ($api->sandbox)
		{
			$paypalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		$data['url']         = $paypalUrl;
		$data['user_detail'] = $userDetail;

		$layout = new JLayoutFile('paypalstandard', $basePath = __DIR__ . '/layout/');

		echo $layout->render($data);

		jexit();
	}

	/**
	 * Gives User payment details
	 *
	 * @param   $order_id
	 * @param   $context
	 *
	 * @return  bool|mixed
	 *
	 * @since   1.2.0
	 */
	protected function getUserDetail($order_id, $context)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($context == 'transaction')
		{
			$txn = $this->helper->transaction->getItem($order_id);

			if ($txn->context == 'user.id')
			{
				// Prefer 'user_id'
				$userId = !empty($txn->user_id) ? (int) $txn->user_id : (int) $txn->context_id;

				$query->select('a.name AS  firstname, a.mobile AS phone, a.address, a.city, a.zip, a.country, a.state_loc')
					->from('#__sellacious_addresses AS a')
					->select('u.email')
					->join('left', '#__users AS u ON u.id = a.user_id')
					->where('a.user_id = ' . $userId)
					->order('a.is_primary DESC');
			}
		}
		else
		{
			$query->select('customer_email AS email, bt_name AS firstname, bt_mobile AS phone, bt_address AS address, order_number')
				->from('#__sellacious_orders')
				->where('id = ' . (int) $order_id);
		}

		try
		{
			return $db->setQuery($query)->loadObject();
		}
		catch (Exception $e)
		{
		}

		return true;
	}

	/**
	 * Capture the authorized payment with the PayPal Standard API.
	 *
	 * @return  array  Transaction's response received from the gateway
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function executePayment()
	{
		$app     = JFactory::getApplication();
		$payment = array();

		if ($app->input->getString('status') == 'approved')
		{
			$data = $app->input->post->getArray(array());
			unset($data['option'], $data['task'], $data['status'], $data['Itemid']);

			$data['cmd'] = '_notify-validate';

			try
			{
				$api = $this->getApiContext();
				$url = 'https://www.paypal.com/cgi-bin/webscr';

				if ($api->sandbox)
				{
					$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
				}

				$http     = JHttpFactory::getHttp();
				$response = $http->post($url, $data, null, 30);
				$response = (object) $response;

				if (isset($response->body) && $response->body == 'VERIFIED')
				{
					$payment['amount']         = $app->input->getFloat('payment_gross');
					$payment['currency']       = $app->input->getString('mc_currency');
					$payment['txn_id']         = $app->input->getString('txn_id');
					$payment['txn_type']       = $app->input->getString('txn_type');
					$payment['verify_sign']    = $app->input->getString('verify_sign');
					$payment['payer_email']    = $app->input->getString('payer_email');
					$payment['payer_id']       = $app->input->getString('payer_id');
					$payment['receiver_email'] = $app->input->getString('receiver_email');
					$payment['auth']           = $app->input->getString('auth');
					$payment['status']         = $app->input->getString('status');
					$payment['payment_status'] = $app->input->getString('payment_status');
					$payment['payment_date']   = $app->input->getString('payment_date');
					$payment['item_number']    = $app->input->getString('item_number');
				}
			}
			catch (Exception $e)
			{
				$payment = null;
			}
		}

		return $payment;
	}

	/**
	 * Generate response data to be stored in the transaction log and save
	 *
	 * @param   array  $payment  The Payment object as the paypal standard response
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function handleResponse($payment)
	{
		$app = JFactory::getApplication();

		if ($payment['status'] == 'approved')
		{
			$status  = static::STATUS_APPROVED;
			$message = JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_API_PAYPALSTANDARD_PAYMENT_COMPLETE');

			if ($payment['payment_status'] == 'Pending')
			{
				$status  = static::STATUS_APPROVAL_HOLD;
				$message = JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_API_PAYPALSTANDARD_PAYMENT_PENDING');
			}

			$registry = new Joomla\Registry\Registry;
			$registry->set('transaction_id', $payment['txn_id']);
			$registry->set('txn_type', $payment['txn_type']);
			$registry->set('payer_id', $payment['payer_id']);
			$registry->set('verify_sign', $payment['verify_sign']);
			$registry->set('auth', $payment['auth']);
			$registry->set('sale_id', $payment['item_number']);
			$registry->set('payer_email', $payment['payer_email']);
			$registry->set('receiver_email', $payment['receiver_email']);
			$registry->set('created', $payment['payment_date']);
			$registry->set('payment_state', $payment['payment_status']);
			$registry->set('amount', $payment['amount']);
			$registry->set('currency', $payment['currency']);

			$data = array(
				'response_code'    => 'PAYPAL STANDARD:' . $payment['payment_status'],
				'response_state'   => $payment['payment_status'],
				'response_message' => $message,
				'response_data'    => $registry->toArray(),
				'transaction_id'   => $payment['txn_id'],
				'state'            => $status,
			);
		}
		else
		{
			$token   = $app->input->get('token', '', 'raw');
			$status  = static::STATUS_ABORTED;
			$message = JText::_('PLG_SELLACIOUSPAYMENT_PAYPALSTANDARD_API_PAYPALSTANDARD_PAYMENT_FAILED');

			$data = array(
				'response_code'    => 'PAYPAL STANDARD:aborted',
				'response_state'   => 'aborted',
				'response_message' => $message,
				'response_data'    => json_encode(array('token' => $token)),
				'transaction_id'   => '',
				'state'            => $status,
			);
		}

		$config = $this->getParams();
		$mode   = $config->get('api_mode', 'sandbox');
		$data   = ArrayHelper::toObject($data);

		$this->saveResponse($data->response_code, $data->response_state, $data->response_message, $data->response_data, $payment['txn_id'], $status, $mode == 'sandbox');

		return $status == 1;
	}
}

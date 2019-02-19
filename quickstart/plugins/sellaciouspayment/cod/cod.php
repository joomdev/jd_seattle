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

/**
 * Plugin to manage payment via 'Sellacious E-Wallet balance' for sellacious shops checkout process
 *
 * @subpackage  COD - Sellacious Payments
 */
class plgSellaciousPaymentCOD extends SellaciousPluginPayment
{
	/**
	 * Whether to force saving the submitted form data for the payment, useful for offline or similar type of payment methods
	 *
	 * @var    bool
	 *
	 * @since  1.4.4
	 */
	protected $forceSaveData = true;

	/**
	 * Returns handlers to the payment methods that will be managed by this plugin
	 *
	 * @param  string $context   The calling context, must be 'com_sellacious.payment' to effect
	 * @param  array  &$handlers ByRef, associative array of handlers
	 *
	 * @return bool
	 */
	public function onCollectHandlers($context, array &$handlers)
	{
		if ($context == 'com_sellacious.payment')
		{
			$handlers['cod'] = JText::_('PLG_SELLACIOUSPAYMENT_COD_API');
		}

		return true;
	}

	/**
	 * Sellacious do not bother about the details a plugin might need.
	 * Plugins are set free to fetch what they want. We'll fetch all the required details for the order here.
	 *
	 * @return  stdClass  All the required details for the transaction execution with the Payment Gateway
	 * @throws  Exception
	 */
	protected function getInvoice()
	{
		$app = JFactory::getApplication();

		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$params     = $app->getUserState('com_sellacious.payment.execution.params');

		$invoice = (object) array(
			'payment_id' => $payment_id,
			'params'     => $params,
		);

		return $invoice;
	}

	/**
	 * Initiate the payment with the Payment Gateway.
	 * If the gateway required any redirect mechanism then do the redirection here,
	 * and it will be captured via <var>onPaymentCallback()</var>. Otherwise return the response data only.
	 *
	 * @param   stdClass  $invoice  The data required by the payment gateway to execute the transaction
	 *
	 * @return  mixed  Transaction's response received from the gateway
	 * @throws  Exception
	 */
	protected function initPayment($invoice)
	{
		$response = new stdClass;

		$response->user_data = $invoice->params;
		$response->code      = 200;
		$response->approved  = true;
		$response->declined  = false;
		$response->error     = false;
		$response->message   = JText::_('PLG_SELLACIOUSPAYMENT_COD_TRANSACTION_APPROVED');

		return $response;
	}

	/**
	 * Generate response data to be stored in the transaction log and save
	 *
	 * @param   object  $response
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	protected function handleResponse($response)
	{
		$state = $response->approved ? 'APPROVED' : 'DECLINED';
		$data  = (object) array(
			'response_code'    => 'COD:' . $state,
			'response_state'   => $state,
			'response_message' => $response->message,
			'response_data'    => $response,
			'transaction_id'   => null,
			'state'            => $response->approved ? 1 : -1,
		);

		$this->saveResponse($data->response_code, $data->response_state, $data->response_message, $data->response_data, $data->transaction_id, $data->state, false);

		return $response->approved;
	}
}

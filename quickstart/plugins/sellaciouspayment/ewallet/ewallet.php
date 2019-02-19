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
use Sellacious\Transaction\TransactionHelper;

defined('_JEXEC') or die;

JLoader::import('sellacious.loader');

/**
 * Plugin to manage payment via 'Sellacious E-Wallet balance' for sellacious shops checkout process
 *
 * @subpackage  Ewallet - Sellacious Payments
 *
 * @since   1.2.0
 */
class plgSellaciousPaymentEwallet extends SellaciousPluginPayment
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
	 * @param   string  $context    The calling context, must be 'com_sellacious.payment' to effect
	 * @param   array   &$handlers  ByRef, associative array of handlers
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function onCollectHandlers($context, array &$handlers)
	{
		if ($context == 'com_sellacious.payment' && !JFactory::getUser()->guest)
		{
			$handlers['ewallet'] = JText::_('PLG_SELLACIOUSPAYMENT_EWALLET_API');
		}

		return true;
	}

	/**
	 * Sellacious do not bother about the details a plugin might need.
	 * Plugins are set free to fetch what they want. We'll fetch all the required details for the order here.
	 *
	 * @return  stdClass  All the required details for the transaction execution with the Payment Gateway
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function getInvoice()
	{
		$app = JFactory::getApplication();

		$payment_id = $app->getUserState('com_sellacious.payment.execution.id');
		$params     = $app->getUserState('com_sellacious.payment.execution.params');
		$payment    = $this->helper->payment->getItem($payment_id);

		$invoice = (object) array(
			'reason'   => $payment->context,
			'order_id' => $payment->order_id,
			'amount'   => $payment->amount_payable,
			'currency' => $payment->currency,
			'params'   => $params,
		);

		return $invoice;
	}

	/**
	 * Create a payment using a previously obtained credit card id.
	 * The corresponding credit card is used as the funding instrument.
	 *
	 * @param   stdClass  $invoice  The data required by the payment gateway to execute the transaction
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function initPayment($invoice)
	{
		// Let any exception here to propagate upward
		$me     = JFactory::getUser();
		$date   = JFactory::getDate()->toSql();
		$helper = SellaciousHelper::getInstance();

		$data = array(
			'id'         => null,
			'order_id'   => $invoice->order_id,
			'user_id'    => $me->id,
			'context'    => 'user.id',
			'context_id' => $me->id,
			'reason'     => $invoice->reason,
			'crdr'       => 'dr',
			'amount'     => $invoice->amount,
			'currency'   => $invoice->currency,
			'balance'    => null,
			'txn_date'   => $date,
			'state'      => 1,
		);

		list($balAmt) = TransactionHelper::getUserBalance($me->id, $invoice->currency);

		if ($balAmt < $invoice->amount)
		{
			$response = (object) $data;

			$response->code     = 403;
			$response->approved = false;
			$response->declined = true;
			$response->error    = false;
			$response->message  = JText::_('PLG_SELLACIOUSPAYMENT_EWALLET_BALANCE_LOW');

			return $response;
		}

		try
		{
			$data['balance'] = $balAmt - $invoice->amount;

			// Insert the transaction to deduct the balance from the ewallet. Behaving like a Gateway!
			$table = SellaciousTable::getInstance('Transaction');
			$table->bind($data);
			$table->check();
			$table->store();

			if ($table->get('id') > 0)
			{
				$response = (object) $table->getProperties();

				$response->code     = 200;
				$response->approved = true;
				$response->declined = false;
				$response->error    = false;
				$response->message  = JText::_('PLG_SELLACIOUSPAYMENT_EWALLET_TRANSACTION_APPROVED');
			}
			else
			{
				$response = (object) $data;

				$response->code     = 501;
				$response->approved = false;
				$response->declined = false;
				$response->error    = true;
				$response->message  = JText::_('PLG_SELLACIOUSPAYMENT_EWALLET_TRANSACTION_FAILED');
			}
		}
		catch (Exception $e)
		{
			$response = (object) $data;

			$response->code     = 501;
			$response->approved = false;
			$response->declined = false;
			$response->error    = true;
			$response->message  = JText::sprintf('PLG_SELLACIOUSPAYMENT_EWALLET_TRANSACTION_FAILED_ERROR', $e->getMessage());
		}

		return $response;
	}

	/**
	 * Generate response data to be stored in the transaction log and save
	 *
	 * @param   object  $response
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function handleResponse($response)
	{
		if ($response->approved)
		{
			$state = 'APPROVED';
		}
		elseif ($response->declined)
		{
			$state = 'DECLINED';
		}
		elseif ($response->error)
		{
			$state = 'ERROR';
		}
		else
		{
			$state = 'UNKNOWN';
		}

		$data = (object) array(
			'response_code'    => 'EWALLET:' . $state,
			'response_state'   => $state,
			'response_message' => $response->message,
			'response_data'    => (array) $response,
			'transaction_id'   => $response->id,
			'state'            => $response->approved ? 1 : -1,
		);

		$this->saveResponse($data->response_code, $data->response_state, $data->response_message, $data->response_data, $data->transaction_id, $data->state, true);

		return $response->approved;
	}
}

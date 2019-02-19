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

use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionHelper;

/**
 * Status controller class.
 */
class SellaciousControllerTransaction extends SellaciousControllerForm
{
	/**
	 * @var string
	 */
	protected $view_list = 'transactions';

	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_TRANSACTION';

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array $data An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowAdd($data = array())
	{
		$me    = JFactory::getUser();
		$type  = $this->app->getUserState("$this->option.edit.$this->context.type");
		$allow = false;

		if ($type == 'addfund')
		{
			$direct    = $this->helper->access->check('transaction.addfund.direct');
			$direct_o  = $this->helper->access->check('transaction.addfund.direct.own');
			$gateway   = $this->helper->access->check('transaction.addfund.gateway');
			$gateway_o = $this->helper->access->check('transaction.addfund.gateway.own');

			$mode    = ArrayHelper::getValue($data, 'mode');
			$user_id = ArrayHelper::getValue($data, 'user_id', $me->id);

			if ($mode == 'direct')
			{
				$allow = $direct || ($user_id == $me->id && $direct_o);
			}
			elseif ($mode == 'gateway')
			{
				$allow = $gateway || ($user_id == $me->id && $gateway_o);
			}
			else
			{
				// Mode can only be validated after submit
				$allow = $gateway || $direct || (($gateway_o || $direct_o) && $user_id == $me->id);
			}
		}
		elseif ($type == 'withdraw')
		{
			$withdraw   = $this->helper->access->check('transaction.withdraw');
			$withdraw_o = $this->helper->access->check('transaction.withdraw.own');

			$user_id = ArrayHelper::getValue($data, 'user_id', $me->id);

			$allow = $withdraw || ($user_id == $me->id && $withdraw_o);
		}

		return $allow;
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Strictly not allowed
		return false;
	}

	/**
	 * Method to add a new record for addfund.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 *
	 * @since   12.2
	 */
	public function add()
	{
		$this->app->setUserState("$this->option.edit.$this->context.type", 'addfund');

		if (!parent::add())
		{
			$this->app->setUserState("$this->option.edit.$this->context.type", null);

			return false;
		}

		return true;
	}

	/**
	 * Method to add a new record for withdrawal.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 *
	 * @since   12.2
	 */
	public function withdraw()
	{
		$this->app->setUserState("$this->option.edit.$this->context.type", 'withdraw');

		if (!parent::add())
		{
			$this->app->setUserState("$this->option.edit.$this->context.type", null);

			return false;
		}

		return true;
	}

	/**
	 * Common function to simply update the form data and update session for it.
	 * Can be used in all contexts such as change of parent, type, category etc.
	 *
	 * @return  bool
	 */
	public function setType()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = $this->input->get('jform', array(), 'array');

		$this->app->setUserState("$this->option.edit.$this->context.data", $post);
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend(), false
			)
		);

		return true;
	}

	/**
	 * Approve or disapprove a locked transaction via ajax
	 *
	 * @return  void
	 */
	public function setApproveAjax()
	{
		$pk    = $this->input->post->getInt('pk');
		$value = $this->input->post->get('value', array(), 'array');

		try
		{
			if (!$this->helper->access->check('transaction.withdraw.approve'))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			$record = $this->helper->transaction->getItem($pk);

			if ($record->id == 0 || $record->state != 2)
			{
				throw new Exception(JText::_($this->text_prefix . '_UPDATE_INVALID_RECORD_SELECTED'));
			}

			$model = $this->getModel();

			$model->setApprove($pk, $value['status'], $value['user_notes']);

			$state   = 1;
			$message = null;
			$data    = null;
		}
		catch (Exception $e)
		{
			$state   = 0;
			$message = $e->getMessage();
			$data    = null;
		}

		echo json_encode(array('state' => $state, 'message' => $message, 'data' => $data));

		jexit();
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param  JModelLegacy  $model      The data model object.
	 * @param  array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		if ($txn_id = (int) $model->getState('transaction.id'))
		{
			$this->app->setUserState("$this->option.edit.$this->context.data", null);

			$transaction = $this->helper->transaction->getItem($txn_id);

			if ($transaction->reason == 'addfund.gateway')
			{
				$this->initPayment($txn_id);
			}
			else
			{
				$this->app->setUserState("$this->option.edit.$this->context.id", null);
				$this->app->setUserState("$this->option.edit.$this->context.type", null);

				$this->input->set('layout', 'receipt');

				$this->setRedirect(
					JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($txn_id), false
					)
				);
			}
		}
	}

	/**
	 * Initialize payment for the given transaction and redirect
	 *
	 * @param   int  $txn_id  The transaction record id
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function initPayment($txn_id = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			$txn_id      = $txn_id ? $txn_id : $this->input->getInt('id');
			$transaction = $this->helper->transaction->getItem($txn_id);

			// todo: Also check if the selected transaction can have a payment routine or not.
			if ($transaction->reason != 'addfund.gateway')
			{
				return true;
			}

			if ($transaction->id == 0)
			{
				throw new Exception($this->text_prefix . '_PAYMENT_INVALID_ITEM');
			}
			elseif ($transaction->state != 0)
			{
				// We may want to allow cancelled/failed transactions as well
				throw new Exception($this->text_prefix . '_PAYMENT_PROCESSED_ALREADY');
			}

			// Set payment parameters
			$paymentId = $this->helper->payment->create('transaction', $transaction->id, $transaction->payment_method_id, $transaction->amount, $transaction->currency);

			// These URLs are for quick identification only > actual response cannot be faked coz we check the payment response back here again.
			$token = JSession::getFormToken();

			$successLink = JRoute::_('index.php?option=com_sellacious&task=transaction.onPayment&status=success&payment_id=' . $paymentId, false);
			$failureLink = JRoute::_('index.php?option=com_sellacious&task=transaction.onPayment&status=failure&payment_id=' . $paymentId, false);
			$cancelLink  = JRoute::_('index.php?option=com_sellacious&task=transaction.onPayment&status=cancel&payment_id=' . $paymentId, false);

			$this->app->setUserState('com_sellacious.payment.execution.id', $paymentId);
			$this->app->setUserState('com_sellacious.payment.execution.params', $transaction->payment_params);
			$this->app->setUserState('com_sellacious.payment.execution.success', $successLink . '&' . $token . '=1');
			$this->app->setUserState('com_sellacious.payment.execution.failure', $failureLink . '&' . $token . '=1');
			$this->app->setUserState('com_sellacious.payment.execution.cancel', $cancelLink . '&' . $token . '=1');

			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&task=payment.initialize&' . $token . '=1', false));
		}
		catch (Exception $e)
		{
			$this->app->setUserState('com_sellacious.payment.execution', null);
			$this->app->enqueueMessage($e->getMessage(), 'error');

			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=transactions', false));

			return false;
		}

		return true;
	}

	/**
	 * Post process the transaction after payment
	 * The status will be updated and the user will be notified according to the payment status
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function onPayment()
	{
		$payment_id = $this->input->getInt('payment_id');

		$payment = $this->helper->payment->getItem($payment_id);

		if ($payment->context != 'transaction' || !$payment->order_id)
		{
			$this->setMessage(JText::_($this->text_prefix . '_INVALID_PAYMENT_RESPONSE'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=transactions'));

			return false;
		}

		if ($payment->state == SellaciousPluginPayment::STATUS_APPROVED)
		{
			$this->helper->transaction->setApproved($payment->order_id);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_APPROVED'), 'success');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_APPROVAL_HOLD)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_APPROVAL_HOLD);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_APPROVAL'), 'info');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_ABORTED)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_CANCELLED);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_ABORTED'), 'notice');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_DECLINED)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_DECLINED);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_FAILED'), 'notice');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_PENDING)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_PENDING);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_PENDING'), 'notice');
		}

		$this->app->setUserState("$this->option.edit.$this->context.data", null);
		$this->app->setUserState("$this->option.edit.$this->context.id", null);
		$this->app->setUserState("$this->option.edit.$this->context.type", null);

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=transaction&layout=receipt&id=' . $payment->order_id, false));

		return true;
	}

	/**
	 * Get wallet balance of the selected user id via ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function getWalletBalanceAjax()
	{
		// Fixme: access check
		$user_id = $this->input->post->getInt('user_id');

		try
		{
			if (!$user_id)
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_USER_SPECIFIED'));
			}

			$currency = $this->helper->currency->getGlobal('code_3');
			$balances = $this->helper->transaction->getBalance($user_id);

			$balances = array_filter($balances, function ($value)
			{
				return abs($value->amount) >= 0.01;
			});

			foreach ($balances as &$balance)
			{
				$balance->convert_currency = $currency;
				$balance->convert_amount   = $this->helper->currency->convert($balance->amount, $balance->currency, $currency);
				$balance->convert_display  = $this->helper->currency->display($balance->amount, $balance->currency, $currency);
			}

			$response = array('state' => 1, 'message' => '', 'data' => array_values($balances));
		}
		catch (Exception $e)
		{
			$response = array('state' => 0, 'message' => $e->getMessage(), 'data' => null);
		}

		echo json_encode($response);

		$this->app->close();
	}

	/**
	 * Convert wallet balance in a selected currency of the selected seller uid to shop currency; via ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function convertBalanceAjax()
	{
		// fixme: access check
		$userId   = $this->input->post->getInt('user_id');
		$currency = $this->input->post->getString('currency');

		try
		{
			if (!$userId)
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_USER_SPECIFIED'));
			}

			// TODO: Allow conversion to any amount and currency by parameter
			list($balAmt) = TransactionHelper::getUserBalance($userId, $currency);
			$g_currency   = $this->helper->currency->getGlobal('code_3');

			if ($balAmt < 0.01)
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_FOREX_PARAMS'));
			}

			$done = TransactionHelper::forexConvert($userId, $balAmt, $currency, $g_currency);

			$response = array(
				'state'   => $done,
				'message' => JText::_($this->text_prefix . ($done ? '_FOREX_SUCCESS' : '_FOREX_FAILED')),
				'data'    => null,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		$this->app->close();
	}
}

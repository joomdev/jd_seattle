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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugin to manage payment via gateway integrations for sellacious shops checkout process
 *
 * @subpackage  Sellacious Payments
 *
 * @since   1.3.0
 */
abstract class SellaciousPluginPayment extends SellaciousPlugin
{
	const STATUS_PENDING = 0;

	const STATUS_ABORTED = -1;

	const STATUS_DECLINED = -2;

	const STATUS_AUTHORIZED = 3;

	const STATUS_APPROVAL_HOLD = 1;

	const STATUS_APPROVED = 2;

	/**
	 * The active payment record. This instance must be set on each plugin call
	 *
	 * @var  stdClass
	 *
	 * @since  1.5.3
	 */
	protected $payment;

	/**
	 * Whether to force saving the submitted form data for the payment, useful for offline or similar type of payment methods
	 *
	 * @var    bool
	 *
	 * @since  1.4.4
	 */
	protected $forceSaveData = false;

	/**
	 * Adds additional fields to the relevant sellacious form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.3.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!parent::onContentPrepareForm($form, $data))
		{
			return false;
		}

		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		$name = $form->getName();

		// No more inject plugin configuration into config form view. Its handled with payment methods.
		if ($name == 'com_sellacious.paymentmethod')
		{
			$my_handlers = array();
			$this->onCollectHandlers('com_sellacious.payment', $my_handlers);

			$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

			if (isset($array['handler']) && array_key_exists($array['handler'], $my_handlers))
			{
				// Load sys languages as we are going to show config page.
				$lang = JFactory::getLanguage();

				$extension = 'plg_' . $this->_type . '_' . $this->_name . '.sys';
				$lang->load($extension, JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load($extension, $this->pluginPath, null, false, true);

				// Inject plugin configuration into paymentmethod edit form.
				$form->loadFile($this->pluginPath . "/$this->_name.xml", false, '//config');
			}
		}

		return true;
	}

	/**
	 * Returns handlers to the payment methods that will be managed by this plugin
	 *
	 * @param   string  $context    The calling context, must be 'com_sellacious.payment' to effect
	 * @param   array   &$handlers  ByRef, associative array of handlers
	 *
	 * @return  bool
	 *
	 * @since   1.3.0
	 */
	abstract public function onCollectHandlers($context, array &$handlers);

	/**
	 * Adds plugin form(s) to the sellacious payment params form for user to fil, such as card details etc.
	 *
	 * @param   string    $context  The calling context
	 * @param   stdClass  $method   Payment Method record object. The "xml" attribute will be added to relevant item.
	 *
	 * @return  bool
	 *
	 * @since   1.3.0
	 */
	public function onLoadPaymentForm($context, &$method)
	{
		// Check for valid contexts.
		$regex   = '|^com_sellacious\.payment\.(\w+)$|';
		$matches = array();

		if (preg_match($regex, $context, $matches))
		{
			$my_handlers = array();
			$payment_ctx = array_pop($matches);

			$this->onCollectHandlers('com_sellacious.payment', $my_handlers);

			if ($method->handler && array_key_exists($method->handler, $my_handlers))
			{
				$xml = $this->getFormXml($method, $payment_ctx);

				if ($xml instanceof SimpleXMLElement)
				{
					$method->xml = $xml;
				}
			}
		}

		return true;
	}

	/**
	 * Get the form for selected handler. Some plugins may set up different forms for different contexts.
	 *
	 * @param   stdClass  $method   payment method object
	 * @param   string    $context  The Payment Context, i.e. for what the payment is to be made - cart/add fund etc
	 *
	 * @return  SimpleXMLElement
	 *
	 * @since   1.3.0
	 */
	protected function getFormXml($method, $context = null)
	{
		if (is_dir($this->pluginPath . '/forms/fields'))
		{
			JFormHelper::addFieldPath($this->pluginPath . '/forms/fields');
		}

		if (is_dir($this->pluginPath . '/forms/rules'))
		{
			JFormHelper::addRulePath($this->pluginPath . '/forms/rules');
		}

		$path = $this->pluginPath . '/forms/' . $method->handler . '.xml';

		if (file_exists($path) && ($xml = simplexml_load_file($path)))
		{
			return $xml;
		}

		return null;
	}

	/**
	 * Triggers payment method to make a payment for the given transaction
	 * The order must have been created in the database prior calling this method
	 * If a redirection mechanism is needed in between, this may be overridden to alter the calling sequence.
	 *
	 * @param   string  $context  The calling context, must be 'com_sellacious.payment' to effect
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	public function onRequestPayment($context)
	{
		$paymentId = $this->getState('id');
		$handler   = $this->helper->payment->getFieldValue($paymentId, 'handler');

		$result   = true;
		$handlers = array();
		$this->onCollectHandlers($context, $handlers);

		if (array_key_exists($handler, $handlers))
		{
			$invoice  = $this->getInvoice();
			$response = $this->initPayment($invoice);
			$result   = $this->handleResponse($response);
		}

		return $result;
	}

	/**
	 * Triggers payment execution on callback return.
	 * This is useful when the gateway requires redirection to its website as part of the transaction.
	 * The GET parameters can be accessed simply with the JInput call.
	 *
	 * @param   string  $context  The calling context, must be 'com_sellacious.payment' to effect
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	public function onPaymentCallback($context)
	{
		return true;
	}

	/**
	 * Sellacious do not bother about the details a plugin might need.
	 * Plugins are set free to fetch what they need for the payment execution operation.
	 *
	 * @return  mixed  All the required details
	 *
	 * @since   1.3.0
	 */
	abstract protected function getInvoice();

	/**
	 * Initialize API configurations with any required token keys or additional settings if any.
	 *
	 * @throws  Exception
	 * @return  object     SDK API settings
	 *
	 * @since   1.3.0
	 */
	protected function getApiContext()
	{
		return null;
	}

	/**
	 * Initiate the payment with the Payment Gateway.
	 * If the gateway required any redirect mechanism then do the redirection here,
	 * and it will be captured via <var>onPaymentCallback()</var>. Otherwise return the response data only.
	 *
	 * @param   mixed  $invoice  The data required by the payment gateway to execute the transaction
	 *
	 * @return  mixed  Transaction's response received from the gateway
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	abstract protected function initPayment($invoice);

	/**
	 * Execute the actual payment with the Payment Gateway.
	 *
	 * @return  mixed  Transaction's response received from the gateway
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	protected function executePayment()
	{
		return null;
	}

	/**
	 * Generate response data to be stored in the transaction log and save
	 *
	 * @param   mixed  $response  The response received from the payment Gateway or API for a transaction
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	abstract protected function handleResponse($response);

	/**
	 * Method to get a state value from the plugin state.
	 * DO NOT directly read from $this->app->getUserState(), this is expected to change in future releases.
	 *
	 * @param   string  $name     The key for state variable
	 * @param   mixed   $default  The default value
	 *
	 * @return  mixed
	 *
	 * @since   1.5.3
	 */
	protected function getState($name, $default = null)
	{
		return $this->app->getUserState('com_sellacious.payment.execution.' . $name, $default);
	}

	/**
	 * Method to set a state value in the plugin state.
	 * DO NOT directly write to $this->app->setUserState(), this is expected to change in future releases.
	 *
	 * @param   string  $name   The key for state variable
	 * @param   mixed   $value  The default value
	 *
	 * @return  mixed
	 *
	 * @since   1.5.3
	 */
	protected function setState($name, $value)
	{
		return $this->app->setUserState('com_sellacious.payment.execution.' . $name, $value);
	}

	/**
	 * Return the plugin params from the selected payment method
	 *
	 * @param   int  $methodId  Explicit method id to load params from, during payment process use session value only.
	 *
	 * @return  Registry
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	protected function getParams($methodId = null)
	{
		if (!$methodId)
		{
			if (isset($this->payment))
			{
				$methodId  = $this->payment->method_id;
			}
			else
			{
				$paymentId = $this->getState('id');
				$methodId  = $this->helper->payment->getFieldValue($paymentId, 'method_id');
			}
		}

		$params = $this->helper->paymentMethod->getFieldValue($methodId, 'params');

		return new Registry($params);
	}

	/**
	 * Method to update the payment status and response data after a response is received from the relevant gateway.
	 *
	 * @param   string  $response_code     Response code as received from gateway
	 * @param   string  $response_state    Response status identifier string/numeric identifier
	 * @param   string  $response_message  Response message to display. May be manipulated to easy understandable language
	 * @param   array   $response_data     Entire response data (or at least the important values) in JSON
	 * @param   string  $transaction_id    Transaction Id returned by the gateway
	 * @param   int     $state             Final status of the payment. 0 = pending, 1 = success, -1 = failed
	 *                                     Success state will be converted to => 1 = approval pending, 2 = approved
	 * @param   bool    $test_mode         Whether the payment transaction was executed in test mode
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	protected function saveResponse($response_code, $response_state, $response_message, $response_data, $transaction_id, $state, $test_mode)
	{
		$payment_id   = $this->getState('id');
		$payment_data = $this->getState('params');
		$now          = JFactory::getDate();

		$method_id = $this->helper->payment->getFieldValue($payment_id, 'method_id');
		$method    = $this->helper->paymentMethod->getMethod($method_id);

		// Success state "1" will be converted to => 1 = approval pending, 2 = approved
		if ($state == 1)
		{
			$state = $method->success_status ? 2 : 1;
		}

		// If save data is enabled we will save it now
		if (!($saveData = $this->forceSaveData))
		{
			$options  = new Registry($method->params);
			$saveData = $options->get('save_form_data');
		}

		$data     = array(
			'data'             => $saveData ? null : $payment_data,
			'response_code'    => $response_code,
			'response_state'   => $response_state,
			'response_message' => $response_message,
			'response_data'    => is_string($response_data) ? $response_data : json_encode($response_data),
			'transaction_id'   => $transaction_id,
			'state'            => $state,
			'test_mode'        => $test_mode,
		);

		try
		{
			$table = $this->helper->payment->getTable();
			$table->load($payment_id);

			if (isset($method->credit_limit))
			{
				// Fixme: We should not use session dependant user instance, payment record should have the userid saved.
				$user = JFactory::getUser();

				$transactions = array(
					(object) array(
						'id'         => null,
						'user_id'    => $user->id,
						'order_id'   => $table->get('order_id'),
						'context'    => 'user.id',
						'context_id' => $user->id,
						'reason'     => $table->get('context'),
						'crdr'       => 'dr',
						'amount'     => $table->get('amount_payable'),
						'currency'   => $table->get('currency'),
						'balance'    => null,
						'txn_date'   => $now->toSql(),
						'notes'      => 'Credit availed for payment via ' . $table->get('method_name') . '. Payment #' . $table->get('id'),
						'state'      => 1,
					)
				);

				$this->helper->transaction->register($transactions);
			}

			/**
			 * If at this stage we find the payment is not anymore 'pending' status we should copy it to a new record.
			 * Hence we do not lose this transaction's history/response. This is important for future tracking of transactions.
			 */
			if ($table->get('state') != 0)
			{
				$table->set('id', 0);
				$table->set('created', null);
				$table->set('created_by', null);
			}

			$table->bind($data);
			$table->check();
			$table->store();
		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('COM_SELLACIOUS_PAYMENT_UPDATE_FAILURE', $e->getMessage()));

			$logPath    = $this->app->get('log_path');
			$data['id'] = $payment_id;
			$dump_code  = md5(microtime()) . '-' . $payment_id;

			if (is_dir($logPath) && is_writable($logPath))
			{
				file_put_contents($logPath . "/payment_dump-$dump_code.payment", serialize($data));
			}
			elseif (is_writable(JPATH_SITE))
			{
				file_put_contents(JPATH_SITE . "/payment_dump-$dump_code.payment", serialize($data));
			}
			else
			{
				JLog::add(JText::sprintf('COM_SELLACIOUS_PAYMENT_DUMP_WRITE_FAILURE', serialize($data)), JLog::EMERGENCY);
			}

			JLog::add(JText::sprintf('COM_SELLACIOUS_PAYMENT_UPDATE_FAILURE_DUMP_WRITTEN', $dump_code), JLog::ERROR, 'jerror');
		}

		return true;
	}

	/**
	 * Get the callback url for API redirect callback
	 *
	 * @param   array  $vars       Additional URL parameters to add to the URL
	 * @param   bool   $stateless  Use stateless interface, meaning the session token will not be validated
	 *
	 * @return  string
	 *
	 * @since   1.4.4
	 */
	protected function getCallbackUrl(array $vars = array(), $stateless = false)
	{
		$token = JSession::getFormToken();

		if ($stateless)
		{
			$query = array(
				'option' => 'com_sellacious',
				'task'   => 'payment.feedback',
			);

			if (isset($this->payment))
			{
				$query['payment_id'] = $this->payment->id;
			}

			$query = array_merge($query, $vars);
		}
		else
		{
			$query = array(
				'option' => 'com_sellacious',
				'task'   => 'payment.callback',
			);
			$query = array_merge($query, $vars, array($token => 1));
		}

		$url = JUri::base() . 'index.php?' . http_build_query($query);

		return  $url;
	}
}

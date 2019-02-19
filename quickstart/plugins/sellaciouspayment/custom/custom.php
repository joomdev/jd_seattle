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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Plugin to manage payment via 'Custom/offline methods' for sellacious shops checkout process
 *
 * @subpackage  Custom - Sellacious Payments
 */
class plgSellaciousPaymentCustom extends SellaciousPluginPayment
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
	 */
	public function onCollectHandlers($context, array &$handlers)
	{
		if ($context == 'com_sellacious.payment')
		{
			$handlers['custom'] = JText::_('PLG_SELLACIOUSPAYMENT_CUSTOM_API');
		}

		return true;
	}

	/**
	 * Get the form for selected handler. Some plugins may set up different forms for different contexts.
	 *
	 * @param   stdClass  $method   payment method object
	 * @param   string    $context  The Payment Context, i.e. for what the payment is to be made
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getFormXml($method, $context = null)
	{
		$xml = parent::getFormXml($method, $context);

		if (isset($method->params))
		{
			// Add custom fields for the method to extend the form.
			$params    = new Registry($method->params);
			$field_ids = $params->get('form_fields');
			$field_ids = ArrayHelper::toInteger($field_ids);

			if (count($field_ids) && $xml instanceof SimpleXMLElement)
			{
				$filter    = array(
						'list.select' => 'a.id',
						'list.order'  => 'a.lft',
						'list.where'  => array('a.level > 1', 'a.state = 1'),
						'id'          => $field_ids,
				);
				$field_ids = $this->helper->field->loadColumn($filter);

				if ($field_ids)
				{
					$groups = $xml->xpath('//fields[@name="params"]');

					foreach ($field_ids as $field_id)
					{
						$this->helper->field->addXmlElement($field_id, $groups[0]);
					}
				}
			}
		}

		return $xml;
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
		$payment    = $this->helper->payment->getItem($payment_id);

		$invoice = (object) array(
			'payment_method' => $payment->method_name,
			'params'         => $params,
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
		$response->message   = JText::sprintf('PLG_SELLACIOUSPAYMENT_CUSTOM_TRANSACTION_APPROVED', $invoice->payment_method);

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
			'response_code'    => 'CUSTOM:' . $state,
			'response_state'   => $state,
			'response_message' => $response->message,
			'response_data'    => $response,
			'transaction_id'   => null,
			'state'            => $response->approved ? 1 : -1,
		);

		$this->saveResponse($data->response_code, $data->response_state, $data->response_message, $data->response_data, $data->transaction_id, $data->state, true);

		return $response->approved;
	}
}

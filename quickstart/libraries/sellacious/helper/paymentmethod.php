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

use Joomla\Registry\Registry;
use Sellacious\Transaction\TransactionHelper;

/**
 * Sellacious helper.
 *
 * @since  1.3.0
 */
class SellaciousHelperPaymentMethod extends SellaciousHelperBase
{
	/**
	 * Discover new payment method plugins and load them into our payment method table
	 *
	 * @return  int
	 *
	 * @since   1.3.0
	 */
	public function discover()
	{
		$filter    = array('list.select' => 'a.handler', 'list.group' => 'a.handler');
		$installed = $this->loadColumn($filter);
		$handlers  = $this->getHandlers();
		$count     = 0;

		foreach ($handlers as $handler => $title)
		{
			if (!in_array($handler, $installed))
			{
				$table = $this->getTable();
				$table->set('title', $title);
				$table->set('description', '');
				$table->set('handler', $handler);
				$table->set('success_status', '');
				$table->set('percent_fee', '0');
				$table->set('flat_fee', '0');
				$table->set('state', '0');

				$table->check();
				$table->store();

				$count++;
			}
		}

		return true;
	}

	/**
	 * Get all active handlers from sellacious payment plugins
	 *
	 * @return  array
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	public function getHandlers()
	{
		$handlers   = array();
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onCollectHandlers', array('com_sellacious.payment', &$handlers));

		return $handlers;
	}

	/**
	 * Get a payment method form from sellacious payment plugins
	 *
	 * @param   object|int  $method  Method object or the method_id for which to load the form
	 *
	 * @return  JForm
	 */
	public function getForm($method)
	{
		if (is_numeric($method))
		{
			$method = $this->getItem($method);
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$result     = $dispatcher->trigger('onLoadPaymentForm', array('com_sellacious.payment.cart', &$method));

		// Skip if something went wrong or no plugin handler could match.
		if (in_array(false, $result, true) || !isset($method->xml))
		{
			return null;
		}

		$form = null;
		$xml  = $method->xml;

		unset($method->xml);

		if ($xml instanceof SimpleXMLElement)
		{
			$fieldsets = $xml->xpath('/form/fieldset[@name="payment"]');
			$fGroups   = $xml->xpath('/form/fieldset[@name="payment"]/fields[@name="params"]');

			// Create empty form if xml is not valid or incomplete.
			if (count($fieldsets) == 0 || count($fGroups) == 0)
			{
				$xml = new SimpleXMLElement('<form><fieldset name="payment"></fieldset></form>');
			}

			$fieldsets = $xml->xpath('/form/fieldset[@name="payment"]');

			$fld = $fieldsets[0]->addChild('field');
			$fld->addAttribute('name', 'method_id');
			$fld->addAttribute('id', 'method_' . $method->id);
			$fld->addAttribute('type', 'hidden');
			$fld->addAttribute('default', $method->id);

			$formName = 'com_sellacious.payment.' . $method->handler . '.method' . $method->id;
			$form     = JForm::getInstance($formName, $xml->asXML(), array('control' => 'jform'));

			// Skip if JForm could not be built.
			if ($form instanceof JForm)
			{
				$results = $dispatcher->trigger('onContentPrepareForm', array($form, array()));

				if (count($results) && in_array(false, $results, true))
				{
					$form = null;

					JLog::add(JText::sprintf('COM_SELLACIOUS_PAYMENTMETHOD_PREPROCESS_FAILED', $method->title), JLog::WARNING);
				}
			}
		}

		return $form;
	}

	/**
	 * Get a list of payment methods applicable for the selected context. This also skips methods with missing handlers.
	 * Optionally we can also get the form for each payment methods along the method object.
	 *
	 * @param   string  $context   Selected context for which to load the methods. such as: cart, addfund
	 * @param   bool    $withForm  Whether to load the method form as well, a 'form' property will be appended to each method object.
	 * @param   int     $userId    The user id for which to retrieve the payment methods/ false = guest_checkout
	 * @param   int     $orderId   The order id/transaction id etc, whatever is relevant for the said context
	 *
	 * @return  stdClass[]
	 */
	public function getMethods($context, $withForm = false, $userId = null, $orderId = 0)
	{
		$methods      = array();
		$handlers     = $this->getHandlers();
		$items        = $this->loadObjectList(array('state' => 1));
		$isSubscribed = $this->helper->access->isSubscribed();
		$user         = JFactory::getUser($userId ?: null);
		$hasCredit    = false;
		$gCurrency    = null;
		$uLimit       = 0;

		// Find defined credit limit
		if ($isSubscribed)
		{
			$filters   = array('list.select' => 'a.credit_limit', 'user_id' => $user->id);
			$uLimit    = $this->helper->client->loadResult($filters);
			$gCurrency = $this->helper->currency->getGlobal('code_3');
			$hasCredit = abs($uLimit) >= 0.01;
		}

		foreach ($items as $method)
		{
			$contexts = (array) json_decode($method->contexts, true);
			$mContext = in_array($context, $contexts);
			$mHandler = array_key_exists($method->handler, $handlers);

			// Handler available and context matched
			if (!$mContext || !$mHandler)
			{
				continue;
			}

			$method->params = json_decode($method->params) ?: array();
			$registry       = new Registry($method);

			// Let the plugins modify the object if needed
			$dispatcher  = $this->helper->core->loadPlugins();
			$plugin_args = array('com_sellacious.paymentmethod.' . $context, $registry, $orderId);
			$responses   = $dispatcher->trigger('onBeforeLoadPaymentMethod', $plugin_args);

			// Validate plugin responses for payment methods
			if (in_array(false, $responses, true))
			{
				continue;
			}

			$method = $registry->toObject();
			$params = $registry->extract('params');

			$useCredit = $isSubscribed && $params->get('use_credit');

			// Check guest checkout allowed or not + no credit for guest checkout
			if ($userId === false && (!$method->allow_guest || $useCredit))
			{
				continue;
			}

			// Check for credit limit
			$crLimit = null;

			if ($useCredit)
			{
				if (!$hasCredit && !$params->get('credit_everyone'))
				{
					continue;
				}

				list($balAmt) = TransactionHelper::getUserBalance($user->id, $gCurrency);

				$cDef    = $params->get('credit_default');
				$crLimit = $balAmt + ($hasCredit ? $uLimit : $cDef);
			}

			$method->credit_limit = $crLimit;

			// Form required for each method object
			if ($withForm)
			{
				$method->form = $this->getForm($method);

				if (!isset($method->form))
				{
					continue;
				}
			}

			$plugin_args = array('com_sellacious.paymentmethod.' . $context, $registry);
			$responses   = $dispatcher->trigger('onAfterLoadPaymentMethod', $plugin_args);

			// Collect for output
			$methods[] = $method;
		}

		return $methods;
	}

	/**
	 * Get a payment method by id
	 * Optionally we can also get the form for each payment methods along the method object.
	 *
	 * @param   int   $id        The method id
	 * @param   bool  $withForm  Whether to load the method form as well, a 'form' property will be appended to each method object.
	 *
	 * @return  stdClass
	 */
	public function getMethod($id, $withForm = false)
	{
		$handlers = $this->getHandlers();
		$method   = $this->loadObject(array('id' => $id));
		$mHandler = $method ? array_key_exists($method->handler, $handlers) : false;

		if (!$mHandler)
		{
			return null;
		}

		// Using current active user instead of the cart user. Need to fix this when fully implementing utilisation of other cart user.
		$user      = JFactory::getUser();
		$filters   = array('list.select' => 'a.credit_limit', 'user_id' => $user->id);
		$gCurrency = $this->helper->currency->getGlobal('code_3');
		$uLimit    = $this->helper->client->loadResult($filters);
		$hasCredit = abs($uLimit) >= 0.01;

		$params = new Registry($method->params);
		$cUse   = $params->get('use_credit');
		$cAll   = $params->get('credit_everyone');
		$cDef   = $params->get('credit_default');

		if (!$cUse)
		{
			$method->credit_limit = null;
		}
		elseif ($hasCredit || $cAll)
		{
			list($balAmt) = TransactionHelper::getUserBalance($user->id, $gCurrency);

			$method->credit_limit = $balAmt + ($hasCredit ? $uLimit : $cDef);
		}
		else
		{
			// Skip this payment method as this is not available for current user
			return null;
		}

		// If no form requested then we are ready. Payment may later fail when form is loaded, coz we didn't verify form yet.
		if (!$withForm)
		{
			return $method;
		}

		$method->form = $this->getForm($method);

		return (isset($method->form) && $method->form instanceof JForm) ? $method : null;
	}
}

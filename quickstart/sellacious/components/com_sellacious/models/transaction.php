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

use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionHelper;

/**
 * Sellacious model.
 *
 * @since   1.1.0
 */
class SellaciousModelTransaction extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('transaction.delete') ||
			($this->helper->access->check('transaction.delete.own') && $record->context_id == JFactory::getUser()->id);

	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		// fixme: check this
		return $this->helper->access->check('transaction.edit.state');
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');

		$vData = parent::validate($form, $data, $group);

		if ($type == 'addfund')
		{
			if (!isset($vData['mode']) || ($vData['mode'] != 'direct' && $vData['mode'] != 'gateway'))
			{
				$this->setError(JText::_('COM_SELLACIOUS_TRANSACTION_ERROR_ADDFUND_MODE_INVALID'));

				return false;
			}

			$vData['currency'] = $this->helper->currency->getGlobal('code_3');
		}
		elseif ($type == 'withdraw')
		{
			$withdrawal = $vData['amount'];

			if (!is_array($withdrawal) || !isset($withdrawal['amount'], $withdrawal['currency']))
			{
				$this->setError(JText::sprintf('COM_SELLACIOUS_TRANSACTION_INVALID_AMOUNT'));
			}

			$currency = $this->helper->currency->getFieldValue($withdrawal['currency'], 'code_3');

			try
			{
				list($balAmt) = TransactionHelper::getUserBalance($vData['user_id'], $currency);
				if (round($balAmt - $withdrawal['amount'], 2) < 0.00)
				{
					$bal_d = $this->helper->currency->display($balAmt, $currency, null);
					$amt_d = $this->helper->currency->display($withdrawal['amount'], $currency, null);

					throw new Exception(JText::sprintf('COM_SELLACIOUS_TRANSACTION_INSUFFICIENT_WALLET_BALANCE', $amt_d, $bal_d));
				}
				$vData['amount']   = $withdrawal['amount'];
				$vData['currency'] = $currency;
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		return $vData;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$me   = JFactory::getUser();
		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');
		$data = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if ($type == 'withdraw')
		{
			if (!$this->helper->access->check('transaction.withdraw'))
			{
				$data->user_id = $this->helper->access->check('transaction.withdraw.own') ? $me->id : null;
			}
		}
		else
		{
			$modes     = array();
			$gateway_a = $this->helper->access->check('transaction.addfund.gateway');
			$gateway_o = $this->helper->access->check('transaction.addfund.gateway.own');
			$direct_a  = $this->helper->access->check('transaction.addfund.direct');
			$direct_o  = $this->helper->access->check('transaction.addfund.direct.own');

			if ($gateway_a || $gateway_o)
			{
				$modes[] = 'gateway';
			}

			if ($direct_a || $direct_o)
			{
				$modes[] = 'direct';
			}

			$data->mode = empty($data->mode) || !in_array($data->mode, $modes) ? reset($modes) : $data->mode;

			if ($data->mode == 'gateway')
			{
				if (!$gateway_a)
				{
					$data->user_id = $gateway_o ? $me->id : null;
				}
			}
			elseif ($data->mode == 'direct')
			{
				if (!$direct_a)
				{
					$data->user_id = $direct_o ? $me->id : null;
				}
			}
			else
			{
				$data->user_id = null;
			}
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $array  The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception  if there is an error loading the form.
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $array, $group = 'sellacious')
	{
		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');
		$data = is_array($array) ? ArrayHelper::toObject($array) : $array;

		if ($type == 'withdraw')
		{
			$form->loadFile('transaction_' . $type);

			if (!$this->helper->access->check('transaction.withdraw'))
			{
				$form->setFieldAttribute('user_id', 'type', 'hidden');
				$form->setFieldAttribute('user_id', 'readonly', 'true');
			}
		}
		else
		{
			$form->loadFile('transaction_' . $type);

			$gateway_a = $this->helper->access->check('transaction.addfund.gateway');
			$gateway_o = $this->helper->access->check('transaction.addfund.gateway.own');
			$direct_a  = $this->helper->access->check('transaction.addfund.direct');
			$direct_o  = $this->helper->access->check('transaction.addfund.direct.own');

			$modes = array();

			if ($gateway_a || $gateway_o)
			{
				$modes[] = 'gateway';
			}

			if ($direct_a || $direct_o)
			{
				$modes[] = 'direct';
			}

			if (count($modes) <= 1)
			{
				$form->setFieldAttribute('mode', 'readonly', 'true');
			}

			if ($data->mode == 'gateway')
			{
				if (!$gateway_a)
				{
					$form->setFieldAttribute('user_id', 'type', 'hidden');
					$form->setFieldAttribute('user_id', 'readonly', 'true');
				}

				if (!empty($data->payment_method_id))
				{
					$methodForm = $this->helper->paymentMethod->getForm($data->payment_method_id);

					if (isset($methodForm))
					{
						$form->load($methodForm->getXml());
					}
				}
			}
			else
			{
				if ($data->mode == 'direct')
				{
					if (!$direct_a)
					{
						$form->setFieldAttribute('user_id', 'type', 'hidden');
						$form->setFieldAttribute('user_id', 'readonly', 'true');
					}
				}
				else
				{
					// Show user field only if he can set a user in both cases.
					if (!$gateway_a || !$direct_a)
					{
						$form->setFieldAttribute('user_id', 'type', 'hidden');
						$form->setFieldAttribute('user_id', 'readonly', 'true');
					}
				}

				$form->removeField('payment_method_id');
			}
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $record  The form data.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($record)
	{
		$now = JFactory::getDate()->toSql();

		$currency = $record['currency'];

		list($balance) = TransactionHelper::getUserBalance($record['user_id'], $currency);

		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');

		// approved=1 / disapproved=-1 / pending=0 / locked=2 / cancelled=-2
		$data = array(
			'order_id'   => 0,
			'user_id'    => $record['user_id'],
			'context'    => 'user.id',
			'context_id' => $record['user_id'],
			'amount'     => $record['amount'],
			'currency'   => $currency,
			'txn_date'   => $now,
			'user_notes' => $record['user_notes'],
		);

		if ($type == 'withdraw')
		{
			$data['crdr']    = 'dr';
			$data['reason']  = 'withdraw';
			$data['state']   = '2';
			$data['balance'] = round($balance - $record['amount'], 2);
			$data['notes']   = "Fund withdrawal request for ({$record['amount']} {$currency}) from wallet for user {$record['user_id']}.";
		}
		elseif ($type == 'addfund')
		{
			$data['crdr']    = 'cr';
			$data['reason']  = 'addfund.' . $record['mode'];
			$data['state']   = $record['mode'] == 'direct' ? '1' : '0';
			$data['balance'] = $balance + $record['amount'];
			$data['notes']   = "Add fund ({$record['amount']} {$currency}) into wallet for user {$record['user_id']}.";

			// We need to call a plugin to handle 'gateway' mode transaction. Currently handled by the controller after save.
			$data['payment_method_id'] = isset($record['payment_method_id']) ? $record['payment_method_id'] : null;
			$data['payment_params']    = isset($record['params']) ? $record['params'] : null;
		}

		return parent::save($data);
	}

	/**
	 * Update transaction status
	 *
	 * @param   int     $pk
	 * @param   int     $state
	 * @param   string  $message
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function setApprove($pk, $state, $message)
	{
		$me  = JFactory::getUser();
		$now = JFactory::getDate();

		$table = $this->getTable();
		$table->load($pk);

		// Update balance for the row if declined/disapproved,
		// todo: also match previous state *correctly* !!IMP!!
		if ($state == -1 && $table->get('state') != -1)
		{
			$table->set('balance', $table->get('balance') + $table->get('amount'));
		}

		$table->set('state', $state);
		$table->set('admin_notes', $message);
		$table->set('approved_by', $me->id);
		$table->set('approval_date', $now->toSql());

		if ($table->store())
		{
			$object     = (object) $table->getProperties();
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onContentChangeState', array('com_sellacious.transaction', array($object->id), $state));
		}
	}
}

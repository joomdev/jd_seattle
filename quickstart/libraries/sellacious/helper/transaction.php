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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionBatch;
use Sellacious\Transaction\TransactionHelper;
use Sellacious\Transaction\TransactionRecord;

defined('_JEXEC') or die;

/**
 * Sellacious helper
 *
 * @since  3.0
 */
class SellaciousHelperTransaction extends SellaciousHelperBase
{
	const STATE_PENDING = 0;

	const STATE_CANCELLED = -2;

	const STATE_DECLINED = -1;

	const STATE_APPROVAL_HOLD = 2;

	const STATE_APPROVED = 1;

	/**
	 * Get a user linked for the given context entity
	 *
	 * @param   string  $context    The context of the entry
	 * @param   int     $contextId  Record's context id
	 *
	 * @return  int  The user id, 0 for shop
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getContextUser($context, $contextId)
	{
		// Currently we are using enumerated context list, we soon need to make this dynamic
		$userId = 0;

		if ($context == 'user.id')
		{
			$userId = $contextId;
		}
		elseif ($context == 'shoprule.id')
		{
			return $this->helper->shopRule->loadResult(array('list.select' => 'a.seller_uid', 'id' => $contextId));
		}

		return $userId;
	}

	/**
	 * Get wallet balance of the selected seller uid
	 *
	 * @param   int     $contextId  Record's context id
	 * @param   string  $currency   Balance amount of which currency to return, default returns array of all currency
	 * @param   string  $context    The context of the entry
	 *
	 * @return  stdClass|stdClass[]  Balance in each currency or single currency balance if specified
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getBalance($contextId, $currency = null, $context = 'user.id')
	{
		if ($context == 'user.id')
		{
			if ($currency)
			{
				list($a, $c, $cr, $dr) = TransactionHelper::getUserBalance($contextId, $currency);

				try
				{
					$d = $this->helper->currency->display($a, $c, null);
				}
				catch (Exception $e)
				{
					$d = null;
				}

				return (object) array('amount_cr' => $cr, 'amount_dr' => $dr, 'currency' => $c, 'amount' => $a, 'display' => $d);
			}

			$filterU = sprintf(
				'((%s AND %s) OR %s)',
				'a.context = ' . $this->db->q('user.id'),
				'a.context_id = ' . (int) $contextId,
				'a.user_id = ' . (int) $contextId
			);

			$cFilter = array(
				'list.select' => 'a.currency',
				'list.group'  => 'a.currency',
				'list.where'  => $filterU,
			);
			$currencies = $this->loadColumn($cFilter);

			$balances = array();

			foreach ($currencies as $currency)
			{
				list($a, $c, $cr, $dr) = TransactionHelper::getUserBalance($contextId, $currency);

				try
				{
					$d = $this->helper->currency->display($a, $c, null);
				}
				catch (Exception $e)
				{
					$d = null;
				}

				$balances[] = (object) array('amount_cr' => $cr, 'amount_dr' => $dr, 'currency' => $c, 'amount' => $a, 'display' => $d);
			}

			return $balances;
		}

		return $this->legacyBalance($contextId, $currency, $context);
	}

	/**
	 * Perform forex conversion for the given amount and account
	 *
	 * @param   float   $amount     The original amount from which to convert
	 * @param   string  $from       The original currency from which to convert
	 * @param   string  $to         The target currency to convert into
	 * @param   string  $context    The Transaction context name to identify the account, see: "transactions" table
	 * @param   int     $contextId  The record id for the <var>$context</var> given, see: "transactions" table
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 *
	 * @deprecated   Use TransactionHelper::forexConvert()
	 */
	public function doForex($amount, $from, $to, $context, $contextId)
	{
		if ($context == 'user.id')
		{
			return TransactionHelper::forexConvert($contextId, $amount, $from, $to);
		}

		return null;
	}

	/**
	 * Record one or more transactions to database, All records must be validated before any of them is inserted into the db.
	 *
	 * @param   stdClass[]  $records  Must contain all required parameters or use alternative methods for record registering
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 *
	 * @deprecated   Use Sellacious\Transaction\TransactionBatch
	 */
	public function register($records)
	{
		$batch = new TransactionBatch;

		foreach ($records as $record)
		{
			/** @var  TransactionRecord  $record */
			$record = ArrayHelper::toObject((array) $record, 'Sellacious\Transaction\TransactionRecord');

			$batch->add($record);
		}

		return $batch->execute();
	}

	/**
	 * Get the full context name calculated from the context identifiers used in transactions table
	 *
	 * @param   string  $context    Table name and field name where context is associated
	 * @param   int     $contextId  Id value for the context item
	 *
	 * @return  string
	 *
	 * @since   1.2.0
	 */
	public function getContext($context, $contextId)
	{
		// Special case for shop itself
		if ($context == 'user.id' && $contextId == 0)
		{
			return JText::_('COM_SELLACIOUS_TRANSACTION_CONTEXT_SHOP_OWNER');
		}

		@list($tableName, $pKey) = explode('.', $context, 2);
		$value = '';

		try
		{
			$table = $this->getTable($tableName);

			if (!($table instanceof JTable) || !property_exists($table, $pKey))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_TRANSACTION_INVALID_TABLE'));
			}

			if ($pKey == 'id')
			{
				$table->load($contextId);
			}
			else
			{
				$table->load(array($pKey => $contextId));
			}

			if (property_exists($table, 'title'))
			{
				$value = $table->get('title');
			}
			elseif (property_exists($table, 'name'))
			{
				$value = $table->get('name');
			}
		}
		catch (Exception $e)
		{
			// Ignore
			$value = '*';
		}

		return $value;
	}

	/**
	 * Set new status for the given transaction
	 *
	 * @param   int  $pk     Record id
	 * @param   int  $state  New status
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function setState($pk, $state)
	{
		$table = $this->getTable();
		$table->load($pk);

		if ($table->get('id'))
		{
			$table->set('state', $state);
			$table->check();
			$table->store();
		}

		return true;
	}

	/**
	 * Mark the given transaction as approved with timestamp update
	 *
	 * @param   int  $txnId  Transaction Id
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function setApproved($txnId)
	{
		$table = $this->getTable();
		$table->load($txnId);

		if ($table->get('id'))
		{
			$date = JFactory::getDate()->toSql();

			$table->set('state', static::STATE_APPROVED);
			$table->set('approval_date', $date);
			$table->check();
			$table->store();
		}

		return true;
	}

	/**
	 * Get a long format transaction number
	 *
	 * @param   int  $pk  Transaction id
	 *
	 * @return  string
	 *
	 * @deprecated  Use stored value directly from the table object
	 *
	 * @since   1.2.0
	 */
	public function getNumber($pk)
	{
		$date = $this->loadResult(array('id' => $pk, 'list.select' => 'a.txn_date'));
		$date = JFactory::getDate($date);

		$d1  = base_convert($date->day, 10, 36);
		$m1  = base_convert($date->month, 10, 16);
		$y2  = str_pad($date->year - 2000, 2, '0', STR_PAD_LEFT);
		$oi5 = str_pad($pk, 5, '0', STR_PAD_LEFT);

		return strtoupper('ST' . $y2 . $m1 . $d1 . $oi5);
	}

	/**
	 * Method to perform the legacy method of balance calculation for non-user transactions
	 * NOTE: DO NOT USE THIS METHOD, THIS WILL GO AWAY WITH the now deprecated getBalance() method.
	 *
	 * @param   int     $contextId
	 * @param   string  $currency
	 * @param   string  $context
	 *
	 * @return  array|mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function legacyBalance($contextId, $currency, $context)
	{
		$query  = $this->db->getQuery(true);
		$table  = $this->helper->transaction->getTable();
		$result = array();

		$query->select('SUM(IF(crdr = ' . $this->db->q('cr') . ', amount, 0)) AS amount_cr');
		$query->select('SUM(IF(crdr = ' . $this->db->q('dr') . ', amount, 0)) AS amount_dr');
		$query->select('currency');

		$query->from($this->db->qn($table->getTableName(), 'a'))
			->where('a.context = ' . $this->db->q($context))
			->where('a.context_id = ' . $this->db->q($contextId))
			->where(sprintf("(a.state = %d OR (a.state = %d AND a.crdr = 'dr'))", static::STATE_APPROVED, static::STATE_APPROVAL_HOLD))
			->group('a.currency');

		if ($currency)
		{
			$query->where('a.currency = ' . $this->db->q($currency));
		}

		$this->db->setQuery($query);

		$iterator = $this->db->getIterator();

		foreach ($iterator as $record)
		{
			$record->currency = $this->helper->currency->getFieldValue($record->currency, 'code_3');
			$record->amount   = $record->amount_cr - $record->amount_dr;
			$record->display  = $this->helper->currency->display($record->amount, $record->currency, null);

			$result[] = $record;
		}

		return $currency ? reset($result) : $result;
	}
}

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
namespace Sellacious\Transaction;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Forex;

defined('_JEXEC') or die;

/**
 * Sellacious Transaction helper object
 *
 * @since   1.6.0
 */
class TransactionHelper
{
	const STATE_PENDING = 0;

	const STATE_CANCELLED = -2;

	const STATE_DECLINED = -1;

	const STATE_APPROVAL_HOLD = 2;

	const STATE_APPROVED = 1;

	/**
	 * Get usable wallet balance of the selected user
	 *
	 * @param   string  $currency  Balance amount of which currency to return
	 *
	 * @return  array  An array with the values [amount, currency, CR, DR] e.g. - [12.99, USD, 112.99, 100.00]
	 *
	 * @since   1.6.0
	 */
	public static function getShopBalance($currency)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select("SUM(IF(a.crdr = 'cr', a.amount, 0)) AS amount_cr");
		$query->select("SUM(IF(a.crdr = 'dr', a.amount, 0)) AS amount_dr");
		$query->select('a.currency');

		$query->from($db->qn('#__sellacious_transactions', 'a'))
			->where("(a.user_id > 0 OR (a.context = 'user.id' AND a.context_id > 0))")
			->where('a.currency = ' . $db->q($currency))
			->group('a.currency');

		// Include all approved 'cr'/'dr', and also include locked 'dr' (for minus)
		$query->where(sprintf("(a.state = %d OR (a.state = %d AND a.crdr = 'dr'))", static::STATE_APPROVED, static::STATE_APPROVAL_HOLD));

		$result = $db->setQuery($query)->loadObject();

		if (!$result)
		{
			return array(0.00, $currency, 0.00, 0.00);
		}

		$cur = $result->currency;
		$crA = $result->amount_cr;
		$drA = $result->amount_dr;
		$bal = $crA - $drA;

		return array($bal, $cur, $crA, $drA);
	}

	/**
	 * Get usable wallet balance of the selected user
	 *
	 * @param   int     $userId     The user id
	 * @param   string  $currency   Balance amount of which currency to return
	 *
	 * @return  array  An array with the values [amount, currency, CR, DR] e.g. - [12.99, USD, 112.99, 100.00]
	 *
	 * @throws  \Exception  The database triggered exception
	 *
	 * @since   1.6.0
	 */
	public static function getUserBalance($userId, $currency)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select("SUM(IF(a.crdr = 'cr', a.amount, 0)) AS amount_cr");
		$query->select("SUM(IF(a.crdr = 'dr', a.amount, 0)) AS amount_dr");
		$query->select('a.currency');

		$filterU = sprintf(
			'((%s AND %s) OR %s)',
			'a.context = ' . $db->q('user.id'),
			'a.context_id = ' . (int) $userId,
			'a.user_id = ' . (int) $userId
		);

		$query->from($db->qn('#__sellacious_transactions', 'a'))
			->where($filterU)
			->where('a.currency = ' . $db->q($currency))
			->group('a.currency');

		// Include all approved 'cr'/'dr', and also include locked 'dr' (for minus)
		$query->where(sprintf("(a.state = %d OR (a.state = %d AND a.crdr = 'dr'))", static::STATE_APPROVED, static::STATE_APPROVAL_HOLD));

		$result = $db->setQuery($query)->loadObject();

		if (!$result)
		{
			return array(0.00, $currency, 0.00, 0.00);
		}

		$cur = $result->currency;
		$crA = $result->amount_cr;
		$drA = $result->amount_dr;
		$bal = $crA - $drA;

		return array($bal, $cur, $crA, $drA);
	}

	/**
	 * Get locked wallet balance of the selected user
	 *
	 * @param   int     $userId    The user id
	 * @param   string  $currency  Amount of which currency to return
	 *
	 * @return  array  An array with the values [amount, currency, CR, DR] e.g. - [12.99, USD, 112.99, 100.00]
	 *
	 * @throws  \Exception  The database triggered exception
	 *
	 * @since   1.6.0
	 */
	public static function getUserLockedBalance($userId, $currency)
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select("SUM(IF(a.crdr = 'cr', a.amount, 0)) AS amount_cr");
		$query->select("SUM(IF(a.crdr = 'dr', a.amount, 0)) AS amount_dr");
		$query->select('a.currency');

		$query->from($db->qn('#__sellacious_transactions', 'a'))
			->where('a.user_id = ' . (int) $userId)
			->where('a.currency = ' . $db->q($currency))
			->group('a.currency');

		// Include all locked 'dr' and un-approved 'cr', both will be *added* as we want total locked fund
		$query->where('a.state = ' . (int) static::STATE_APPROVAL_HOLD);

		$result = $db->setQuery($query)->loadObject();

		if (!$result)
		{
			return array(0.00, $currency, 0.00, 0.00);
		}

		$cur = $result->currency;
		$crA = $result->amount_cr;
		$drA = $result->amount_dr;
		$bal = $crA + $drA;

		return array($bal, $cur, $crA, $drA);
	}

	/**
	 * Convert balance of a user from Perform forex conversion for the given amount and account
	 *
	 * @param   int     $userId  The user id
	 * @param   float   $amount  The original amount from which to convert
	 * @param   string  $from    The original currency from which to convert
	 * @param   string  $to      The target currency to convert into
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public static function forexConvert($userId, $amount, $from, $to)
	{
		if ($from == $to)
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_TRANSACTION_FOREX_SAME_CURRENCY'));
		}

		list($balAmount) = static::getUserBalance($userId, $from);

		if ($balAmount < $amount)
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_TRANSACTION_FOREX_BALANCE_LOW'));
		}

		$helper = \SellaciousHelper::getInstance();
		$api    = $helper->config->get('forex_api', 'Fixer');
		$forex  = Forex::getInstance($api, $from);

		$converted = $forex->convert($amount)->to($to)->asNumber(2);

		if ($converted < 0.01)
		{
			throw new \Exception(\JText::_('COM_SELLACIOUS_TRANSACTION_FOREX_OUTPUT_EMPTY'));
		}

		$entry  = array(
			'id'            => null,
			'txn_number'    => null,
			'order_id'      => 0,
			'user_id'       => $userId,
			'context'       => 'user.id',
			'context_id'    => $userId,
			'reason'        => 'forex',
			'crdr'          => null,
			'amount'        => null,
			'currency'      => null,
			'balance'       => null,
			'txn_date'      => \JFactory::getDate()->toSql(),
			'notes'         => "Forex from $amount $from to $converted $to",
			'user_notes'    => null,
			'admin_notes'   => null,
			'approved_by'   => null,
			'approval_date' => null,
			'tags'          => null,
			'state'         => 1,
			'created'       => null,
			'created_by'    => null,
			'params'        => null,
		);
		$debit  = array_merge($entry, array('crdr' => 'dr', 'amount' => $amount, 'currency' => $from));
		$credit = array_merge($entry, array('crdr' => 'cr', 'amount' => $converted, 'currency' => $to));

		/** @var   TransactionRecord  $dRecord */
		$dRecord = ArrayHelper::toObject($debit, 'Sellacious\Transaction\TransactionRecord');

		/** @var   TransactionRecord  $cRecord */
		$cRecord = ArrayHelper::toObject($credit, 'Sellacious\Transaction\TransactionRecord');

		$batch = new TransactionBatch;
		$batch->add($dRecord);
		$batch->add($cRecord);

		$done = $batch->execute();

		return $done;
	}

	/**
	 * Build a transaction number as a super key for this table
	 *
	 * @param   object  $entry  The transaction entry
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public static function getNumber($entry)
	{
		$value = null;

		if ($entry->id && $entry->txn_date)
		{
			$date = \JFactory::getDate($entry->txn_date);

			$d1 = base_convert($date->day, 10, 36);
			$m1 = base_convert($date->month, 10, 16);
			$y2 = str_pad($date->year - 2000, 2, '0', STR_PAD_LEFT);
			$o5 = str_pad($entry->id, 5, '0', STR_PAD_LEFT);

			$value = strtoupper('ST' . $y2 . $m1 . $d1 . $o5);
		}

		return $value;
	}
}

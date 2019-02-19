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

defined('_JEXEC') or die;

/**
 * Sellacious Transaction Object to allow batch processing of linked transactions.
 *
 * @since   1.6.0
 */
class TransactionBatch
{
	/**
	 * The batch entries
	 *
	 * @var   TransactionRecord[]
	 *
	 * @since   1.6.0
	 */
	protected $entries = array();

	/**
	 * Method to add a transaction entry to the current batch
	 *
	 * @param   TransactionRecord  $record  The record to add into the batch
	 *
	 * @return  string  The entry identifier which can be used to retrieve the record via {@see   get()} method
	 *
	 * @since   1.6.0
	 */
	public function add(TransactionRecord $record)
	{
		$hash = spl_object_hash($record);

		$this->entries[$hash] = $record;

		return $hash;
	}

	/**
	 * Method to retrieve an entry by its identifier in the batch
	 *
	 * @param   string  $key  The identifier as returned by {@see   add()} method
	 *
	 * @return  TransactionRecord
	 *
	 * @since   1.6.0
	 */
	public function get($key)
	{
		if (isset($this->entries[$key]))
		{
			return $this->entries[$key];
		}

		return null;
	}

	/**
	 * Method to retrieve all the entries in the batch
	 *
	 * @return  TransactionRecord[]
	 *
	 * @since   1.6.0
	 */
	public function getAll()
	{
		return $this->entries;
	}

	/**
	 * Method to execute the transaction batch and store all the entries in the batch into the database
	 * This will also attempt a rollback automatically if any entry fails, therefore no need to call rollback explicitly on failure.
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function execute()
	{
		$db = \JFactory::getDbo();

		foreach ($this->entries as $entry)
		{
			// Todo: Calculate tentative balance including this entry
			$saved = $db->insertObject('#__sellacious_transactions', $entry, 'id');

			if ($saved)
			{
				$o = new \stdClass;

				$o->id         = $entry->id;
				$o->txn_number = TransactionHelper::getNumber($entry);

				$saved = $db->updateObject('#__sellacious_transactions', $o, array('id'));
			}

			if (!$saved)
			{
				$this->rollback();

				return false;
			}
		}

		return true;
	}

	/**
	 * Rollback the transaction batch. All the entries in this batch will be removed from database
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function rollback()
	{
		$pks = array();

		foreach ($this->entries as $entry)
		{
			if ($entry->id)
			{
				$pks[] = (int) $entry->id;
			}
		}

		if (count($pks))
		{
			$db    = \JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->delete('#__sellacious_transactions')->where('id IN ('  . implode(',', $pks) . ')');

			$db->setQuery($query)->execute();
		}

		return true;
	}
}

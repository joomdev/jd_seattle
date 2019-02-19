<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Transaction\TransactionHelper;

defined('_JEXEC') or die;

/**
 * Transaction Table class
 *
 * @since   1.2.0
 */
class SellaciousTableTransaction extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('payment_params', 'params');

		parent::__construct('#__sellacious_transactions', 'id', $db);
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$saved = parent::store($updateNulls);

		if ($saved && empty($this->txn_number))
		{
			$o = new stdClass;

			$o->id         = $this->get('id');
			$o->txn_number = TransactionHelper::getNumber($this);

			if ($this->_db->updateObject($this->getTableName(), $o, array('id'), false))
			{
				$this->set('txn_number', $o->txn_number);
			}
		}

		return $saved;
	}

	/**
	 * Build a transaction number as a super key for this table
	 *
	 * @return  string
	 *
	 * @since   1.4.1
	 *
	 * @deprecated   Use TransactionHelper::getNumber()
	 */
	public function getNumber()
	{
		return TransactionHelper::getNumber($this);
	}
}

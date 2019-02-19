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
 * Sellacious Transaction record object.
 *
 * @since   1.6.0
 */
class TransactionRecord
{
	/**
	 * Record id in database
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $id;

	/**
	 * The user oriented identifier for this entry
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $txn_number;

	/**
	 * The relevant order id for this transaction
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $order_id;

	/**
	 * The beneficiary user id for this transaction
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $user_id;

	/**
	 * The context type for which this entry is made
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $context;

	/**
	 * The id of the relevant context
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $context_id;

	/**
	 * A reason identifier to identify the transaction type at system level
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $reason;

	/**
	 * Whether this is a credit (cr) note or a debit (dr) note
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $crdr;

	/**
	 * Amount of the transaction
	 *
	 * @var  float
	 *
	 * @since  1.6.0
	 */
	public $amount;

	/**
	 * Currency of the transaction
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $currency;

	/**
	 * Balance after this transaction
	 *
	 * @var  float
	 *
	 * @since  1.6.0
	 */
	public $balance;

	/**
	 * Date of transaction
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $txn_date;

	/**
	 * System generated note
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $notes;

	/**
	 * User submitted note
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $user_notes;

	/**
	 * Admin notes
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $admin_notes;

	/**
	 * The id of the user who approved this (if applicable)
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $approved_by;

	/**
	 * The date when this was approved (if applicable)
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $approval_date;

	/**
	 * The tags/keyword to find this transaction
	 *
	 * @var  array
	 *
	 * @since  1.6.0
	 */
	public $tags;

	/**
	 * The status of this transaction
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $state;

	/**
	 * The creation date
	 *
	 * @var  string
	 *
	 * @since  1.6.0
	 */
	public $created;

	/**
	 * The user id of the creator
	 *
	 * @var  int
	 *
	 * @since  1.6.0
	 */
	public $created_by;

	/**
	 * Additional parameters
	 *
	 * @var  array
	 *
	 * @since  1.6.0
	 */
	public $params;

	/**
	 * Setter method to disallow any invalid property name to be accessed
	 *
	 * @param   string  $key    The property name
	 * @param   mixed   $value  The new property value
	 *
	 * @since  1.6.0
	 */
	public function __set($key, $value)
	{
		trigger_error('Trying to set undefined property "' . $key . '" of ' . get_class($this), E_USER_WARNING);
	}
}

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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Currency Table class
 */
class SellaciousTableCurrency extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_currencies', 'id', $db);
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  key-value pairs to check the table row uniqueness against the row being checked
	 */
	protected function getUniqueConditions()
	{
		$conditions = array();

		$conditions['code_3'] = array('code_3' => $this->get('code_3'));
		$conditions['title']  = array('title' => $this->get('title'));

		return $conditions;
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   array  $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'code_3')
		{
			return JText::sprintf('COM_SELLACIOUS_TABLE_UNIQUE_KEY_CURRENCY_CODE3_X', $table->get('code_3'));
		}
		elseif ($uk_index === 'title')
		{
			return JText::sprintf('COM_SELLACIOUS_TABLE_UNIQUE_KEY_CURRENCY_TITLE_X', $table->get('title'));
		}

		return false;
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @since   1.5.2
	 */
	public function check()
	{
		$gCurrency = $this->helper->currency->getGlobal();

		if ($this->id > 0)
		{
			if ($this->state != 1)
			{
				// Check for Global Currency
				if ($gCurrency->id == $this->id)
				{
					throw new UnexpectedValueException(JText::sprintf('COM_SELLACIOUS_ERROR_GLOBAL_CURRENCY_DISABLE', $gCurrency->title, $gCurrency->code_3));
				}

				// Check for Seller Currency
				$activeSellersCurrencies = $this->helper->seller->loadColumn(array('list.select' => 'a.currency', 'state' => 1));
				$activeSellersCurrencies = array_unique(array_filter($activeSellersCurrencies));

				foreach ($activeSellersCurrencies as $currency)
				{
					$sCurrency = $this->helper->currency->getItem($currency);

					if ($sCurrency->id == $this->id)
					{
						throw new UnexpectedValueException(JText::sprintf('COM_SELLACIOUS_ERROR_SELLER_CURRENCY_DISABLE', $sCurrency->title, $sCurrency->code_3));
					}
				}

				// Check for Wallet/Transactions Currency
				$activeTransactionCurrencies = $this->helper->transaction->loadColumn(array('list.select' => 'a.currency', 'state' => 1));
				$activeTransactionCurrencies = array_unique(array_filter($activeTransactionCurrencies));

				foreach ($activeTransactionCurrencies as $currency)
				{
					$wCurrency = $this->helper->currency->getItem($currency);

					if ($wCurrency->id == $this->id)
					{
						throw new UnexpectedValueException(JText::sprintf('COM_SELLACIOUS_ERROR_WALLET_CURRENCY_DISABLE', $wCurrency->title, $wCurrency->code_3));
					}
				}
			}
		}

		return true;
	}

	/**
	 * Override to make sure to obey following -
	 * Global Currency cannot be unpublished
	 * Active Seller Currency cannot be unpublished
	 *
	 * @param   int[] $pks
	 * @param   int   $state
	 * @param   int   $userId
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$pks            = ArrayHelper::toInteger($pks);
		$gCurrency = $this->helper->currency->getGlobal();

		if ($state != 1 && count($pks))
		{
			// Check for Global Currency
			if (in_array($gCurrency->id, $pks))
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_GLOBAL_CURRENCY_DISABLE', $gCurrency->title, $gCurrency->code_3));
			}

			// Check for Seller Currency
			$activeSellersCurrencies = $this->helper->seller->loadColumn(array('list.select' => 'a.currency', 'state' => 1));
			$activeSellersCurrencies = array_unique(array_filter($activeSellersCurrencies));

			foreach ($activeSellersCurrencies as $currency)
			{
				$sCurrency = $this->helper->currency->getItem($currency);

				if (in_array($sCurrency->id, $pks))
				{
					throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_SELLER_CURRENCY_DISABLE', $sCurrency->title, $sCurrency->code_3));
				}
			}

			// Check for Wallet/Transactions Currency
			$activeTransactionCurrencies = $this->helper->transaction->loadColumn(array('list.select' => 'a.currency', 'state' => 1));
			$activeTransactionCurrencies = array_unique(array_filter($activeTransactionCurrencies));

			foreach ($activeTransactionCurrencies as $currency)
			{
				$wCurrency = $this->helper->currency->getItem($currency);

				if (in_array($wCurrency->id, $pks))
				{
					throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_WALLET_CURRENCY_DISABLE', $wCurrency->title, $wCurrency->code_3));
				}
			}
		}

		return parent::publish($pks, $state, $userId);
	}
}

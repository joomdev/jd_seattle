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

/**
 * Product Attributes Table class
 */
class SellaciousTableProductPrices extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_product_prices', 'id', $db);
	}

	/**
	 * Override to check whether price_override value is applicable or not
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function check()
	{
		$this->set('product_price', $this->get('ovr_price') > 0 ? $this->get('ovr_price') : $this->get('calculated_price'));

		if ($this->get('is_fallback'))
		{
			$table = static::getInstance($this->getName());
			$k     = $this->_tbl_key;
			$u_key = array(
				'seller_uid'  => $this->get('seller_uid'),
				'product_id'  => $this->get('product_id'),
				'is_fallback' => 1,
			);

			if ($table->load($u_key) && ($table->$k != $this->$k || $this->$k == 0))
			{
				$this->$k = $table->$k;
			}
		}

		return parent::check();
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 */
	protected function getUniqueConditions()
	{
		return array();
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   array   $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable  $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		return false;
	}

}

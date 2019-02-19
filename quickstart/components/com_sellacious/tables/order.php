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
 * Product Table class
 *
 * @since  3.0
 */
class SellaciousTableOrder extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database instance
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('payment_params', 'shoprules', 'shipping_params', 'checkout_forms');

		parent::__construct('#__sellacious_orders', 'id', $db);
	}

	/**
	 * Override getUniqueConditions, We don't want the parent's logic here
	 *
	 * @return  array
	 */
	protected function getUniqueConditions()
	{
		return array();
	}
}

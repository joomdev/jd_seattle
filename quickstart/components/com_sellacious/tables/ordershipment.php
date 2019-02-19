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
class SellaciousTableOrderShipment extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  &$db  Database instance
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_order_shipments', 'id', $db);
	}

	/**
	 * Override getUniqueConditions, We don't want the parent's logic here
	 *
	 * @return  array
	 */
	protected function getUniqueConditions()
	{
		$conditions = array();

		$conditions['order-item'] = array(
			'order_id' => $this->get('order_id'),
			'item_uid' => $this->get('item_uid')
		);

		return $conditions;
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param  array   $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param  JTable  $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'order-item')
		{
			return JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_UNIQUE_TEST_ERROR');
		}

		return false;
	}
}

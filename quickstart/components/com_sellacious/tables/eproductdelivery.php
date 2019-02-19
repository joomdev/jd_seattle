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

/**
 * Table class
 */
class SellaciousTableEProductDelivery extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_eproduct_delivery', 'id', $db);
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 */
	protected function getUniqueConditions()
	{
		return array(
			'order_item' => array('order_id' => $this->get('order_id'), 'item_uid' => $this->get('item_uid')),
		);
	}
}

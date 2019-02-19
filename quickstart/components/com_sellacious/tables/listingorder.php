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
class SellaciousTableListingOrder extends SellaciousTable
{
	var $customer_id = '';
	var $customer_firstname = '';
	var $customer_lastname = '';
	var $customer_company = '';
	var $customer_phone = '';
	var $customer_email = '';
	var $customer_fax = '';
	var $customer_ip = '';
	var $billing_street1 = '';
	var $billing_street2 = '';
	var $billing_district = '';
	var $billing_landmark = '';
	var $billing_state = '';
	var $billing_zip = '';
	var $billing_country = '';
	var $billing_phone = '';
	var $order_status = '';
	var $order_datetime = '';
	var $order_notes = '';
	var $discount_name = '';
	var $discount_code = '';
	var $order_subtotal = '';
	var $discount_amount = '';
	var $order_tax = '';
	var $total_tax = '';
	var $order_total = '';
	var $trans_id = '';
	var $order_cost = '';
	var $trans_fee_percent = '';
	var $trans_fee = '';
	var $payment_type = '';
	var $payment_message = '';
	var $notes = '';
	var $sales_notes = '';
	var $state = '';
	var $params = '';

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver &$db Database instance
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params');

		parent::__construct('#__sellacious_listing_orders', 'id', $db);
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

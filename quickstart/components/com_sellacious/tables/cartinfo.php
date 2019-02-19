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
class SellaciousTableCartInfo extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array(
			'payment_params',
			'ship_quotes',
			'shipment_params',
			'params',
		);

		parent::__construct('#__sellacious_cart_info', 'id', $db);
	}

	/**
	 * Overloaded check function to prevent guest user cart info
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function check()
	{
		if (empty($this->user_id) && empty($this->cart_token))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CART_CARTINFO_ERROR_USER_UNREGISTERED'));
		}

		return parent::check();
	}
}

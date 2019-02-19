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
class SellaciousTableCart extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('ship_quotes', 'params');

		parent::__construct('#__sellacious_cart', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function check()
	{
		$app  = JFactory::getApplication();

		$this->def('remote_ip', $app->input->server->getString('REMOTE_ADDR'));

		return parent::check();
	}

	/**
	 * Overloaded getNextOrder function to match additional criteria
	 *
	 * @param   string  $where
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getNextOrder($where = '')
	{
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$where = array();

		if($user->guest)
		{
			// Guest user should be identified by his IP only, may cause false match but that all we can do.
			// We are also matching the cart cookie hash now.
			$ip = $app->input->server->getString('REMOTE_ADDR');
			$where[] = 'remote_ip = ' . $this->_db->q($ip);
		}

		$where[] = 'user_id = '.$this->_db->q($user->id);

		return parent::getNextOrder($where);
	}
}

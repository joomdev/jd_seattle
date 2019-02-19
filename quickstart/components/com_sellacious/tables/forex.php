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
 * Forex Table class
 */
class SellaciousTableForex extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver   $db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_forex', 'id', $db);
	}

	/**
	* Overloaded check function
	*/
	public function check()
	{
		if (!$this->get('id'))
		{
			$this->set('state', 1);
		}

		// verify that the conversion rate is "uniquely active". No error just set to archived.
		$table = SellaciousTable::getInstance('Forex');

		$filter = array('x_from' => $this->get('x_from'), 'x_to' => $this->get('x_to'), 'state' => '1');

		while($table->load($filter) && ($table->get('id') != $this->get('id') || $this->get('id') == 0))
		{
			$table->publish(null, 2);
		}

		return parent::check();
	}
}

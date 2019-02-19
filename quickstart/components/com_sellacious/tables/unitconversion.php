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
 * UnitConversion Table class
 */
class SellaciousTableUnitConversion extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver  $db A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_unitconversions', 'id', $db);
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

		return parent::check();
	}

	/**
	 * Overloaded
	 *
	 */
	protected function getUniqueConditions()
	{
		$conditions   = parent::getUniqueConditions();
		$conditions['from-to'] = array('from' => $this->get('from'), 'to' => $this->get('to'));

		return $conditions;
	}
}

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
 * Unit Table class
 */
class SellaciousTableUnit extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_units', 'id', $db);
	}

	/**
	 * Overloaded check function
	 */
	public function check()
	{
		if (empty($this->unit_group))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_UNIT_NO_UNITGROUP'));
		}

		return parent::check();
	}
}

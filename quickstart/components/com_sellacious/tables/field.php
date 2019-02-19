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
 * Form fields Table class
 *
 */
class SellaciousTableField extends SellaciousTableNested
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params');

		parent::__construct('#__sellacious_fields', 'id', $db);
	}

	/**
	 * Overloaded check function
	 */
	public function check()
	{
		if($this->get('type') == 'fieldgroup')
		{
			// A field group must be under root only, update silently if not so.
			if($this->parent_id != 1)
			{
				$this->parent_id = 1;

				$this->setLocation($this->parent_id, 'last-child');
			}
		}
		else
		{
			// Everything else must be under some fieldgroup.
			$parent = SellaciousTable::getInstance('Field');
			$parent->load($this->parent_id);

			if($parent->get('type') == 'fieldgroup')
			{
				// This should/may not be required, but no harm keeping it.
				$this->set('context', $parent->get('context'));
			}
			else
			{
				throw new Exception(JText::_('COM_SELLACIOUS_FIELD_HAS_NO_FIELDGROUP'));
			}
		}

		return parent::check();
	}

}

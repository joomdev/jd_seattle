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
class SellaciousTableStatus extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  &$db  Database instance
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('allow_change_to', 'usergroups');

		parent::__construct('#__sellacious_statuses', 'id', $db);
	}

	/**
	 * Assess that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 *
	 * @throws  Exception
	 * @throws  RuntimeException on database error.
	 * @throws  UnexpectedValueException
	 */
	public function check()
	{
		// If editing a core record prevent change of type, otherwise remove is_core flag
		if ($id = $this->get('id'))
		{
			$table = static::getInstance($this->getName());

			$table->load($id);

			if ($table->get('is_core'))
			{
				$this->set('type', null);
				$this->set('state', 1);
			}
		}
		else
		{
			$this->set('is_core', 0);
		}

		return parent::check();
	}

	/**
	 * Override getUniqueConditions, we don't want the parent's logic here
	 *
	 * @return  array
	 */
	protected function getUniqueConditions()
	{
		return array();
	}
}

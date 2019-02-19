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
 * Group Table class
 */
class SellaciousTableLocation extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_locations', 'id', $db);
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 */
	protected function getUniqueConditions()
	{
		$conditions = array();

		if (!$this->helper->config->get('allow_duplicate_location'))
		{
			$conditions['parent_id-title'] = array('parent_id' => $this->get('parent_id'), 'title' => $this->get('title'));
		}

		return $conditions;
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   array   $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable  $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'parent_id-title')
		{
			return JText::sprintf('COM_SELLACIOUS_LOCATION_TABLE_UNIQUE_ITEM', $table->get('title'));
		}

		return false;
	}
}

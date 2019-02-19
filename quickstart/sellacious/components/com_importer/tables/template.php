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
 * Import Templates Table class
 *
 * @since   1.5.2
 */
class ImporterTableTemplate extends SellaciousTable
{
	/**
	 * Flag to set whether to increment the alias or not
	 *
	 * @var  bool
	 *
	 * @since  1.5.2
	 */
	protected $_incrementAlias = false;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db
	 *
	 * @since   1.5.2
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array(
			'mapping',
			'params',
		);

		parent::__construct('#__importer_templates', 'id', $db);
	}

	/**
	 * Static method to get an instance of a SellaciousTable class if it can be found in
	 * the table include paths.  To add include paths for searching for JTable classes.
	 *
	 * @param   string  $type    The type (name) of the JTable class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the JTable object.
	 *
	 * @return  JTable  A JTable object if found or boolean false if one could not be found.
	 *
	 * @see     JTable::addIncludePath()
	 *
	 * @since   1.5.2
	 */
	public static function getInstance($type, $prefix = 'ImporterTable', $config = array())
	{
		return parent::getInstance($type, $prefix, $config);
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 *
	 * @since   1.5.2
	 */
	protected function getUniqueConditions()
	{
		$conditions = array();

		if (property_exists($this, 'alias'))
		{
			$conditions['alias'] = array('alias' => $this->get('alias'));
		}

		return $conditions;
	}

	/**
	 * Get Custom error message for each uniqueness error
	 *
	 * @param   string  $uk_index  Array index/identifier of unique keys returned by getUniqueConditions
	 * @param   JTable  $table     Table object with which conflicted
	 *
	 * @return  bool|string
	 *
	 * @since   1.5.2
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		if ($uk_index === 'alias')
		{
			return JText::sprintf('COM_IMPORTER_IMPORT_UNIQUE_ALIAS', $this->get('title'));
		}

		return false;
	}
}

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
 * Import history Table class
 *
 * @since   1.6.1
 */
class ImporterTableImport extends SellaciousTable
{
	/**
	 * Flag to set whether to increment the alias or not
	 *
	 * @var  bool
	 *
	 * @since  1.6.1
	 */
	protected $_incrementAlias = false;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db
	 *
	 * @since   1.6.1
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array(
			'mapping',
			'options',
			'progress',
			'params',
		);

		parent::__construct('#__importer_imports', 'id', $db);
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
	 * @since   1.6.1
	 */
	public static function getInstance($type, $prefix = 'ImporterTable', $config = array())
	{
		return parent::getInstance($type, $prefix, $config);
	}
}

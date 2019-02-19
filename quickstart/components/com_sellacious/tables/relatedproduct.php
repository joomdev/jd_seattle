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
 * Coupon Table class
 *
 * @since   1.2.0
 */
class SellaciousTableRelatedProduct extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_relatedproducts', 'id', $db);
	}

	/**
	 * Asset that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 *
	 * @since   1.2.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException on database error.
	 * @throws  UnexpectedValueException
	 */
	public function check()
	{
		$name = $this->get('group_title');

		list($name, $alias) = $this->findGroup($name);

		$this->set('group_title', $name);
		$this->set('group_alias', $alias);

		return parent::check();
	}

	/**
	 * Find an existing group that matches the given group name ignoring any variations of same names
	 *
	 * @param   string  $name  The name to lookup
	 *
	 * @return  string[]  An array containing the group title and alias
	 *
	 * @since   1.6.0
	 */
	public function findGroup($name)
	{
		$alias = JFilterOutput::stringURLSafe($name);

		if (trim(str_replace('-', '', $alias)) == '')
		{
			$alias = base64_encode(strtolower($name));
		}

		$table = self::getInstance($this->getName());
		$k     = $this->_tbl_key;

		// If this alias is already defined, use associated title itself, ignore current
		if ($table->load(array('group_alias' => $alias)) && ($table->$k != $this->$k || $this->$k == 0))
		{
			$name  = $table->get('group_title');
			$alias = $table->get('group_alias');
		}

		return array($name, $alias);
	}
}

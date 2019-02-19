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
 * Message Table class
 */
class SellaciousTableMessage extends SellaciousTableNested
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver $db A database connector object
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params');

		parent::__construct('#__sellacious_messages', 'id', $db);
	}

	/**
	 * Asset that the nested set data is valid.
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
		$now = JFactory::getDate();
		$me  = JFactory::getUser();

		$this->alias = $now->format('Y-m-d-H-i-s-') . $me->id;

		return parent::check();
	}

}

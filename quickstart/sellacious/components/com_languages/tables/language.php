<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Languages table.
 *
 * @since  11.1
 */
class LanguagesTableLanguage extends SellaciousTable
{
	/**
	 * @var   int
	 *
	 * @since   1.6.0
	 */
	public $lang_id;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $lang_code;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $title;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $title_native;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $sef;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $image;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $description;

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	public $sitename;

	/**
	 * @var   int
	 *
	 * @since   1.6.0
	 */
	public $published;

	/**
	 * @var   int
	 *
	 * @since   1.6.0
	 */
	public $ordering;

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   11.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__languages', 'lang_id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception
	 *
	 * @since   11.1
	 */
	public function check()
	{
		if (trim($this->title) == '')
		{
			throw new Exception(JText::_('JLIB_DATABASE_ERROR_LANGUAGE_NO_TITLE'));
		}

		return true;
	}

	/**
	 * Returns an array of conditions to meet for the uniqueness of the row, of course other than the primary key
	 *
	 * @return  array  Key-value pairs to check the table row uniqueness against the row being checked
	 *
	 * @since   1.1.0
	 */
	protected function getUniqueConditions()
	{
		$conditions = array();

		$conditions['lang_code'] = array('lang_code' => $this->lang_code);
		$conditions['sef']       = array('sef' => $this->sef);
		$conditions['image']     = array('image' => $this->image);

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
	 * @since   1.1.0
	 */
	protected function getUniqueError($uk_index, JTable $table)
	{
		$messages = array();

		$messages['lang_code'] = JText::_('JLIB_DATABASE_ERROR_LANGUAGE_UNIQUE_LANG_CODE');
		$messages['sef']       = JText::_('JLIB_DATABASE_ERROR_LANGUAGE_UNIQUE_SEF');
		$messages['image']     = JText::_('JLIB_DATABASE_ERROR_LANGUAGE_UNIQUE_IMAGE');

		return isset($messages[$uk_index]) ? $messages[$uk_index] : false;
	}
}

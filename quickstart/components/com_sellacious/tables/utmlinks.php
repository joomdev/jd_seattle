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
 */
class SellaciousTableUtmLinks extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_utm_links', 'id', $db);
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
		$table = static::getInstance($this->getName());
		$keys  = array('utm_id' => $this->get('utm_id'), 'page_url' => $this->get('page_url'));

		$table->load($keys);

		// Increment hits for duplicate record same day and overwrite
		if ($table->get('id'))
		{
			if ($this->isToday($table->get('created')))
			{
				$this->set('id', $table->get('id'));
				$this->set('hits', $table->get('hits') + 1);
			}
			else
			{
				$this->set('id', 0);
				$this->set('hits', 1);
			}

		}
		elseif ($this->get('id') == 0)
		{
			$this->set('hits', 1);
		}

		return parent::check();
	}

	/**
	 * Check if the given date is today (compared in UTC only)
	 *
	 * @param   string  $date
	 *
	 * @return  bool
	 */
	private function isToday($date)
	{
		$today = JFactory::getDate()->format('Ymd');
		$given = JFactory::getDate($date)->format('Ymd');

		return $today == $given;
	}
}

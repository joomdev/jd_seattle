<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import;

// no direct access.
defined('_JEXEC') or die;

/**
 * This base object will be immutable, however this can be extended
 * and the child classes may allow property write if needed.
 *
 * @package  Sellacious\Import
 *
 * @property-read  $name
 * @property-read  $title
 * @property-read  $active
 * @property-read  $csv
 *
 * @since   1.5.2
 */
class ImportHandler
{
	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $_name;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $_title;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	protected $_csv = true;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	protected $_active = false;

	/**
	 * Constructor
	 *
	 * @param   string  $name   Identifier for this handler
	 * @param   string  $title  Text label used to display for this handler
	 * @param   bool    $csv    Whether this handler supports import from CSV (default = true)
	 *
	 * @since   1.5.2
	 */
	public function __construct($name, $title, $csv = true)
	{
		$this->_name  = (string) $name;
		$this->_title = (string) $title;
		$this->_csv   = (bool) $csv;
	}

	/**
	 * Set the active flag for this handler so that we can activate the page for this
	 *
	 * @param   bool  $active
	 *
	 * @since   1.5.2
	 */
	public function setActive($active = true)
	{
		$this->_active = $active;
	}

	/**
	 * This is an immutable object
	 *
	 * @param   string  $name  The property name
	 *
	 * @return  mixed
	 *
	 * @since   1.5.2
	 */
	public function __get($name)
	{
		$name = '_' . $name;

		if (isset($this->$name))
		{
			return $this->$name;
		}

		return null;
	}
}

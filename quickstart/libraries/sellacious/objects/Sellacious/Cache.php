<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious;

defined('_JEXEC') or die;

/**
 * Sellacious Cache Object.
 *
 * @since  1.5.0
 */
abstract class Cache
{
	/**
	 * Sellacious application helper object.
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since  1.5.0
	 */
	protected $helper;

	/**
	 * The database driver object.
	 *
	 * @var    \JDatabaseDriver
	 *
	 * @since  1.5.0
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function __construct()
	{
		$this->helper = \SellaciousHelper::getInstance();
		$this->db     = \JFactory::getDbo();
	}

	/**
	 * Build the cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	abstract public function build();
}

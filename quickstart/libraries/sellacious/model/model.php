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
defined('_JEXEC') or die;

/**
 * Base class for a Joomla Model
 *
 * @package  Sellacious
 *
 * @since    3.0
 */
class SellaciousModel extends JModelLegacy
{
	/**
	 * Sellacious Helper
	 *
	 * @var    \SellaciousHelper
	 * @since  3.0
	 */
	protected $helper;

	/**
	 * @var  \JApplicationCms
	 *
	 * @since   1.6.0
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @throws  Exception
	 *
	 * @see     JModelLegacy
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		$this->app    = JFactory::getApplication();
		$this->helper = SellaciousHelper::getInstance();

		parent::__construct($config);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = '', $prefix = 'SellaciousTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}
}

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
 * Sellacious item model
 *
 * @package  Sellacious
 *
 * @since    3.0
 */
class SellaciousModelItem extends JModelItem
{
	/**
	 * @var   SellaciousHelper
	 *
	 * @since  1.0.0
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
	 * @see     JModelItem
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
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = $this->app->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function getTable($type = '', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}
}

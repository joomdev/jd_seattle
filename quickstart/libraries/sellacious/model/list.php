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
 * Sellacious list model
 *
 * @package  Sellacious
 *
 * @since    3.0
 */
class SellaciousModelList extends JModelList
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
	 * @see     JModelList
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
	 * @param   string $name    The table name. Optional.
	 * @param   string $prefix  The class prefix. Optional.
	 * @param   array  $options Configuration array for model. Optional.
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

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// If the context is set, assume that stateful lists are used.
		if ($this->context)
		{
			$limit = $this->app->input->get('limit');
			$start = $this->app->input->get('limitstart');
			$order = $this->app->input->get('filter_order');
			$dir   = $this->app->input->get('filter_order_Dir');

			$list  = (array) $this->app->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

			if (isset($limit) || !isset($list['limit']))
			{
				$list = array_merge($list, array('limit' => $limit ?: $this->app->get('list_limit')));
			}

			if (isset($start) || !isset($list['start']))
			{
				$list = array_merge($list, array('start' => $start));
			}

			if (isset($order) || !isset($list['ordering']))
			{
				$list = array_merge($list, array('ordering' => $order));
			}

			if (isset($dir) || !isset($list['direction']))
			{
				$list = array_merge($list, array('direction' => $dir));
			}

			$this->app->input->set('list', $list);
		}

		parent::populateState($ordering, $direction);

		// Just to avoid logic change in all list models in sellacious which use "fullordering"
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$order     = $this->state->get('list.fullordering');

		if ($ordering && $direction)
		{
			$this->state->set('list.fullordering', trim($ordering . ' ' . $direction));
		}

		if ($order)
		{
			list($ordering, $direction) = explode(' ', $order);

			$this->state->set('list.ordering', $ordering);
			$this->state->set('list.direction', $direction);
			$this->state->set('list.fullordering', trim($ordering . ' ' . $direction));
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string	A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query  The query.
	 * @param   integer  $start  Offset.
	 * @param   integer  $limit  The number of records.
	 *
	 * @return  stdClass[]  An array of results.
	 *
	 * @since   1.5.2
	 *
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $start = 0, $limit = 0)
	{
		$list = parent::_getList($query, $start, $limit);

		if (is_array($list))
		{
			$list = $this->processList($list);
		}

		return $list;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  The items loaded from the database using the list query
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	protected function processList($items)
	{
		return $items;
	}
}

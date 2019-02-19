<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Reporting Model
 *
 * @since  1.6.0
 */
class SellaciousreportingModelReports extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'r.title',
				'r.handler',
				'r.state',
				'r.created',
				'id', 'r.id'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();

		$userCategories = ReportingHelper::getUserCategories();

		// Create the base select statement.
		$query->select('r.*')
			->from($db->quoteName('#__sellacious_reports', 'r'));

		if (!empty($userCategories) && !$user->authorise('core.admin'))
		{
			$query->join('LEFT', '#__sellacious_reports_permissions b ON b.report_id = r.id');
		}

		// Filter by Plugin
		$plugin = $this->getState('filter.handler');

		if ($plugin)
		{
			$plugin = $db->quote($plugin);
			$query->where('handler = ' . $plugin);
		}

		// Filter: like / search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('r.title LIKE ' . $like . 'or r.handler LIKE ' . $like);
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('r.state = ' . (int) $state);
		}
		elseif ($state == '')
		{
			$query->where('r.state IN (0, 1)');
		}

		// Filter by User categories
		if (!empty($userCategories) && !$user->authorise('core.admin'))
		{
			$clause = array();
			$clause[] = 'b.user_cat_id IS NULL';

			foreach ($userCategories as $category)
			{
				$clause[] = 'FIND_IN_SET(' . (int)$category . ', b.user_cat_id)';
			}

			$query->where('(' . implode('||', $clause) . ')');
			$query->where('(b.permission_type IS NULL OR b.permission_type IN (' . $db->quote('view') . ', ' . $db->quote('edit') . '))');

			$query->group('r.id');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'r.title ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Method to get a list of users.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.0
	 */
	public function getItems()
	{
		return parent::getItems();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState($ordering, $direction);

		// Initialise variables.
		$app = JFactory::getApplication();

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');

		// Omit double (white-)spaces and set state
		$this->setState('filter.search', preg_replace('/\s+/', ' ', $search));

		$handler = $app->getUserStateFromRequest($this->context . '.filter.handler', 'filter_handler', '', 'string');
		$this->setState('filter.handler', $handler);
	}
}

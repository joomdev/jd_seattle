<?php

/**
 *  
 * @package    Com_Jdprofiler
 * @author      Joomdev
 * @copyright  Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Jdprofiler records.
 *
 * @since  1.6
 */
class JdprofilerModelProfiles extends JModelList
{
    
        
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.`id`',
				'name', 'a.`name`',
				'alias', 'a.`alias`',
				'state', 'a.`state`',
				'email', 'a.`email`',
				'phone', 'a.`phone`',
				'designation', 'a.`designation`',
				'image', 'a.`image`',
				'sbio', 'a.`sbio`',
				'lbio', 'a.`lbio`',
				'team', 'a.`team`',
				'location', 'a.`location`',
				'social', 'a.`social`',
				'skills', 'a.`skills`',
				'details', 'a.`details`',
				'ordering', 'a.`ordering`',
				'created_by', 'a.`created_by`',
				'modified_by', 'a.`modified_by`',
				'created_on', 'a.`created_on`',
				'modified_on', 'a.`modified_on`',
			);
		}

		parent::__construct($config);
	}

    
        
    
        
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);


		$designation = $app->getUserStateFromRequest($this->context . '.filter.designation', 'filter_designation');
		$this->setState('filter.designation', $designation);

		
		$team = $app->getUserStateFromRequest($this->context . '.filter.designation', 'filter_team');
		$this->setState('filter.team', $team);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_jdprofiler');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.name', 'asc');

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
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.designation');
         return parent::getStoreId($id);
                
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__jdprofiler_profiles` AS a');
                
		// Join over the tags: skills
		$query->leftJoin($db->quoteName('#__tags', 'tags') . ' ON FIND_IN_SET(tags.id, a.skills)');

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
					 
		// Join over the user field 'designation'
		$query->select('`designation`.title AS `designation_by`');
		$query->join('LEFT', '#__jdprofiler_designation AS `designation` ON `designation`.title = a.`designation`');


		// Join over the user field 'team'	
		$query->select('`team`.title AS `team_by`');
		$query->join('LEFT', '#__jdprofiler_team AS `team` ON `team`.title = a.`team`');
		
		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by designation 
		 $team = $this->getState('filter.team');
		if (IS_STRING($team)){
			$team = $db->Quote('%' . $db->escape($team, true) . '%');

			 $query->where('a.team LIKE'.$team);
		}

		// Filter by designation 
		$designation = $this->getState('filter.designation');
		if (IS_STRING($designation)){
			$designation = $db->Quote('%' . $db->escape($designation, true) . '%');

			$query->where('a.designation LIKE'.$designation);
		}
	
			
		// Filter by search in title

		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . '  OR  a.email LIKE ' . $search . '  OR  a.phone LIKE ' . $search . ' )');
			}
		}
                
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "a.id");
		$orderDirn = $this->state->get('list.direction', "ASC");

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
		//echo $query;
		// $db->replacePrefix( (string) $query );//debug
		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
                

		return $items;
	}
}

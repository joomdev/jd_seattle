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
 * Methods supporting a list of Sellacious records.
 */
class SellaciousModelLocations extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'state', 'a.state',
				'title', 'a.title',
				'iso_code', 'a.iso_code',
				'continent_title', 'a.continent_title',
				'country_title', 'a.country_title',
				'state_title', 'a.state_title',
				'district_title', 'a.district_title',
				'zip_title', 'a.zip_title',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_locations', 'a'))
			->where('a.parent_id > 0');

		// Filter the comments over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%', false);
				$query->where('a.title LIKE ' . $search);
			}
		}

		// Filter on the level.
		if ($type = $this->getState('filter.type'))
		{
			// allowing multiple types, esp. for modal list "types in csv"
			$type = array_map(array($db, 'q'), explode(',', $type));
			$query->where('a.type IN (' . implode(', ', $type) . ')');
		}

		// Filter on the continent.
		if ($continent = $this->getState('filter.continent'))
		{
			$query->where('a.continent_id = ' . $db->q($continent));
		}

		// Filter on the country.
		if ($country = $this->getState('filter.country'))
		{
			$query->where('a.country_id = ' . $db->q($country));
		}

		// Filter on the state.
		if ($state = $this->getState('filter.state_loc'))
		{
			$query->where('a.state_id = ' . $db->q($state));
		}

		// Filter on the district.
		if ($district = $this->getState('filter.district'))
		{
			$query->where('a.district_id = ' . $db->q($district));
		}

		// Filter by published state
		if (is_numeric($state = $this->getState('filter.state')))
		{
			$query->where('a.state = ' . (int) $state);
		}
		elseif ($state == '')
		{
			$query->where('a.state IN (0, 1)');
		}

		$ordering = $this->state->get('list.fullordering', 'a.id ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}

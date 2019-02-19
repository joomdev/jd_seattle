<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Location model.
 */
class SellaciousModelLocation extends SellaciousModel
{
	/**
	 * Get list of suggestions filtered by id, type and search keyword
	 *
	 * @param   string    $word          Search query to match
	 * @param   string[]  $types         Type of location to search
	 * @param   int       $parents       Parent item under which the search should be limited
	 * @param   int[]     $address_type  Address type to restrict results as
	 *
	 * @return  stdClass[]
	 */
	public function suggest($word = '', $types = null, $parents = null, $address_type = null)
	{
		// Todo: implement pagination
		$filters = array(
			'list.select' => 'a.id, a.title, a.type, a.title full_title',
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1'),
			'list.order'  => 'a.title',
		);

		// Frontend does not have to handle multiple parents yet
		if ($parents && $parents != 1)
		{
			$pCond = array(
				'a.continent_id IN (' . implode(', ', (array) $parents) . ')',
				'a.country_id IN (' . implode(', ', (array) $parents) . ')',
				'a.state_id IN (' . implode(', ', (array) $parents) . ')',
				'a.district_id IN (' . implode(', ', (array) $parents) . ')',
			);

			$filters['list.where'][] = '(' . implode(' OR ', $pCond) . ')';
		}

		switch ($address_type)
		{
			case 'billing':
				$pksB  = $this->helper->location->getBilling();
				$where = $this->getFilter($pksB);
				break;

			case 'shipping':
				$pksS  = $this->helper->location->getShipping();
				$where = $this->getFilter($pksS);
				break;

			case 'both':
			case 'any':
				$pksB  = $this->helper->location->getBilling();
				$pksS  = $this->helper->location->getShipping();
				$where = array(
					$this->getFilter($pksB),
					$this->getFilter($pksS),
				);
				$where = array_filter($where);
				$glue  = $address_type == 'both' ? ' AND ' : ' OR ';
				$where = $glue == ' AND ' || count($where) == 2 ? '(' . implode($glue, $where) . ')' : null;
				break;

			default:
				$where = null;
				break;
		}

		if ($where)
		{
			$filters['list.where'][] = $where;
		}

		if (count($types))
		{
			$filters['type'] = $types;
		}

		if (strlen($word))
		{
			$match = $this->_db->q('%' . $this->_db->escape($word) . '%', false);

			$filters['list.where'][] = '(a.title LIKE ' . $match . ' OR a.iso_code LIKE ' . $match . ')';
		}

		$items = $this->helper->location->loadObjectList($filters);

		if (!$items)
		{
			return array();
		}

		return $items;
	}

	/**
	 * Get list of items for given ids
	 *
	 * @param   int[]     $pks    Restricted list of ids to limit the search range, any parent or child of these are allowed rest not allowed.
	 * @param   string[]  $types  Geolocation types 
	 *
	 * @return  stdClass[]
	 *
	 * @since  1.5.3
	 */
	public function getInfo($pks = null, $types = null)
	{
		$pks   = ArrayHelper::toInteger((array) $pks);
		$types = (array) $types;

		if (count($pks) == 0)
		{
			return array();
		}

		$key     = isset($types) && $types[0] == 'zip' ? 'title' : 'id';
		$filters = array(
			'list.select' => 'a.id, a.title, a.type, a.title full_title',
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1'),
			'list.order'  => 'a.title',
			$key          => $pks,
		);

		$items = $this->helper->location->loadObjectList($filters);

		if (!$items)
		{
			return array();
		}

		return $items;
	}

	/**
	 * Build geolocation filter condition based on given selected locations
	 *
	 * @param   array  $pks  Geolocation ids for the selected locations
	 *
	 * @return  string
	 *
	 * @since   1.4.0
	 */
	protected function getFilter($pks)
	{
		$where   = null;
		$addr_to = array_reduce($pks, 'array_merge', array());
		$parents = $this->helper->location->getParents($addr_to, true);

		if ($parents)
		{
			$where = array(
				$pks['continent'] ? 'a.continent_id IN (' . implode(', ', $pks['continent']) . ')' : null,
				$pks['country'] ? 'a.country_id IN (' . implode(', ', $pks['country']) . ')' : null,
				$pks['state'] ? 'a.state_id IN (' . implode(', ', $pks['state']) . ')' : null,
				$pks['district'] ? 'a.district_id IN (' . implode(', ', $pks['district']) . ')' : null,
				$pks['zip'] ? 'a.zip_id IN (' . implode(', ', $pks['zip']) . ')' : null,
				$parents ? 'a.id  IN (' . implode(', ', $parents) . ')' : null,
			);
			$where = '(' . implode(' OR ', array_filter($where)) . ')';
		}

		return $where;
	}
}

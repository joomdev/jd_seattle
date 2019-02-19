<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious helper.
 *
 * @since   1.1.0
 */
class SellaciousHelperLocation extends SellaciousHelperBase
{
	/**
	 * Get a filtered list of locations as configured in global config for billing addresses
	 *
	 * @param   string  $type  Type of geolocation to find
	 *
	 * @return  array
	 *
	 * @since   1.3.3
	 */
	public function getBilling($type = null)
	{
		static $selected = array();

		if (!$selected)
		{
			$selected = array(
				'continent' => $this->helper->config->get('bill_to_continent'),
				'country'   => $this->helper->config->get('bill_to_country'),
				'state'     => $this->helper->config->get('bill_to_state'),
				'district'  => $this->helper->config->get('bill_to_district'),
				'area'      => $this->helper->config->get('bill_to_area'),
				'zip'       => $this->helper->config->get('bill_to_zip'),
			);

			foreach ($selected as $k => $v)
			{
				$selected[$k] = array_filter(ArrayHelper::toInteger(explode(',', $v)));
			}
		}

		return $type ? ArrayHelper::getValue($selected, $type, array(), 'array') : $selected;
	}

	/**
	 * Get a filtered list of locations as configured in global config for shipping addresses
	 *
	 * @param   string  $type  Type of geolocation to find
	 *
	 * @return  array
	 *
	 * @since   1.3.3
	 */
	public function getShipping($type = null)
	{
		static $selected = array();

		if (!$selected)
		{
			$selected = array(
				'continent' => $this->helper->config->get('ship_to_continent'),
				'country'   => $this->helper->config->get('ship_to_country'),
				'state'     => $this->helper->config->get('ship_to_state'),
				'district'  => $this->helper->config->get('ship_to_district'),
				'area'      => $this->helper->config->get('ship_to_area'),
				'zip'       => $this->helper->config->get('ship_to_zip'),
			);

			foreach ($selected as $k => $v)
			{
				$selected[$k] = array_filter(ArrayHelper::toInteger(explode(',', $v)));
			}
		}

		return $type ? ArrayHelper::getValue($selected, $type, array(), 'array') : $selected;
	}

	/**
	 * Check a geolocation is whether it is allowed against the given set of selected geolocations
	 *
	 * @param   stdClass  $geo      The geolocation record to check for
	 * @param   int[][]   $allowed  The allowed list of geolocations
	 *
	 * @return  bool|null  Value null  = if a child is selected (implies - allowed but cannot be inherited),
	 *                     Value true  = if self or a parent is selected (implies - can be inherited),
	 *                     Value false = if not allowed
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function isAllowed($geo, $allowed)
	{
		if (!$geo)
		{
			return null;
		}

		if (is_numeric($geo))
		{
			$geo = $this->loadObject(array('id' => $geo));
		}

		if (!$geo)
		{
			return false;
		}

		// No filtering, allow everything and allow inherit
		$allowed = array_filter($allowed);

		if (count($allowed) == 0)
		{
			return true;
		}

		$blank   = array('continent' => array(), 'country' => array(), 'state' => array(), 'district' => array(), 'zip' => array());
		$allowed = array_replace($blank, $allowed);

		// Self or Parent is selected, can be further inherited as well
		if (in_array($geo->id, $allowed[$geo->type])
			|| in_array($geo->continent_id, $allowed['continent'])
			|| in_array($geo->country_id, $allowed['country'])
			|| in_array($geo->state_id, $allowed['state'])
			|| in_array($geo->district_id, $allowed['district'])
			|| in_array($geo->zip_id, $allowed['zip'])
		)
		{
			return true;
		}

		// If a child is selected, we allow inclusion **BUT** this cannot be inherited
		if (in_array($geo->type, array('continent', 'country', 'state', 'district', 'zip')))
		{
			$type_id = $geo->type . '_id';
			$filters = array(
				$type_id => $geo->id,
				'id'     => array_reduce($allowed, 'array_merge', array()),
			);
			$count   = $this->count($filters);

			if ($count > 0)
			{
				return null;
			}
		}

		return false;
	}

	/**
	 * Check an address whether it is allowed for the given context
	 *
	 * @param   object|int    $address    The address object or address id
	 * @param   array|string  $selection  The address type/context. Acceptable values: BT, ST, or the full 2D grouped array of allowed geo-locations
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function isAddressAllowed($address, $selection)
	{
		if (is_numeric($address))
		{
			$address = $this->helper->user->getAddressById($address);
		}

		if (!isset($address) || !$address->id)
		{
			return false;
		}

		if (is_array($selection))
		{
			$allowed = $selection;
		}
		elseif (is_string($selection) && strtoupper($selection) == 'BT')
		{
			$allowed = $this->getBilling();
		}
		elseif (is_string($selection) && strtoupper($selection) == 'ST')
		{
			$allowed = $this->getShipping();
		}
		else
		{
			return true;
		}

		if (is_bool($allow = $this->isAllowed($address->country, $allowed)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->state_loc, $allowed)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->district, $allowed)))
		{
			return $allow;
		}

		/*
		 * If no preference is set at all it would be allowed already and we won't reach here.
		 * Therefore, if we are here it means either all upper level fields are blank or allowed by selected zip.
		 * Hence any region constraint in upper level due to zip must have already checked against.
		 * Also selected zip would be blank as this would not bring inherit up to here.
		 */
		if (!$address->zip)
		{
			return true;
		}

		$selected = ArrayHelper::getValue($allowed, 'zip', array(), 'array');
		$zipCodes = (array) $this->loadColumn(array('list.select' => 'a.title', 'id' => $selected));

		return count($zipCodes) == 0 || in_array($address->zip, $zipCodes);
	}

	/**
	 * Get list of query filtered by given ids as parent
	 *
	 * @param   int[]   $ids        The concerned ids
	 * @param   string  $direction  Ancestor = A, Descendant = D, Both = '' (default)
	 *
	 * @return  int[]
	 *
	 * @since   1.1.0
	 */
	public function getAncestry($ids, $direction = '')
	{
		$column = array();

		if ($ids)
		{
			$ancestors   = $direction == '' || $direction == 'A' ? $this->getParents($ids, true) : array();
			$descendants = $direction == '' || $direction == 'D' ? $this->getChildren($ids, true) : array();
			$column      = array_filter(array_unique(array_merge($ancestors, $descendants)));
		}

		return $column;
	}

	/**
	 * Return a list of parent items for given item or items.
	 *
	 * @note    This is not a nested table structure, however has its own hierarchy format.
	 *
	 * @param   int|int[]  $pks        Item id or a list of ids for which parents are to be found
	 * @param   bool       $inclusive  Whether the output list should contain the queried ids as well
	 *
	 * @return  int[]
	 *
	 * @since   1.1.0
	 */
	public function getParents($pks, $inclusive)
	{
		$filters = array('list.select' => 'DISTINCT a.continent_id', 'id' => $pks);
		$anc[]   = (array) $this->loadColumn($filters);

		$filters = array('list.select' => 'DISTINCT a.country_id', 'id' => $pks);
		$anc[]   = (array) $this->loadColumn($filters);

		$filters = array('list.select' => 'DISTINCT a.state_id', 'id' => $pks);
		$anc[]   = (array) $this->loadColumn($filters);

		$filters = array('list.select' => 'DISTINCT a.district_id', 'id' => $pks);
		$anc[]   = (array) $this->loadColumn($filters);

		$filters = array('list.select' => 'DISTINCT a.zip_id', 'id' => $pks);
		$anc[]   = (array) $this->loadColumn($filters);

		if ($inclusive)
		{
			$filters = array('list.select' => 'a.id', 'id' => $pks);
			$anc[]   = (array) $this->loadColumn($filters);
		}

		$ancestors = array_reduce($anc, 'array_merge', array());

		return array_filter($ancestors);
	}

	/**
	 * Return a list of child records for given record.
	 *
	 * @note    This is not a nested table structure, however has its own hierarchy format.
	 *
	 * @param   int|int[] $parent_ids Item id for which parents are to be found
	 * @param   bool      $inclusive  Whether the output list should contain the queried id as well
	 * @param   array     $where      Other additional filter criteria for the children
	 *
	 * @return  int[]
	 *
	 * @since   1.1.0
	 */
	public function getChildren($parent_ids, $inclusive, array $where = array())
	{
		$pks  = implode(', ', array_map('intval', (array) $parent_ids));
		$cond = array(
			'a.continent_id IN (' . $pks . ')',
			'a.country_id IN (' . $pks . ')',
			'a.state_id IN (' . $pks . ')',
			'a.district_id  IN (' . $pks . ')',
			'a.zip_id  IN (' . $pks . ')',
		);

		if ($inclusive)
		{
			$cond[] = 'a.id  IN (' . $pks . ')';
		}

		$where[]     = '(' . implode(' OR ', $cond) . ')';
		$filters     = array('list.select' => 'a.id', 'list.where' => $where);
		$descendants = $this->loadColumn($filters);

		return $descendants;
	}

	/**
	 * Get Client IP Address.
	 *
	 * @return  mixed
	 *
	 * @since   1.2.0
	 */
	public function getClientIP()
	{
		$app  = JFactory::getApplication();
		$keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');

		foreach ($keys as $key)
		{
			if ($ip = $app->input->server->getString($key))
			{
				return $ip;
			}
		}

		return null;
	}

	/**
	 * Get Currency from IP
	 *
	 * @param   string  $ip
	 * @param   string  $default
	 *
	 * @return  string
	 *
	 * @since   1.2.0
	 */
	public function ipToCurrency($ip = null, $default = null)
	{
		return $this->ipInfo($ip, 'currencyCode') ?: $default;
	}

	/**
	 * Get Country code from IP
	 *
	 * @param   string  $ip
	 * @param   string  $default
	 *
	 * @return  string
	 *
	 * @since   1.2.0
	 */
	public function ipToCountry($ip = null, $default = null)
	{
		return $this->ipInfo($ip, 'countryCode') ?: $default;
	}

	/**
	 * Get Country name from IP
	 *
	 * @param   string  $ip
	 * @param   string  $default
	 *
	 * @return  string
	 *
	 * @since   1.5.0
	 */
	public function ipToCountryName($ip = null, $default = null)
	{
		return $this->ipInfo($ip, 'countryName') ?: $default;
	}

	/**
	 * Utility function to clear geolocation parent locations titles cache from the database
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function clearCache()
	{
		// Create clear cache query
		$query = $this->db->getQuery(true);
		$query->update($this->db->qn($this->table, 'location'))
			->set('location.continent_title = NULL')
			->set('location.country_title = NULL')
			->set('location.state_title = NULL')
			->set('location.district_title = NULL')
			->set('location.area_title = NULL')
			->set('location.zip_title = NULL');

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Utility function to rebuild geolocation parent locations titles cache into the database
	 *
	 * @param   int  $id  The location id
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function buildCache($id = 0)
	{
		ignore_user_abort(true);

		// Create clear cache query
		$query_d = $this->db->getQuery(true);
		$query_d->update($this->db->qn($this->table, 'location'))
			->set('location.continent_title = NULL')
			->set('location.country_title = NULL')
			->set('location.state_title = NULL')
			->set('location.district_title = NULL')
			->set('location.area_title = NULL')
			->set('location.zip_title = NULL');

		if ($id > 0)
		{
			$query_d->where('location.id = ' . $id);
		}

		// Create query template to generate cache
		$query_w = $this->db->getQuery(true);
		$query_w->update($this->db->qn($this->table, 'location'))
				->join('inner', $this->db->qn($this->table, '%1$s') . ' ON %1$s.id = location.%1$s_id')
				->set('location.%1$s_title = %1$s.title')
				->where('location.%1$s_id > 0');

		if ($id > 0)
		{
			$query_w->where('location.id = ' . $id);
		}

		$types = array('continent', 'country', 'state', 'district', 'area', 'zip');

		// Collect all queries
		$queries   = array();
		$queries[] = (string) $query_d;

		foreach ($types as $type)
		{
			$queries[] = sprintf($query_w, $type);
		}

		try
		{
			foreach ($queries as $query)
			{
				// Reset PHP timeout for each script
				set_time_limit(150);

				$this->db->setQuery($query)->execute();
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Export the database records to file for later import
	 *
	 * @return  int
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function export()
	{
		ignore_user_abort(true);

		$start    = 0;
		$limit    = 1000;
		$fp_log   = fopen(JPATH_SITE . '/logs/dump-state-' . JHtml::_('date', 'now', 'YmdHis') . '.log', 'wb');
		$pointers = array();
		$columns  = array(
			's' => array('id', 'title', 'iso_code', 'parent_id', 'type', 'continent_id', 'country_id', 'state_id', 'district_id', 'zip_id'),
			'g' => array('title', 'iso_code', 'type', 'continent_title', 'country_title', 'state_title', 'district_title', 'zip_title'),
		);
		$begin    = microtime(true);

		do
		{
			set_time_limit(100);

			$filter   = array(
				'list.where'  => 'a.parent_id > 0',
				'list.start'  => $start,
				'list.limit'  => $limit,
			);
			$iterator = $this->getIterator($filter);
			$count    = $iterator->count();

			foreach ($iterator as $i => $item)
			{
				// Open export file pointers.
				$this->_open_files($item, $columns, $pointers);

				// Now output to each pointer as appropriate.
				$state = $this->_write_files($item, $columns, $pointers);

				fputcsv($fp_log, array($item->id, $state, $ct = microtime(true), $ct - $begin));
			}

			$start += $limit;
		}
		while ($count);

		// Close all pointers
		$this->_close_files($pointers);

		fputcsv($fp_log, array('EOF', '1', $ct = microtime(true), $ct - $begin));

		fclose($fp_log);

		return $count;
	}

	/**
	 * Import the database records from a csv file
	 *
	 * @param   string  $filename  File name from which to import
	 * @param   array   $options   Import options
	 *                             * force:   true = remove conflict and import, false = skip import on conflict
	 *                             * country: null = import everything (default), array = import given country ids only
	 *
	 * @return  int
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function import($filename, $options = array())
	{
		ignore_user_abort(true);

		$map  = array();
		$fp   = fopen($filename, 'r');

		// First row contains header
		$keys  = fgetcsv($fp);
		$table = $this->getTable();

		// Validate file structure
		if (count($keys) < 4)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_INVALID_FILE_STRUCTURE'));
		}

		foreach ($keys as $key)
		{
			if (!property_exists($table, $key))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_INVALID_FILE_STRUCTURE'));
			}
		}

		$force     = array_key_exists('force', $options) ? (bool) $options['force'] : false;
		$selected  = array_key_exists('country', $options) ? $options['country'] : null;

		// Get countries id for existing records so that we prevent duplicate entries
		$existing = $this->loadColumn(array('list.select' => 'DISTINCT a.country_id', 'list.where' => 'a.country_id > 0'));

		if (!is_array($existing))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_ANALYZE_EXISTING_ERROR'));
		}

		while($row = fgetcsv($fp))
		{
			set_time_limit(30);

			$record = array_combine($keys, $row);

			// Skip if we have a selection list and this country is not in it
			if (is_array($selected))
			{
				$country_id = $record['type'] == 'country' ? (int) $record['id'] : (int) $record['country_id'];

				if (!in_array($country_id, $selected))
				{
					continue;
				}
			}

			// If the children of selected country already exists and force is off, do not import. If force is ON remove that from DB first.
			$c_index = array_search($record['country_id'], $existing);

			if ($c_index !== false)
			{
				if ($force)
				{
					try
					{
						$dQuery = $this->db->getQuery(true)->delete($this->table)->where('country_id = ' . (int) $record['country_id']);

						$this->db->setQuery($dQuery)->execute();

						// This no longer is in db, so remove reference to avoid repeated delete query.
						unset($existing[$c_index]);
					}
					catch (Exception $e)
					{
						// Could not remove, skip as if not forced
						continue;
					}
				}
				else
				{
					continue;
				}
			}

			$old_id    = $record['id'];
			$parent_id = $record['parent_id'];
			$native    = in_array($record['type'], array('', 'country', 'continent'));

			// If a native already exists do not insert again
			if ($native)
			{
				$table = $this->getTable();
				$table->load($old_id);

				if ($table->get('id'))
				{
					$map[$old_id] = $old_id;

					continue;
				}
			}

			// If the PARENT is native i.e. - root OR a continent OR a country then leave the parent_id as is, else get from id-map.
			if ($parent_id != 1 && $parent_id != $record['continent_id'] && $parent_id != $record['country_id'])
			{
				$parent_id = ArrayHelper::getValue($map, $parent_id);
			}

			// If parent is not known we can't place it appropriately.
			if (!$parent_id)
			{
				continue;
			}

			// Now we have everything. Ready to insert new record.
			$item = new stdClass;

			$item->id           = $native ? $old_id : null;
			$item->title        = $record['title'];
			$item->iso_code     = $record['iso_code'];
			$item->type         = $record['type'];
			$item->parent_id    = $parent_id;
			$item->continent_id = $record['continent_id'];
			$item->country_id   = $record['country_id'];
			$item->state_id     = ArrayHelper::getValue($map, $record['state_id'], 0);
			$item->district_id  = ArrayHelper::getValue($map, $record['district_id'], 0);
			$item->zip_id       = ArrayHelper::getValue($map, $record['zip_id'], 0);
			$item->state        = 1;

			$this->db->insertObject($this->table, $item, 'id');

			$map[$old_id] = $item->id;
		}

		return count($map);
	}

	/**
	 * Open the files for export
	 *
	 * @param   stdClass  $item
	 * @param   array     $keys
	 * @param   array     $pointers
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	protected function _open_files($item, $keys, &$pointers)
	{
		$app      = JFactory::getApplication();
		$tmp_path = $app->get('tmp_path');

		// Export file pointers for sellacious application
		// world
		$filename = $tmp_path . '/sellacious/world.csv';

		if (!is_file($filename) && !isset($pointers['world_s']))
		{
			if ($fp = fopen($filename, 'wb'))
			{
				fputcsv($fp, $keys['s']);
			}

			$pointers['world_s'][0] = $fp;
			unset($fp);
		}

		// continent
		$filename = $tmp_path . '/sellacious/continent/' . $item->continent_id . '-' . $item->continent_title . '.csv';

		if (!is_file($filename) && !isset($pointers['continent_s'][$item->continent_id]))
		{
			if ($fp = fopen($filename, 'wb'))
			{
				fputcsv($fp, $keys['s']);
			}

			$pointers['continent_s'][$item->continent_id] = $fp;
			unset($fp);
		}

		// country
		$filename = $tmp_path . '/sellacious/country/' . $item->country_id . '-' . $item->country_title . '.csv';

		if (!is_file($filename) && !isset($pointers['country_s'][$item->country_id]))
		{
			if ($fp = fopen($filename, 'wb'))
			{
				fputcsv($fp, $keys['s']);
			}

			$pointers['country_s'][$item->country_id] = $fp;
			unset($fp);
		}

		// Export file pointers for public
		// world
		$filename = $tmp_path . '/generic/world.csv';

		if (!is_file($filename) && !isset($pointers['world_g']))
		{
			if ($fp = fopen($filename, 'wb'))
			{
				fputcsv($fp, $keys['g']);
			}

			$pointers['world_g'][0] = $fp;
			unset($fp);
		}

		// continent
		// $continent_id    = $item->type == 'continent' ? $item->id : $item->continent_id;
		// $continent_title = $item->type == 'continent' ? $item->title : $item->continent_title;
		$continent_id    = $item->continent_id;
		$continent_title = $item->continent_title;

		$filename        = $tmp_path . '/generic/continent/' . $continent_id . '-' . $continent_title . '.csv';

		if (!is_file($filename) && !isset($pointers['continent_g'][$continent_id]))
		{
			if ($fp = fopen($filename, 'wb'))
			{
				fputcsv($fp, $keys['g']);
			}

			$pointers['continent_g'][$continent_id] = $fp;
			unset($fp);
		}

		// country
		// $country_id    = $item->type == 'country' ? $item->id : $item->country_id;
		// $country_title = $item->type == 'country' ? $item->title : $item->country_title;
		$country_id    = $item->country_id;
		$country_title = $item->country_title;

		$filename      = $tmp_path . '/generic/country/' . $country_id . '-' . $country_title . '.csv';

		if (!is_file($filename) && !isset($pointers['country_g'][$country_id]))
		{
			if ($fp = fopen($filename, 'wb'))
			{
				fputcsv($fp, $keys['g']);
			}

			$pointers['country_g'][$country_id] = $fp;
			unset($fp);
		}
	}

	/**
	 * Write the output to export files
	 *
	 * @param   stdClass  $item
	 * @param   array     $keys
	 * @param   array     $pointers
	 *
	 * @throws  Exception
	 * @return  string
	 *
	 * @since   1.4.0
	 */
	protected function _write_files($item, $keys, &$pointers)
	{
		$keysS = array_flip($keys['s']);
		$keysG = array_flip($keys['g']);
		$vars  = get_object_vars($item);
		$state = '';

		// Export file pointers for sellacious application
		$data  = array_intersect_key($vars, $keysS);

		// world
		$p     = $pointers['world_s'][0];
		$state = $p ? $state . (false !== fputcsv($p, $data) ? '1' : '0') : $state . 'X';
		unset($p);

		// continent
		$p     = $pointers['continent_s'][$item->continent_id];
		$state = $p ? $state . (false !== fputcsv($p, $data) ? '1' : '0') : $state . 'X';
		unset($p);

		// country
		$p     = $pointers['country_s'][$item->country_id];
		$state = $p ? $state . (false !== fputcsv($p, $data) ? '1' : '0') : $state . 'X';
		unset($p);

		// Export file pointers for public distribution
		$data  = array_intersect_key($vars, $keysG);

		// $continent_id = $item->type == 'continent' ? $item->id : $item->continent_id;
		// $country_id   = $item->type == 'country' ? $item->id : $item->country_id;
		$continent_id = $item->continent_id;
		$country_id   = $item->country_id;

		// world
		$p     = $pointers['world_g'][0];
		$state = $p ? $state . (false !== fputcsv($p, $data) ? '1' : '0') : $state . 'X';
		unset($p);

		// continent
		$p     = $pointers['continent_g'][$continent_id];
		$state = $p ? $state . (false !== fputcsv($p, $data) ? '1' : '0') : $state . 'X';
		unset($p);

		// country
		$p     = $pointers['country_g'][$country_id];
		$state = $p ? $state . (false !== fputcsv($p, $data) ? '1' : '0') : $state . 'X';
		unset($p);

		return $state;
	}

	/**
	 * Close the opened files for export
	 *
	 * @param   array  $pointers
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	protected function _close_files(&$pointers)
	{
		foreach ($pointers as $pointer)
		{
			foreach ($pointer as $resource)
			{
				if ($resource)
				{
					fclose($resource);
				}
			}
		}
	}

	/**
	 * Analyze the import csv file for the conflicts in database records
	 *
	 * @param   string  $filename  File name from which to import
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function analyze($filename)
	{
		ignore_user_abort(true);

		$fp   = fopen($filename, 'r');

		// First row contains column header
		$keys  = fgetcsv($fp);
		$table = $this->getTable();

		if (count($keys) < 4)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_INVALID_FILE_STRUCTURE'));
		}

		foreach ($keys as $key)
		{
			if (!property_exists($table, $key))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_INVALID_FILE_STRUCTURE'));
			}
		}

		// Get countries id for existing records so that we prevent duplicate entries
		$filter    = array(
			'list.select' => 'a.country_id, COUNT(1) AS count',
			'list.where'  => 'a.country_id > 0',
			'list.group'  => 'a.country_id',
		);
		$countries = $this->loadObjectList($filter);

		if (!is_array($countries))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_LOCATIONS_IMPORT_ANALYZE_EXISTING_ERROR'));
		}

		$countries = $this->helper->core->arrayAssoc($countries, 'country_id', 'count');
		$imports   = array();

		while($row = fgetcsv($fp))
		{
			set_time_limit(30);

			$record = array_combine($keys, $row);

			// A native will be skipped when already exist otherwise it will be imported.
			if ($record['type'] == '' || $record['type'] == 'continent')
			{
				continue;
			}

			$country_id = $record['type'] == 'country' ? (int) $record['id'] : (int) $record['country_id'];
			$import     = ArrayHelper::getValue($imports, $country_id);

			if (!$import)
			{
				$c = $this->loadObject(array('list.select' => 'a.title, a.iso_code', 'id' => $country_id));

				$import->id       = $country_id;
				$import->title    = isset($c->title) ? $c->title : '';
				$import->iso_code = isset($c->iso_code) ? $c->iso_code : '';
				$import->current  = ArrayHelper::getValue($countries, $country_id, 0);
				$import->import   = 0;
				$import->self     = false;

				$imports[$country_id] = $import;
			}

			if ($record['type'] == 'country')
			{
				$import->self = true;

				if ($import->title == '')
				{
					$import->title = $record['title'];
				}

				if ($import->iso_code == '')
				{
					$import->iso_code = $record['iso_code'];
				}
			}
			else
			{
				$import->import += 1;
			}
		}

		return $imports;
	}

	/**
	 * Get the record id for the given location based on its title, type and parent
	 *
	 * @param   string  $title    The location title
	 * @param   string  $type     The location type: country, state etc.
	 * @param   int     parentId  The parent to limit the lookup, if omitted any first match will be returned.
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function getIdByName($title, $type, $parentId = null)
	{
		$filters = array(
			'list.select' => 'a.id',
			'title'       => $title,
			'type'        => $type,
		);

		if ($parentId)
		{
			$filters['parent_id'] = $parentId;
		}

		return $this->loadResult($filters);
	}

	/**
	 * Get the record id for the given location based on its iso code, type and parent
	 *
	 * @param   string  $code     The location title
	 * @param   string  $type     The location type: country, state etc.
	 * @param   int     parentId  The parent to limit the lookup, if omitted any first match will be returned.
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function getIdByISO($code, $type, $parentId = null)
	{
		if ($type != 'country')
		{
			$isoCol = 'iso_code';
		}
		elseif (is_numeric($code))
		{
			$isoCol = 'iso_numeric';
		}
		elseif (strlen($code) == 2)
		{
			$isoCol = 'iso_alpha_2';
		}
		elseif (strlen($code) == 3)
		{
			$isoCol = 'iso_alpha_3';
		}
		else
		{
			return 0;
		}

		$filters = array(
			'list.select' => 'a.id',
			'type'        => $type,
			$isoCol       => $code,
		);

		if ($parentId)
		{
			$filters['parent_id'] = $parentId;
		}

		$id = $this->loadResult($filters);

		if (!$id && ($isoCol == 'iso_alpha_2' || $isoCol == 'iso_alpha_3'))
		{
			$filters = array(
				'list.select' => 'a.id',
				'type'        => $type,
				'iso_code'    => $code,
			);

			if ($parentId)
			{
				$filters['parent_id'] = $parentId;
			}

			$id = $this->loadResult($filters);
		}

		return $id;
	}

	/**
	 * Get the title for the given record id from location table
	 *
	 * @param   int  $id  The location id
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function getTitle($id)
	{
		return $this->loadResult(array('list.select' => 'a.title', 'id' => $id));
	}

	/**
	 * Load IP information from geo-ip for the given IP
	 *
	 * @param   string  $ip   IP address to lookup
	 * @param   string  $key  The value to retrieve
	 *
	 * @return  string|string[]  The IP information as obtained, if key is omitted entire data is returned
	 *
	 * @since   1.6.0
	 */
	public function ipInfo($ip = null, $key = null)
	{
		if ($ip === null)
		{
			$ip = $this->getClientIP();
		}

		/**
		 * [geoplugin_request] => 8.8.8.8
		 * [geoplugin_status] => 206
		 * [geoplugin_delay] => 2ms
		 * [geoplugin_city] =>
		 * [geoplugin_region] =>
		 * [geoplugin_regionCode] =>
		 * [geoplugin_regionName] =>
		 * [geoplugin_areaCode] =>
		 * [geoplugin_dmaCode] =>
		 * [geoplugin_countryCode] => US
		 * [geoplugin_countryName] => United States
		 * [geoplugin_inEU] => 0
		 * [geoplugin_euVATrate] =>
		 * [geoplugin_continentCode] => NA
		 * [geoplugin_continentName] => North America
		 * [geoplugin_latitude] => 37.751
		 * [geoplugin_longitude] => -97.822
		 * [geoplugin_locationAccuracyRadius] => 1000
		 * [geoplugin_timezone] =>
		 * [geoplugin_currencyCode] => USD
		 * [geoplugin_currencySymbol] => &#36;
		 * [geoplugin_currencySymbol_UTF8] => $
		 * [geoplugin_currencyConverter] => 1
		 */
		try
		{
			$app    = JFactory::getApplication();
			$stored = $app->getUserState('com_sellacious.geoplugin.data', array());

			if (isset($stored[$ip]))
			{
				$data = (array) $stored[$ip];
			}
			else
			{
				$geoUrl = 'http://www.geoplugin.net/php.gp?ip=' . $ip;
				$http   = JHttpFactory::getHttp();
				$info   = $http->get($geoUrl);
				$info   = @unserialize($info->body);

				if (is_array($info) && isset($info['geoplugin_status']))
				{
					$registry = new Registry;
					$registry->loadArray($info, true, '_');
					$data     = (array) $registry->get('geoplugin') ?: array();

					$stored[$ip] = $data;

					$app->setUserState('com_sellacious.geoplugin.data', $stored);
				}
				else
				{
					return null;
				}
			}

			return $key ? ArrayHelper::getValue($data, $key) : $data;
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	/**
	 * Method to save geolocation details
	 *
	 * @param   string  $context   The calling context
	 * @param   int     $recordId  The reference id with respect to the context
	 * @param   array   $data      The data to save
	 *
	 * @return  bool
	 *
	 * @since   1.6.1
	 */
	public function saveGeoLocation($context, $recordId, $data)
	{
		$table = SellaciousTable::getInstance('Geolocation');
		$table->load(array(
			'context'   => $context,
			'record_id' => $recordId,
		));

		$data['context']   = $context;
		$data['record_id'] = $recordId;

		$table->bind($data);
		$table->store();
		$table->check();

		return true;
	}

	/**
	 * Method to get Geolocation details
	 *
	 * @param   string  $context  The calling context
	 * @param   int     $recordId The record id
	 *
	 * @return  array
	 *
	 * @since   1.6.1
	 */
	public function getGeoLocation($context, $recordId)
	{
		$table = SellaciousTable::getInstance('Geolocation');
		$table->load(array(
			'context'   => $context,
			'record_id' => $recordId,
		));

		return $table->getProperties(1);
	}
}

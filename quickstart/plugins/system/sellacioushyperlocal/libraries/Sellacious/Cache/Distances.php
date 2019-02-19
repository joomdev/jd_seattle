<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Cache;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Cache;
use Sellacious\Config\ConfigHelper;

/**
 * Sellacious Distances Cache Object.
 *
 * @since  1.6.0
 */
class Distances extends Cache
{
	/**
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $cacheTable = '#__sellacious_cache_distances';

	/**
	 * @var    array
	 *
	 * @since  1.6.0
	 */
	protected $shippable_coordinates = array();

	/**
	 * Build the cache.
	 *
	 * @return \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function build()
	{
		$cache = $this->distancesCache();

		return $cache;
	}

	/**
	 * Method to purge all cache
	 *
	 * @since   1.6.0
	 */
	public function purgeCache()
	{
		$this->db->truncateTable($this->cacheTable);
	}

	/**
	 * Method to remove all cache for the current user
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function deleteCache()
	{
		$hash = $this->getHashCode();

		$query = $this->db->getQuery(true);
		$query->delete($this->cacheTable);
		$query->where('hash = ' . $this->db->quote($hash));

		$this->db->setQuery($query);

		$this->db->execute();
	}

	/**
	 * Build the cache for distances
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	protected function distancesCache()
	{
		$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$hlParams = $hlConfig->getParams();

		if (empty($hlParams->get('hyperlocal_type')))
		{
			return;
		}

		$hash = $this->getHashCode();
		$key  = $hlParams->get('google_api_key', '');

		$filters = array();
		$filters['list.select'] = 'a.user_id, a.store_location, sh.shipping_distance';
		$filters['list.join'][] = array('inner', '#__sellacious_seller_hyperlocal AS sh ON sh.seller_uid = a.user_id');
		$filters['list.where'][] = 'a.state = 1';
		$sellers = $this->helper->seller->loadObjectList($filters);

		$records = array();

		foreach ($sellers as $seller)
		{
			$storeLocation = explode(',', $seller->store_location);

			if (count($storeLocation) < 2)
			{
				continue;
			}

			// Calculate distance between Store location and shippable location filter
			$url = "https://maps.googleapis.com/maps/api/distancematrix/json?key=".$key."&origins=".$this->shippable_coordinates['lat'].",".$this->shippable_coordinates['long']."&destinations=".trim($storeLocation[0]).",".trim($storeLocation[1])."&mode=driving&language=pl-PL";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_a = json_decode($response, true);

			if (isset($response_a['rows'][0]['elements'][0]['distance']))
			{
				$distance = $response_a['rows'][0]['elements'][0]['distance']['value'];  // in meters

				// Store distance
				$record                           = new \stdClass();
				$record->user_id                  = \JFactory::getUser()->id;
				$record->seller_uid               = $seller->user_id;
				$record->hash                     = $hash;
				$record->seller_shipping_distance = $seller->shipping_distance;
				$record->shipping_filter_lat      = $this->shippable_coordinates['lat'];
				$record->shipping_filter_long     = $this->shippable_coordinates['long'];
				$record->store_location_lat       = $storeLocation[0];
				$record->store_location_long      = $storeLocation[1];
				$record->distance                 = $distance;
				$record->cache_created            = \JFactory::getDate()->toSql();

				$this->db->insertObject($this->cacheTable, $record);

				$records[] = $record;
			}
		}

		return $records;
	}

	/**
	 * Method to get distances from cache
	 *
	 * @param   string  $hash  The hash code for cache entry
	 *
	 * @return  bool|mixed
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function getDistances($hash)
	{
		if (!$hash || !$this->checkCacheExpiry())
		{
			return false;
		}

		$user  = \JFactory::getUser();
		$query = $this->db->getQuery(true);

		$query->select('a.seller_uid, a.seller_shipping_distance, a.distance');
		$query->from($this->db->qn($this->cacheTable, 'a'));
		$query->where('a.hash = ' . $this->db->quote($hash));

		if ($user->id)
		{
			$query->where('a.user_id = ' . $user->id);
		}

		$this->db->setQuery($query);

		$results = $this->db->loadObjectList();

		if (empty($results))
		{
			$results = $this->build();
		}

		return $results;
	}

	/**
	 * Method to check Cache Expiry
	 *
	 * @return   bool
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	protected function checkCacheExpiry()
	{
		$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$hlParams = $hlConfig->getParams();

		if (empty($hlParams->get('hyperlocal_type')))
		{
			return false;
		}

		$expiry = $hlParams->get('cache_expiry', 360); // In minutes
		$date   = \JFactory::getDate();

		$query = $this->db->getQuery(true);
		$query->select('id')->from($this->cacheTable);
		$query->where('ROUND(TIME_TO_SEC(TIMEDIFF(' . $this->db->quote($date->toSql()) . ', cache_created))/60) > ' . $expiry);

		$this->db->setQuery($query);

		$result = $this->db->loadResult();

		if (!empty($result))
		{
			$this->purgeCache();
			$this->build();
		}

		return true;
	}

	/**
	 * Method to set shippable coordinates
	 *
	 * @param   array  $shippable_coordinates  Shippable coordinates
	 *
	 * @since   1.6.0
	 */
	public function setShippableCoordinates($shippable_coordinates)
	{
		$this->shippable_coordinates = $shippable_coordinates;
	}

	/**
	 * Get a unique hash code
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function getHashCode()
	{
		$session = \JFactory::getSession();
		$hash    = $session->get('distance_hash', '', 'hyperlocal');

		if (!$hash)
		{
			$registry = new Registry;
			$registry->set('user_id', \JFactory::getUser()->id);
			$registry->set('shippable_coordinates', $this->shippable_coordinates);

			// Generate hash code for cache and set it to session
			$hash = sha1($registry);

			$session->set('distance_hash', $hash, 'hyperlocal');
		}

		return $hash;
	}
}

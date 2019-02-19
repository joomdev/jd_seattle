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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious seller helper
 *
 * @since   1.0.0
 */
class SellaciousHelperSeller extends SellaciousHelperBase
{
	/**
	 * Generate SQL query from the given filters and other clauses
	 *
	 * @param   array  $filters
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	public function getListQuery($filters)
	{
		$db    = $this->db;
		$query = parent::getListQuery($filters);

		if (!isset($filters['list.from']))
		{
			$query->join('INNER', $db->qn('#__users', 'u') . ' ON a.user_id = u.id');
		}

		return $query;
	}

	/**
	 * Get the seller category for the given user
	 *
	 * @param   int   $userId
	 * @param   bool  $useDefault
	 * @param   bool  $full
	 *
	 * @return  int|stdClass
	 *
	 * @since   1.5.1
	 */
	public function getCategory($userId, $useDefault = false, $full = false)
	{
		$filter   = array(
			'list.select' => 'c.*',
			'list.join'   => array(array('inner', '#__sellacious_categories AS c ON c.id = a.category_id')),
			'user_id'     => $userId,
		);
		$category = $this->loadObject($filter);

		if (!$category && $useDefault)
		{
			$category = $this->helper->category->getDefault('seller');
		}

		return $category ? ($full ? $category : $category->id) : null;
	}

	/**
	 * Check whether the given user is an (active, optionally) seller or not
	 *
	 * @param   int   $user_id  User Id to query, default current user
	 * @param   bool  $active   Check enabled state
	 *
	 * @return  int
	 *
	 * @since   1.0.0
	 */
	public function is($user_id = null, $active = true)
	{
		$me     = JFactory::getUser($user_id);
		$filter = array('list.select' => 'a.category_id', 'user_id' => $me->id);

		if ($active)
		{
			$filter['state'] = 1;
		}

		return $this->loadResult($filter);
	}

	/**
	 * Set shippable locations for the selected seller
	 *
	 * @param   int    $seller_uid  Seller user id
	 * @param   int[]  $pks         Geo-location ids
	 *
	 * @since   1.0.0
	 */
	public function setShipLocations($seller_uid, $pks)
	{
		if ($seller_uid)
		{
			// If none selected, set to all
			if (count($pks) == 0)
			{
				// Calling method should handle this instead?
				$pks = array(0);
			}

			$current = $this->getShipLocations($seller_uid);

			$new    = array_filter(array_diff($pks, $current));
			$remove = array_filter(array_diff($current, $pks));

			if (count($remove))
			{
				$query = $this->db->getQuery(true);
				$query->delete('#__sellacious_seller_shippable')
					->where('seller_uid = ' . (int) $seller_uid)
					->where('gl_id IN (' . implode(',', $remove) . ')');
				$this->db->setQuery($query)->execute();
			}

			if (count($new))
			{
				$query = $this->db->getQuery(true);
				$query->insert('#__sellacious_seller_shippable')
					->columns(array('seller_uid', 'gl_id', 'state'));

				foreach ($new as $i)
				{
					$query->clear('values')->values(sprintf('%d, %d, %d', $seller_uid, $i, 1));
					$this->db->setQuery($query)->execute();
				}
			}
		}
	}

	/**
	 * Return the list of shippable geo locations for the selected seller
	 *
	 * @param   int   $seller_uid  Seller user id
	 * @param   bool  $grouped     Whether to group by geo location type
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getShipLocations($seller_uid, $grouped = false)
	{
		$query = $this->db->getQuery(true);

		$query->select('a.gl_id')
			->from('#__sellacious_seller_shippable AS a')
			->where('a.seller_uid = ' . (int) $seller_uid)
			->where('a.gl_id > 0');

		if ($grouped)
		{
			$query->select('g.type')->join('left', '#__sellacious_locations AS g ON g.id = a.gl_id');

			$ids  = array();
			$rows = $this->db->setQuery($query)->loadObjectList();

			foreach ($rows as $row)
			{
				$ids[$row->type][] = $row->gl_id;
			}
		}
		else
		{
			$ids = $this->db->setQuery($query)->loadColumn();
		}

		return $ids;
	}

	/**
	 * Check an address whether it is allowed for the shipping by the selected seller
	 *
	 * @param   int|object  $address     The address object or address id
	 * @param   int         $seller_uid  The type/context of address. Acceptable values are: BT, ST
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function isAddressShippable($address, $seller_uid)
	{
		// Should we use seller~address caching here? Not doing to prevent any undesirable effect until sure.
		if (is_numeric($address))
		{
			$address = $this->helper->user->getAddressById($address);
		}

		if (empty($address->id))
		{
			return false;
		}

		$allowed = $this->getShipLocations($seller_uid, true);

		if (empty($allowed))
		{
			return true;
		}

		if (is_bool($allow = $this->helper->location->isAllowed($address->country, $allowed)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->helper->location->isAllowed($address->state_loc, $allowed)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->helper->location->isAllowed($address->district, $allowed)))
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
	 * Add seller commissions for the given seller for each product category maps
	 *
	 * @param   int    $sellerUid         Seller user id
	 * @param   array  $sellerCommission  Array [product_category_id => commission]
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function setCommissions($sellerUid, $sellerCommission)
	{
		$commissions = $this->getCommissions($sellerUid);

		foreach ($sellerCommission as $productCatid => $commission)
		{
			$old = ArrayHelper::getValue($commissions, $productCatid);

			$this->setCommission($productCatid, $sellerUid, $commission, $old);
		}

		return true;
	}

	/**
	 * Set the commission value for the given product category and seller category map
	 *
	 * @param   int     $productCatid  The product category id that would be affected
	 * @param   int     $sellerUid     The seller category id that would be affected
	 * @param   string  $commission    The new commission rate (float value or a string containing a float suffixed by % sign)
	 * @param   string  $old           The old commission rate (float value or a string containing a float suffixed by % sign)
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function setCommission($productCatid, $sellerUid, $commission, $old)
	{
		$query = $this->db->getQuery(true);
		$zero  = trim($commission, '% ') == 0;

		// Insert if has value and not already exists
		if (!isset($old))
		{
			if (!$zero)
			{
				$query->insert('#__sellacious_seller_commissions')
					->columns('seller_uid, product_catid, commission')
					->values(implode(', ', $this->db->q(array($sellerUid, $productCatid, $commission))));

				$this->db->setQuery($query)->execute();
			}
		}
		else
		{
			// Delete if ZERO, and already exists
			if ($zero)
			{
				$query->delete('#__sellacious_seller_commissions')
					->where('seller_uid = ' . $this->db->q($sellerUid))
					->where('product_catid = ' . $this->db->q($productCatid));

				$this->db->setQuery($query)->execute();
			}
			// Update only if modified
			elseif ($commission != $old)
			{
				$query->update('#__sellacious_seller_commissions')
					->set('commission = ' . $this->db->q($commission))
					->where('seller_uid = ' . $this->db->q($sellerUid))
					->where('product_catid = ' . $this->db->q($productCatid));

				$this->db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Fetch seller commissions for the given seller for each product category maps
	 *
	 * @param   int  $sellerUid  Seller user id
	 *
	 * @return  array  Commissions for each product category
	 *
	 * @since   1.5.0
	 */
	public function getCommissions($sellerUid)
	{
		$query = $this->db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__sellacious_seller_commissions')
			->where('seller_uid = ' . $this->db->q($sellerUid));

		$items  = $this->db->setQuery($query)->loadObjectList();
		$result = ArrayHelper::getColumn((array) $items, 'commission', 'product_catid');

		return $result;
	}

	/**
	 * Fetch seller commission for the given product category
	 *
	 * @param   int   $sellerUid     Seller category id
	 * @param   int   $productCatid  Array [product_category_id => commission]
	 * @param   bool  $inherit       Whether to inherit value from parent category
	 *
	 * @return  mixed  The commission amount/rate
	 *
	 * @since   1.5.0
	 */
	public function getCommission($sellerUid, $productCatid, $inherit = false)
	{
		$query = $this->db->getQuery(true);

		$query->select('product_catid, commission')
			->from('#__sellacious_seller_commissions')
			->where('seller_uid = ' . $this->db->q($sellerUid))
			->where('product_catid = ' . $this->db->q($productCatid));

		$result = $this->db->setQuery($query)->loadResult();

		if (!$result && $inherit)
		{
			$filter   = array('list.select' => 'a.parent_id', 'id' => $productCatid);
			$parentId = $this->helper->category->loadResult($filter);
			$result   = $this->getCommission($sellerUid, $parentId, $inherit);
		}

		return $result;
	}

	/**
	 * Get Products count of a particular Seller
	 *
	 * @param    $seller_uid  Seller user id
	 *
	 * @return   int|mixed
	 *
	 * @since    1.6.0
	 */
	public function getSellerProductCount($seller_uid)
	{
		$db     = JFactory::getDbo();
		$result = 0;

		$query = $db->getQuery(true);
		$query->select('COUNT(a.product_id)')
			->from('#__sellacious_cache_products as a')
			->where('a.seller_uid = ' . (int) $seller_uid)
			->where('a.product_active = 1');

		if ((int) $this->helper->config->get('multi_variant') < 2)
		{
			$query->where('a.variant_id = 0');
		}

		$result = $db->setQuery($query)->loadResult();

		return $result;
	}

	/**
	 * Method to get seller stores for a moduel
	 *
	 * @param  Joomla\Registry\Registry  $params  The module parameters
	 * @param  string                    $type    The module type
	 *
	 * @return  \stdClass[]
	 *
	 * @since  1.6.0
	 */
	public function getModStores($params, $type = 'stores')
	{
		$default_seller = $this->helper->config->get('default_seller', -1);
		$multi_seller   = $this->helper->config->get('multi_seller', 0);

		$limit       = $params->get('total_records', '50');
		$category_id = $params->get('category_id', '0');
		$ordering    = $params->get('ordering', '3');
		$orderBy     = $params->get('orderby', 'DESC');
		$filters     = array();

		$filters['list.select'][] = 'a.*, u.name, u.username, u.email';

		switch ($ordering)
		{
			case "1":
				$ord = 'a.title ' . $orderBy;
				break;
			case "2":
				$ord = 'rand() ';
				break;
			default:
				$ord = 'rand() ';
		}

		$filters['list.where'][] = 'a.state = 1';
		$filters['list.where'][] = 'u.block = 0';

		if ($category_id)
		{
			$filters['list.where'][] = 'a.category_id = ' . (int) $category_id;
		}

		if (!$multi_seller)
		{
			$filters['list.where'][] = 'a.user_id = ' . $default_seller;
		}

		$filters['list.order'][] = $ord;
		$filters['list.start']   = 0;
		$filters['list.limit']   = $limit;

		$stores = $this->helper->seller->loadObjectList($filters);

		return $stores;
	}
}

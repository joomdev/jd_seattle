<?php
/**
 * @version     1.6.1
 * @package     Sellacious Users Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package  Sellacious
 *
 * @since   1.6.0
 */
class ModSellaciousUsersHelper
{

	/**
	 * Get All Users List
	 *
	 * @param   \Joomla\Registry\Registry  $params  module parameters
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	public static function getUsers(&$params)
	{
		$db       = JFactory::getDbo();
		$helper   = SellaciousHelper::getInstance();
		$query    = $db->getQuery(true);

		// Module params
		$limit        = (int) $params->get('count', '10');
		$profile_type = $params->get('profile_type', '');
		$ordering     = $params->get('ordering', '');
		$category     = (int) $params->get('category', '');

		$query = $db->getQuery(true);
		$categoryClient   = $helper->category->getDefault('client', 'a.id');
		$categoryClientId = $categoryClient ? (int) $categoryClient->id : 0;

		$ccid = 'CASE COALESCE(client.category_id, 0) WHEN 0 THEN ' . $categoryClientId . ' ELSE client.category_id END';
		$query->select('u.id, u.name, u.username, u.email, u.block as state, u.activation')
			->from('#__users u')
			->group('u.id')

			->select('a.id as profile_id, a.mobile, a.website, a.currency, a.ordering')
			->select('a.bankinfo, a.taxinfo, a.state as profile_state, a.ordering')
			->join('LEFT', '#__sellacious_profiles a ON a.user_id = u.id')

			->select($ccid . ' AS client_category_id')
			->select('cc.title client_category_name')
			->join('LEFT', '#__sellacious_clients client ON client.user_id = u.id')
			->join('LEFT', '#__sellacious_categories cc ON cc.id = ' . $ccid)

			->select('mfr.category_id AS mfr_category_id, mfr.title AS mfr_company')
			->select('mc.title mfr_category_name')
			->join('LEFT', '#__sellacious_manufacturers mfr ON mfr.user_id = u.id')
			->join('LEFT', '#__sellacious_categories mc ON mc.id = mfr.category_id')

			->select('staff.category_id AS staff_category_id')
			->select('sc.title staff_category_name')
			->join('LEFT', '#__sellacious_staffs staff ON staff.user_id = u.id')
			->join('LEFT', '#__sellacious_categories sc ON sc.id = staff.category_id')

			->select('seller.category_id AS seller_category_id, seller.title AS seller_company, seller.store_name AS seller_store')
			->select('vc.title seller_category_name')
			->join('LEFT', '#__sellacious_sellers seller ON seller.user_id = u.id')
			->join('LEFT', '#__sellacious_categories vc ON vc.id = seller.category_id');

		$query->select('COUNT(DISTINCT oi.order_id) as order_count, SUM(oi.sub_total) as order_amount, SUM(oi.quantity) as order_product_quantity')
			->join('LEFT', '#__sellacious_order_items oi ON oi.seller_uid = u.id');

		// Filter by published state
		$state = 1;

		if (is_numeric($state))
		{
			$query->where('u.block = ' . (int) ($state == 0));
		}

		// Filter by profile_type
		if (!empty($profile_type) && in_array($profile_type, explode(',', 'client,seller,staff,mfr')))
		{
			$query->where($db->qn($profile_type . '.category_id') . ' != ' . $db->q(''))->where($profile_type . '.state = 1');
		}

		// Filter by category(ies)
		if ($category)
		{
			$cond = array(
				'client.category_id = ' . $category,
				'seller.category_id = ' . $category,
				'staff.category_id = ' . $category,
				'mfr.category_id = ' . $category,
			);

			$query->where('(' . implode(' OR ', $cond) . ')');
		}

		if ($ordering === 'oc')
		{
			$query->order($db->quoteName('order_count') . ' DESC');
		}
		elseif ($ordering === 'oa')
		{
			$query->order($db->quoteName('order_amount') . ' DESC');
		}
		else
		{
			$ordering = $params->get('ordering', 'a.ordering ASC');

			if (trim($ordering))
			{
				$query->order($db->escape($ordering));
			}
		}

		$db->setQuery($query, 0, $limit);

		try
		{
			$items = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$items = array();
		}

		return $items;
	}

	/**
	 * Get Avatar
	 *
	 * @param   $user_id    int
	 * @param   $type       string
	 * @param   $avatar     string (Image from Profile OR Logo)
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public static function getAvatar($user_id, $type, $avatar)
	{
		$image  = '';
		$helper = SellaciousHelper::getInstance();

		if ($avatar == 'avatar')
		{
			$image = $helper->media->getImage('user.avatar', $user_id, true);
		}
		else
		{
			$record_id = $helper->$type->loadResult(array('list.select' => 'a.id', 'user_id' => $user_id));

			if ($record_id)
			{
				$tableField = $type . 's.logo';
				$image      = $helper->media->getImage($tableField, $record_id, true);
			}
		}

		return $image;
	}
}

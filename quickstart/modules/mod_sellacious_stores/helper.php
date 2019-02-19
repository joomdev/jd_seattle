<?php
/**
 * @version     1.6.1
 * @package     Sellacious Seller Stores Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class ModSellaciousStores
{
	/**
	 * Get Products count of a particular Seller
	 *
	 * @param $seller_uid
	 *
	 * @return int|mixed
	 *
	 * @since 1.5.3
	 */

	public static function getSellerProductCount($seller_uid)
	{
		$helper = SellaciousHelper::getInstance();
		$db     = JFactory::getDbo();
		$result = 0;

		$query = $db->getQuery(true);
		$query->select('COUNT(a.product_id)')
			->from('#__sellacious_cache_products as a')
			->where('a.seller_uid = ' . (int) $seller_uid)
			->where('a.product_active = 1');

		if ((int) $helper->config->get('multi_variant') < 2)
		{
			$query->where('a.variant_id = 0');
		}

		$result = $db->setQuery($query)->loadResult();

		return $result;
	}
}

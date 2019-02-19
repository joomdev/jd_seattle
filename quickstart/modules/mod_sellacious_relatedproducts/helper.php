<?php
/**
 * @version     1.6.1
 * @package     Sellacious Related Products Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class ModSellaciousRelatedProducts
{
	public static function getSellerInfo($seller_uid)
	{
		$db     = JFactory::getDbo();
		$result = new stdClass;
		$select = $db->getQuery(true);
		$select->select('mobile')->from('#__sellacious_profiles')->where('user_id = ' . (int) $seller_uid);
		$mobile = $db->setQuery($select)->loadResult();

		if (!empty($mobile))
		{
			$result->seller_mobile = $mobile;
		}
		else
		{
			$result->seller_mobile = '(' . JText::_('MOD_SELLACIOUS_RELATEDPRODUCTS_NO_NUMBER_GIVEN') . ')';
		}

		$seller_email = JFactory::getUser($seller_uid)->email;

		if (!empty($seller_email))
		{
			$result->seller_email = $seller_email;
		}
		else
		{
			$result->seller_email = '(' . JText::_('MOD_SELLACIOUS_RELATEDPRODUCTS_NO_EMAIL_GIVEN') . ')';
		}

		return $result;
	}

	public static function getCheapestSeller($sellers, $pid)
	{
		$db        = JFactory::getDbo();
		$sellerIds = array();

		foreach ($sellers as $seller)
		{
			array_push($sellerIds, $seller->seller_uid);
		}

		if (empty($sellerIds))
		{
			return false;
		}

		$select = $db->getQuery(true);
		$select->select('*')
			->from('#__sellacious_product_prices')
			->where('product_id = ' . (int) $pid)
			->where('is_fallback = 1')
			->where('seller_uid IN (' . implode(",", $sellerIds) . ')')
			->order('product_price ASC');
		$result = $db->setQuery($select)->loadObject();

		return $result;
	}

	public static function getFilteredSelectedProducts($pIds)
	{
		$db     = JFactory::getDbo();
		$helper = SellaciousHelper::getInstance();

		$filters['list.join'][] = array('inner', '#__sellacious_product_sellers AS ps ON ps.product_id = a.id');
		$filters['list.join'][] = array('inner', '#__sellacious_product_prices AS p ON p.product_id = a.id and p.seller_uid = ps.seller_uid');

		$filters['list.select'][] = 'DISTINCT a.id';

		$filters['list.where'][] = 'a.id IN (' . implode(",", $pIds) . ')';

		if ($helper->config->get('multi_seller') < 2)
		{
			$filters['list.group'] = 'a.id';
		}

		if ($helper->config->get('hide_zero_priced'))
		{
			$filters['list.where'][] = '(p.product_price > 0 OR ps.price_display > 0)';
		}

		if ($helper->config->get('hide_out_of_stock'))
		{
			$filters['list.where'][] = 'ps.stock + ps.over_stock > 0';
		}

		$allowed = $helper->config->get('allowed_product_type') == 'both' ? array(
			'physical',
			'electronic',
		) : array($helper->config->get('allowed_product_type'));

		if ($helper->config->get('allowed_product_package'))
		{
			$allowed[] = 'package';
		}

		$filters['list.where'][] = '(a.type = ' . implode(' OR a.type = ', $db->quote($allowed)) . ')';

		$filters['list.where'][] = 'a.state = 1';

		$products = $helper->product->loadColumn($filters);

		return $products;
	}
}

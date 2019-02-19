<?php
/**
 * @version     1.6.1
 * @package     Sellacious Special Category Products Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class ModSellaciousSpecialProducts
{
	public static function getSellerInfo($seller_uid)
	{
		$db = JFactory::getDbo();
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
			$result->seller_mobile = '(' . JText::_('MOD_SELLACIOUS_SPECIALCATSPRODUCTS_NO_NUMBER_GIVEN') . ')';
		}
		$seller_email = JFactory::getUser($seller_uid)->email;
		if (!empty($seller_email))
		{
			$result->seller_email = $seller_email;
		}
		else
		{
			$result->seller_email = '(' . JText::_('MOD_SELLACIOUS_SPECIALCATSPRODUCTS_NO_EMAIL_GIVEN') . ')';
		}

		return $result;
	}
}

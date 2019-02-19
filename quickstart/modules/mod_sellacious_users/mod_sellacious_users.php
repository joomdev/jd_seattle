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

jimport('sellacious.loader');

if (class_exists('SellaciousHelper'))
{
	JLoader::register('ModSellaciousUsersHelper', __DIR__ . '/helper.php');

	/** @var  Joomla\Registry\Registry $params */
	$helper        = SellaciousHelper::getInstance();
	$class_sfx     = $params->get('class_sfx', '');
	$limit         = $params->get('count', '10');
	$avatar        = $params->get('avatar', 'avatar');
	$show_avatar   = $params->get('show_avatar', '1');
	$show_name     = $params->get('show_name', '1');
	$show_username = $params->get('show_username', '1');
	$show_email    = $params->get('show_email', '1');
	$show_mobile   = $params->get('show_mobile', '1');
	$show_company  = $params->get('show_company', '1');
	$show_rating   = $params->get('show_store_rating', '1');
	$show_link     = $params->get('show_link_to_store', '1');
	$show_amount   = $params->get('show_order_amount', '1');
	$show_ord_count= $params->get('show_order_count', '1');
	$ordering      = $params->get('ordering', '');

	$users = ModSellaciousUsersHelper::getUsers($params);

	if (empty($users))
	{
		return;
	}

	require JModuleHelper::getLayoutPath('mod_sellacious_users');

}

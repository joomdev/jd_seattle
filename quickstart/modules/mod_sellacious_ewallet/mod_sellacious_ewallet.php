<?php
/**
 * @version     1.6.1
 * @package     Sellacious E-Wallet Module
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
	/** @var  Joomla\Registry\Registry $params */
	$helper     = SellaciousHelper::getInstance();
	$me         = JFactory::getUser();
	$g_currency = $helper->currency->getGlobal('code_3');
	$u_currency = $helper->currency->forUser($me->id, 'code_3');
	$wallet_bal = $helper->transaction->getBalance($me->id, $g_currency);
	$class_sfx  = $params->get('class_sfx', '');

	$user_currency_preference = $helper->config->get('user_currency');

	require JModuleHelper::getLayoutPath('mod_sellacious_ewallet');

}

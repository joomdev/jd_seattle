<?php
/**
 * @version     1.6.1
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

/** @var  Joomla\Registry\Registry  $params */
$helper    = SellaciousHelper::getInstance();
$allow_checkout = $helper->config->get('allow_checkout');

if (!$allow_checkout)
{
	return;
}

$cart      = $helper->cart->getCart();
$class_sfx = $params->get('class_sfx', '');

require JModuleHelper::getLayoutPath('mod_sellacious_cart');

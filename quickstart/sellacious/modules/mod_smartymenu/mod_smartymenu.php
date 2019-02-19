<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

jimport('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	return;
}

// Include the module helper classes.
if (!class_exists('ModSmartyMenuHelper'))
{
	require __DIR__ . '/helper.php';
}

if (!class_exists('JAdminCssSmartyMenu'))
{
	require __DIR__ . '/smartymenu.php';
}

$doc    = JFactory::getDocument();
$lang   = JFactory::getLanguage();
$user   = JFactory::getUser();
$app    = JFactory::getApplication();
$helper = new ModSmartyMenuHelper;
$menu   = new JAdminCssSmartyMenu;

/** @var  Registry  $params */
$parent_menus = $helper->getMenus($params);

$enabled = $app->input->getBool('hidemainmenu') ? false : true;

$doc->addScript('templates/sellacious/js/jquery.jarvismenu.js');
$doc->addScript('templates/sellacious/js/navmenu.js');

// Render the module layout
require JModuleHelper::getLayoutPath('mod_smartymenu', $params->get('layout', 'default'));

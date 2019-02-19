<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (version_compare(JVERSION, '3.7.0', '<'))
{
	JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_MENUS_SELLACIOUS_SUPPORTED_JOOMLA_VERSION', '3.7', JVERSION), 'info');

	return;
}

jimport('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	throw new RuntimeException(JText::_('COM_MENUS_SELLACIOUS_LIBRARY_NOT_FOUND'));
}

$app = JFactory::getApplication();

$controller = JControllerLegacy::getInstance('Menus');
$controller->execute($app->input->get('task'));
$controller->redirect();

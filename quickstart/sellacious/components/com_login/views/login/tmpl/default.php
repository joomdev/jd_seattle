<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.noframes');

if (JFactory::getApplication()->input->get('e'))
{
	JLog::add(JText::_('COM_LOGIN_LOGIN_DENIED'), JLog::WARNING, 'jerror');
}

// Get the login modules
// If you want to use a completely different login module change the value of name
// in your layout override.

$loginModule = LoginModelLogin::getLoginModule('mod_login');

echo JModuleHelper::renderModule($loginModule, array('style' => 'rounded', 'id' => 'section-box'));

//Get any other modules in the login position.
//If you want to use a different position for the modules, change the name here in your override.
$modules = JModuleHelper::getModules('login');

// Render the login modules
foreach ($modules as $module)
{
	if ($module->module != 'mod_login')
	{
		echo JModuleHelper::renderModule($module, array('style' => 'rounded', 'id' => 'section-box'));
	}
}

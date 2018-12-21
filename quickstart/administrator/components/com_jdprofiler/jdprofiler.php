<?php
/**
 *  
 * @package    Com_Jdprofiler
 * @author      Joomdev
 * @copyright  Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jdprofiler'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Jdprofiler', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::register('JdprofilerHelper', JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jdprofiler.php');

$controller = JControllerLegacy::getInstance('Jdprofiler');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
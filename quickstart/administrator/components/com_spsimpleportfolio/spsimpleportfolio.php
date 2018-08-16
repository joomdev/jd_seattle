<?php
/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

if (!JFactory::getUser()->authorise('core.manage', 'com_spsimpleportfolio')) {
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require helper file
JLoader::register('SpsimpleportfolioHelper', JPATH_COMPONENT . '/helpers/spsimpleportfolio.php');
$controller = JControllerLegacy::getInstance('Spsimpleportfolio');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

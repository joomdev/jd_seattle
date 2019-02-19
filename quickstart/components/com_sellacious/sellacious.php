<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

// Include dependencies
JLoader::import('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	JLog::add(JText::_('COM_SELLACIOUS_LIBRARY_NOT_FOUND'));

	return false;
}

$app        = JFactory::getApplication();
$helper     = SellaciousHelper::getInstance();
$controller	= JControllerLegacy::getInstance('Sellacious');
$task       = $app->input->getCmd('task');

$controller->execute($task);
$controller->redirect();

// Meta Redirect check will occur only if not redirected by the controller above.
$helper->core->metaRedirect();

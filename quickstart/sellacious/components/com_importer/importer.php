<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

// Include dependencies
JLoader::import('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	throw new Exception(JText::_('COM_IMPORTER_SELLACIOUS_LIBRARY_MISSING'));
}

$app        = JFactory::getApplication();
$helper     = SellaciousHelper::getInstance();
$controller = JControllerLegacy::getInstance('Importer');
$task       = $app->input->getCmd('task');

// B/C for < v1.6.0
if (!defined('S_VERSION_CORE'))
{
	define('S_VERSION_CORE', $helper->core->getAppVersion());
}

$controller->execute($task);
$controller->redirect();

// Meta Redirect check will occur only if not redirected by the controller above.
$helper->core->metaRedirect();

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

// Include dependencies
JLoader::import('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	JLog::add('COM_SELLACIOUSOPC_LIBRARY_NOT_FOUND');

	return false;
}

JFactory::getLanguage()->load('com_sellacious', JPATH_SITE . '/components/com_sellacious', 'en-GB', true);

$controller = JControllerLegacy::getInstance('Sellaciousopc');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

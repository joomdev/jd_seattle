<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

// Include dependencies
JLoader::import('sellacious.loader');
JLoader::import('components.com_sellaciousreporting.libraries.loader', JPATH_SITE);

if (!class_exists('SellaciousHelper'))
{
	throw new Exception(JText::_('COM_SELLACIOUSREPORTING_SELLACIOUS_LIBRARY_MISSING'));
}

JTable::addIncludePath(__DIR__ . '/tables');
JForm::addFormPath(__DIR__ . '/models/forms');
JForm::addFieldPath(__DIR__ . '/models/fields');

JLoader::register('ReportingHelper', __DIR__ . '/helpers/reporting.php');

$controller = JControllerLegacy::getInstance('Sellaciousreporting');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

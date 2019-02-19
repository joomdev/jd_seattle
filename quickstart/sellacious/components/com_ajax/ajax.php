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

$app = JFactory::getApplication();

JLoader::register('AjaxHelper', __DIR__ . '/helper.php');

$format = $app->input->getWord('format');

// Default to RAW format if not JSON
if (strtolower($format) != 'json')
{
	$app->input->set('format', null);
}

$controller = JControllerLegacy::getInstance('Ajax');
$controller->execute('onAjax');

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

// Get the breadcrumbs
/** @var  \Joomla\Registry\Registry  $params */
$list  = ModBreadCrumbsHelper::getList($params);
$count = count($list);

// Set the default separator
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$layout = JModuleHelper::getLayoutPath('mod_breadcrumbs', $params->get('layout', 'default'));

require $layout;

<?php
/**
 * @package     SP Simple Portfolio
 * @subpackage  mod_spsimpleportfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

JHtml::_('jquery.framework');
jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_spsimpleportfolio/models');
require_once JPATH_BASE . '/components/com_spsimpleportfolio/helpers/helper.php';

$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::root(true) . '/components/com_spsimpleportfolio/assets/css/featherlight.min.css' );
$doc->addStylesheet( JURI::root(true) . '/components/com_spsimpleportfolio/assets/css/spsimpleportfolio.css' );
$doc->addScript( JURI::root(true) . '/components/com_spsimpleportfolio/assets/js/jquery.shuffle.modernizr.min.js' );
$doc->addScript( JURI::root(true) . '/components/com_spsimpleportfolio/assets/js/featherlight.min.js' );
$doc->addScript( JURI::root(true) . '/components/com_spsimpleportfolio/assets/js/spsimpleportfolio.js' );

$items = ModSpsimpleportfolioHelper::getItems($params);
$model = JModelLegacy::getInstance('Items', 'SpsimpleportfolioModel');
$tagList = $model->getTagList($items);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_spsimpleportfolio', $params->get('layout', 'default'));

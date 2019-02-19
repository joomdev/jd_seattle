<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_toolbar
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

$toolbar = Toolbar::getInstance()->render();

require JModuleHelper::getLayoutPath('mod_toolbar', $params->get('layout', 'default'));

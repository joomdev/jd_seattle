<?php

/**
 * Helper class for Jd Testimonials! module
 * @package     Jd Testimonials
 * @copyright   Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.
 */
// No direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';
$layout = $params->get('select_view', 'default');
require JModuleHelper::getLayoutPath('mod_jdtestimonials', $layout);

<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
if (version_compare(PHP_VERSION, '5.5', '<'))
{
	die('Your host needs to use PHP 5.5 or higher to run Sellacious!');
}

// Saves the start time and memory usage.
$startTime = microtime(1);
$startMem  = memory_get_usage();

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used in the entry folder rather than "const" to not error for PHP 5.2 and lower
 */
define('_JEXEC', 1);

define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_BASE . '/includes/libraries/application.php';
require_once JPATH_BASE . '/includes/libraries/router.php';
require_once JPATH_BASE . '/includes/libraries/menu.php';

// Load this already to avoid loading from Joomla CMS, we want to skip unrecognised plugins
require_once JPATH_BASE . '/includes/libraries/PluginHelper.php';

// Mark afterLoad in the profiler.
JDEBUG ? JProfiler::getInstance('Application')->setStart($startTime, $startMem)->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('sellacious');

// Execute the application.
$app->execute();
